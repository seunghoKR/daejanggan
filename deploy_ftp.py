import os
import ftplib
import pathlib

FTP_HOST = '115.68.168.246'
FTP_USER = 'ndaejanggan'
FTP_PASS = 'seungho0409#'
FTP_REMOTE_ROOT = '/public_html'

LOCAL_BASE = pathlib.Path(__file__).resolve().parent

UPLOAD_ITEMS = [
    ('index.php', 'index.php'),
    ('.htaccess', '.htaccess'),
    ('config', 'config'),
    ('core', 'core'),
    ('controllers', 'controllers'),
    ('views', 'views'),
    ('public/assets', 'assets'),
    ('uploads', 'uploads'),
]

def ensure_remote_dir(ftp, remote_dir_path):
    """현재 위치와 상관없이 절대/상대 경로로 디렉토리 생성 및 이동"""
    ftp.cwd(FTP_REMOTE_ROOT)
    if not remote_dir_path or remote_dir_path in ('.', '/'):
        return
    parts = remote_dir_path.strip('/').replace('\\', '/').split('/')
    for part in parts:
        try:
            ftp.mkd(part)
        except Exception:
            pass
        ftp.cwd(part)

def upload_file_direct(ftp, local_path, remote_rel_dir, filename):
    ensure_remote_dir(ftp, remote_rel_dir)
    with open(local_path, 'rb') as f:
        ftp.storbinary(f'STOR {filename}', f)
    display_path = f"{remote_rel_dir}/{filename}" if remote_rel_dir else filename
    print(f'  [OK] Uploaded: {display_path}')

def upload_directory_tree(ftp, local_dir_path, target_root_dir):
    for root, dirs, files in os.walk(local_dir_path):
        rel = os.path.relpath(root, local_dir_path)
        if rel == '.':
            current_remote = target_root_dir
        else:
            current_remote = f"{target_root_dir}/{rel}".replace('\\', '/')
        
        for file in files:
            full_local = os.path.join(root, file)
            upload_file_direct(ftp, full_local, current_remote, file)

print('=== Daejanggan Mall FTP Deployment Started ===')
ftp = ftplib.FTP(FTP_HOST)
ftp.login(FTP_USER, FTP_PASS)
print(f'Logged in to FTP server. Target: {FTP_REMOTE_ROOT}')

for local_item, remote_target in UPLOAD_ITEMS:
    local_path = LOCAL_BASE / local_item
    if not local_path.exists():
        print(f'Skipping nonexistent: {local_item}')
        continue

    print(f'\nDeploying {local_item} -> {remote_target}...')
    if local_path.is_file():
        upload_file_direct(ftp, str(local_path), '', remote_target)
    elif local_path.is_dir():
        upload_directory_tree(ftp, str(local_path), remote_target)

# Clean up
ftp.cwd(FTP_REMOTE_ROOT)
for cleanup_file in ['init_db.php', 'deploy_init_db.php', 'daejanggan_mall.sql']:
    try:
        ftp.delete(cleanup_file)
    except Exception:
        pass

ftp.quit()
print('\n=== FTP Deployment Succeeded 100%! ===')
