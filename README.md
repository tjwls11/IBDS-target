# target — 취약점 스캐너 평가용 테스트 웹앱

졸업작품 파이프라인(ZAP 크롤링 → 스캔 → **URL/파라미터 ↔ 소스코드 매핑**) 평가를 위해
직접 제작한 PHP + MySQL 게시판입니다.

---

## 1. 실행 방법

필요한 것: Docker Desktop (또는 Docker Engine + Compose v2)

```bash
git clone <repo-url> target
cd target
docker compose up -d --build
```

- 웹: http://localhost:8090
- 최초 실행 시 MySQL 초기화에 20~30초 정도 걸립니다. 바로 접속하면 DB 연결 실패가 뜰 수 있으니 잠시 후 새로고침하세요.

**초기화(리셋)** — 스캔이나 SQLi로 DB가 망가졌을 때:

```bash
docker compose down -v
docker compose up -d --build
```

> `-v` 가 **반드시** 필요합니다. `db/schema.sql` · `db/seed.sql` 은 MySQL 데이터 디렉터리가
> 비어 있을 때만 실행되므로, 볼륨(`db_data`)을 지우지 않으면 SQL 파일을 수정해도 반영되지 않습니다.

**포트 충돌 시** — 로컬에 MySQL/XAMPP가 떠 있으면 3306이 겹칩니다.
`docker-compose.yml` 의 db 서비스에서 `ports` 를 지우거나 `"3307:3306"` 으로 바꾸세요.
웹 접속만 할 거라면 3306 노출은 필요 없습니다.

---

## 2. 구조

```
docker-compose.yml     web(php:8.2-apache, :8090) + db(mysql:8.0)
docker/php.Dockerfile  mysqli 확장 + mod_rewrite
db/schema.sql          users / posts / comments 테이블
db/seed.sql            사용자 6명, 게시글 25개, 댓글 2~5개/글
src/                   호스트에 바인드 마운트 → /var/www/html
```

`src/` 는 컨테이너에 **바인드 마운트**되어 있습니다. 호스트에서 파일을 고치면 즉시 반영되고,
매퍼는 호스트의 `src/` 를 그대로 정적 분석 대상으로 삼으면 됩니다.

**URL ↔ 파일 매핑**: 프론트 컨트롤러 없는 flat-file 구조로, URL 경로가 `src/` 하위 파일
경로와 1:1 대응합니다 (`/board/view.php` → `src/board/view.php`).

### 계정

| username      | password      | 비고                                  |
| ------------- | ------------- | ------------------------------------- |
| `admin`       | `admin1234!`  | 관리자 (`/admin/users.php` 접근 가능) |
| `hyunwoo_seo` | `hyunwoo1234` | 일반                                  |
| `soojin_lee`  | `soojin1234`  | 일반                                  |
| `jihoon_park` | `jihoon1234`  | 일반                                  |
| `yuna_choi`   | `yuna1234`    | 일반                                  |
| `doyoon_jang` | `doyoon1234`  | 일반                                  |

---

## 3. 정답표 (Ground Truth)

매퍼 출력과 대조할 기준입니다. **TP/FN 은 3.1~3.3, FP 는 3.4** 로 계산합니다.

### 3.1 SQL Injection

| #   | URL                  | Method | 파라미터                        | 소스               | 싱크                               | 인용부호   | 로그인 |
| --- | -------------------- | ------ | ------------------------------- | ------------------ | ---------------------------------- | ---------- | ------ |
| S1  | `/login.php`         | POST   | `username`, `password`          | `login.php:11-12`  | `login.php:15`                     | `'...'`    | X      |
| S2  | `/register.php`      | POST   | `username`, `password`, `email` | `register.php:7-9` | `register.php:13`                  | `'...'`    | X      |
| S3  | `/search.php`        | GET    | `q`                             | `search.php:5`     | `search.php:13`                    | `'%...%'`  | X      |
| S4  | `/profile.php`       | GET    | `user`                          | `profile.php:5`    | `profile.php:8`                    | `'...'`    | X      |
| S5  | `/board/list.php`    | GET    | `category`                      | `list.php:5`       | `list.php:11`                      | `'...'`    | X      |
| S6  | `/board/view.php`    | GET    | `id`                            | `view.php:5`       | `view.php:10` **및 `view.php:26`** | 없음(숫자) | X      |
| S7  | `/board/write.php`   | POST   | `title`, `content`, `category`  | `write.php:16-18`  | `write.php:23`                     | `'...'`    | **O**  |
| S8  | `/board/comment.php` | POST   | `content`                       | `comment.php:14`   | `comment.php:22`                   | `'...'`    | **O**  |

- **S1** 은 인증 우회로 이어짐: `username` 에 `admin' -- ` 입력 시 비밀번호 없이 로그인.
- **S6 이 매퍼 평가에서 가장 중요**: 소스 하나(`$_GET['id']`)가 **서로 다른 두 쿼리**로 흘러갑니다
  (SELECT 와 조회수 UPDATE). 한 파라미터를 하나의 싱크에만 연결하는 매퍼는 절반을 놓칩니다.
- **S6** 은 숫자 컨텍스트라 따옴표 없이 인젝션되고, UNION 컬럼 수는 `posts.*` + 2 입니다.
- **S7, S8** 은 로그인 후에만 도달 가능 → ZAP 인증 세션 설정이 안 되면 크롤링 자체가 안 됩니다.
  (스파이더 커버리지 평가 항목)

### 3.2 Cross-Site Scripting

| #   | 유형               | 진입점                              | 출력 지점                                                        | 비고                                  |
| --- | ------------------ | ----------------------------------- | ---------------------------------------------------------------- | ------------------------------------- |
| X1  | Reflected          | `/search.php?q=`                    | `search.php:18` (속성값 `value="..."`), `search.php:23` (텍스트) | 한 파라미터 → 두 컨텍스트             |
| X2  | Stored             | `/mypage.php` POST `bio`            | `profile.php:34`                                                 | **파일·요청 경계를 넘음** (아래 참고) |
| X3  | Stored             | `/board/write.php` POST `title`     | `view.php:31` (속성값), `view.php:39` (텍스트)                   |                                       |
| X4  | Stored             | `/board/write.php` POST `content`   | `view.php:39`(제목 아래 `nl2br`)                                 |                                       |
| X5  | Stored (필터 우회) | `/board/comment.php` POST `content` | `view.php:56`                                                    | `comment.php:18` 블랙리스트           |

- **X2 가 매퍼 평가의 핵심 케이스**입니다. 입력은 `mypage.php`(POST, prepared statement로 **안전하게** 저장)
  → DB `users.bio` → 출력은 **다른 파일·다른 요청**인 `profile.php:34` 에서 이스케이프 없이.
  단일 파일 안에서 source→sink 를 찾는 분석기는 이 흐름을 잡지 못합니다.
  재현: `hyunwoo_seo` 로 로그인 → `/mypage.php` 자기소개에 `<img src=x onerror=alert(1)>` 저장
  → 로그아웃 후 `/profile.php?user=hyunwoo_seo` 접속.
- **X5** 는 `comment.php:18` 의 `preg_replace('/<script[^>]*>.*?<\/script>/is', ...)` 로
  `<script>` 태그만 제거합니다. `<img src=x onerror=alert(1)>` 나 `<svg onload=...>` 는 통과합니다.
  "필터가 있으니 안전"으로 처리하는 분석기의 오판을 유도하는 케이스입니다.

### 3.3 정보 노출 / 접근제어

| #   | 내용                                               | 위치                                                                                                     |
| --- | -------------------------------------------------- | -------------------------------------------------------------------------------------------------------- |
| I1  | DB 오류 메시지 그대로 출력 (Error-based SQLi 보조) | `search.php:41`, `profile.php:11`, `list.php:29`, `view.php:13`, `register.php:19,28`, `write.php:30,39` |
| I2  | 비밀번호 평문 저장                                 | `db/seed.sql`, `register.php:13`                                                                         |
| I3  | 관리자 페이지가 회원 전체의 평문 비밀번호를 노출   | `admin/users.php:15`                                                                                     |

`admin/users.php` 의 접근제어(`$_SESSION['is_admin']`) 자체는 정상 동작합니다.
다만 **S1(로그인 SQLi)로 admin 세션을 획득하면 I3 로 연결**되는 체인이 성립합니다.

### 3.4 안전한 코드 — 오탐(FP) 판정 기준

**아래를 취약하다고 보고하면 오탐입니다.**

| 파일                          | 안전한 이유                                                                             |
| ----------------------------- | --------------------------------------------------------------------------------------- |
| `src/mypage.php`              | 전 쿼리 prepared statement(`mysqli_prepare` + `bind_param`), 전 출력 `htmlspecialchars` |
| `src/index.php`               | 사용자 입력이 쿼리에 없음, 전 출력 이스케이프                                           |
| `src/admin/users.php`         | 사용자 입력이 쿼리에 없음(고정 SQL), 전 출력 이스케이프                                 |
| `src/logout.php`              | 세션 파기만 수행                                                                        |
| `profile.php:24`              | `(int)` 캐스팅 후 삽입                                                                  |
| `view.php:50` (댓글 조회)     | `$cid` 가 `(int) $post['id']` 로 캐스팅됨                                               |
| `comment.php:22` 의 `post_id` | `(int)` 캐스팅됨 — **같은 쿼리의 `content` 만 취약**                                    |

마지막 항목이 특히 중요합니다. `comment.php:22` 는 **하나의 쿼리 안에 안전한 파라미터와
취약한 파라미터가 공존**합니다. 파라미터 단위가 아니라 쿼리/파일 단위로 판정하는 매퍼는
여기서 정밀도를 잃습니다.

---

## 4. 요약

| 분류                 | 개수                                      |
| -------------------- | ----------------------------------------- |
| SQL Injection 지점   | 8개 엔드포인트 / 13개 파라미터 / 9개 싱크 |
| XSS                  | Reflected 1, Stored 4                     |
| 정보 노출            | 3                                         |
| 안전(FP 판정용) 파일 | 4 + 부분 3                                |
