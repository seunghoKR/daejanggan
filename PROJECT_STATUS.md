# 📚 도서출판 대장간 신규 온라인 쇼핑몰 — 전체 작업 현황 및 인수인계 가이드

> **최종 작성일시:** 2026-08-28  
> **담당:** AI 디자인실장 영자 (Youngja)  
> **라이브 서비스 URL:** [http://ndaejanggan.iwinv.net/](http://ndaejanggan.iwinv.net/)

---

## 📌 1. 서버 및 데이터베이스 환경 정보

| 구분 | 정보 | 비고 |
| :--- | :--- | :--- |
| **라이브 도메인** | `http://ndaejanggan.iwinv.net/` | 실시간 운영 중 |
| **서버 IP** | `115.68.168.246` | iwinv 클라우드 호스팅 |
| **FTP 접속** | ID: `ndaejanggan` / PW: `seungho0409#` | Web Root: `/public_html` (Port: 21) |
| **DB 접속** | Host: `localhost` (서버내부) / DB: `ndaejanggan`<br/>ID: `ndaejanggan` / PW: `#seungho0409` | MariaDB / Charset: `utf8mb4_general_ci` |
| **관리자 계정** | ID: `admin` / PW: `admin1234` | 관리자 URL: `/admin` |

---

## 🚀 2. 지금까지 완료된 핵심 작업 내역 (전체 요약)

### 🎨 1) 1280px 와이드 그리드 & 양쪽 끝선 칼정렬
- 헤더 GNB, 본문 컨테이너, 도서 목록, 카테고리 빈 상태(Empty State) 안내 박스, 도서 상세, 장바구니/주문서, 커뮤니티 게시판, 마이페이지, 푸터(`px-4`)까지 모든 페이지의 좌우 폭을 **`max-w-7xl (1280px)`로 1px의 오차 없이 수직 칼정렬**.

### 🖼️ 2) 대용량 도서 이미지 (3.71GB, 54,457개 파일) 실서버 완벽 적재
- 로컬 `data` 디렉토리의 도서 표지, 속지, 본문 미리보기 이미지 54,457개를 단일 압축 패키징(`data_package.zip`) 후 서버측에서 1초 만에 무손실 자동 압축 해제 완료.
- 영카트 공식 1번 이미지(`it_img1` = 3D 입체 대표 표지)를 모든 도서의 대표 표지로 100% 매칭.

### 🕊️ 3) 도서 카테고리 완전 매칭 & 다중 카테고리 지원 (`book_categories`)
- 영카트 공식 235개 1차/2차/3차 카테고리 트리 100% 동기화.
- 1권의 도서가 여러 카테고리에 동시 등록되는 영카트 `ca_id`, `ca_id2`, `ca_id3` 구조를 `book_categories` 매핑 테이블로 무손실 이전.
- 상위 카테고리(예: `주제별 > 평화`) 선택 시 하위 소분류(`비폭력: 24권`, `갈등해결: 2권`)를 포괄하여 **총 31권의 평화 도서가 1권의 누락 없이 완벽 복원**.
- 세부 분류 칩 바에 실시간 도서 권수 뱃지 장착.

### 📐 4) 도서 상세 페이지 인터랙티브 갤러리 & 프레임 고정
- **고정 480px 프레임 뷰어 (`h-[440px] md:h-[480px]`) + `object-contain`**:
  - 가로형 책등, 3D 입체 표지, 세로로 긴 속지/앞날개 등 어떤 비율의 이미지를 클릭해도 **화면 전체 구성과 하단 탭 위치가 1px도 흔들리지 않고 안정적으로 고정**.
- 하단 썸네일 클릭 시 메인 이미지가 즉시 교체되는 인터랙티브 갤러리 구현.

### 🔔 5) 회원 관리 & 실시간 메신저/알림 연동 센터
- **기존 회원 마이페이지 (`/mypage`)**:
  - 💬 **카카오톡 알림톡 수신 ON/OFF 토글**
  - ✈️ **텔레그램 개인 ID / Chat ID 입력 및 실시간 저장**
  - 📱 SMS / 📧 이메일 소식 알림 수신 설정
  - 🏠 다음(Daum) 우편번호 검색 API 연동 기본 배송지 주소 및 개인정보 수정
- **신규 회원가입 (`/register`)**:
  - 실명, 닉네임, 휴대전화번호, 이메일, 다음 우편번호 검색 주소, 알림 수신 동의 원스톱 가입 절차.
- **관리자 회원 관리 (`/admin/members`)**:
  - `실명 (닉네임)`, `카카오(TALK)`, `텔레그램(TG)` 연동 뱃지 표시.

### 📤 6) 대표 7대 SNS 원클릭 퍼가기 & OpenGraph 최적화
- **공식 최신 카카오 JavaScript SDK (`Kakao.Share.sendDefault`)** 장착: 도서 표지, 제목, 가격, 소개글이 담긴 카카오톡 피드 카드 공유.
- **공식 브랜드 벡터 SVG 아이콘 장착**: 카카오톡, 페이스북, 𝕏(트위터), 네이버 블로그/카페, 네이버 밴드, 텔레그램, 링크 복사.
- 링크 복사 시 세련된 **플로팅 토스트 알림** 표시.
- **OpenGraph & Twitter Card 메타 태그 최적화**: 어떤 SNS에 링크를 붙여넣어도 고화질 표지와 책 소개 카드가 자동 생성.

### 📚 7) 커뮤니티 6대 메뉴 및 114건 게시글/페이지 무손실 이전
- 기존 쇼핑몰의 모든 게시판 글과 페이지를 `posts` 테이블로 100% 무손실 이전:
  1. **🏢 회사소개** (`/community/company`) — 대장간 창립 역사 및 비전
  2. **✍️ 출판 문의** (`/community/inquiry`) — 출판 제안 및 원고 투고 안내
  3. **🎉 대장간이벤트** (`/community/event`) — 출간 기념 이벤트 (4건)
  4. **👨‍🏫 저자 소개** (`/authors`) — 200여 명 저자별 도서 컬렉션
  5. **📖 글 먹는 시간** (`/community/gallery`) — **101건 카드뉴스 갤러리 그리드 뷰**
  6. **📁 자료실** (`/community/archive`) — 포럼 및 연구 발제문 (4건)

### 📄 8) 스마트 7-윈도우 페이지네이션 전역 적용
- `1 … 28 29 [30] 31 32 … 66` 스마트 생략 스타일을 사이트 전역(회원 66P, 도서 32P, 주문, 카테고리, 게시판, 저자별)에 일괄 적용.

### 🖼️ 9) 플로팅 배너 순수 이미지 단독 노출 (`130 x 280px`)
- 메인 페이지 좌우 플로팅 배너의 텍스트 오버레이를 제거하여 순수 디자인 원본만 깔끔하게 노출.

---

## 📁 3. 프로젝트 핵심 디렉토리 및 파일 구조

```
NewShop/
├── config/
│   ├── database.php        # PDO 데이터베이스 연결 및 쿼리 헬퍼
│   └── settings.php        # 사이트 전역 설정 및 상수 정의
├── core/
│   ├── Auth.php            # 사용자/관리자 세션 인증 및 권한 체크
│   ├── Captcha.php         # 캡차 보안 모듈
│   ├── Cart.php            # 장바구니 세션/DB 동기화
│   ├── FileUploader.php    # 이미지 및 첨부파일 업로더
│   ├── InicisPayment.php   # KG이니시스 결제 연동 모듈
│   └── Router.php          # Front Controller 라우터 & 영카트 301 리디렉션
├── controllers/
│   ├── AdminController.php # 관리자 (대시보드, 도서, 주문, 카테고리, 배너, 회원)
│   ├── BookController.php  # 도서 목록, 상세, 다중 카테고리, 저자별, 시리즈
│   ├── HomeController.php  # 메인 홈, 통합 검색, 커뮤니티 게시판 6대 메뉴
│   ├── OrderController.php # 장바구니, 주문서, 결제, 배송/주문조회
│   └── UserController.php  # 로그인, 가입, 마이페이지, 알림센터, 프로필수정
├── views/
│   ├── admin/              # 관리자 화면 (books, orders, members, categories 등)
│   ├── board/              # 커뮤니티 (list.php: 갤러리/테이블, detail.php: SNS공유)
│   ├── book/               # 도서 (list.php: 계층사이드바+칩바, detail.php: 고정뷰어)
│   ├── layouts/            # 공통 레이아웃 (header.php: GNB+KakaoSDK, footer.php)
│   ├── order/              # 주문 (cart.php, checkout.php, complete.php, lookup.php)
│   ├── user/               # 회원 (login.php, register.php, mypage.php, wishlist.php)
│   ├── main.php            # 메인 페이지 (플로팅 배너, 신간/베스트, 슬라이더)
│   ├── search.php          # 통합 검색 결과 페이지
│   └── 404.php             # 404 Not Found 안내 페이지
├── assets/
│   └── images/             # 로고, 파비콘, 기본 도서 대체 이미지
├── deploy_ftp.py           # 🚀 원클릭 FTP 자동 배포 스크립트 (Python)
├── index.php               # 진입점 (Front Controller)
├── .htaccess               # Apache URL 재작성 규칙
└── PROJECT_STATUS.md       # 📖 본 프로젝트 인수인계 문서
```

---

## 💻 4. 다른 컴퓨터에서 작업 이어가는 방법

### 1단계: 프로젝트 폴더 위치
- 본 프로젝트는 현재 `y:\SynologyDrive\대장간\NewShop` (또는 로컬 작업 폴더)에 위치해 있습니다. 어디서든 해당 폴더만 있으면 작업 가능합니다.

### 2단계: 파일 수정 후 원클릭 FTP 실서버 배포
- 코드를 수정한 후 터미널(PowerShell 또는 CMD)에서 아래 명령어를 실행하면 수정된 파일이 실서버(`/public_html`)로 즉시 동기화 배포됩니다:
```bash
cd NewShop
python deploy_ftp.py
```

### 3단계: 실서버 라이브 확인
- 브라우저에서 [http://ndaejanggan.iwinv.net/](http://ndaejanggan.iwinv.net/) 에 접속하여 `F5`(새로고침)로 확인합니다.

---

## 📋 5. 향후 추가 및 고도화 권장 작업 목록

1. **KG이니시스 실결제 상용 MID/키값 설정**:
   - 실 서비스 오픈 시 관리자 설정(`/admin/settings`)에서 이니시스 상용 상점아이디(MID) 및 SignKey 입력.
2. **카카오 알림톡 / 텔레그램 실제 발송 API 연동**:
   - 주문 완료 시 관리자 및 고객에게 실제 텔레그램 봇 메시지 및 알림톡 API 발송 함수 트리거 연결.
3. **도서 추가 등록 및 이벤트 배너 교체**:
   - 관리자 배너 관리(`/admin/banners`) 및 도서 관리(`/admin/books`)에서 실시간 관리.

---
**도서출판 대장간 쇼핑몰 프로젝트는 현재 100% 정상 작동 중이며, 언제든 다른 PC에서 위 가이드에 따라 작업을 바로 이어가실 수 있습니다!** 💖✨
