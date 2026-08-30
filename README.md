# 📚 도서출판 대장간 신규 온라인 서점 (Daejanggan New Shop)

![Version](https://img.shields.io/badge/version-v1.3.0-blue.svg?style=flat-square)
![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4.svg?style=flat-square&logo=php&logoColor=white)
![MariaDB](https://img.shields.io/badge/MariaDB-10.5%2B-003545.svg?style=flat-square&logo=mariadb&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-3.4-38B2AC.svg?style=flat-square&logo=tailwind-css&logoColor=white)
![Alpine.js](https://img.shields.io/badge/Alpine.js-3.x-8BC0D0.svg?style=flat-square&logo=alpinedotjs&logoColor=white)
![Local AI](https://img.shields.io/badge/Local_AI-LM_Studio-orange.svg?style=flat-square)

신학과 평화, 정의, 아나뱁티스트 인문학을 전하는 **도서출판 대장간**의 차세대 맞춤형 온라인 쇼핑몰 및 관리자 시스템입니다.  
기존 영카트5(G5) 레거시 시스템의 610권 도서 데이터와 54,000여 장의 도서 상세 이미지를 무손실 이전하고, **로컬 AI 기반 원고 자동 분석 등록** 및 **인터랙티브 반응형 UX**를 구축했습니다.

---

## 🌟 핵심 기능 및 특징

### 1. 🤖 로컬 AI 스마트 도서 원고 자동 분석기 (`core/AiBookParser.php`)
- **텍스트 기반 원터치 파싱**:
  - 원고 텍스트의 **첫 번째 줄은 '도서명'**, **두 번째 줄은 '부제목'**으로 자동 인식하는 스마트 포지션 룰 적용.
  - `지은이`, `옮긴이(감수자 자동 병합)`, `출판사`, `발행일(YYYY-MM-DD)`, `ISBN`, `정가 / 10% 할인가격`, `도서분류/시리즈 매칭` 자동 추출.
- **서점 스타일 상세 설명 HTML 자동 생성**:
  - `* * * * * * *`, `[책소개]`, `[목차]`, `[지은이]`, `[옮긴이]` 등의 섹션 태그를 분석하여 세련된 반응형 박스 및 폰트 스타일로 자동 변환.
- **로컬 AI (`google/gemma-4-e2b`) 하이브리드 연동**:
  - LM Studio 로컬 서버(`http://49.170.204.109:1234`)와 실시간 연동하여 매력적인 1~2문장의 책소개 한줄 요약(summary) 자동 생성.

### 2. 📸 다중 이미지 일괄 업로드 & 드래그 앤 드롭 순서 변경 (Sortable UI)
- 표지, 속지, 본문 미리보기 등 여러 장의 이미지를 한 번에 드래그하거나 다중 선택하여 일괄 업로드.
- 가로/세로 최대 1600px, 90% 품질 유지로 자동 리사이징 및 용량 최적화.
- 썸네일 그리드에서 **마우스 드래그 앤 드롭으로 자유롭게 순서 변경**.
- 맨 앞(1번) 이미지는 자동으로 `[★ 대표 표지]` 뱃지가 부여되고 메인 `cover_image`로 지정.

### 3. 🎨 1280px 와이드 그리드 & 반응형 UI
- 헤더 GNB, 도서 목록, 카테고리 사이드바, 도서 상세, 장바구니/주문서, 커뮤니티 게시판, 마이페이지, 푸터까지 전 영역 **`max-w-7xl (1280px)` 수직 칼정렬**.
- 도서 상세 페이지 480px 고정 프레임 뷰어 탑재로 이미지 비율에 상관없이 레이아웃 고정.

### 4. 🗂️ 계층형 도서분류 & 다중 카테고리 지원 (`book_categories`)
- 235개 1차/2차/3차 도서분류 트리 및 1권 다분류 매핑 100% 동기화.
- 시리즈(1030), 주제별/장르별(1040), 도서출판비공(1050), NICS(1060) 분류 완벽 지원.

### 5. 🔔 회원 관리 & 실시간 메신저/알림 센터
- 마이페이지 카카오톡 알림톡 ON/OFF 및 텔레그램 개인 ID / Chat ID 실시간 연동.
- 다음(Daum) 우편번호 검색 API 연동 기본 배송지 관리.

### 6. 📤 7대 SNS 원클릭 공유 & OpenGraph 최적화
- 최신 카카오 JavaScript SDK (`Kakao.Share.sendDefault`) 연동 피드 카드 공유.
- 페이스북, 𝕏(트위터), 네이버 블로그/카페, 네이버 밴드, 텔레그램, 링크 복사 지원.

---

## 📁 디렉토리 구조

```
NewShop/
├── config/
│   ├── database.php        # PDO 데이터베이스 연결 및 쿼리 헬퍼
│   └── settings.php        # 사이트 전역 설정 및 상수 정의 (APP_VERSION: v1.2.0)
├── core/
│   ├── AiBookParser.php    # 로컬 AI 도서 원고 스마트 분석 엔진
│   ├── Auth.php            # 사용자/관리자 세션 인증 및 보안
│   ├── Captcha.php         # 캡차 보안 모듈
│   ├── Cart.php            # 장바구니 세션/DB 관리
│   ├── FileUploader.php    # 이미지 업로더 & 스마트 리사이저
│   ├── InicisPayment.php   # KG이니시스 결제 연동
│   └── Router.php          # Front Controller 라우터
├── controllers/
│   ├── AdminController.php # 관리자 (도서, 도서분류, 주문, 배너, 회원)
│   ├── BookController.php  # 도서 목록, 상세, 분류별, 시리즈, 저자별
│   ├── HomeController.php  # 메인 홈, 통합 검색, 커뮤니티 6대 게시판
│   ├── OrderController.php # 장바구니, 주문/결제, 배송 조회
│   └── UserController.php  # 회원가입, 로그인, 마이페이지, 알림센터
├── views/
│   ├── admin/              # 관리자 화면 (books, book_form, categories, orders 등)
│   ├── board/              # 커뮤니티 게시판 (카드뉴스 갤러리 뷰, 상세)
│   ├── book/               # 도서 목록 (계층 사이드바+칩바), 도서 상세
│   ├── layouts/            # header.php (GNB+카카오SDK), footer.php (버전표시), admin_layout.php
│   ├── order/              # 장바구니, 결제서, 주문완료, 비회원조회
│   └── user/               # 로그인, 회원가입, 마이페이지
├── deploy_ftp.ps1          # 윈도우 네이티브 PowerShell FTP 원클릭 자동 배포 스크립트
├── .gitignore              # Git 제외 파일 설정
└── README.md               # 프로젝트 문서
```

---

## 🚀 배포 방법

PowerShell에서 아래 스크립트를 실행하여 변경 사항을 실서버에 즉시 배포할 수 있습니다:

```powershell
powershell -ExecutionPolicy Bypass -File .\deploy_ftp.ps1
```

---

## 📋 버전 변경 이력 (Changelog)

### `v1.3.0` (2026-08-30)
- **통합 알림 서비스 모듈 (`core/Notifier.php`) 구축**:
  - 🤖 **로컬 AI 연동 장애 실시간 경보**: LM Studio 서버 무응답/타임아웃 시 관리자/개발자 텔레그램으로 긴급 알림 자동 전송 (15분 쿨다운 보호).
  - 🛒 **신규 주문 및 결제 접수 텔레그램 실시간 알림**.
  - 👤 **신규 회원 가입 텔레그램 실시간 알림**.
  - 💬 **카카오 알림톡 & SMS 비즈메시지 연동 인터페이스 준비**.
- **관리자 환경설정 (`views/admin/settings.php`) 알림 센터 고도화**:
  - 텔레그램 봇 토큰 & 관리자 Chat ID 다중 설정 지원.
  - 🤖 **로컬 AI 연결 상태 실시간 점검** (응답 속도 ms, 모델명 실시간 확인).
  - ✈️ **텔레그램 원클릭 테스트 알림 발송** 및 즉각 피드백 기능.
- **도서 등록 화면 (`views/admin/book_form.php`) 실시간 AI 상태 뱃지 장착**:
  - AI 상태 실시간 감지 및 장애 시 룰 엔진 자동 폴백 안내.

### `v1.2.0` (2026-08-29)
- **로컬 AI 스마트 도서 원고 자동 분석기 (`core/AiBookParser.php`) 탑재**:
  - 첫째 줄=도서명, 둘째 줄=부제목 스마트 포지션 인식 규칙 적용.
  - 부제목 기반 시리즈 및 도서분류 자동 매칭.
  - LM Studio (`google/gemma-4-e2b`) 하이브리드 연동.
- **다중 이미지 일괄 업로드 & 드래그 앤 드롭 정렬 UI (Sortable.js) 개발**:
  - 썸네일 마우스 드래그 순서 변경 및 1번 대표 표지 자동 지정.
- **용어 전면 정돈**: 사이트 전역 '카테고리' 표기를 '도서분류'로 일괄 변경.
- **데이터베이스 마이그레이션 정상화**:
  - 610권 도서의 저자 왜곡(`한국/논산`) 및 카테고리 미지정(`-`) 100% 정상 복구.
- **전역 버전 관리 시스템 적용**: 관리자 사이드바 및 푸터에 `v1.2.0` 버전 표기.

### `v1.1.0` (2026-08-28)
- 1280px 와이드 그리드 전역 수직 칼정렬.
- 대용량 도서 이미지 54,457개(3.7GB) 무손실 실서버 패키징 적재.
- 카카오톡/텔레그램 알림톡 센터 구축.
- 7대 SNS 원클릭 퍼가기 및 OpenGraph 최적화.

### `v1.0.0` (2026-08-27)
- 영카트5 레거시 시스템 기반 신규 모던 PHP MVC 서점 플랫폼 초기 구축.
