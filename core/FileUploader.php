<?php

declare(strict_types=1);

/**
 * FileUploader — 보안 파일 업로더 + 스마트 자동 리사이징/압축
 *
 * - 확장자 화이트리스트 검증
 * - MIME 타입 실제 검사 (getimagesize)
 * - 비율 유지 자동 리사이징 (스마트 최적화)
 * - 파일명을 UUID로 변환하여 경로 노출 방지
 * - 업로드 디렉토리 자동 생성
 */
final class FileUploader
{
    private string $uploadPath;
    private string $uploadUrl;
    private array  $allowedExts;
    private int    $maxBytes;
    private int    $maxWidth;
    private int    $maxHeight;

    public function __construct(
        string $subdir      = 'books',
        array  $allowedExts = ALLOWED_IMG_EXT,
        int    $maxBytes    = 10 * 1024 * 1024,  // 10 MB
        int    $maxWidth    = 1600,
        int    $maxHeight   = 1600
    ) {
        $this->uploadPath  = UPLOAD_PATH . '/' . $subdir;
        $this->uploadUrl   = UPLOAD_URL  . '/' . $subdir;
        $this->allowedExts = $allowedExts;
        $this->maxBytes    = $maxBytes;
        $this->maxWidth    = $maxWidth;
        $this->maxHeight   = $maxHeight;

        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }

    /**
     * 단일 파일 업로드 및 스마트 리사이징
     *
     * @param  array  $file   $_FILES['field'] 형태
     * @return string         업로드된 파일의 웹 URL 경로
     * @throws RuntimeException
     */
    public function upload(array $file): string
    {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('파일 업로드 오류: ' . ($file['error'] ?? 'unknown'));
        }

        if ($file['size'] > $this->maxBytes) {
            throw new RuntimeException(sprintf(
                '파일 크기가 너무 큽니다 (최대 %dMB).',
                $this->maxBytes / 1024 / 1024
            ));
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $this->allowedExts, true)) {
            throw new RuntimeException('허용되지 않는 파일 형식입니다.');
        }

        // 실제 이미지 크기 검사
        $imgInfo = @getimagesize($file['tmp_name']);
        if ($imgInfo === false) {
            throw new RuntimeException('유효하지 않은 이미지 파일입니다.');
        }

        $newName = $this->generateFilename($ext);
        $dest    = $this->uploadPath . '/' . $newName;

        // GD 라이브러리를 통한 스마트 리사이징 & 저장
        $resized = $this->resizeAndSave($file['tmp_name'], $dest, $imgInfo, $ext);
        if (!$resized) {
            // 리사이징 실패 시 원본 저장
            if (!move_uploaded_file($file['tmp_name'], $dest)) {
                throw new RuntimeException('파일 저장에 실패했습니다.');
            }
        }

        return $this->uploadUrl . '/' . $newName;
    }

    /**
     * 비율 유지 스마트 리사이징
     */
    private function resizeAndSave(string $srcPath, string $destPath, array $info, string $ext): bool
    {
        if (!extension_loaded('gd')) {
            return false;
        }

        $origW = $info[0];
        $origH = $info[1];
        $mime  = $info['mime'];

        // 이미지 객체 로드
        $srcImg = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($srcPath),
            'image/png'  => @imagecreatefrompng($srcPath),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($srcPath) : false,
            'image/gif'  => @imagecreatefromgif($srcPath),
            default      => false
        };

        if (!$srcImg) {
            return false;
        }

        // 축소 비율 계산 (가로/세로 중 초과되는 비율 적용)
        $ratio = 1.0;
        if ($origW > $this->maxWidth || $origH > $this->maxHeight) {
            $ratio = min($this->maxWidth / $origW, $this->maxHeight / $origH);
        }

        $newW = (int)round($origW * $ratio);
        $newH = (int)round($origH * $ratio);

        $dstImg = imagecreatetruecolor($newW, $newH);

        // PNG / WebP 투명도 보존
        if ($mime === 'image/png' || $mime === 'image/webp') {
            imagealphablending($dstImg, false);
            imagesavealpha($dstImg, true);
            $transparent = imagecolorallocatealpha($dstImg, 255, 255, 255, 127);
            imagefilledrectangle($dstImg, 0, 0, $newW, $newH, $transparent);
        }

        imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        // 저장
        $saved = match ($mime) {
            'image/jpeg' => imagejpeg($dstImg, $destPath, 90),
            'image/png'  => imagepng($dstImg, $destPath, 8),
            'image/webp' => function_exists('imagewebp') ? imagewebp($dstImg, $destPath, 90) : false,
            'image/gif'  => imagegif($dstImg, $destPath),
            default      => false
        };

        imagedestroy($srcImg);
        imagedestroy($dstImg);

        return (bool)$saved;
    }

    /**
     * UUID 기반 파일명 생성 (충돌 방지)
     */
    private function generateFilename(string $ext): string
    {
        $uuid = sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
        return $uuid . '.' . $ext;
    }

    /**
     * 파일 삭제
     */
    public static function delete(string $urlPath): void
    {
        $filePath = APP_ROOT . $urlPath;
        if (is_file($filePath)) {
            unlink($filePath);
        }
    }
}
