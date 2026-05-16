<div dir="rtl">

# AzadiRelay

**AzadiRelay** یک پیام‌رسان سبک، خودمیزبان و bridge-first است؛ برای زمانی که دو نصب جدا از هم دارید:

- یک نصب **داخلی** روی هاست ایران
- یک نصب **خارجی / Global** روی هاست خارج کشور
- یک مسیر Relay برای رد شدن درخواست‌های Bridge از مسیر **MHR-CFW**

هدف پروژه این است که ارتباط خصوصی بین کاربرهای داخل و خارج برقرار شود، بدون اینکه برنامه پر از قابلیت اضافه مثل چت عمومی، گروه، کانال، تماس یا سیستم تیک آبی باشد.

---

## مسیر کلی ارتباط

```text
AzadiRelay Internal روی هاست ایران
        │
        │ bridge request
        ▼
MHR-CFW Proxy روی VPS Ubuntu
        │  HTTP proxy : 0.0.0.0:8085
        ▼
Google Apps Script
        ▼
Cloudflare Worker
        ▼
AzadiRelay Global روی هاست خارج کشور
```

پورت پیش‌فرض MHR-CFW در این راهنما:

```text
8085
```

پس در `config.php` نسخه داخلی AzadiRelay مقدار proxy معمولاً این می‌شود:

```php
'bridge_proxy_url' => 'http://YOUR_VPS_IP:8085',
```

> IP، دامنه، کلیدها و secretهای واقعی را داخل GitHub قرار ندهید.

---

## چیزی که باید تهیه کنید

### 1. هاست ایران برای نسخه Internal

برای هاست داخلی، این موارد مهم است:

| مورد | مقدار پیشنهادی |
|---|---|
| PHP | نسخه 8 یا بالاتر |
| دیتابیس | SQLite |
| افزونه PHP | `pdo_sqlite` فعال باشد |
| Cron Job | داشته باشد |
| File Manager | برای آپلود ZIP داشته باشد |
| SSL | بهتر است فعال باشد |

قبل از خرید یا نصب، از پشتیبانی هاست بپرسید:

```text
آیا PHP PDO SQLite روی هاست فعال است؟
آیا امکان اجرای Cron Job وجود دارد؟
آیا فایل‌های PHP اجازه نوشتن کنار index.php را دارند؟
```

اگر گفتند `pdo_sqlite` فعال نیست، این پروژه درست اجرا نمی‌شود تا زمانی که فعالش کنند.

---

### 2. هاست خارج کشور برای نسخه Global

برای هاست خارجی هم همین موارد لازم است:

| مورد | مقدار پیشنهادی |
|---|---|
| PHP | نسخه 8 یا بالاتر |
| SQLite | فعال |
| pdo_sqlite | فعال |
| SSL | فعال |
| دامنه | بهتر است معتبر و پایدار باشد |

اگر دامنه‌ای که برای خارج کشور استفاده می‌کنید در بعضی کشورها باز نمی‌شود، بهتر است یک دامنه پایدارتر بگیرید یا آن را از مسیر Cloudflare عبور دهید.

برای دامنه خارجی، بهتر است از دامنه‌های خیلی ناشناس یا بی‌اعتبار استفاده نکنید. دامنه‌ای انتخاب کنید که در کشورهایی مثل امارات، ترکیه، اروپا و آمریکا راحت‌تر باز شود.

---

### 3. VPS Ubuntu برای MHR-CFW

برای VPS بهتر است این مشخصات را بگیرید:

| مورد | مقدار پیشنهادی |
|---|---|
| سیستم‌عامل | Ubuntu 22.04 یا 24.04 |
| RAM | حداقل 1GB |
| CPU | حداقل 1 Core |
| IP | IPv4 عمومی |
| دسترسی | SSH root یا sudo |
| پورت | امکان باز کردن TCP 8085 |

VPS قرار است MHR-CFW را اجرا کند و روی پورت `8085` به عنوان HTTP proxy گوش بدهد.

---

## فایل‌هایی که باید روی هاست دانلود خودتان بگذارید

چون ممکن است GitHub روی بعضی شبکه‌ها باز نشود، بهتر است فایل‌های لازم را روی یک مسیر دانلود خودتان بگذارید؛ مثلاً:

```text
https://YOUR-DOWNLOAD-HOST/mhr/mhr-cfw-main.zip
https://YOUR-DOWNLOAD-HOST/mhr/worker.js
https://YOUR-DOWNLOAD-HOST/mhr/Code.gs
```

اگر مسیر دانلود شما چیز دیگری است، در دستورهای پایین فقط آدرس‌ها را با آدرس خودتان جایگزین کنید.

نمونه متغیرهایی که در VPS استفاده می‌کنیم:

```bash
MHR_ZIP_URL="https://YOUR-DOWNLOAD-HOST/mhr/mhr-cfw-main.zip"
```

---

# بخش اول — نصب AzadiRelay روی هاست‌ها

## نصب نسخه Internal روی هاست ایران

1. وارد cPanel یا کنترل‌پنل هاست ایران شوید.
2. وارد **File Manager** شوید.
3. وارد پوشه‌ای شوید که می‌خواهید برنامه نصب شود. مثال:

```text
public_html/azadi
```

4. فایل ZIP نسخه Internal را آپلود کنید.
5. روی فایل ZIP بزنید و **Extract** کنید.
6. فایل‌ها باید کنار هم باشند. مهم‌ترین حالت درست این است:

```text
azadi/
├── index.php
├── config.php
├── chat_mw.db
├── bridge_cron.php
├── bridge_endpoint.php
├── db_check.php
└── ...
```

مهم: `chat_mw.db` باید کنار `index.php` باشد. اسم پوشه مهم نیست.

درست:

```text
public_html/azadi/index.php
public_html/azadi/chat_mw.db
```

اشتباه:

```text
public_html/azadi/index.php
public_html/azadi/database/chat_mw.db
```

---

## تست دیتابیس روی هاست

بعد از آپلود، این آدرس را باز کنید:

```text
https://YOUR-INTERNAL-DOMAIN/azadi/db_check.php
```

اگر همه چیز درست باشد باید ببینید:

```text
pdo_sqlite: فعال ✅
فایل دیتابیس موجود است: بله ✅
فایل دیتابیس writable: بله ✅
اتصال و نوشتن دیتابیس موفق بود ✅
```

اگر `pdo_sqlite` فعال نبود، از داخل cPanel به این مسیر بروید:

```text
Select PHP Version → Extensions → pdo_sqlite
```

تیک `pdo_sqlite` را بزنید و ذخیره کنید.

اگر این گزینه را نمی‌بینید، باید به پشتیبانی هاست پیام بدهید:

```text
لطفاً افزونه PHP pdo_sqlite و sqlite3 را برای دامنه من فعال کنید.
```

---

## تنظیم config.php نسخه Internal

فایل `config.php` را باز کنید و فقط این قسمت‌ها را تغییر دهید:

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

نکات مهم:

- آخر آدرس‌ها `/` نگذارید.
- `bridge_secret` باید در نسخه Internal و Global دقیقاً یکی باشد.
- `cron_key` را خصوصی نگه دارید.
- `bridge_proxy_url` فقط در نسخه داخلی لازم است، چون نسخه داخلی باید از مسیر MHR-CFW به خارج وصل شود.
- IP واقعی VPS خودتان را در GitHub منتشر نکنید.

---

## نصب نسخه Global روی هاست خارج کشور

مثل نسخه داخلی:

1. وارد هاست خارجی شوید.
2. یک پوشه بسازید. مثال:

```text
public_html/azadi
```

3. فایل ZIP نسخه Global را آپلود و Extract کنید.
4. این آدرس را تست کنید:

```text
https://YOUR-GLOBAL-DOMAIN/azadi/db_check.php
```

---

## تنظیم config.php نسخه Global

در نسخه Global مقدارها شبیه زیر باشد:

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

در Global معمولاً `bridge_proxy_url` خالی می‌ماند.

---

# بخش دوم — ساخت Cloudflare Worker برای MHR-CFW

Cloudflare Worker در MHR-CFW نقش خروجی درخواست‌ها را دارد.

## ساخت Worker

1. وارد Cloudflare شوید:

```text
https://dash.cloudflare.com
```

2. از منوی سمت چپ بروید به:

```text
Compute (Workers) → Workers & Pages
```

3. روی **Create** بزنید.
4. گزینه **Start with Hello World** را انتخاب کنید.
5. یک نام بگذارید. مثال:

```text
azadi-relay-worker
```

6. روی **Deploy** بزنید.
7. بعد از ساخته شدن Worker، روی **Edit code** بزنید.
8. همه کد پیش‌فرض را پاک کنید.
9. فایل `worker.js` مربوط به MHR-CFW را باز کنید و کل کد آن را داخل Cloudflare paste کنید.
10. داخل کد Worker این خط را پیدا کنید:

```javascript
const WORKER_URL = "myworker.workers.dev";
```

11. مقدار آن را با آدرس Worker خودتان عوض کنید. مثال:

```javascript
const WORKER_URL = "azadi-relay-worker.YOURNAME.workers.dev";
```

12. روی **Deploy** بزنید.
13. آدرس Worker را نگه دارید. شکل آن معمولاً این است:

```text
https://azadi-relay-worker.YOURNAME.workers.dev
```

---

# بخش سوم — ساخت Google Apps Script برای MHR-CFW

Google Apps Script در MHR-CFW نقش دروازه مسیر گوگل را دارد.

## ساخت پروژه

1. وارد این آدرس شوید:

```text
https://script.google.com
```

2. روی **New project** بزنید.
3. کد پیش‌فرض را کامل پاک کنید.
4. فایل `Code.gs` مربوط به MHR-CFW را باز کنید و کل کد را paste کنید.
5. این دو خط را پیدا کنید:

```javascript
const AUTH_KEY = "STRONG_SECRET_KEY";
const WORKER_URL = "https://example.workers.dev";
```

6. برای `AUTH_KEY` یک رمز طولانی بگذارید. مثال:

```javascript
const AUTH_KEY = "CHANGE_THIS_TO_A_LONG_RANDOM_SECRET_123456";
```

7. برای `WORKER_URL` آدرس Worker خودتان را بگذارید:

```javascript
const WORKER_URL = "https://azadi-relay-worker.YOURNAME.workers.dev";
```

8. ذخیره کنید.
9. از بالا روی **Deploy** بزنید.
10. گزینه **New deployment** را بزنید.
11. کنار **Select type** روی چرخ‌دنده بزنید.
12. گزینه **Web app** را انتخاب کنید.
13. تنظیمات را این‌طور بگذارید:

```text
Execute as      : Me
Who has access  : Anyone
```

14. روی **Deploy** بزنید.
15. اگر Google هشدار داد:

```text
Advanced → Go to project → Allow
```

16. بعد از Deploy، یک **Deployment ID** می‌گیرید. شکل آن شبیه این است:

```text
AKfycbxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

این Deployment ID را برای تنظیم `config.json` روی VPS نگه دارید.

---

# بخش چهارم — نصب MHR-CFW روی VPS Ubuntu

در این روش از `git clone` استفاده نمی‌کنیم، چون ممکن است GitHub روی VPS یا شبکه شما باز نشود. فایل ZIP را از هاست دانلود خودتان با `wget` می‌گیریم.

## ورود به VPS

از کامپیوتر خودتان وارد VPS شوید:

```bash
ssh root@YOUR_VPS_IP
```

اگر کاربر شما root نیست:

```bash
ssh USER@YOUR_VPS_IP
```

و قبل دستورها `sudo` بگذارید.

---

## آماده‌سازی سیستم

```bash
apt update && apt upgrade -y
apt install -y curl wget unzip nano ufw python3 python3-pip python3-venv ca-certificates
```

اگر دستور بالا خطا داد، یک بار این را بزنید و دوباره امتحان کنید:

```bash
apt --fix-broken install -y
```

---

## دانلود MHR-CFW از لینک داخلی خودتان

اول مسیر نصب را بسازید:

```bash
mkdir -p /opt/mhr-cfw
cd /opt
```

حالا فایل ZIP را دانلود کنید. آدرس را با لینک واقعی خودتان عوض کنید:

```bash
wget "https://YOUR-DOWNLOAD-HOST/mhr/mhr-cfw-main.zip" -O mhr-cfw-main.zip
```

اگر `wget` کار نکرد:

```bash
curl -L "https://YOUR-DOWNLOAD-HOST/mhr/mhr-cfw-main.zip" -o mhr-cfw-main.zip
```

حالا Extract کنید:

```bash
rm -rf /opt/mhr-cfw /opt/mhr-cfw-main
unzip /opt/mhr-cfw-main.zip -d /opt
mv /opt/mhr-cfw-main /opt/mhr-cfw
cd /opt/mhr-cfw
```

چک کنید فایل‌ها وجود دارند:

```bash
ls -la
```

باید فایل‌هایی مثل این ببینید:

```text
main.py
requirements.txt
config.example.json
deploy/
run.sh
```

---

## ساخت محیط Python و نصب کتابخانه‌ها

```bash
cd /opt/mhr-cfw
python3 -m venv .venv
.venv/bin/python -m pip install --upgrade pip
.venv/bin/python -m pip install -r requirements.txt
```

اگر نصب کتابخانه‌ها به خاطر PyPI خطا داد، از mirror استفاده کنید:

```bash
.venv/bin/python -m pip install -r requirements.txt -i https://mirror-pypi.runflare.com/simple/ --trusted-host mirror-pypi.runflare.com
```

---

## ساخت config.json

```bash
cd /opt/mhr-cfw
cp config.example.json config.json
nano config.json
```

داخل nano این کلیدها را پیدا کنید و تغییر دهید:

```json
{
  "mode": "apps_script",
  "google_ip": "216.239.38.120",
  "front_domain": "www.google.com",
  "script_id": "YOUR_APPS_SCRIPT_DEPLOYMENT_ID",
  "auth_key": "SAME_AUTH_KEY_AS_CODE_GS",
  "listen_host": "0.0.0.0",
  "listen_port": 8085,
  "socks5_enabled": true,
  "socks5_port": 1080,
  "log_level": "INFO",
  "verify_ssl": true,
  "lan_sharing": true
}
```

حتماً این دو مورد را دقیق بگذارید:

```json
"listen_host": "0.0.0.0",
"listen_port": 8085
```

اگر `listen_host` روی `127.0.0.1` بماند، فقط خود VPS به proxy دسترسی دارد و هاست ایران نمی‌تواند به آن وصل شود.

برای ذخیره در nano:

```text
Ctrl + O
Enter
Ctrl + X
```

---

## باز کردن پورت 8085 روی VPS

```bash
ufw allow OpenSSH
ufw allow 8085/tcp
ufw --force enable
ufw status
```

در خروجی باید چیزی شبیه این ببینید:

```text
8085/tcp ALLOW Anywhere
```

اگر VPS شما پنل Firewall جدا دارد، داخل پنل شرکت VPS هم TCP پورت `8085` را باز کنید.

---

## تست اجرای دستی MHR-CFW

قبل از systemd یک بار دستی اجرا کنید:

```bash
cd /opt/mhr-cfw
.venv/bin/python main.py --config /opt/mhr-cfw/config.json
```

اگر درست باشد باید پیامی شبیه این ببینید:

```text
HTTP proxy listening on 0.0.0.0:8085
```

برای خروج:

```text
Ctrl + C
```

---

## اجرای دائمی در بک‌گراند با systemd

فایل سرویس بسازید:

```bash
nano /etc/systemd/system/mhr-cfw.service
```

این متن را داخلش بگذارید:

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

ذخیره کنید و خارج شوید.

حالا سرویس را فعال کنید:

```bash
systemctl daemon-reload
systemctl enable mhr-cfw
systemctl start mhr-cfw
systemctl status mhr-cfw
```

اگر status سبز یا active بود یعنی سرویس روشن است.

برای دیدن لاگ زنده:

```bash
journalctl -u mhr-cfw -f
```

برای ری‌استارت:

```bash
systemctl restart mhr-cfw
```

برای خاموش کردن:

```bash
systemctl stop mhr-cfw
```

---

## چک اینکه واقعاً روی 0.0.0.0:8085 پخش می‌شود

```bash
ss -lntp | grep 8085
```

خروجی درست:

```text
0.0.0.0:8085
```

اگر دیدید:

```text
127.0.0.1:8085
```

یعنی `config.json` هنوز درست نیست. دوباره این فایل را باز کنید:

```bash
nano /opt/mhr-cfw/config.json
```

و مقدار را این کنید:

```json
"listen_host": "0.0.0.0"
```

بعد:

```bash
systemctl restart mhr-cfw
```

---

## تست proxy از بیرون

از یک سرور دیگر یا از هاست ایران اگر SSH دارید:

```bash
curl -I -x http://YOUR_VPS_IP:8085 https://example.com
```

اگر خطای SSL دیدید:

```bash
curl -k -I -x http://YOUR_VPS_IP:8085 https://example.com
```

اگر جواب HTTP برگشت، مسیر proxy فعال است.

---

# بخش پنجم — اتصال AzadiRelay به MHR-CFW

بعد از روشن شدن MHR روی VPS، به هاست ایران برگردید و `config.php` نسخه Internal را باز کنید.

این خط را پیدا کنید:

```php
'bridge_proxy_url' => '',
```

و به این شکل تغییر دهید:

```php
'bridge_proxy_url' => 'http://YOUR_VPS_IP:8085',
```

بعد این آدرس را تست کنید:

```text
https://YOUR-INTERNAL-DOMAIN/azadi/bridge_health.php
```

اگر bridge روشن باشد باید وضعیت bridge را ببینید.

---

# بخش ششم — Cron Job برای Bridge

Bridge باید مرتب اجرا شود تا پیام‌های صف‌شده را ارسال و دریافت کند.

در cPanel هاست ایران بروید به:

```text
Cron Jobs
```

یک cron با اجرای هر 1 دقیقه بسازید.

روش PHP مستقیم:

```bash
*/1 * * * * php -q /home/USERNAME/public_html/azadi/bridge_cron.php >/dev/null 2>&1
```

اگر مسیر PHP متفاوت بود، این را از هاست بپرسید:

```text
مسیر کامل PHP CLI روی هاست من چیست؟
```

گاهی مسیر این است:

```bash
*/1 * * * * /usr/local/bin/php -q /home/USERNAME/public_html/azadi/bridge_cron.php >/dev/null 2>&1
```

روش URL اگر هاست اجازه PHP CLI نمی‌دهد:

```bash
*/1 * * * * curl -fsS "https://YOUR-INTERNAL-DOMAIN/azadi/bridge_cron.php?key=YOUR_CRON_KEY" >/dev/null 2>&1
```

`YOUR_CRON_KEY` همان مقداری است که در `config.php` گذاشته‌اید.

---

# بخش هفتم — اگر دامنه خارج کشور در بعضی کشورها باز نشد

گاهی یک دامنه یا پسوند دامنه در بعضی کشورها درست باز نمی‌شود. در این حالت دو راه دارید:

## راهکار 1: دامنه معتبرتر بگیرید

برای نسخه Global بهتر است دامنه‌ای بگیرید که در کشورهای مختلف راحت‌تر resolve شود.

بعد در `config.php` هر دو سرور این مقدار را تغییر دهید:

```php
'foreign_base_url' => 'https://YOUR-NEW-GLOBAL-DOMAIN/azadi',
```

## راهکار 2: قرار دادن دامنه Global پشت Cloudflare

1. وارد Cloudflare شوید:

```text
https://dash.cloudflare.com
```

2. روی **Add a site** بزنید.
3. دامنه خارجی خود را وارد کنید.
4. پلن Free را انتخاب کنید.
5. Cloudflare دو nameserver به شما می‌دهد.
6. بروید داخل پنل جایی که دامنه را خریدید.
7. Nameserverهای دامنه را با nameserverهای Cloudflare جایگزین کنید.
8. چند دقیقه تا چند ساعت صبر کنید تا فعال شود.
9. داخل Cloudflare بروید به:

```text
DNS → Records
```

10. رکورد دامنه یا ساب‌دامین Global را بسازید.

اگر هاست خارجی IP اختصاصی دارد:

```text
Type: A
Name: global
IPv4: IP_OF_GLOBAL_HOST
Proxy status: Proxied
```

اگر هاست خارجی فقط CNAME داده:

```text
Type: CNAME
Name: global
Target: TARGET_FROM_HOSTING
Proxy status: Proxied
```

11. بروید به:

```text
SSL/TLS → Overview
```

و حالت را بگذارید روی:

```text
Full
```

نه `Flexible`.

12. اگر bridge خطای 403 یا challenge گرفت، در Cloudflare این مسیرها را از cache و challenge خارج کنید:

```text
/bridge_endpoint.php
/bridge_mailbox.php
/bridge_cron.php
/db_check.php
```

در Cloudflare معمولاً از این مسیر انجام می‌شود:

```text
Rules → Cache Rules
```

یک Rule بسازید و برای مسیرهای بالا Cache را Bypass کنید.

اگر WAF مزاحم شد:

```text
Security → WAF
```

برای مسیرهای bridge یک rule بسازید که challenge نگذارد.

بعد در `config.php` مقدار Global را با دامنه Cloudflare عوض کنید:

```php
'foreign_base_url' => 'https://global.YOUR-DOMAIN.com/azadi',
```

---

# بخش هشتم — خطاهای رایج و راه‌حل

## خطای pdo_sqlite

نشانه:

```text
pdo_sqlite: غیرفعال
```

راه‌حل:

```text
cPanel → Select PHP Version → Extensions → pdo_sqlite
```

اگر نبود، به پشتیبانی هاست پیام بدهید.

---

## خطای دیتابیس writable نیست

نشانه:

```text
فایل دیتابیس writable: خیر
```

راه‌حل:

- دسترسی پوشه نصب معمولاً `755`
- دسترسی فایل `chat_mw.db` معمولاً `664` یا `666` در صورت اجبار هاست

از File Manager روی فایل بزنید:

```text
Permissions
```

---

## برنامه هنوز splash یا کش قدیمی نشان می‌دهد

اگر قبلاً نسخه دیگری نصب کرده بودید:

- از مرورگر Clear Site Data بزنید
- اگر PWA نصب کرده‌اید uninstall کنید
- دوباره صفحه را باز کنید

---

## MHR روی VPS روشن است ولی هاست ایران وصل نمی‌شود

چک کنید:

```bash
systemctl status mhr-cfw
ss -lntp | grep 8085
ufw status
```

باید ببینید:

```text
0.0.0.0:8085
8085/tcp ALLOW
```

اگر `127.0.0.1:8085` بود، `listen_host` اشتباه است.

---

## curl با proxy جواب نمی‌دهد

تست:

```bash
curl -k -I -x http://YOUR_VPS_IP:8085 https://example.com
```

اگر timeout شد:

- پورت 8085 در UFW بسته است
- پورت 8085 در پنل VPS بسته است
- MHR اجرا نیست
- `listen_host` روی `127.0.0.1` مانده
- Google Apps Script یا Worker اشتباه تنظیم شده

---

## AUTH_KEY اشتباه است

اگر MHR خطای auth داد، این دو مقدار باید دقیقاً یکی باشند:

- `AUTH_KEY` در `Code.gs`
- `auth_key` در `/opt/mhr-cfw/config.json`

حتی یک فاصله یا کاراکتر اضافه باعث خطا می‌شود.

---

## Deployment ID اشتباه است

اگر script جواب نمی‌دهد، در Google Apps Script دوباره بروید به:

```text
Deploy → Manage deployments
```

Deployment ID را دوباره کپی کنید و در `config.json` بگذارید:

```json
"script_id": "AKfycbxxxxxxxxxxxxxxxxxxxxxxxx"
```

بعد:

```bash
systemctl restart mhr-cfw
```

---

## Worker اشتباه است

اگر Worker خطا می‌دهد:

- در Cloudflare روی Worker بروید
- **Edit code** را باز کنید
- آدرس Worker داخل کد را چک کنید
- دوباره **Deploy** کنید

---

## Bridge پیام نمی‌فرستد

چک کنید:

1. `bridge_enabled` روشن باشد:

```php
'bridge_enabled' => true,
```

2. `bridge_secret` روی هر دو سرور یکی باشد.
3. `foreign_base_url` درست باشد.
4. cron فعال باشد.
5. `bridge_proxy_url` در نسخه Internal درست باشد.
6. روی Global، `bridge_endpoint.php` باز شود.

---

# نکات امنیتی مهم

این موارد را هیچ‌وقت داخل GitHub نگذارید:

```text
IP واقعی VPS
bridge_secret واقعی
cron_key واقعی
AUTH_KEY واقعی MHR
آدرس خصوصی Worker
آدرس خصوصی Google Apps Script
رمز ادمین واقعی
```

برای GitHub فقط نمونه بگذارید:

```php
'bridge_proxy_url' => 'http://YOUR_VPS_IP:8085',
'bridge_secret' => 'CHANGE_THIS_SECRET',
'cron_key' => 'CHANGE_THIS_CRON_KEY',
```

بعد از نصب واقعی، بهتر است رمز ادمین را از `admin/admin` تغییر دهید.

---

# چک‌لیست نهایی نصب

قبل از اینکه پروژه را عملیاتی کنید، این‌ها را یکی‌یکی تیک بزنید:

```text
[ ] نسخه Internal روی هاست ایران نصب شده
[ ] نسخه Global روی هاست خارج نصب شده
[ ] db_check.php روی هر دو سبز است
[ ] pdo_sqlite فعال است
[ ] bridge_secret روی هر دو سرور یکی است
[ ] cron_key خصوصی است
[ ] MHR-CFW روی VPS نصب شده
[ ] config.json روی VPS مقدار listen_host = 0.0.0.0 دارد
[ ] پورت 8085 روی VPS باز است
[ ] systemd فعال است و MHR بعد reboot روشن می‌شود
[ ] Cloudflare Worker ساخته و deploy شده
[ ] Google Apps Script ساخته و deploy شده
[ ] Deployment ID داخل config.json قرار گرفته
[ ] AUTH_KEY در Code.gs و config.json یکی است
[ ] bridge_proxy_url در Internal برابر http://YOUR_VPS_IP:8085 است
[ ] ارسال پیام داخلی به خارجی تست شده
[ ] ارسال پیام خارجی به داخلی تست شده
```

---

# ساختار پیشنهادی فایل‌ها در هاست دانلود

برای اینکه کاربران راحت نصب کنند، می‌توانید فایل‌ها را این‌طور روی هاست دانلود قرار دهید:

```text
/mhr/mhr-cfw-main.zip
/mhr/worker.js
/mhr/Code.gs
/releases/AzadiRelay_internal.zip
/releases/AzadiRelay_global.zip
```

بعد در آموزش خودتان فقط لینک‌ها را جایگزین کنید.

---

## لایسنس

این پروژه برای انتشار آزاد و استفاده self-hosted طراحی شده است. قبل از انتشار عمومی، فایل `LICENSE` را مطابق سیاست پروژه اضافه کنید.

---

## Repository

```text
https://github.com/M0lavi/azadirelay
```

</div>
