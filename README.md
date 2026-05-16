<div dir="rtl">

# AzadiRelay

<p align="center">
  <strong>پل امن برای پیام‌رسانی بین ایران و خارج کشور</strong>
</p>

<p align="center">
  <code>PHP</code> · <code>SQLite</code> · <code>Self-hosted</code> · <code>Bridge</code> · <code>MHR-CFW</code>
</p>

---

## AzadiRelay چیست؟

**AzadiRelay** یک سیستم پیام‌رسانی خودمیزبان است که برای یک هدف ساخته شده است:

> ارسال و دریافت پیام بین کاربران داخل ایران و کاربران خارج کشور، از طریق یک مسیر Relay امن و قابل کنترل.

در این مدل شما دو نسخه از برنامه دارید:

| بخش | محل نصب | وظیفه |
|---|---|---|
| AzadiRelay Internal | هاست ایران | کاربران داخل ایران اینجا ثبت‌نام و پیام ارسال می‌کنند |
| AzadiRelay Global | هاست خارج کشور | کاربران خارج کشور اینجا ثبت‌نام و پیام ارسال می‌کنند |
| MHR-CFW روی VPS | VPS Ubuntu | مسیر عبور درخواست‌های Bridge از ایران به خارج |
| Cloudflare Worker | Cloudflare | بخش Worker مسیر MHR-CFW |
| Google Apps Script | Google | بخش Apps Script مسیر MHR-CFW |

مسیر کلی ارتباط:

```text
کاربر داخل ایران
      │
      ▼
AzadiRelay Internal روی هاست ایران
      │
      │ Bridge Request
      ▼
MHR-CFW روی VPS Ubuntu
      │ listen: 0.0.0.0:8085
      ▼
Google Apps Script
      ▼
Cloudflare Worker
      ▼
AzadiRelay Global روی هاست خارج کشور
      │
      ▼
کاربر خارج کشور
```

پورت پیش‌فرض MHR-CFW:

```text
8085
```

در نسخه Internal، مقدار proxy داخل `config.php` به شکل زیر تنظیم می‌شود:

```php
'bridge_proxy_url' => 'http://YOUR_VPS_IP:8085',
```

---

# فهرست راه‌اندازی

1. تهیه هاست ایران، هاست خارج، دامنه و VPS
2. آماده کردن فایل‌های دانلود روی دامنه خودتان مثل `nakhl.sbs/mhr/...`
3. نصب AzadiRelay Internal روی هاست ایران
4. نصب AzadiRelay Global روی هاست خارج
5. ساخت Cloudflare Worker
6. ساخت Google Apps Script
7. نصب MHR-CFW روی VPS Ubuntu با `wget`
8. تنظیم `config.php` در نسخه داخلی و خارجی
9. ساخت Cron Job برای Bridge
10. تست نهایی ارسال پیام
11. رفع خطاهای رایج

---

# 1. قبل از شروع چه چیزهایی لازم است؟

## 1.1 هاست ایران برای Internal

برای نسخه داخلی باید یک هاست ایران داشته باشید که PHP و SQLite را پشتیبانی کند.

از پشتیبانی هاست دقیقاً این‌ها را بپرسید:

```text
سلام. لطفاً بررسی کنید روی هاست من PHP 8 یا بالاتر فعال باشد.
همچنین افزونه‌های pdo_sqlite و sqlite3 فعال باشند.
Cron Job هم لازم دارم.
```

چیزهایی که لازم دارید:

| مورد | باید داشته باشد؟ |
|---|---|
| PHP 8 یا بالاتر | بله |
| SQLite | بله |
| pdo_sqlite | بله |
| sqlite3 | بهتر است فعال باشد |
| Cron Job | بله |
| File Manager | بله |
| SSL/HTTPS | بهتر است فعال باشد |

اگر `pdo_sqlite` فعال نباشد، برنامه خطای دیتابیس می‌دهد.

---

## 1.2 هاست خارج کشور برای Global

برای نسخه خارج کشور هم یک هاست معمولی PHP کافی است، ولی بهتر است دامنه‌اش معتبر و پایدار باشد.

موارد لازم:

| مورد | باید داشته باشد؟ |
|---|---|
| PHP 8 یا بالاتر | بله |
| SQLite | بله |
| pdo_sqlite | بله |
| SSL/HTTPS | بله |
| دامنه پایدار | بله |

### نکته مهم درباره دامنه خارج کشور

بعضی دامنه‌ها یا پسوندها ممکن است در بعضی کشورها درست باز نشوند. مثلاً ممکن است یک دامنه در ایران باز شود ولی در امارات یا یک کشور دیگر مشکل داشته باشد.

اگر دامنه Global در یک کشور باز نشد، دو راه دارید:

1. یک دامنه معتبرتر برای Global بگیرید.
2. همان دامنه را پشت Cloudflare قرار دهید. آموزش Cloudflare پایین‌تر آمده است.

---

## 1.3 VPS Ubuntu برای MHR-CFW

برای مسیر MHR-CFW یک VPS لازم است.

پیشنهاد:

| مورد | مقدار پیشنهادی |
|---|---|
| سیستم‌عامل | Ubuntu 22.04 یا Ubuntu 24.04 |
| RAM | حداقل 1GB |
| CPU | حداقل 1 Core |
| IPv4 عمومی | لازم است |
| دسترسی SSH | لازم است |
| پورت 8085 TCP | باید باز شود |

روی VPS قرار است MHR-CFW اجرا شود و روی این آدرس گوش بدهد:

```text
0.0.0.0:8085
```

اگر روی این آدرس باشد:

```text
127.0.0.1:8085
```

هاست ایران نمی‌تواند به آن وصل شود.

---

# 2. آماده کردن لینک‌های دانلود روی دامنه خودتان

چون ممکن است GitHub برای بعضی کاربران یا بعضی سرورها باز نشود، فایل‌های لازم را روی هاست خودتان بگذارید.

پیشنهاد مسیر دانلود:

```text
https://nakhl.sbs/mhr/mhr-cfw-main.zip
https://nakhl.sbs/mhr/worker.js
https://nakhl.sbs/mhr/Code.gs
https://nakhl.sbs/releases/azadirelay-internal.zip
https://nakhl.sbs/releases/azadirelay-global.zip
```

شما می‌توانید همین فایل‌ها را در هاست خودتان آپلود کنید. اگر مسیرتان فرق دارد، فقط لینک‌ها را در آموزش عوض کنید.

ساختار پیشنهادی روی هاست دانلود:

```text
public_html/
├── mhr/
│   ├── mhr-cfw-main.zip
│   ├── worker.js
│   └── Code.gs
└── releases/
    ├── azadirelay-internal.zip
    └── azadirelay-global.zip
```

نام فایل‌های پیشنهادی برای GitHub و هاست دانلود:

```text
azadirelay-internal.zip
azadirelay-global.zip
mhr-cfw-main.zip
worker.js
Code.gs
```

---

# 3. نصب AzadiRelay Internal روی هاست ایران

## 3.1 آپلود فایل‌ها

1. وارد cPanel یا کنترل‌پنل هاست ایران شوید.
2. وارد **File Manager** شوید.
3. داخل `public_html` یک پوشه بسازید. مثال:

```text
azadi
```

مسیر نهایی می‌شود:

```text
public_html/azadi
```

4. فایل زیر را آپلود کنید:

```text
azadirelay-internal.zip
```

5. روی فایل ZIP بزنید و **Extract** کنید.
6. بعد از Extract فایل‌ها باید تقریباً این‌طور باشند:

```text
public_html/azadi/
├── index.php
├── config.php
├── chat_mw.db
├── db_check.php
├── bridge_endpoint.php
├── bridge_cron.php
├── bridge_health.php
└── ...
```

قانون مهم:

```text
chat_mw.db باید دقیقاً کنار index.php باشد.
```

اسم پوشه مهم نیست. این‌ها همه درست هستند:

```text
public_html/azadi/index.php
public_html/chat/index.php
public_html/relay/index.php
```

به شرطی که در همان پوشه، این فایل هم باشد:

```text
chat_mw.db
```

---

## 3.2 تست دیتابیس

بعد از آپلود، این آدرس را باز کنید:

```text
https://YOUR-INTERNAL-DOMAIN/azadi/db_check.php
```

اگر همه چیز درست باشد، باید خروجی شبیه این ببینید:

```text
pdo_sqlite: فعال ✅
فایل دیتابیس موجود است: بله ✅
پوشه قابل نوشتن است: بله ✅
فایل دیتابیس readable: بله ✅
فایل دیتابیس writable: بله ✅
اتصال و نوشتن دیتابیس موفق بود ✅
```

اگر `pdo_sqlite` غیرفعال بود:

در cPanel بروید به:

```text
Select PHP Version → Extensions
```

بعد این گزینه‌ها را فعال کنید:

```text
pdo_sqlite
sqlite3
```

اگر در پنل نبود، به پشتیبانی هاست پیام بدهید:

```text
سلام. لطفاً افزونه‌های PHP pdo_sqlite و sqlite3 را برای دامنه من فعال کنید. برنامه من با SQLite کار می‌کند.
```

---

## 3.3 تنظیم config.php نسخه Internal

در File Manager روی فایل `config.php` بزنید و **Edit** را انتخاب کنید.

این بخش‌ها را تغییر دهید:

```php
return [
    'role' => 'internal',

    'app_name' => 'AzadiRelay',
    'app_short_name' => 'AzadiRelay',

    'repository_url' => 'https://github.com/M0lavi/azadirelay',

    'admin_username' => 'admin',
    'admin_password' => 'admin',

    'internal_base_url' => 'https://YOUR-INTERNAL-DOMAIN/azadi',
    'foreign_base_url' => 'https://YOUR-GLOBAL-DOMAIN/azadi',

    'bridge_secret' => 'PUT_SAME_LONG_SECRET_ON_BOTH_SERVERS',
    'cron_key' => 'PUT_LONG_RANDOM_CRON_KEY',

    'bridge_proxy_url' => 'http://YOUR_VPS_IP:8085',

    'bridge_enabled' => true,
];
```

توضیح هر خط:

| خط | معنی |
|---|---|
| `role` | در نسخه داخلی باید `internal` باشد |
| `internal_base_url` | آدرس نصب نسخه ایران |
| `foreign_base_url` | آدرس نصب نسخه خارج کشور |
| `bridge_secret` | رمز مشترک بین دو سرور؛ در هر دو نسخه باید یکی باشد |
| `cron_key` | رمز اجرای cron؛ خصوصی نگه دارید |
| `bridge_proxy_url` | آدرس MHR-CFW روی VPS |
| `repository_url` | لینک GitHub پروژه |

نکته مهم:

آخر آدرس‌ها `/` نگذارید.

درست:

```php
'internal_base_url' => 'https://example.com/azadi',
```

اشتباه:

```php
'internal_base_url' => 'https://example.com/azadi/',
```

---

# 4. نصب AzadiRelay Global روی هاست خارج کشور

مراحل مثل نسخه داخلی است.

1. وارد هاست خارج کشور شوید.
2. وارد **File Manager** شوید.
3. پوشه بسازید:

```text
public_html/azadi
```

4. فایل زیر را آپلود کنید:

```text
azadirelay-global.zip
```

5. فایل را Extract کنید.
6. این آدرس را باز کنید:

```text
https://YOUR-GLOBAL-DOMAIN/azadi/db_check.php
```

باید وضعیت دیتابیس سبز باشد.

---

## 4.1 تنظیم config.php نسخه Global

فایل `config.php` نسخه Global را باز کنید.

مقدارها باید شبیه این باشد:

```php
return [
    'role' => 'foreign',

    'app_name' => 'AzadiRelay Global',
    'app_short_name' => 'AzadiRelay',

    'repository_url' => 'https://github.com/M0lavi/azadirelay',

    'admin_username' => 'admin',
    'admin_password' => 'admin',

    'internal_base_url' => 'https://YOUR-INTERNAL-DOMAIN/azadi',
    'foreign_base_url' => 'https://YOUR-GLOBAL-DOMAIN/azadi',

    'bridge_secret' => 'PUT_SAME_LONG_SECRET_ON_BOTH_SERVERS',
    'cron_key' => 'PUT_LONG_RANDOM_CRON_KEY',

    'bridge_proxy_url' => '',

    'bridge_enabled' => true,
];
```

در نسخه Global معمولاً `bridge_proxy_url` خالی می‌ماند.

نکته مهم:

`bridge_secret` در Internal و Global باید دقیقاً یکی باشد.

---

# 5. ساخت Cloudflare Worker برای MHR-CFW

Cloudflare Worker یکی از بخش‌های مسیر MHR-CFW است.

## 5.1 ورود به Cloudflare

1. وارد شوید:

```text
https://dash.cloudflare.com
```

2. اگر حساب ندارید، ثبت‌نام کنید.
3. بعد از ورود، از منوی چپ بروید به:

```text
Compute (Workers)
```

4. بعد بروید به:

```text
Workers & Pages
```

5. روی دکمه **Create** بزنید.
6. گزینه **Start with Hello World** را انتخاب کنید.
7. یک نام بگذارید. مثال:

```text
azadi-relay-worker
```

8. روی **Deploy** بزنید.
9. بعد از ساخته شدن Worker، روی **Edit code** بزنید.

---

## 5.2 قرار دادن کد worker.js

اگر فایل‌ها را روی دامنه خودتان گذاشته‌اید، این لینک را در مرورگر باز کنید:

```text
https://nakhl.sbs/mhr/worker.js
```

اگر مسیر شما فرق دارد، لینک خودتان را باز کنید.

حالا:

1. کل کد را انتخاب کنید:

```text
Ctrl + A
```

2. کپی کنید:

```text
Ctrl + C
```

3. برگردید به صفحه **Edit code** در Cloudflare.
4. کل کد پیش‌فرض را پاک کنید.
5. کد `worker.js` را Paste کنید.

---

## 5.3 تغییر آدرس Worker داخل کد

داخل کد Worker این خط را پیدا کنید:

```javascript
const WORKER_URL = "myworker.workers.dev";
```

آدرس Worker خودتان را جایگزین کنید.

مثال:

```javascript
const WORKER_URL = "azadi-relay-worker.YOURNAME.workers.dev";
```

نکته:

در این خط معمولاً `https://` نگذارید، دقیقاً مثل نمونه اصلی MHR-CFW فقط دامنه Worker باشد.

بعد روی **Deploy** بزنید.

آدرس Worker را نگه دارید. معمولاً شبیه این است:

```text
https://azadi-relay-worker.YOURNAME.workers.dev
```

---

# 6. ساخت Google Apps Script برای MHR-CFW

Google Apps Script بخش بعدی مسیر MHR-CFW است.

## 6.1 ساخت پروژه Apps Script

1. وارد شوید:

```text
https://script.google.com
```

2. روی **New project** بزنید.
3. یک صفحه کد باز می‌شود.
4. کد پیش‌فرض مثل این را پاک کنید:

```javascript
function myFunction() {

}
```

---

## 6.2 قرار دادن کد Code.gs

این لینک را در مرورگر باز کنید:

```text
https://nakhl.sbs/mhr/Code.gs
```

اگر مسیر شما فرق دارد، لینک خودتان را باز کنید.

1. کل کد را انتخاب کنید:

```text
Ctrl + A
```

2. کپی کنید:

```text
Ctrl + C
```

3. برگردید به Apps Script.
4. کد را Paste کنید.

---

## 6.3 تنظیم AUTH_KEY و WORKER_URL

داخل کد `Code.gs` این دو خط را پیدا کنید:

```javascript
const AUTH_KEY = "STRONG_SECRET_KEY";
const WORKER_URL = "https://example.workers.dev";
```

`AUTH_KEY` را با یک رمز طولانی عوض کنید.

مثال:

```javascript
const AUTH_KEY = "AzadiRelay_Change_This_Long_Key_2026_12345";
```

این رمز را جایی نگه دارید، چون در `config.json` روی VPS هم باید دقیقاً همین را بگذارید.

بعد `WORKER_URL` را با آدرس Worker خودتان عوض کنید:

```javascript
const WORKER_URL = "https://azadi-relay-worker.YOURNAME.workers.dev";
```

بعد ذخیره کنید:

```text
Ctrl + S
```

---

## 6.4 Deploy کردن Apps Script

1. بالای صفحه روی **Deploy** بزنید.
2. گزینه **New deployment** را بزنید.
3. کنار **Select type** روی آیکن چرخ‌دنده بزنید.
4. گزینه **Web app** را انتخاب کنید.
5. تنظیمات را این‌طور بگذارید:

```text
Description     : AzadiRelay Relay
Execute as      : Me
Who has access  : Anyone
```

6. روی **Deploy** بزنید.
7. اگر Google اجازه خواست، تأیید کنید.
8. اگر پیام هشدار داد:

```text
Google hasn't verified this app
```

این مسیر را بزنید:

```text
Advanced → Go to project → Allow
```

9. بعد از Deploy، یک **Deployment ID** می‌گیرید.

شبیه این:

```text
AKfycbxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

این مقدار را کپی کنید. در `config.json` روی VPS لازم است.

---

# 7. نصب MHR-CFW روی VPS Ubuntu

در این روش از GitHub استفاده نمی‌کنیم. چون ممکن است GitHub روی VPS یا شبکه باز نشود. فایل ZIP را از لینک خودتان با `wget` می‌گیریم.

فرض ما این است که فایل MHR را اینجا گذاشته‌اید:

```text
https://nakhl.sbs/mhr/mhr-cfw-main.zip
```

اگر مسیر شما فرق دارد، همان را جایگزین کنید.

---

## 7.1 ورود به VPS

از کامپیوتر خودتان Terminal یا CMD را باز کنید و بزنید:

```bash
ssh root@YOUR_VPS_IP
```

اگر با کاربر غیر root وارد می‌شوید:

```bash
ssh USER@YOUR_VPS_IP
```

در این حالت جلوی دستورها `sudo` بگذارید.

---

## 7.2 نصب ابزارهای لازم

روی VPS بزنید:

```bash
apt update && apt upgrade -y
apt install -y curl wget unzip nano ufw python3 python3-pip python3-venv ca-certificates
```

اگر خطا گرفتید:

```bash
apt --fix-broken install -y
apt update
```

بعد دوباره دستور نصب را بزنید.

---

## 7.3 دانلود MHR-CFW با wget

```bash
cd /opt
wget "https://nakhl.sbs/mhr/mhr-cfw-main.zip" -O mhr-cfw-main.zip
```

اگر `wget` جواب نداد:

```bash
curl -L "https://nakhl.sbs/mhr/mhr-cfw-main.zip" -o mhr-cfw-main.zip
```

اگر شما فایل را در مسیر دیگری گذاشته‌اید، لینک را عوض کنید.

مثال:

```bash
wget "https://YOUR-DOMAIN.com/mhr/mhr-cfw-main.zip" -O mhr-cfw-main.zip
```

---

## 7.4 باز کردن فایل ZIP

```bash
rm -rf /opt/mhr-cfw /opt/mhr-cfw-main
unzip /opt/mhr-cfw-main.zip -d /opt
mv /opt/mhr-cfw-main /opt/mhr-cfw
cd /opt/mhr-cfw
```

حالا بزنید:

```bash
ls -la
```

باید فایل‌هایی شبیه این ببینید:

```text
main.py
requirements.txt
config.example.json
run.sh
setup.py
deploy/
```

اگر این فایل‌ها نبودند، یعنی ZIP درست Extract نشده یا داخل ZIP یک پوشه اضافه وجود دارد.

---

## 7.5 ساخت محیط Python

```bash
cd /opt/mhr-cfw
python3 -m venv .venv
.venv/bin/python -m pip install --upgrade pip
.venv/bin/python -m pip install -r requirements.txt
```

اگر نصب packageها از PyPI خطا داد:

```bash
.venv/bin/python -m pip install -r requirements.txt -i https://mirror-pypi.runflare.com/simple/ --trusted-host mirror-pypi.runflare.com
```

---

## 7.6 ساخت config.json

```bash
cd /opt/mhr-cfw
cp config.example.json config.json
nano config.json
```

داخل فایل مقدارها را تنظیم کنید.

نمونه تنظیم مناسب برای VPS:

```json
{
  "mode": "apps_script",
  "google_ip": "216.239.38.120",
  "front_domain": "www.google.com",
  "script_id": "YOUR_APPS_SCRIPT_DEPLOYMENT_ID",
  "auth_key": "SAME_AUTH_KEY_AS_CODE_GS",
  "listen_host": "0.0.0.0",
  "socks5_enabled": true,
  "listen_port": 8085,
  "socks5_port": 1080,
  "log_level": "INFO",
  "verify_ssl": true,
  "lan_sharing": true,
  "relay_timeout": 25,
  "tls_connect_timeout": 15,
  "tcp_connect_timeout": 10
}
```

این‌ها را حتماً درست بگذارید:

```json
"script_id": "Deployment ID که از Google Apps Script گرفتید",
"auth_key": "همان AUTH_KEY که در Code.gs گذاشتید",
"listen_host": "0.0.0.0",
"listen_port": 8085
```

ذخیره در nano:

```text
Ctrl + O
Enter
Ctrl + X
```

---

## 7.7 باز کردن پورت 8085

```bash
ufw allow OpenSSH
ufw allow 8085/tcp
ufw --force enable
ufw status
```

در خروجی باید این را ببینید:

```text
8085/tcp ALLOW Anywhere
```

اگر شرکت VPS پنل Firewall جدا دارد، داخل پنل هم TCP پورت `8085` را باز کنید.

---

## 7.8 تست اجرای دستی MHR-CFW

قبل از بک‌گراند کردن، یک بار دستی اجرا کنید:

```bash
cd /opt/mhr-cfw
.venv/bin/python main.py --config /opt/mhr-cfw/config.json
```

اگر درست باشد، باید چیزی شبیه این ببینید:

```text
HTTP proxy listening on 0.0.0.0:8085
SOCKS5 proxy listening on 0.0.0.0:1080
```

برای خروج:

```text
Ctrl + C
```

اگر دیدید:

```text
HTTP proxy listening on 127.0.0.1:8085
```

یعنی `listen_host` هنوز درست نیست. دوباره باز کنید:

```bash
nano /opt/mhr-cfw/config.json
```

و بگذارید:

```json
"listen_host": "0.0.0.0"
```

---

## 7.9 اجرای دائمی MHR-CFW در بک‌گراند

برای اینکه بعد از بستن SSH یا ری‌استارت VPS، MHR خاموش نشود، باید systemd service بسازیم.

فایل سرویس را بسازید:

```bash
nano /etc/systemd/system/mhr-cfw.service
```

این متن را کامل داخلش بگذارید:

```ini
[Unit]
Description=MHR-CFW HTTP Proxy for AzadiRelay
After=network-online.target
Wants=network-online.target

[Service]
Type=simple
WorkingDirectory=/opt/mhr-cfw
ExecStart=/opt/mhr-cfw/.venv/bin/python /opt/mhr-cfw/main.py --config /opt/mhr-cfw/config.json
Restart=always
RestartSec=5
User=root

[Install]
WantedBy=multi-user.target
```

ذخیره کنید:

```text
Ctrl + O
Enter
Ctrl + X
```

حالا سرویس را فعال کنید:

```bash
systemctl daemon-reload
systemctl enable mhr-cfw
systemctl start mhr-cfw
systemctl status mhr-cfw
```

اگر وضعیت `active` بود، درست است.

برای دیدن لاگ زنده:

```bash
journalctl -u mhr-cfw -f
```

برای ری‌استارت:

```bash
systemctl restart mhr-cfw
```

برای توقف:

```bash
systemctl stop mhr-cfw
```

---

## 7.10 چک کردن اینکه MHR روی 0.0.0.0 پخش می‌شود

```bash
ss -lntp | grep 8085
```

خروجی درست:

```text
0.0.0.0:8085
```

خروجی اشتباه:

```text
127.0.0.1:8085
```

اگر خروجی اشتباه بود:

```bash
nano /opt/mhr-cfw/config.json
```

این را اصلاح کنید:

```json
"listen_host": "0.0.0.0"
```

بعد:

```bash
systemctl restart mhr-cfw
ss -lntp | grep 8085
```

---

## 7.11 تست Proxy

از یک سیستم دیگر یا از هاست ایران اگر SSH دارید:

```bash
curl -I -x http://YOUR_VPS_IP:8085 https://example.com
```

اگر SSL خطا داد، برای تست بزنید:

```bash
curl -k -I -x http://YOUR_VPS_IP:8085 https://example.com
```

اگر header برگشت، proxy کار می‌کند.

---

# 8. اتصال AzadiRelay Internal به MHR-CFW

برگردید به هاست ایران و فایل `config.php` نسخه Internal را باز کنید.

این خط را پیدا کنید:

```php
'bridge_proxy_url' => '',
```

یا اگر مقدار نمونه داشت، آن را به این تبدیل کنید:

```php
'bridge_proxy_url' => 'http://YOUR_VPS_IP:8085',
```

بعد این آدرس را باز کنید:

```text
https://YOUR-INTERNAL-DOMAIN/azadi/bridge_health.php
```

اگر فایل health در نسخه شما فعال باشد، وضعیت Bridge را نشان می‌دهد.

---

# 9. ساخت Cron Job برای Bridge

Bridge باید مرتب اجرا شود تا پیام‌های صف‌شده را ارسال و دریافت کند.

در cPanel هاست ایران بروید به:

```text
Cron Jobs
```

یک cron با اجرای هر 1 دقیقه بسازید.

## روش PHP CLI

مسیر واقعی هاست را باید از File Manager یا پشتیبانی هاست پیدا کنید. نمونه:

```bash
*/1 * * * * php -q /home/USERNAME/public_html/azadi/bridge_cron.php >/dev/null 2>&1
```

اگر `php` کار نکرد، این را امتحان کنید:

```bash
*/1 * * * * /usr/local/bin/php -q /home/USERNAME/public_html/azadi/bridge_cron.php >/dev/null 2>&1
```

## روش URL با cron key

اگر هاست PHP CLI نداد:

```bash
*/1 * * * * curl -fsS "https://YOUR-INTERNAL-DOMAIN/azadi/bridge_cron.php?key=YOUR_CRON_KEY" >/dev/null 2>&1
```

`YOUR_CRON_KEY` همان مقدار داخل `config.php` است.

---

# 10. اگر دامنه Global در بعضی کشورها باز نشد

اگر دامنه Global در کشورهایی مثل امارات باز نمی‌شود، اول ساده‌ترین راه این است که دامنه معتبرتر بگیرید.

اگر دامنه را می‌خواهید پشت Cloudflare ببرید:

## 10.1 اضافه کردن دامنه به Cloudflare

1. وارد شوید:

```text
https://dash.cloudflare.com
```

2. روی **Add a site** بزنید.
3. دامنه Global را وارد کنید.
4. پلن **Free** را انتخاب کنید.
5. Cloudflare دو nameserver می‌دهد.
6. وارد پنل دامنه شوید و nameserverهای دامنه را با nameserverهای Cloudflare عوض کنید.
7. صبر کنید تا Cloudflare دامنه را Active کند.

---

## 10.2 ساخت DNS برای Global

در Cloudflare بروید به:

```text
DNS → Records
```

اگر هاست خارجی IP اختصاصی دارد:

```text
Type: A
Name: global
IPv4 address: IP_OF_GLOBAL_HOST
Proxy status: Proxied
```

اگر هاست خارجی CNAME داده:

```text
Type: CNAME
Name: global
Target: TARGET_FROM_HOSTING
Proxy status: Proxied
```

بعد آدرس Global شما می‌شود:

```text
https://global.YOUR-DOMAIN.com/azadi
```

این آدرس را در هر دو `config.php` بگذارید:

```php
'foreign_base_url' => 'https://global.YOUR-DOMAIN.com/azadi',
```

---

## 10.3 تنظیم SSL در Cloudflare

بروید به:

```text
SSL/TLS → Overview
```

حالت را بگذارید روی:

```text
Full
```

روی `Flexible` نگذارید، چون ممکن است POSTهای Bridge و sessionها مشکل بگیرند.

---

## 10.4 جلوگیری از Cache و Challenge روی Bridge

اگر Cloudflare روی مسیرهای Bridge چالش گذاشت یا cache کرد، پیام‌ها درست رد نمی‌شوند.

در Cloudflare بروید به:

```text
Rules → Cache Rules
```

برای این مسیرها Cache را Bypass کنید:

```text
/bridge_endpoint.php
/bridge_mailbox.php
/bridge_cron.php
/bridge_health.php
/db_check.php
```

اگر WAF یا Challenge مشکل داد:

```text
Security → WAF
```

برای مسیرهای بالا Rule بسازید که Challenge نگذارد.

---

# 11. تست نهایی

## 11.1 تست Internal

این‌ها را باز کنید:

```text
https://YOUR-INTERNAL-DOMAIN/azadi/db_check.php
https://YOUR-INTERNAL-DOMAIN/azadi/
```

ثبت‌نام کنید و وارد شوید.

## 11.2 تست Global

این‌ها را باز کنید:

```text
https://YOUR-GLOBAL-DOMAIN/azadi/db_check.php
https://YOUR-GLOBAL-DOMAIN/azadi/
```

ثبت‌نام کنید و وارد شوید.

## 11.3 تست MHR روی VPS

روی VPS:

```bash
systemctl status mhr-cfw
ss -lntp | grep 8085
journalctl -u mhr-cfw -n 50
```

باید ببینید:

```text
active
0.0.0.0:8085
```

## 11.4 تست ارسال پیام

1. یک کاربر در Internal بسازید.
2. یک کاربر در Global بسازید.
3. مطمئن شوید sync کاربران انجام شده است.
4. از کاربر Internal به کاربر Global پیام بدهید.
5. Cron باید پیام را منتقل کند.
6. از Global جواب بدهید.
7. Cron داخلی باید پیام برگشتی را دریافت کند.

---

# 12. خطاهای رایج و راه‌حل

## 12.1 خطای دیتابیس یا pdo_sqlite

نشانه:

```text
pdo_sqlite: غیرفعال
```

راه‌حل:

```text
cPanel → Select PHP Version → Extensions → pdo_sqlite
```

یا پیام به پشتیبانی:

```text
لطفاً pdo_sqlite و sqlite3 را برای PHP فعال کنید.
```

---

## 12.2 فایل دیتابیس writable نیست

نشانه:

```text
فایل دیتابیس writable: خیر
```

راه‌حل:

در File Manager دسترسی‌ها را بررسی کنید:

```text
پوشه نصب: 755
chat_mw.db: 664
```

اگر هاست سخت‌گیر بود، برای تست موقت:

```text
chat_mw.db: 666
```

---

## 12.3 MHR بالا نمی‌آید

روی VPS بزنید:

```bash
journalctl -u mhr-cfw -n 100
```

اگر خطای `auth_key` دیدید، مقدار `auth_key` در `config.json` خالی یا اشتباه است.

اگر خطای `script_id` دیدید، Deployment ID را درست نگذاشته‌اید.

---

## 12.4 MHR فقط روی localhost است

چک:

```bash
ss -lntp | grep 8085
```

اگر خروجی:

```text
127.0.0.1:8085
```

بود، این فایل را باز کنید:

```bash
nano /opt/mhr-cfw/config.json
```

و بگذارید:

```json
"listen_host": "0.0.0.0"
```

بعد:

```bash
systemctl restart mhr-cfw
```

---

## 12.5 پورت VPS بسته است

روی VPS:

```bash
ufw status
```

باید ببینید:

```text
8085/tcp ALLOW
```

اگر نبود:

```bash
ufw allow 8085/tcp
ufw reload
```

اگر باز هم وصل نشد، داخل پنل شرکت VPS هم firewall را بررسی کنید.

---

## 12.6 Apps Script unauthorized می‌دهد

علت:

`AUTH_KEY` در `Code.gs` با `auth_key` در `config.json` یکی نیست.

راه‌حل:

- `Code.gs` را چک کنید.
- `config.json` را چک کنید.
- دو مقدار باید دقیقاً یکی باشند.
- بعد از تغییر `Code.gs` دوباره Deploy کنید.

---

## 12.7 بعد از تغییر Code.gs هنوز کار نمی‌کند

در Google Apps Script فقط Save کافی نیست. باید دوباره Deploy کنید:

```text
Deploy → New deployment → Web app → Deploy
```

بعد Deployment ID جدید را داخل `config.json` بگذارید و MHR را restart کنید:

```bash
systemctl restart mhr-cfw
```

---

## 12.8 Worker اشتباه تنظیم شده

در Cloudflare بروید به:

```text
Workers & Pages → Worker شما → Edit code
```

این خط را چک کنید:

```javascript
const WORKER_URL = "azadi-relay-worker.YOURNAME.workers.dev";
```

بعد **Deploy** کنید.

---

## 12.9 پیام از Internal به Global نمی‌رود

این موارد را چک کنید:

```text
bridge_enabled = true
bridge_secret روی دو سرور یکی است
foreign_base_url درست است
bridge_proxy_url در Internal درست است
MHR روی VPS active است
Cron فعال است
Global bridge_endpoint.php باز می‌شود
```

---

## 12.10 Cron اجرا نمی‌شود

در cPanel مسیر PHP را بررسی کنید.

اگر دستور زیر جواب نداد:

```bash
php -q /home/USERNAME/public_html/azadi/bridge_cron.php
```

از پشتیبانی هاست بپرسید:

```text
مسیر کامل PHP CLI روی هاست من چیست؟
```

یا از روش URL استفاده کنید:

```bash
curl -fsS "https://YOUR-INTERNAL-DOMAIN/azadi/bridge_cron.php?key=YOUR_CRON_KEY"
```

---

# 13. نکات امنیتی مهم

این موارد را عمومی نکنید:

```text
IP واقعی VPS
bridge_secret واقعی
cron_key واقعی
AUTH_KEY واقعی MHR
آدرس خصوصی Google Apps Script
رمز واقعی admin
```

برای GitHub فقط نمونه بگذارید:

```php
'bridge_proxy_url' => 'http://YOUR_VPS_IP:8085',
'bridge_secret' => 'CHANGE_THIS_SECRET',
'cron_key' => 'CHANGE_THIS_CRON_KEY',
```

بعد از نصب واقعی، رمز ادمین را از `admin/admin` تغییر دهید.

---

# 14. فایل‌هایی که باید در GitHub قرار بگیرند

پیشنهاد ساختار ریپازیتوری:

```text
azadirelay/
├── README.md
├── LICENSE
├── releases/
│   ├── azadirelay-internal.zip
│   └── azadirelay-global.zip
└── mhr/
    ├── mhr-cfw-main.zip
    ├── worker.js
    └── Code.gs
```

اسم فایل‌های ZIP پیشنهادی:

```text
azadirelay-internal.zip
azadirelay-global.zip
```

اگر خواستید نسخه‌گذاری کنید:

```text
azadirelay-internal-v1.0.0.zip
azadirelay-global-v1.0.0.zip
```

---

# 15. چک‌لیست نهایی نصب

```text
[ ] هاست ایران تهیه شده
[ ] هاست خارج کشور تهیه شده
[ ] VPS Ubuntu تهیه شده
[ ] فایل‌های دانلود روی nakhl.sbs/mhr قرار گرفته‌اند
[ ] AzadiRelay Internal نصب شده
[ ] AzadiRelay Global نصب شده
[ ] db_check.php روی هر دو سرور سبز است
[ ] pdo_sqlite فعال است
[ ] Cloudflare Worker ساخته و Deploy شده
[ ] Google Apps Script ساخته و Deploy شده
[ ] Deployment ID در config.json قرار گرفته
[ ] AUTH_KEY در Code.gs و config.json یکی است
[ ] MHR-CFW با wget روی VPS نصب شده
[ ] listen_host برابر 0.0.0.0 است
[ ] listen_port برابر 8085 است
[ ] systemd فعال است
[ ] پورت 8085 باز است
[ ] bridge_proxy_url در Internal تنظیم شده
[ ] Cron Job ساخته شده
[ ] ارسال پیام از ایران به خارج تست شده
[ ] ارسال پیام از خارج به ایران تست شده
```

---

# License

AzadiRelay تحت لایسنس MIT منتشر می‌شود.

بخش MHR-CFW متعلق به پروژه اصلی خودش است و باید لایسنس و نام پروژه اصلی حفظ شود.

---

<p align="center">
  <strong>AzadiRelay — Secure messaging bridge for cross-border communication</strong>
</p>

</div>
