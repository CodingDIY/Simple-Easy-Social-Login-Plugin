# Simple Easy Social Login

Simple Easy Social Login은 WordPress 사이트에 빠르고 간편한 소셜 로그인 기능을 추가할 수 있는 가볍고 사용자 친화적인 플러그인입니다.

**Google, Facebook, LinkedIn(무료)**과 **Naver, Kakao, Line(프리미엄)** 로그인을 지원하며,  
한국·일본·중국 등 아시아 지역을 타겟으로 한 웹사이트뿐만 아니라 유럽 및 남미 지역 사용자에게도 잘 동작하도록 설계되었습니다.

이 플러그인은 WordPress 기본 로그인 및 회원가입 페이지와 자연스럽게 연동되며,  
WooCommerce 로그인 및 회원가입 폼도 지원합니다.  
소셜 프로필 이미지는 WordPress 사용자 프로필의 아바타로 자동 동기화할 수 있습니다.

또한 **확장 가능한 Provider 아키텍처**를 기반으로 제작되어,  
필요한 경우 새로운 OAuth Provider를 별도의 Add-on 플러그인 형태로 추가할 수 있습니다.

---

## ✨ 주요 기능

- Google 로그인 (무료)
- Facebook 로그인 (무료)
- LinkedIn 로그인 (무료)
- Naver 로그인 (프리미엄)
- Kakao 로그인 (프리미엄)
- Line 로그인 (프리미엄)
- 사용자 아바타 자동 동기화
- 이메일 기반 기존 WordPress 사용자 자동 연결
- 로그인 / 로그아웃 / 회원가입 후 리디렉션 URL 설정
- Provider 설정을 위한 간결하고 직관적인 관리자 UI
- 숏코드 지원: [se_social_login]
- WordPress 로그인 및 회원가입 폼에 자동 표시
- WooCommerce 로그인 및 회원가입 폼 지원 (선택 사항)
- WordPress 코딩 표준을 따르는 가벼운 구조
- 불필요한 데이터베이스 테이블 생성 없음
- 새로운 OAuth Provider를 Add-on 플러그인으로 확장 가능한 Provider 시스템

---

## 🐞 디버그 로그

SESLP는 OAuth 및 소셜 로그인 문제를 진단할 수 있는  
내장 디버그 로그 시스템을 제공합니다.

자세한 로그 설명은 워드프레스 관리자 화면에서 확인할 수 있습니다:
**SESLP → Guides → Debug Log & Troubleshooting**

로그 파일 생성 위치:

- `/wp-content/SESLP-debug.log`
- `/wp-content/debug.log` (`WP_DEBUG_LOG` 활성화 시)

---

## 🚀 설치 방법

1. 플러그인을 `/wp-content/plugins/simple-easy-social-login/` 디렉토리에 업로드합니다.
2. WordPress 관리자에서 **플러그인 → 설치된 플러그인** 메뉴를 통해 활성화합니다.
3. **설정 → Simple Easy Social Login** 메뉴로 이동합니다.
4. 각 소셜 로그인 Provider의 Client ID와 Client Secret을 입력합니다.
5. 설정을 저장합니다.
6. 프론트엔드에서 소셜 로그인 버튼이 정상적으로 표시되는지 확인합니다.

---

## ❓ 자주 묻는 질문

### WooCommerce와 함께 사용할 수 있나요?

네. WooCommerce의 로그인 및 회원가입 폼과 연동됩니다.

### WooCommerce 로그인은 되는데, 리디렉션 동작이 다릅니다. 정상인가요?

네. WooCommerce가 활성화된 경우, 로그인 후 기본적으로 **내 계정(My Account)** 페이지로 이동합니다.  
리디렉션 URL은 플러그인 설정 또는 제공되는 필터를 통해 변경할 수 있습니다.

### WooCommerce 사이트에서 소셜 로그인이 작동하지 않을 경우 무엇을 확인해야 하나요?

다음 사항을 확인해 주세요:

- WooCommerce가 최신 안정 버전으로 업데이트되어 있는지
- 플러그인 설정에서 해당 소셜 로그인 제공자가 활성화되어 있는지
- Client ID와 Client Secret 값이 정확한지
- 리디렉션 / 콜백 URL이 각 소셜 네트워크 개발자 콘솔에 정확히 등록되어 있는지
- 커스텀 로그인 또는 결제 템플릿에서 WooCommerce 기본 훅을 제거하지 않았는지
- 디버그 로그가 활성화되어 있고 `/wp-content/SESLP-debug.log` 파일을 확인했는지

### Google 로그인만 사용할 수 있나요?

네. 각 Provider는 개별적으로 활성화하거나 비활성화할 수 있습니다.

### 프리미엄 라이선스는 언제 필요하나요?

**Naver, Kakao, Line** 로그인을 사용하려면 프리미엄 라이선스가 필요합니다.  
Google, Facebook, LinkedIn은 무료로 사용할 수 있습니다.

### 숏코드가 제공되나요?

네. 아래 숏코드를 사용하여 원하는 위치에 소셜 로그인 버튼을 삽입할 수 있습니다.
[se_social_login]

### 사용자 아바타도 자동으로 가져오나요?

네. Google, Facebook 등 일부 Provider의 경우 프로필 이미지를 자동으로 가져와 WordPress 아바타로 동기화할 수 있습니다.

---

## 🖼 스크린샷

1. 워드프레스 로그인 페이지에 표시되는 소셜 로그인 버튼(리스트형).
2. 로그인 화면에 표시되는 아이콘 전용 소셜 로그인 버튼 레이아웃.
3. 로그인 후 리다이렉트 옵션(대시보드, 프로필, 메인 페이지 또는 사용자 지정 URL).
4. 디버그 로그, UI 레이아웃 옵션, 숏코드 및 언인스톨 설정.
5. OAuth 리다이렉트 규칙과 기본 요구 사항을 설명하는 내장 설정 가이드.
6. Google OAuth 동의 화면 및 클라이언트 설정을 위한 단계별 가이드.
7. Google, Facebook, LinkedIn 로그인 인증 정보를 위한 관리자 설정 화면.
8. Naver, Kakao, LINE 로그인 제공자 관리자 설정 화면.
9. 모든 지원 제공자에서 공통으로 사용되는 리다이렉트 URI 규칙.
10. 디버그 로그 위치 및 문제 해결 개요.
11. 일반적인 OAuth 오류, 권장 해결 방법 및 디버그 로그 확인 위치.

---

## 📝 변경 사항 (Changelog)

### 1.9.9

- 공개 릴리스를 위한 스크린샷 및 문서 최종 정리
- 로그인 흐름, 설정, 가이드, 문제 해결을 포함한 스크린샷 설명 추가
- 문서 전반의 가독성 및 일관성 개선

### 1.9.8

- `SESLP_Avatar::resolve_user()`에서 발생하던 치명적인 타입 오류를 수정하여 `WP_User|null` 반환을 보장
- 아바타 폴백 처리 개선:
  - 소셜 프로필 이미지가 없거나 유효하지 않은 경우 워드프레스 기본 아바타로 안전하게 대체
  - 아바타 이미지 깨짐 현상 방지 (예: LinkedIn 프로필 이미지 문제)
- 아바타 렌더링과 관련된 소규모 안정성 개선

### 1.9.7

- README에 디버그 로그 섹션 추가
- 관리자 가이드에 상세 디버그 로그 설명 통합 (다국어)
- 로그 파일 경로 문서 통일 (`/wp-content/SESLP-debug.log`)
- 문서 구조 정리 및 일관성 개선

### 1.9.6

- 설정 페이지 사용성 개선
- Secret 키 표시/숨기기 토글 추가
- WordPress 코어 스타일 충돌 문제 수정
- Pro/Max 플랜 감지 로직 개선

### 1.9.5

- 대규모 리팩토링
- Helpers 통합 및 Provider 구조 개선
- 설정 UI 정비
- 안정성 및 유지보수성 향상

### 1.9.3

- 가이드(Guides) 번역 업데이트
- 설정 페이지에 숏코드 표시 추가

### 1.9.2

- 내부 구조 정리
- Guides 로더 클래스 추가
- 템플릿 구조 재정비
- 설정 및 CSS 로더 안정성 개선

### 1.9.1

- 관리자 가이드 페이지 추가
- Markdown 기반 다국어 문서 렌더링 (Parsedown 적용)
- UI 스타일 개선

### 1.9.0

- 대규모 리팩토링 준비 단계
- i18n 헬퍼 확장
- 안전한 포매팅 및 로깅 구조 개선

### 1.7.23

- 번역 업데이트

### 1.7.22

- 기존 로그인 Provider가 디버그 메시지에 표시되도록 개선

### 1.7.21

- 동일 이메일 중복 가입 시 에러 메시지에 Provider 이름 표시
- JavaScript를 통해 10초 후 에러 메시지 자동 숨김 처리

### 1.7.19

- 동일 이메일로 중복 계정 생성 방지
- OAuth 흐름 개선:
  - `get_access_token()`
  - `get_user_info()`
  - `create_or_link_user()`

### 1.7.18

- Google Client ID/Secret 필드의 툴팁 제거
- 코드 구조 정리
- Line 로그인 버튼의 "(Email required)" 텍스트 제거

### 1.7.17

- Line 로그인 관련 문제 수정:
  - 재로그인 시 중복 사용자 생성 방지
  - `/complete-profile` 페이지 재등장 문제 수정
  - 이메일 업데이트 허용으로 "Invalid request" 오류 해결
- `SESLP_Logger`를 통한 디버깅 로그 통합

### 1.7.16

- 디버그 로그에서 라이선스 키 마스킹 처리 (예: abc\*\*\*\*123)
- 디버깅을 위한 `wp_options` 확인 안내 추가
- 로그 기록 실패 시 관리자 알림 추가

### 1.7.15

- 디버그 로그 기록 실패 문제 수정
- WordPress 로컬 시간대 기준으로 타임스탬프 적용
- 설정 저장 시 디버그 로그 추가

### 1.7.5

- 최신 보안 패치 적용
- 성능 최적화 및 사용자 경험 개선

### 1.7.0

- 소셜 로그인 버튼 동기화 개선
- 보안 강화 및 버그 수정

### 1.7.3

- 디버깅 시스템 개선
- 전용 debug 디렉토리 추가

### 1.6.0

- Plus/Premium 선택 시 라이선스 키 영역 표시 로직 복원

### 1.5.0

- `seslp_license_type` 옵션 등록
- 설정 저장 시 Free로 초기화되던 문제 수정

### 1.4.0

- `admin_enqueue_scripts`를 사용하여 관리자 `style.css` 로드 문제 해결

### 1.3.0

- 라디오 버튼 UI 개선
- 인라인 CSS를 `style.css`로 이동

### 1.2.0

- 라이선스 타입 선택 기능 추가 (Free / Plus / Premium)
- 설정 페이지 UI 정렬 개선

### 1.1.0

- 다국어 지원 및 번역 파일 로딩 기능 추가
- 사용자 인증 로직 개선

### 1.0.0

- 최초 릴리스
- Google, Facebook, Naver, Kakao, Line, Weibo 소셜 로그인 기능 추가

---

## 📄 라이선스

GPLv2 or later  
https://www.gnu.org/licenses/gpl-2.0.html
