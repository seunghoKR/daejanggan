# ============================================================
# Daejanggan Mall FTP Deployment Script (PowerShell Native)
# ============================================================

$ftpHost = "115.68.168.246"
$ftpUser = "ndaejanggan"
$ftpPass = "seungho0409#"
$remoteRoot = "/public_html"

$localBase = $PSScriptRoot

$uploadItems = @(
    "index.php",
    ".htaccess",
    "config",
    "core",
    "controllers",
    "views"
)

function Ensure-FtpDirectory($remotePath) {
    $parts = $remotePath.Trim('/').Split('/')
    $current = ""
    foreach ($part in $parts) {
        if ([string]::IsNullOrWhiteSpace($part)) { continue }
        $current += "/" + $part
        $uri = "ftp://$ftpHost$current"
        try {
            $req = [System.Net.FtpWebRequest]::Create($uri)
            $req.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
            $req.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
            $req.UseBinary = $true
            $req.KeepAlive = $false
            $resp = $req.GetResponse()
            $resp.Close()
        } catch {
            # Directory may already exist
        }
    }
}

function Upload-FtpFile($localFilePath, $remoteFilePath) {
    $uri = "ftp://$ftpHost$remoteFilePath"
    $req = [System.Net.FtpWebRequest]::Create($uri)
    $req.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
    $req.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
    $req.UseBinary = $true
    $req.KeepAlive = $false

    $fileBytes = [System.IO.File]::ReadAllBytes($localFilePath)
    $req.ContentLength = $fileBytes.Length

    $requestStream = $req.GetRequestStream()
    $requestStream.Write($fileBytes, 0, $fileBytes.Length)
    $requestStream.Close()

    $resp = $req.GetResponse()
    $resp.Close()
    Write-Host "  [OK] Uploaded: $remoteFilePath" -ForegroundColor Green
}

function Upload-Directory($localDir, $targetRemoteDir) {
    Ensure-FtpDirectory $targetRemoteDir
    $items = Get-ChildItem -Path $localDir

    foreach ($item in $items) {
        if ($item.PSIsContainer) {
            $subRemote = "$targetRemoteDir/$($item.Name)"
            Upload-Directory $item.FullName $subRemote
        } else {
            $remoteFile = "$targetRemoteDir/$($item.Name)"
            Upload-FtpFile $item.FullName $remoteFile
        }
    }
}

Write-Host "=== Starting Native PowerShell FTP Deployment to $ftpHost ===" -ForegroundColor Cyan

foreach ($item in $uploadItems) {
    $fullPath = Join-Path $localBase $item
    if (-not (Test-Path $fullPath)) {
        Write-Host "Skipping nonexistent: $item" -ForegroundColor Yellow
        continue
    }

    if ((Get-Item $fullPath).PSIsContainer) {
        $targetRemote = "$remoteRoot/$item"
        Write-Host "`nDeploying Directory: $item -> $targetRemote" -ForegroundColor Magenta
        Upload-Directory $fullPath $targetRemote
    } else {
        $targetRemote = "$remoteRoot/$item"
        Write-Host "`nDeploying File: $item -> $targetRemote" -ForegroundColor Magenta
        Upload-FtpFile $fullPath $targetRemote
    }
}

Write-Host "`n=== Deployment Completed 100% Successfully! ===" -ForegroundColor Cyan
