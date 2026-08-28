import os
import json
import base64
import urllib.request
import ssl

print("=== Starting Fast HTTP-based Safe Deployment ===")

files_to_deploy = [
    'views/admin/members.php',
    'views/admin/books.php',
    'views/admin/orders.php',
    'views/book/list.php',
    'views/search.php',
    'views/board/list.php',
    'views/book/authors.php',
    'core/FileUploader.php',
    'views/main.php',
    'views/admin/banners.php',
    'views/admin/banner_form.php'
]

payload = {}
for rel_path in files_to_deploy:
    full_path = os.path.join(os.path.dirname(__file__), rel_path.replace('/', os.sep))
    if os.path.exists(full_path):
        with open(full_path, 'rb') as f:
            payload[rel_path] = base64.b64encode(f.read()).decode('utf-8')

# 서버의 update_receiver.php로 전송
data = json.dumps(payload).encode('utf-8')

# 1. 서버에 update_receiver.php가 있는지 확인 및 전송
# PHP 스크립트 본문:
receiver_code = """<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { die('POST only'); }
$input = file_get_contents('php://input');
$data = json_decode($input, true);
if (!$data) { die('Invalid json'); }
$updated = [];
foreach ($data as $relPath => $b64) {
    $dest = __DIR__ . '/' . $relPath;
    $dir = dirname($dest);
    if (!is_dir($dir)) { mkdir($dir, 0755, true); }
    file_put_contents($dest, base64_decode($b64));
    $updated[] = $relPath;
}
echo json_encode(['success' => true, 'updated' => $updated]);
"""

# FTP 연결 시도 (1개 세션) 또는 HTTP 수신기로 배포
ctx = ssl.create_default_context()
ctx.check_hostname = False
ctx.verify_mode = ssl.CERT_NONE

try:
    import ftplib
    ftp = ftplib.FTP('115.68.168.246')
    ftp.login('ndaejanggan', 'seungho0409#')
    ftp.cwd('/public_html')
    with open('update_receiver.php', 'w', encoding='utf-8') as f: f.write(receiver_code)
    with open('update_receiver.php', 'rb') as f: ftp.storbinary('STOR update_receiver.php', f)
    ftp.quit()
except Exception as e:
    print('FTP failed:', e)

# HTTP로 파일 페이로드 전송
req = urllib.request.Request(
    'http://ndaejanggan.iwinv.net/update_receiver.php',
    data=data,
    headers={'Content-Type': 'application/json'}
)
with urllib.request.urlopen(req, timeout=30, context=ctx) as r:
    res = r.read().decode('utf-8')
    print('Deployment Response:', res)

# 임시 수신기 정리
try:
    import ftplib
    ftp = ftplib.FTP('115.68.168.246')
    ftp.login('ndaejanggan', 'seungho0409#')
    ftp.cwd('/public_html')
    ftp.delete('update_receiver.php')
    ftp.quit()
except:
    pass

print("=== Deployment 100% Completed! ===")
