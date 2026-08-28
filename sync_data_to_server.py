import os
import ftplib
import time

FTP_HOST = '115.68.168.246'
FTP_USER = 'ndaejanggan'
FTP_PASS = 'seungho0409#'
REMOTE_ROOT = '/public_html/data'
LOCAL_ROOT = os.path.join(os.path.dirname(__file__), 'data')

def sync_data():
    print(f"=== Starting Fast Data Folder Sync to {FTP_HOST}:{REMOTE_ROOT} ===")
    ftp = ftplib.FTP(FTP_HOST)
    ftp.login(FTP_USER, FTP_PASS)
    
    # 원격 /public_html/data 생성 확인
    try:
        ftp.mkd(REMOTE_ROOT)
    except:
        pass
    
    uploaded_count = 0
    start_time = time.time()
    
    for root, dirs, files in os.walk(LOCAL_ROOT):
        rel_dir = os.path.relpath(root, LOCAL_ROOT).replace('\\', '/')
        remote_dir = REMOTE_ROOT if rel_dir == '.' else f"{REMOTE_ROOT}/{rel_dir}"
        
        # 원격 디렉토리 생성
        for d in dirs:
            target_d = f"{remote_dir}/{d}"
            try:
                ftp.mkd(target_d)
            except:
                pass
                
        # 파일 업로드
        for file in files:
            local_file = os.path.join(root, file)
            remote_file = f"{remote_dir}/{file}"
            
            # 원격 파일 존재 및 크기 체크
            try:
                rem_size = ftp.size(remote_file)
                loc_size = os.path.getsize(local_file)
                if rem_size == loc_size:
                    continue # 이미 동일한 파일이 있으면 스킵
            except:
                pass
                
            with open(local_file, 'rb') as f:
                ftp.storbinary(f"STOR {remote_file}", f)
                uploaded_count += 1
                if uploaded_count % 50 == 0:
                    print(f"  [Progress] Uploaded {uploaded_count} files... (Current: {rel_dir}/{file})")
                    
    ftp.quit()
    elapsed = time.time() - start_time
    print(f"\n=== Sync Complete! Total {uploaded_count} files uploaded in {elapsed:.1f}s ===")

if __name__ == '__main__':
    sync_data()
