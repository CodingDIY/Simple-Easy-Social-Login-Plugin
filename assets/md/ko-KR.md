> 이 문서는 **Simple Easy Social Login (SESLP)** 플러그인에서 각 소셜 로그인 공급자(Google, Facebook, LinkedIn, Naver, Kakao, Line)를 설정하는 방법을 안내합니다.  
> 모든 로그인은 **OAuth 2.0 또는 OpenID Connect(OIDC)** 표준에 기반하며,  
> 각 플랫폼의 콘솔에서 발급받은 **Client ID / Secret**을 입력해야 합니다.

---

## 🔧 공통 설정 가이드

#### 1) **Redirect URI 규칙:**

`https://{도메인}/?social_login={provider}`  
 예:

- Google → `https://example.com/?social_login=google`
- Facebook → `https://example.com/?social_login=facebook`
- LinkedIn → `https://example.com/?social_login=linkedin`
- Naver → `https://example.com/?social_login=naver`
- Kakao → `https://example.com/?social_login=kakao`
- LINE → `https://example.com/?social_login=line`

#### 2) **HTTPS 필수**

대부분의 공급자는 HTTPS가 필요하며, `http://` 리디렉트는 거부

#### 3) **정확한 일치**:

Redirect URI는 콘솔에 등록된 값과 **100% 일치**해야 함  
 (프로토콜, 서브도메인, 패스, 쿼리 등)

#### 4) **이메일 비공개 사용자 처리**:

일부 공급자는 사용자가 이메일 제공을 거부할 수 있음. 플러그인은 내부적으로 ID 기반 사용자 연결 지원

#### 5) **로그 확인 경로**

- `/wp-content/SESLP-debug.log`
- `/wp-content/debug.log` (`WP_DEBUG_LOG = true`)

## 🐞 디버그 로그 및 문제 해결

SESLP는 OAuth 및 소셜 로그인 문제를 진단하는 데 도움이 되는 전용 디버그 로그 파일을 제공합니다.

<details>
  <summary><strong>SESLP 디버그 로그 읽는 방법</strong></summary>

#### 로그 파일 위치

- `/wp-content/SESLP-debug.log` (SESLP 디버그 로그)
- `/wp-content/debug.log` (`WP_DEBUG_LOG = true`)

#### 로그 형식

```
[YYYY-MM-DD HH:MM:SS Z] [LEVEL] Message {"key":"value",...}
```

- `Z`: UTC 또는 워드프레스 로컬 시간대 (예: KST) — SESLP 설정에서 선택 가능
- 개인정보 보호: 이메일 / 토큰 / 시크릿 값은 자동으로 마스킹 처리됨 (예: `r********@g****.com`)

#### OAuth 흐름 로그 (일반적)

**1) OAuth 시작**

```
[DEBUG] State created {"provider":"google","state":"906****23","ttl":"10min"}
```

의미: CSRF 보호를 위한 state 토큰이 생성되었습니다. `ttl`은 **10분간 유효**합니다.

**2) 콜백 진입**

```
[DEBUG] Auth route triggered {"provider":"google","has_code":1}
```

의미: 콜백 라우트에 진입했습니다. `has_code:1` → OAuth `code` 값을 수신했습니다.

**3) State 검증**

성공:

```
[DEBUG] State validated {"provider":"google","state":"906****23"}
```

실패:

```
[WARNING] State validation failed: not found/expired {"provider":"google","state":"906****23"}
```

**4) 토큰 교환**

```
[DEBUG] Token response (google) {"has_access_token":1}
```

의미: 액세스 토큰을 정상적으로 획득했습니다.

실패:

```
[ERROR] Token request failed (google) {"error":"..."}
```

**5) 사용자 정보 요청**

```
[ERROR] Userinfo request failed (google)
[WARNING] Invalid userinfo (google)
```

**6) 사용자 연결 (Linker)**

```
[DEBUG] Linker: signing in user {"user_id":45,"provider":"google","created":0}
[INFO]  Login success (google) {"user_id":45,"email":"r********@g****.com"}
```

**7) 리디렉션**

```
[DEBUG] Redirect decision {"mode":"profile","user_id":45,"url":"https://example.com/wp-admin/profile.php"}
```

#### 빠른 참조 테이블

| 로그 메시지 (요약)      | 원인 추정                                 | 조치 방법                                 |
| ----------------------- | ----------------------------------------- | ----------------------------------------- |
| State validation failed | 타임아웃, 탭 전환, 중복 요청              | 빠르게 재시도, 시크릿/프라이빗 모드 사용  |
| Token request failed    | Client ID/Secret/Redirect 오류, 요청 차단 | 개발자 콘솔, 방화벽, 서버 시간 확인       |
| Userinfo invalid        | 스코프 누락 또는 이메일 비공개            | `email, profile` 스코프 추가, 사용자 동의 |
| User create failed      | 계정 충돌 또는 워드프레스 제한            | 기존 사용자, 멀티사이트 규칙 확인         |
| Redirect missing        | 코드 내 조기 return                       | 콜백 이후 Redirect 클래스 실행 여부 확인  |

#### 버그 리포트에 포함하면 좋은 정보

- 관련 로그 라인 (마스킹된 상태)
- 사용한 Provider (Google / Naver 등)
- 리디렉션 모드 / 커스텀 URL
- 디버그 로그 활성화 상태
- 워드프레스 환경 (단일 사이트, 멀티사이트, 캐시 플러그인)

</details>

---

## 🌍 Provider별 설정 가이드

> 아래의 각 공급자를 확장하고 해당 공급자를 위해 준비한 한국어 가이드 콘텐츠를 붙여넣으세요.

---

<details open>
<summary><strong>Google (구글)</strong></summary>

> - **권장 스코프:** `openid email profile`
> - **Redirect URI 규칙:** `https://{도메인}/?social_login=google`

---

#### 1) 준비 사항 (필수 체크리스트)

(1) **HTTPS 권장/사실상 필수** (로컬은 신뢰된 개발용 인증서 사용).

(2) Redirect URI는 콘솔에 등록한 값과 **100% 일치**해야 합니다. 예) `https://example.com/?social_login=google`

(3) 테스트 모드에서는 **테스트 사용자**만 로그인 가능(최대 100명).

(4) 앱 홈페이지/개인정보처리방침/약관 URL을 사용하는 경우, **앱 도메인(Authorized domains)** 등록 및 **소유권 검증** 필요할 수 있음.

#### 2) 프로젝트/동의 화면 설정

(1) **Google Cloud Console** 접속
[https://console.cloud.google.com/apis/credentials](https://console.cloud.google.com/apis/credentials)

(2) 상단 프로젝트 선택 → **새 프로젝트 생성**(필요 시).

(3) 사이드바 **API 및 서비스 → OAuth 동의 화면** 이동.

(4) **사용자 유형** 선택: 보통 **외부(External)**.

(5) **앱 정보** 입력

- 앱 이름, 사용자 지원 이메일, (선택) 로고.

(6) **앱 도메인(App domain)** 섹션

- 앱 홈페이지 URL, 개인정보처리방침 URL, 서비스 약관 URL 입력
- **Authorized domains**에 **루트 도메인(예: example.com)** 추가 → **저장**
- 필요 시, Search Console을 통한 **도메인 소유권 검증** 진행.

(7) **범위(Scopes)** 설정

- **권장:** `openid`, `email`, `profile`
- 민감/제한 스코프는 운영 전 검토가 필요할 수 있음.

(8) **테스트 사용자** 추가 (테스트 모드 유지 시 로그인 허용할 이메일 추가).

(9) **저장**.

> 참고: 기본 스코프(`openid email profile`)만 사용하면 **검토 없이** 운영(게시) 가능인 경우가 많습니다.

#### 3) OAuth 클라이언트 생성 (웹 애플리케이션)

(1) 사이드바 **API 및 서비스 → 사용자 인증 정보(Credentials)**.

(2) 상단 **+ 사용자 인증 정보 만들기 → OAuth 클라이언트 ID**.

(3) **애플리케이션 유형:** `웹 애플리케이션`.

(4) **이름**은 구분 가능한 식으로 입력(예: `SESLP – Front`).

(5) **승인된 리디렉션 URI(Authorized redirect URIs)** 추가

- `https://{도메인}/?social_login=google`

(6) **만들기(Create)** → 표시되는 **Client ID / Client Secret** 복사.

> (선택) JavaScript 기원(Authorized JavaScript origins)은 **코드 그랜트**를 쓰는 본 플러그인에는 보통 불필요합니다.

#### 4) 워드프레스(플러그인) 설정

(1) WP 관리자 → **SESLP 설정 → Google** 탭.

(2) **Client ID / Client Secret** 붙여넣기 → **저장**.

(3) 사이트 프론트에서 **Google 로그인 버튼으로 실 테스트**.

#### 5) 테스트 → 운영(게시) 전환

(1) **OAuth 동의 화면 → 게시 상태(Publishing status)** 확인.

(2) 테스트에서 운영으로 전환하려면:

- 앱 정보(로고/앱 도메인/정책/약관) 정확히 입력되었는지 점검.
- 불필요 스코프 제거, 필요한 스코프만 유지.
- (민감 스코프 사용 시) 검토 요청 제출.

(3) 운영 전환 후에는 모든 Google 계정 사용자가 로그인 가능.

#### 6) 자주 발생하는 오류 & 해결

(1) **redirect_uri_mismatch**

→ 콘솔에 등록된 Redirect URI와 실제 요청 URI가 **조금이라도 다르면** 발생(프로토콜/서브도메인/슬래시/쿼리 포함). 정확히 일치하도록 수정.

(2) **access_denied / disallowed_useragent**

→ 브라우저/인앱 환경 제약. 일반 브라우저에서 재시도.

(3) **invalid_client / unauthorized_client**

→ Client ID/Secret 오타 또는 앱 상태(삭제/비활성). 자격 증명 재발급/재확인.

(4) **email이 없음**

→ 스코프에 `email` 포함 여부, 동의 화면 노출, 계정의 이메일 공개/보안 설정 점검. 동의 화면에서 이메일 권한 용도 명확히 설명.

> **로그 확인:**
>
> - `wp-content/SESLP-debug.log` (플러그인 디버그 ON)
> - `wp-content/debug.log` (WP_DEBUG, WP_DEBUG_LOG = true)

#### 7) 요약 체크리스트

- [ ] OAuth 동의 화면: 앱 정보/도메인/정책/약관/스코프/테스트 사용자 설정
- [ ] 사용자 인증 정보: **웹 애플리케이션** 클라이언트 생성
- [ ] Redirect URI: `https://{도메인}/?social_login=google` 등록
- [ ] SESLP: Client ID/Secret 저장 후 실제 로그인 테스트
- [ ] 운영 전환 시 게시 상태 변경(필요 시 검토 제출)

</details>

---

<details>
<summary><strong>Facebook (페이스북)</strong></summary>

> - **Redirect URI:** `https://{도메인}/?social_login=facebook`
> - **요청 권한(권장):** `public_profile`, `email`
> - Facebook은 `openid`를 사용하지 않습니다.

---

### 1) 앱 생성 및 제품 추가

(1) **Meta for Developers** 접속 → 로그인
[https://developers.facebook.com/](https://developers.facebook.com/)

(2) **Create App** → 일반(Consumer 등) 유형 선택 → 앱 생성

(3) 왼쪽 **Products**에서 **Facebook Login** 추가

(4) **Settings(설정)** 진입 → 아래 항목 점검

- **Client OAuth Login:** ON
- **Web OAuth Login:** ON
- **Valid OAuth Redirect URIs:**
  - `https://{도메인}/?social_login=facebook` 추가
- (선택) **Enforce HTTPS:** 기본 권장

#### 2) 앱 기본 설정 (App Settings → Basic)

(1) **App Domains:** `example.com` (앱 정책/약관/홈페이지 URL의 도메인)

(2) **Privacy Policy URL:** 공개 접근 가능한 정책 페이지

(3) **Terms of Service URL:** 공개 접근 가능한 약관 페이지

(4) **User Data Deletion:** 지침 URL 또는 삭제 엔드포인트 제공

(5) **Category / App Icon:** 적절히 설정 후 **Save**

#### 3) 스코프(권한) 및 App Review

(1) 일반 로그인에 필요한 기본 권한은 **`public_profile`**, 선택적 이메일은 **`email`**

(2) 대부분 **`email`은 검수 없이 사용 가능**하나 지역/계정별 예외가 있을 수 있음

(3) 페이지/광고 등 **고급 권한**은 **App Review** 및 **Business Verification** 필요

#### 4) 모드 전환(개발 → 운영)

- 상단 또는 앱 설정 영역에서 **앱 모드: Development → Live**로 전환

#### 5) Live 전환 전 체크리스트

- [ ] Privacy Policy / Terms / Data Deletion URL 준비
- [ ] Valid OAuth Redirect URIs 정확 입력
- [ ] 불필요 권한 제거, 필요한 권한만 요청
- [ ] (필요 시) App Review/Business Verification 완료

#### 6) 워드프레스 설정 (SESLP)

(1) WP 관리자 → **SESLP 설정 → Facebook**

(2) **App ID / App Secret** 입력 → 저장

(3) 프론트에서 **Facebook 로그인 버튼**으로 실제 테스트

#### 7) 트러블슈팅

(1) **Can't Load URL / redirect_uri 에러**

→ `Valid OAuth Redirect URIs`에 **정확히 동일한 URI**가 등록되어 있는지 확인 (프로토콜, 서브도메인, 슬래시, 쿼리 스트링 포함)

(2) **email null**

→ 사용자가 Facebook에 이메일을 등록하지 않았거나 비공개인 경우. **id 기반 계정 연결 로직** 준비, 동의 화면에서 email 권한 용도 명확히 설명

(3) **권한 관련 오류**

→ 요청 스코프가 기본 범위를 넘어가면 **App Review/Business Verification** 필요

(4) **Live 전환 불가**

→ 정책/약관/데이터 삭제 지침 URL이 **누락/비공개**인 경우. 공개 URL 제공 필수

</details>

---

<details>
<summary><strong>LinkedIn (링크드인)</strong></summary>

> - **Redirect URI:** `https://{도메인}/?social_login=linkedin`
> - **필수 설정:** OpenID Connect(OIDC) 활성화
> - **권장 스코프:** `openid`, `profile`, `email`
> - LinkedIn은 기존 `r_liteprofile`, `r_emailaddress` 스코프를 **점진적 폐기**하고 있으며, 신규 앱은 **반드시 OIDC 표준 스코프**를 사용해야 합니다.

---

#### 1) 애플리케이션 생성

(1) **LinkedIn Developers 콘솔** 접속

→ [https://www.linkedin.com/developers/apps](https://www.linkedin.com/developers/apps)

(2) LinkedIn 계정으로 로그인

(3) **Create app** 클릭

(4) 필수 항목 입력

- **App name:** 서비스명 (예: `MySite LinkedIn Login`)
- **LinkedIn Page:** 회사 페이지 (없으면 “None” 선택)
- **App logo:** 100×100 이상 PNG/JPG
- **Privacy Policy URL:** 공개 접근 가능한 개인정보처리방침
- **Business Email:** 검증 가능한 이메일

(5) **Create app** 클릭 → 앱 생성 완료

> **Development Mode** 기본 적용 → `openid`, `profile`, `email` 기반 로그인 테스트 **즉시 가능** (게시 전까지 제한 없음)

#### 2) OpenID Connect(OIDC) 활성화

(1) 생성된 앱 페이지 → **Products** 탭 이동

(2) **Sign In with LinkedIn using OpenID Connect** 항목 찾기

(3) **Add product** 클릭 → 승인 후 OIDC 사용 가능

(4) **Auth 탭**에서 OIDC 관련 설정 확인 가능

> **OIDC 스코프 사용 필수**
>
> - `openid` → ID 토큰 발급
> - `profile` → 이름, 사진, 헤드라인 등
> - `email` → 이메일 주소

#### 3) OAuth 2.0 설정 (Auth 탭)

(1) **Auth → OAuth 2.0 settings** 이동

(2) **Redirect URLs** 섹션에 아래 URI 추가

→ `https://{도메인}/?social_login=linkedin`

(3) **정확한 일치** 확인 (프로토콜, 서브도메인, 슬래시, 쿼리 포함)

(4) 여러 환경 사용 시 각각 등록

- 로컬(Local): `https://localhost:3000/?social_login=linkedin`
- 스테이징(Staging): `https://staging.example.com/?social_login=linkedin`
- 운영(Production): `https://example.com/?social_login=linkedin`

(5) **Save** 클릭

#### 4) Client ID / Client Secret 확인 및 입력

(1) **Auth 탭** 상단에서 확인

- **Client ID**
- **Client Secret**

(2) 워드프레스 관리자 → **SESLP 설정 → LinkedIn** 탭

(3) 두 값 붙여넣기 → **저장**

(4) 프론트엔드에서 **LinkedIn 로그인 버튼**으로 실제 테스트

> **보안 주의:**
>
> - Client Secret은 절대 노출 금지
> - 필요 시 **Regenerate secret**으로 재발급

#### 5) 스코프(Scopes) 및 권한 설명

| 스코프    | 설명                                | 비고     |
| --------- | ----------------------------------- | -------- |
| `openid`  | OpenID Connect 표준 ID 토큰 반환    | **필수** |
| `profile` | 이름, 사진, 헤드라인 등 프로필 정보 | **필수** |
| `email`   | 이메일 주소 반환                    | **필수** |

> **구형 스코프 (`r_liteprofile`, `r_emailaddress`)**
>
> → **2024년 이후 점진적 폐기 예정**  
> → 신규 앱은 **사용 불가**, 기존 앱도 OIDC 전환 권장

#### 6) 트러블슈팅 및 유의사항

(1) **redirect_uri_mismatch**

→ 등록된 URI와 요청 URI가 **조금이라도 다를 경우** 발생  
 → 프로토콜, 서브도메인, 슬래시, 쿼리 **100% 일치** 확인

(2) **invalid_client**

→ Client ID/Secret 오타 또는 앱 비활성 상태  
 → 재확인 또는 **Regenerate secret**

(3) **email NULL**

→ 사용자 동의 거부 또는 `email` 스코프 누락  
 → 동의 화면에서 이메일 용도 명확히 설명

(4) **insufficient_scope**

→ 요청 스코프가 앱에 승인되지 않음  
 → OIDC 활성화 및 스코프 재확인

(5) **OIDC not enabled**

→ Products 탭에서 **OpenID Connect** 추가 안 함

> **로그 확인 경로:**
>
> - `/wp-content/SESLP-debug.log` (SESLP 디버그 ON 시)
> - `/wp-content/debug.log` (WP_DEBUG = true)

#### 7) 요약 체크리스트

- [ ] 앱 생성 완료 (`https://www.linkedin.com/developers/apps`)
- [ ] **Sign In with LinkedIn using OpenID Connect** 제품 추가
- [ ] Redirect URI 정확히 등록 (`https://{도메인}/?social_login=linkedin`)
- [ ] Client ID / Secret → SESLP 입력 및 저장
- [ ] 스코프: `openid profile email` (구형 스코프 사용 금지)
- [ ] HTTPS 환경에서 프론트엔드 로그인 테스트 완료

---

> **참고:**
>
> - SESLP는 **OIDC 인증 흐름**에 완벽 대응합니다.
> - 기존 OAuth 2.0 방식은 **더 이상 지원되지 않으며**,
> - 신규 개발 시 반드시 **OpenID Connect**를 사용하세요.

</details>

---

<details>
<summary><strong>Naver (네이버)</strong></summary>

> - **Redirect URI:** `https://{도메인}/?social_login=naver`
> - **권장 스코프:** 기본 프로필(`name`), 이메일(`email`)
> - 네이버는 **네아로(Naver Login)** API를 사용하며, **HTTPS 필수**

---

### 1) 애플리케이션 등록

(1) **네이버 개발자 센터** 접속

→ [https://developers.naver.com/apps/](https://developers.naver.com/apps/)

(2) 네이버 계정으로 로그인

(3) 상단 메뉴 **Application → 애플리케이션 등록** 클릭

(4) 필수 항목 입력

- **애플리케이션 이름:** 서비스명 (예: `MySite Naver Login`)
- **사용 API:** `네아로 (Naver Login)` 선택
- **환경 추가 → 웹(Web)** 클릭
  - **서비스 URL:** `https://example.com` (루트 도메인)
  - **Callback URL:** `https://example.com/?social_login=naver`

(5) 약관 동의 후 **등록** 클릭 → 앱 생성 완료

> **주의:**
>
> - **HTTPS 필수** → HTTP 등록 시 오류 발생
> - **서브도메인별 별도 등록 필요**  
>   예: `blog.example.com`은 `example.com`과 별개로 등록

#### 2) Client ID / Client Secret 확인

(1) 등록 후 **내 애플리케이션** 목록으로 이동

(2) 생성된 앱 클릭 → **Client ID**, **Client Secret** 확인

(3) 두 값을 복사

#### 3) 워드프레스(플러그인) 설정

(1) WP 관리자 → **SESLP 설정 → Naver** 탭

(2) **Client ID / Client Secret** 붙여넣기

(3) **Redirect URI**는 등록한 값과 **100% 일치** 확인: `https://{도메인}/?social_login=naver`

(4) **저장** → 프론트엔드에서 **Naver 로그인 버튼**으로 실 테스트

#### 4) 권한 및 정보 제공 설정

| 정보       | 스코프    | 비고          |
| ---------- | --------- | ------------- |
| 이름       | `name`    | 기본 제공     |
| 이메일     | `email`   | 기본 제공     |
| 성별, 생일 | 별도 요청 | **검수 필요** |

> - 사용자는 로그인 시 **동의 화면**에서 정보 제공 **동의/거부** 선택 가능
> - **이메일 거부 시 `email = null`** → 플러그인에서 **ID 기반 계정 연결 로직** 준비 필요
> - **민감 정보(성별, 생일 등)** 요청 시 **네이버 앱 검수** 필수

#### 5) 트러블슈팅 및 유의사항

(1) **Redirect URI mismatch 오류**

→ 콜백 URL이 콘솔 등록값과 **조금이라도 다를 경우** 발생  
 → 프로토콜, 서브도메인, 슬래시, 쿼리 **완전 일치** 확인

(2) **HTTPS 미사용 오류**

→ HTTP 등록 불가 → 반드시 HTTPS 사용

(3) **서브도메인 오류**

→ `sub.example.com`은 `example.com`과 별개 → 각각 등록

(4) **email NULL 문제**

→ 사용자 이메일 비공개 또는 거부 → **ID 기반 계정 연결** 권장

(5) **앱 검수 필요**

→ 기본 로그인(`name`, `email`)은 **검수 없이 사용 가능**  
 → 성별, 생일 등 추가 정보는 **검수 후 승인 필요**

> **로그 확인 경로:**
>
> - `/wp-content/SESLP-debug.log` (SESLP 디버그 ON 시)
> - `/wp-content/debug.log` (WP_DEBUG = true)

#### 6) 요약 체크리스트

- [ ] 네이버 개발자센터 앱 등록 완료
- [ ] **Callback URL** 정확히 등록
- [ ] **HTTPS** 환경 사용
- [ ] 서브도메인별 별도 등록 (필요 시)
- [ ] Client ID / Secret → SESLP 입력 및 저장
- [ ] 이메일 동의/거부 시 동작 테스트
- [ ] 프론트엔드 로그인 버튼으로 실제 테스트 완료

---

> **참고:**
>
> - SESLP는 **네아로(Naver Login)** 인증 흐름에 완벽 대응합니다.
> - 기본 로그인(`name`, `email`)은 **검수 없이 즉시 사용 가능**합니다.

</details>

---

<details>
<summary><strong>Kakao (카카오)</strong></summary>

> - **Redirect URI:** `https://{도메인}/?social_login=kakao`
> - **권장 스코프:** `profile_nickname`, `profile_image`, `account_email`
> - `account_email`은 **개인 실명 인증 또는 사업자 등록 완료 후** 사용 가능
> - **HTTPS 필수**, **Client Secret 활성화 필수**

---

### 1) 애플리케이션 생성

(1) **Kakao Developers** 접속

→ [https://developers.kakao.com/](https://developers.kakao.com/)

(2) 카카오 계정 로그인 → 상단 메뉴 **내 애플리케이션 → 애플리케이션 추가하기** 클릭

(3) 필수 정보 입력

- **앱 이름:** 서비스명 (예: `MySite Kakao Login`)
- **회사명:** 개인/사업자명
- **카테고리:** 웹/앱 선택
- **운영 정책 동의** 체크

(4) **저장** → 앱 생성 완료

#### 2) 카카오 로그인 활성화

(1) 왼쪽 메뉴 → **제품 설정 > 카카오 로그인**

(2) **카카오 로그인 활성화** 스위치를 **ON**

(3) **Redirect URI 등록** 섹션

- URI 입력: `https://{도메인}/?social_login=kakao`
- **저장** 클릭

(4) **도메인 일치 확인**: Redirect URI의 도메인 부분이 **플랫폼 등록 도메인과 100% 일치**해야 함

#### 3) 사용자 동의 항목(스코프) 설정

(1) 왼쪽 메뉴 → **동의 항목**

(2) 아래 항목 추가 및 설정

| 스코프             | 설명          | 동의 방식     | 비고                      |
| ------------------ | ------------- | ------------- | ------------------------- |
| `profile_nickname` | 닉네임        | 필수/선택     | 기본                      |
| `profile_image`    | 프로필 이미지 | 필수/선택     | 기본                      |
| `account_email`    | 이메일 주소   | **선택 동의** | **실명/사업자 인증 필요** |

(3) 각 항목에 **동의 목적** 명확히 기재 (예: “회원 식별 및 로그인”)

(4) **저장**

> **주의:**
>
> - `account_email`, `birthyear`, `phone_number` 등 민감 정보는  
>   **실명 인증 또는 사업자 등록 완료 후** 활성화 가능
> - 검수 없이 사용 불가

#### 4) 웹 플랫폼 도메인 등록

(1) 왼쪽 메뉴 → **앱 설정 > 플랫폼**

(2) **웹 플랫폼 등록** 클릭

(3) **사이트 도메인** 입력: `https://{도메인}`

- 예: `https://example.com`

(4) **저장** → **Redirect URI 도메인과 정확히 일치**해야 인증 성공

#### 5) 보안 설정 – Client Secret 생성 및 활성화

(1) 왼쪽 메뉴 → **제품 설정 > 보안**

(2) **Client Secret 사용** → **ON**

(3) **Secret 생성** 클릭 → 생성된 값 **복사**

(4) **활성화 상태** → **사용 중**으로 변경

(5) **저장**

> **생성만 하고 활성화 안 하면 `invalid_client` 오류 발생**

#### 6) REST API 키 확인 (Client ID)

(1) 왼쪽 메뉴 → **앱 키**

(2) **REST API 키** 값 복사 → SESLP 플러그인에서 **Client ID**로 사용

#### 7) 워드프레스(플러그인) 설정

(1) WP 관리자 → **SESLP 설정 → Kakao** 탭

(2) **Client ID** ← REST API 키  
 **Client Secret** ← 생성한 Secret 값

(3) **저장**

(4) 프론트엔드에서 **카카오 로그인 버튼**으로 **실제 로그인 테스트**

#### 8) 트러블슈팅 및 유의사항

(1) **redirect_uri_mismatch**

→ 등록된 URI와 요청 URI가 **조금이라도 다를 경우** 발생  
 → 프로토콜, 서브도메인, 슬래시, 쿼리 **100% 일치** 확인

(2) **invalid_client**

→ Client Secret **생성 후 활성화 안 함** 또는 오타

(3) **email NULL**

→ 사용자가 이메일 제공 **거부** 또는 **실명/사업자 인증 미완료**

(4) **도메인 불일치**

→ 플랫폼 도메인 ≠ Redirect URI 도메인 → 인증 실패

(5) **HTTPS 미사용**

→ **반드시 HTTPS** 사용 → HTTP 불가

> **로그 확인 경로:**
>
> - `/wp-content/SESLP-debug.log` (SESLP 디버그 ON)
> - `/wp-content/debug.log` (WP_DEBUG = true)

### 9) 요약 체크리스트

- [ ] 카카오 로그인 **활성화**
- [ ] Redirect URI 정확히 등록 (`https://{도메인}/?social_login=kakao`)
- [ ] 웹 플랫폼 도메인 등록 (`https://{도메인}`)
- [ ] 동의 항목 설정 (`profile_nickname`, `profile_image`, `account_email`)
- [ ] Client Secret **생성 + 활성화**
- [ ] REST API 키 → Client ID, Secret → SESLP 입력
- [ ] **HTTPS 환경**에서 프론트엔드 로그인 테스트 완료

---

> **참고:**
>
> - SESLP는 **카카오 로그인 OAuth 2.0 + Client Secret** 흐름에 완벽 대응합니다.
> - 기본 로그인(`nickname`, `image`)은 **검수 없이 즉시 사용 가능**합니다.

</details>

---

<details>
<summary><strong>LINE (라인)</strong></summary>

> - **Redirect URI:** `https://{도메인}/?social_login=line`
> - **필수 설정:** OpenID Connect 활성화, **Email address permission 신청 및 승인**
> - **권장 스코프:** `openid`, `profile`, `email`
> - **HTTPS 필수**, 이메일 수집 시 **신청 → 승인 절차 반드시 필요**

---

### 1) 제공자(Provider) 및 채널 생성

(1) **LINE Developers 콘솔** 접속

→ [https://developers.line.biz/console/](https://developers.line.biz/console/)

(2) **LINE 비즈니스 계정**으로 로그인 (개인 계정 불가)

(3) **새로운 제공자 만들기** 클릭 → 이름 입력 → **Create**

(4) 생성된 제공자 아래 → **Channels** 탭

(5) **Create a LINE Login channel** 선택

(6) 설정 입력

- **Channel type:** `LINE Login` 확인
- **Provider:** 위에서 생성한 제공자 선택
- **Region:** 서비스 대상 국가 (예: `South Korea`, `Japan`)
- **Channel name / description / icon:** 사용자 동의 화면에 표시됨

(7) 약관 동의 → **Create**

#### 2) OpenID Connect 활성화 및 이메일 권한 신청

(1) 생성된 채널 페이지 → 왼쪽 메뉴 **OpenID Connect**

(2) **Email address permission** 항목 옆 **Apply** 클릭

(3) 신청서 작성

- **Privacy Policy URL** 입력 (공개 접근 가능해야 함)
- **Privacy Policy 스크린샷** 업로드
- 동의 체크 후 **Submit**

(4) **승인 완료 전까지 `email` 스코프 사용 불가**

→ 승인 후 `email` 정상 반환

> **승인 소요 시간:** 보통 1~3일 (영업일 기준)

#### 3) Callback URL 등록 및 채널 게시(Publish)

(1) 왼쪽 메뉴 → **LINE Login**

(2) **Callback URL** 필드에 입력

→ `https://{도메인}/?social_login=line`

(3) **정확한 일치 필수**

- 프로토콜: `https://` (HTTP 불가)
- 도메인, 경로, 쿼리스트링까지 **100% 일치**

(4) **Save** 클릭

(5) 채널 상태를 **Published (게시됨)** 으로 변경

- **Development 상태에서는 테스트만 가능**
- **Published 되어야 실제 서비스 적용**

#### 4) Channel ID / Channel Secret 확인

(1) 채널 페이지 상단 또는 **Basic settings** 탭

(2) **Channel ID** → SESLP **Client ID**

(3) **Channel Secret** → SESLP **Client Secret**

#### 5) 워드프레스(플러그인) 설정

(1) WP 관리자 → **SESLP 설정 → LINE** 탭

(2) **Client ID** ← Channel ID  
 **Client Secret** ← Channel Secret

(3) **저장**

(4) 프론트엔드에서 **LINE 로그인 버튼**으로 **실제 테스트**

### 6) 트러블슈팅 및 유의사항

(1) **redirect_uri_mismatch**

→ Callback URL이 콘솔 등록값과 **조금이라도 다를 경우** 발생  
 → `https://`, 도메인, 쿼리 `?social_login=line` **완전 일치** 확인

(2) **invalid_client** → Channel Secret 오타 또는 **Published 안 됨**

(3) **email NULL**

→ **이메일 권한 신청 미승인** 또는 사용자 거부  
 → 승인 여부는 LINE Developers 콘솔에서 확인

(4) **HTTP 사용 불가**

→ **반드시 HTTPS** → 로컬 테스트 시 `https://localhost`도 허용됨

(5) **Development 모드 제한**

→ **Published 전까지는 테스트 계정만 로그인 가능**

> **로그 확인 경로:**
>
> - `/wp-content/SESLP-debug.log` (SESLP 디버그 ON)
> - `/wp-content/debug.log` (WP_DEBUG = true)

#### 7) 요약 체크리스트

- [ ] LINE 비즈니스 계정으로 **제공자 + LINE Login 채널 생성**
- [ ] **Email address permission 신청 및 승인 완료**
- [ ] **Callback URL** 정확히 등록 (`https://{도메인}/?social_login=line`)
- [ ] **HTTPS 사용**, **Published 상태로 전환**
- [ ] Channel ID / Secret → SESLP 입력 및 저장
- [ ] 프론트엔드에서 **실제 로그인 테스트 완료**

> **참고:**
>
> - SESLP는 **LINE Login v2.1 + OpenID Connect** 흐름에 완벽 대응합니다.
> - 이메일 수집 시 **반드시 사전 승인** 필요합니다.

</details>
