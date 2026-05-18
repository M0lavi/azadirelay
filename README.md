<div dir="rtl">

# AzadiRelay

**AzadiRelay** برای برقراری ارتباط امن بین ایران و خارج کشور ساخته شده است.

با این پروژه شما دو نسخه از برنامه را نصب می‌کنید:

- یک نسخه روی **هاست ایران** برای کاربران داخل ایران
- یک نسخه روی **هاست خارج کشور** برای کاربران خارج کشور
- یک مسیر **MHR-CFW روی VPS** برای اینکه نسخه داخلی بتواند از مسیر امن و پایدار به نسخه خارجی پیام بفرستد

کار اصلی پروژه:

```text
ارسال پیام از ایران به خارج کشور و دریافت پاسخ، از مسیر Bridge و MHR-CFW
```

---

## نقشه مسیر ارتباط

```text
کاربر داخل ایران
    ↓
AzadiRelay Internal روی هاست ایران
    ↓
MHR-CFW روی VPS Ubuntu  →  پورت 8085
    ↓
Google Apps Script
    ↓
Cloudflare Worker مربوط به MHR
    ↓
AzadiRelay External / Global روی هاست خارج کشور
    ↓
کاربر خارج کشور
```

پورت پیش‌فرض MHR در این آموزش:

```text
8085
```

لینک‌های دانلود فایل‌های MHR در این آموزش از مسیر داخلی زیر استفاده می‌کنند:

```text
https://nakhl.sbs/mhr/
```

اگر فایل‌ها را در مسیر دیگری گذاشتید، فقط همان لینک‌ها را در دستورها عوض کنید.

---

# 1. قبل از شروع چه چیزهایی لازم است؟

## 1.1 هاست ایران

این هاست محل نصب نسخه داخلی است.

از پشتیبانی هاست ایران بپرسید این موارد فعال باشد:

```text
PHP 8 یا بالاتر
pdo_sqlite
sqlite3
Cron Job
SSL
امکان نوشتن فایل توسط PHP
```

اگر `pdo_sqlite` فعال نباشد، برنامه به دیتابیس وصل نمی‌شود.

در cPanel معمولاً از این مسیر فعال می‌شود:

```text
Select PHP Version → Extensions → pdo_sqlite
```

اگر این گزینه نبود، به پشتیبانی هاست پیام بدهید:

```text
لطفاً افزونه‌های pdo_sqlite و sqlite3 را برای PHP دامنه من فعال کنید.
```

---

## 1.2 هاست خارج کشور

این هاست محل نصب نسخه خارجی / Global است.

برای هاست خارج هم همین موارد لازم است:

```text
PHP 8 یا بالاتر
pdo_sqlite
sqlite3
SSL
امکان نوشتن فایل توسط PHP
```

دامنه خارجی بهتر است معتبر و پایدار باشد. اگر دیدید دامنه خارجی در بعضی کشورها باز نمی‌شود، پایین همین README بخش **ساخت Worker جدا برای دامنه خارجی** را انجام دهید.

---

## 1.3 VPS Ubuntu

VPS برای اجرای MHR-CFW است.

مشخصات پیشنهادی:

```text
Ubuntu 22.04 یا Ubuntu 24.04
حداقل 1GB RAM
IPv4 عمومی
دسترسی SSH
باز بودن پورت TCP 8085
```

---

## 1.4 حساب Cloudflare و Google

برای MHR به این دو حساب نیاز است:

```text
Cloudflare Account
Google Account
```

Cloudflare برای ساخت Worker استفاده می‌شود.
Google برای ساخت Apps Script استفاده می‌شود.

---

# 2. فایل‌هایی که باید داخل GitHub قرار بگیرند

ساختار پیشنهادی ریپازیتوری:

```text
azadirelay/
├── README.md
├── LICENSE
├── CONFIG_INTERNAL_EXAMPLE.php
├── CONFIG_EXTERNAL_EXAMPLE.php
├── releases/
│   ├── azadirelay-internal-iran-host.zip
│   └── azadirelay-external-global-host.zip
├── mhr/
│   ├── mhr-cfw-main.zip
│   ├── worker.js
│   ├── Code.gs
│   └── config.example.json
└── cloudflare/
    └── azadirelay-global-domain-proxy-worker.js
```

فایل‌های MHR را بهتر است روی هاست دانلود خودتان هم بگذارید تا کاربران برای نصب روی VPS از GitHub دانلود نکنند:

```text
https://nakhl.sbs/mhr/mhr-cfw-main.zip
https://nakhl.sbs/mhr/worker.js
https://nakhl.sbs/mhr/Code.gs
https://nakhl.sbs/mhr/azadirelay-global-domain-proxy-worker.js
```

---

# 3. نصب نسخه Internal روی هاست ایران

1. وارد cPanel یا پنل هاست ایران شوید.
2. وارد **File Manager** شوید.
3. داخل `public_html` یک پوشه بسازید. مثال:

```text
azadi
```

4. فایل زیر را آپلود کنید:

```text
releases/azadirelay-internal-iran-host.zip
```

5. فایل ZIP را Extract کنید.
6. فایل‌ها باید کنار هم باشند:

```text
public_html/azadi/index.php
public_html/azadi/config.php
public_html/azadi/chat_mw.db
public_html/azadi/bridge_cron.php
public_html/azadi/bridge_endpoint.php
```

مهم‌ترین نکته:

```text
chat_mw.db باید کنار index.php باشد.
نام پوشه مهم نیست.
```

درست:

```text
public_html/azadi/index.php
public_html/azadi/chat_mw.db
```

اشتباه:

```text
public_html/azadi/index.php
public_html/azadi/db/chat_mw.db
```

---

## 3.1 تست دیتابیس نسخه داخلی

بعد از آپلود، این آدرس را باز کنید:

```text
https://YOUR-IRAN-DOMAIN/azadi/db_check.php
```

اگر همه چیز درست باشد، باید مواردی شبیه این ببینید:

```text
pdo_sqlite: فعال ✅
فایل دیتابیس موجود است: بله ✅
پوشه قابل نوشتن است: بله ✅
فایل دیتابیس readable: بله ✅
فایل دیتابیس writable: بله ✅
اتصال و نوشتن دیتابیس موفق بود ✅
```

اگر این صفحه سبز نبود، اول مشکل دیتابیس و `pdo_sqlite` را حل کنید؛ بعد سراغ مراحل بعد بروید.

---

# 4. نصب نسخه External / Global روی هاست خارج کشور

1. وارد پنل هاست خارج کشور شوید.
2. داخل `public_html` یک پوشه بسازید. مثال:

```text
azadi
```

3. فایل زیر را آپلود کنید:

```text
releases/azadirelay-external-global-host.zip
```

4. Extract کنید.
5. این آدرس را باز کنید:

```text
https://YOUR-GLOBAL-DOMAIN/azadi/db_check.php
```

باید مثل نسخه داخلی وضعیت دیتابیس سبز باشد.

---

# 5. ساخت Cloudflare Worker برای MHR

این Worker مربوط به خود MHR-CFW است.

## 5.1 ورود به Cloudflare

1. بروید به:

```text
https://dash.cloudflare.com
```

2. وارد حساب شوید.
3. از منوی سمت چپ بروید به:

```text
Compute (Workers) → Workers & Pages
```

4. روی **Create** بزنید.
5. گزینه **Start with Hello World** را انتخاب کنید.
6. یک نام بگذارید. مثال:

```text
azadi-mhr-relay
```

7. روی **Deploy** بزنید.
8. بعد از ساخته شدن، روی **Edit code** بزنید.
9. کل کد پیش‌فرض را پاک کنید.
10. فایل زیر را باز کنید:

```text
https://nakhl.sbs/mhr/worker.js
```

یا اگر از فایل‌های همین ریپو استفاده می‌کنید:

```text
mhr/worker.js
```

11. کل کد `worker.js` را داخل Cloudflare paste کنید.

---

## 5.2 تغییر آدرس Worker داخل worker.js

داخل کد Worker این خط را پیدا کنید:

```javascript
const WORKER_URL = "myworker.workers.dev";
```

آدرس Worker خودتان را جایگزین کنید.

مثال:

```javascript
const WORKER_URL = "azadi-mhr-relay.USERNAME.workers.dev";
```

نکته مهم:

```text
اینجا https:// نگذارید.
فقط hostname را بگذارید.
```

درست:

```javascript
const WORKER_URL = "azadi-mhr-relay.USERNAME.workers.dev";
```

اشتباه:

```javascript
const WORKER_URL = "https://azadi-mhr-relay.USERNAME.workers.dev";
```

بعد روی **Deploy** بزنید.

آدرس کامل Worker را هم نگه دارید، چون در Google Script لازم می‌شود:

```text
https://azadi-mhr-relay.USERNAME.workers.dev
```

---

# 6. ساخت Google Apps Script برای MHR

1. بروید به:

```text
https://script.google.com
```

2. روی **New project** بزنید.
3. کد پیش‌فرض را کامل پاک کنید.
4. فایل زیر را باز کنید:

```text
https://nakhl.sbs/mhr/Code.gs
```

یا از ریپو:

```text
mhr/Code.gs
```

5. کل کد `Code.gs` را paste کنید.

---

## 6.1 تغییر AUTH_KEY و WORKER_URL داخل Code.gs

داخل `Code.gs` این دو خط را پیدا کنید:

```javascript
const AUTH_KEY = "STRONG_SECRET_KEY";
const WORKER_URL = "https://example.workers.dev";
```

برای `AUTH_KEY` یک رمز طولانی بگذارید. مثال:

```javascript
const AUTH_KEY = "AzadiRelay_MHR_Secret_2026_x9K_ChangeMe";
```

این رمز را نگه دارید. همین مقدار باید بعداً داخل `config.json` در VPS هم وارد شود.

برای `WORKER_URL` آدرس کامل Worker خودتان را بگذارید:

```javascript
const WORKER_URL = "https://azadi-mhr-relay.USERNAME.workers.dev";
```

اینجا باید `https://` داشته باشد.

درست:

```javascript
const WORKER_URL = "https://azadi-mhr-relay.USERNAME.workers.dev";
```

بعد ذخیره کنید.

---

## 6.2 Deploy کردن Google Apps Script به عنوان Web app

از بالای صفحه روی **Deploy** بزنید.

بعد:

```text
New deployment
```

کنار **Select type** روی آیکن چرخ‌دنده بزنید.

گزینه زیر را انتخاب کنید:

```text
Web app
```

تنظیمات را اینطور بگذارید:

```text
Description     : AzadiRelay MHR
Execute as      : Me
Who has access  : Anyone
```

بعد روی **Deploy** بزنید.

اگر Google اجازه خواست:

```text
Authorize access
انتخاب حساب Google
Advanced
Go to project
Allow
```

بعد از Deploy، Google یک لینک شبیه این می‌دهد:

```text
https://script.google.com/macros/s/AKfycbxxxxxxxxxxxxxxxxxxxxxxxx/exec
```

برای MHR فقط قسمت وسط لازم است:

```text
AKfycbxxxxxxxxxxxxxxxxxxxxxxxx
```

یعنی:

```text
فقط مقدار بعد از /s/ و قبل از /exec را کپی کنید.
کل لینک را داخل config.json نگذارید.
```

درست:

```json
"script_id": "AKfycbxxxxxxxxxxxxxxxxxxxxxxxx"
```

اشتباه:

```json
"script_id": "https://script.google.com/macros/s/AKfycbxxxxxxxxxxxxxxxxxxxxxxxx/exec"
```

---

# 7. نصب MHR-CFW روی VPS Ubuntu

در این مرحله از GitHub دانلود نمی‌کنیم. فایل از لینک داخلی دانلود می‌شود:

```text
https://nakhl.sbs/mhr/mhr-cfw-main.zip
```

## 7.1 ورود به VPS

روی سیستم خودتان ترمینال باز کنید و وارد VPS شوید:

```bash
ssh root@YOUR_VPS_IP
```

به جای `YOUR_VPS_IP` آی‌پی VPS خودتان را بگذارید.

---

## 7.2 نصب ابزارهای لازم

این دستور را کامل بزنید:

```bash
apt update && apt upgrade -y
apt install -y curl wget unzip nano ufw python3 python3-pip python3-venv ca-certificates
```

اگر خطای package گرفتید:

```bash
apt --fix-broken install -y
apt update
```

بعد دوباره دستور نصب را بزنید.

---

## 7.3 دانلود MHR از nakhl.sbs

```bash
cd /opt
wget https://nakhl.sbs/mhr/mhr-cfw-main.zip -O mhr-cfw-main.zip
```

اگر `wget` کار نکرد:

```bash
curl -L https://nakhl.sbs/mhr/mhr-cfw-main.zip -o mhr-cfw-main.zip
```

---

## 7.4 باز کردن فایل ZIP

```bash
rm -rf /opt/mhr-cfw /opt/mhr-cfw-main
unzip /opt/mhr-cfw-main.zip -d /opt
mv /opt/mhr-cfw-main /opt/mhr-cfw
cd /opt/mhr-cfw
```

حالا چک کنید فایل‌ها هستند:

```bash
ls -la
```

باید فایل‌هایی مثل این ببینید:

```text
main.py
requirements.txt
config.example.json
run.sh
setup.py
deploy
```

---

## 7.5 نصب کتابخانه‌های Python

```bash
cd /opt/mhr-cfw
python3 -m venv .venv
.venv/bin/python -m pip install --upgrade pip
.venv/bin/python -m pip install -r requirements.txt
```

اگر نصب packageها مشکل داشت، این دستور را بزنید:

```bash
.venv/bin/python -m pip install -r requirements.txt -i https://mirror-pypi.runflare.com/simple/ --trusted-host mirror-pypi.runflare.com
```

---

## 7.6 ساخت config.json

فایل اصلی نمونه را تغییر ندهید. از روی آن کپی بگیرید:

```bash
cd /opt/mhr-cfw
cp config.example.json config.json
nano config.json
```

داخل `config.json` فقط همین مقدارهای موجود را تغییر دهید. خط جدید اضافه نکنید.

این مقدار را پیدا کنید:

```json
"script_id": "YOUR_APPS_SCRIPT_DEPLOYMENT_ID"
```

به این شکل تغییر دهید:

```json
"script_id": "AKfycbxxxxxxxxxxxxxxxxxxxxxxxx"
```

این همان مقدار Google Apps Script است؛ فقط `AKfycb...`، نه کل لینک.

بعد این مقدار را پیدا کنید:

```json
"auth_key": "CHANGE_ME_TO_A_STRONG_SECRET"
```

همان رمزی را بگذارید که داخل `Code.gs` برای `AUTH_KEY` گذاشتید:

```json
"auth_key": "AzadiRelay_MHR_Secret_2026_x9K_ChangeMe"
```

بعد این مقدار را پیدا کنید:

```json
"listen_host": "127.0.0.1"
```

و به این تغییر دهید:

```json
"listen_host": "0.0.0.0"
```

پورت را هم چک کنید:

```json
"listen_port": 8085
```

اگر همین بود، دست نزنید. اگر نبود، روی `8085` بگذارید.

نکته خیلی مهم:

```text
داخل config.json مقدار worker_url اضافه نکنید.
داخل config.json secret جدید اضافه نکنید.
فقط همان فیلدهای موجود را ویرایش کنید.
```

برای ذخیره در nano:

```text
Ctrl + O
Enter
Ctrl + X
```

---

# 8. اجرای MHR روی VPS

بعد از تنظیم `config.json`، داخل همان صفحه SSH این دستور را بزنید:

```bash
cd /opt/mhr-cfw
.venv/bin/python main.py
```

اگر همه چیز درست باشد، خروجی شبیه این می‌بینید:

```text
HTTP proxy listening on 0.0.0.0:8085
```

یا در بین لاگ‌ها باید ببینید که proxy روی پورت `8085` روشن شده است.

این صفحه را نبندید.

```text
تا وقتی این صفحه SSH باز باشد، MHR روشن می‌ماند.
اگر صفحه را ببندید، Ctrl+C بزنید، یا VPS ریستارت شود، MHR خاموش می‌شود.
```

اگر خواستید بعداً همیشه بعد از ریبوت هم خودکار روشن شود، می‌توانید برایش سرویس systemd بسازید؛ ولی برای راه‌اندازی ساده، همین باز ماندن صفحه کافی است.

---

## 8.1 چک پورت 8085

اگر یک SSH دیگر باز کردید، می‌توانید این را بزنید:

```bash
ss -lntp | grep 8085
```

خروجی درست باید شامل این باشد:

```text
0.0.0.0:8085
```

اگر دیدید:

```text
127.0.0.1:8085
```

یعنی `listen_host` اشتباه مانده. دوباره فایل را باز کنید:

```bash
nano /opt/mhr-cfw/config.json
```

و این مقدار را درست کنید:

```json
"listen_host": "0.0.0.0"
```

بعد MHR را دوباره اجرا کنید.

---

## 8.2 باز بودن پورت در فایروال VPS

در یک صفحه SSH دیگر این دستورها را بزنید:

```bash
ufw allow OpenSSH
ufw allow 8085/tcp
ufw --force enable
ufw status
```

باید ببینید:

```text
8085/tcp ALLOW Anywhere
```

اگر شرکت VPS پنل Firewall جدا دارد، داخل پنل VPS هم TCP پورت `8085` را باز کنید.

---

# 9. تنظیم فایل config.php در نسخه Internal و External

## 9.1 رمزهای مشترک

در هر دو نسخه، یعنی هم Internal و هم External، این دو مقدار را بگذارید:

```php
'bridge_secret' => 'یک رمز طولانی مشترک',
'cron_key' => 'یک رمز طولانی دیگر',
```

مهم:

```text
bridge_secret باید در هر دو نسخه دقیقاً یکی باشد.
cron_key هم بهتر است در هر دو نسخه یکی باشد.
```

نمونه:

```php
'bridge_secret' => 'AzadiRelay_Bridge_2026_x9K_Private_Secret',
'cron_key' => 'AzadiRelay_Cron_2026_7pL_Private_Key',
```

این رمزها را در GitHub یا اسکرین‌شات عمومی قرار ندهید.

---

## 9.2 config.php نسخه Internal

روی هاست ایران، فایل زیر را باز کنید:

```text
config.php
```

مقادیر اصلی باید شبیه این باشد:

```php
'role' => 'internal',

'internal_base_url' => 'https://YOUR-IRAN-DOMAIN/azadi',
'foreign_base_url' => 'https://YOUR-GLOBAL-DOMAIN/azadi',

'bridge_secret' => 'AzadiRelay_Bridge_2026_x9K_Private_Secret',
'cron_key' => 'AzadiRelay_Cron_2026_7pL_Private_Key',

'bridge_proxy_url' => 'http://YOUR_VPS_IP:8085',
```

به جای `YOUR_VPS_IP` آی‌پی VPS را بگذارید.

مثال:

```php
'bridge_proxy_url' => 'http://123.123.123.123:8085',
```

---

## 9.3 config.php نسخه External / Global

روی هاست خارج کشور، فایل `config.php` را باز کنید.

مقادیر اصلی باید شبیه این باشد:

```php
'role' => 'foreign',

'internal_base_url' => 'https://YOUR-IRAN-DOMAIN/azadi',
'foreign_base_url' => 'https://YOUR-GLOBAL-DOMAIN/azadi',

'bridge_secret' => 'AzadiRelay_Bridge_2026_x9K_Private_Secret',
'cron_key' => 'AzadiRelay_Cron_2026_7pL_Private_Key',

'bridge_proxy_url' => '',
```

در نسخه خارجی، `bridge_proxy_url` معمولاً خالی می‌ماند.

---

# 10. تنظیم Cron روی هاست ایران

Bridge باید مرتب اجرا شود تا پیام‌ها جابه‌جا شوند.

در cPanel هاست ایران بروید به:

```text
Cron Jobs
```

یک Cron با اجرای هر 1 دقیقه بسازید.

اگر PHP CLI فعال است:

```bash
*/1 * * * * php -q /home/USERNAME/public_html/azadi/bridge_cron.php >/dev/null 2>&1
```

اگر مسیر PHP روی هاست فرق داشت:

```bash
*/1 * * * * /usr/local/bin/php -q /home/USERNAME/public_html/azadi/bridge_cron.php >/dev/null 2>&1
```

اگر هاست فقط اجرای URL را اجازه می‌دهد:

```bash
*/1 * * * * curl -fsS "https://YOUR-IRAN-DOMAIN/azadi/bridge_cron.php?key=YOUR_CRON_KEY" >/dev/null 2>&1
```

`YOUR_CRON_KEY` همان مقدار `cron_key` داخل `config.php` است.

---

# 11. اگر دامنه خارجی در بعضی کشورها باز نشد

اگر آدرس نسخه خارجی / Global در بعضی کشورها باز نمی‌شود، یک Worker جدا بسازید. این Worker با Worker مربوط به MHR فرق دارد.

کار این Worker جدا:

```text
آدرس Worker را به عنوان آدرس نسخه خارجی استفاده می‌کنید،
و Worker درخواست‌ها را به هاست خارجی واقعی می‌فرستد.
```

## 11.1 ساخت Worker جدا برای Global

1. وارد Cloudflare شوید:

```text
https://dash.cloudflare.com
```

2. بروید به:

```text
Compute (Workers) → Workers & Pages
```

3. روی **Create** بزنید.
4. **Start with Hello World** را انتخاب کنید.
5. نام بگذارید. مثال:

```text
azadi-global-proxy
```

6. Deploy کنید.
7. روی **Edit code** بزنید.
8. کد پیش‌فرض را کامل پاک کنید.
9. فایل زیر را باز کنید:

```text
https://nakhl.sbs/mhr/azadirelay-global-domain-proxy-worker.js
```

یا از ریپو:

```text
cloudflare/azadirelay-global-domain-proxy-worker.js
```

10. کد را paste کنید.

داخل کد این خط را پیدا کنید:

```javascript
const ORIGIN_BASE = "https://YOUR-GLOBAL-HOST/azadi";
```

آدرس واقعی نصب Global را بگذارید:

```javascript
const ORIGIN_BASE = "https://your-real-global-domain.com/azadi";
```

آخر آدرس `/` نگذارید.

درست:

```javascript
const ORIGIN_BASE = "https://your-real-global-domain.com/azadi";
```

اشتباه:

```javascript
const ORIGIN_BASE = "https://your-real-global-domain.com/azadi/";
```

بعد **Deploy** کنید.

حالا Worker یک لینک می‌دهد، مثلاً:

```text
https://azadi-global-proxy.USERNAME.workers.dev
```

در نسخه Internal، مقدار `foreign_base_url` را به همین Worker تغییر دهید:

```php
'foreign_base_url' => 'https://azadi-global-proxy.USERNAME.workers.dev',
```

در نسخه External، `foreign_base_url` می‌تواند همان آدرس واقعی هاست خارجی بماند:

```php
'foreign_base_url' => 'https://your-real-global-domain.com/azadi',
```

---

# 12. تست نهایی

## 12.1 تست MHR

وقتی MHR روی VPS روشن است، روی VPS یا یک سرور دیگر تست کنید:

```bash
curl -I -x http://YOUR_VPS_IP:8085 https://example.com
```

اگر header برگشت، پروکسی روشن است.

اگر timeout شد:

```text
پورت 8085 بسته است
MHR اجرا نیست
listen_host هنوز 127.0.0.1 است
Google Script یا Worker اشتباه تنظیم شده
```

---

## 12.2 تست Bridge

روی نسخه Internal این صفحه را باز کنید:

```text
https://YOUR-IRAN-DOMAIN/azadi/bridge_health.php
```

بعد یک کاربر در Internal و یک کاربر در External بسازید و ارسال پیام را تست کنید.

---

# 13. خطاهای رایج

## خطای دیتابیس

اگر این پیام را دیدید:

```text
خطای اتصال پایگاه‌داده
```

اول این آدرس را باز کنید:

```text
/db_check.php
```

اگر `pdo_sqlite` غیرفعال بود، در cPanel فعالش کنید یا به پشتیبانی پیام بدهید.

---

## MHR اجرا می‌شود ولی هاست ایران وصل نمی‌شود

چک کنید:

```bash
ss -lntp | grep 8085
```

باید این باشد:

```text
0.0.0.0:8085
```

اگر این بود:

```text
127.0.0.1:8085
```

فایل config را درست کنید:

```bash
nano /opt/mhr-cfw/config.json
```

و بگذارید:

```json
"listen_host": "0.0.0.0"
```

---

## AUTH_KEY اشتباه است

این دو مقدار باید دقیقاً یکی باشند:

```text
AUTH_KEY داخل Code.gs
auth_key داخل config.json روی VPS
```

حتی یک فاصله اضافه هم باعث خراب شدن اتصال می‌شود.

---

## script_id اشتباه است

داخل Google Apps Script لینک کامل شبیه این است:

```text
https://script.google.com/macros/s/AKfycbxxxxxxxxxxxxxxxx/exec
```

داخل `config.json` فقط این قسمت را بگذارید:

```text
AKfycbxxxxxxxxxxxxxxxx
```

---

## Worker مربوط به MHR اشتباه است

داخل `worker.js` باید `WORKER_URL` بدون `https://` باشد:

```javascript
const WORKER_URL = "azadi-mhr-relay.USERNAME.workers.dev";
```

داخل `Code.gs` باید `WORKER_URL` با `https://` باشد:

```javascript
const WORKER_URL = "https://azadi-mhr-relay.USERNAME.workers.dev";
```

---

## Cron کار نمی‌کند

چک کنید:

```text
مسیر bridge_cron.php درست باشد
cron_key درست باشد
PHP CLI روی هاست فعال باشد
```

اگر PHP CLI کار نکرد، روش curl را استفاده کنید:

```bash
*/1 * * * * curl -fsS "https://YOUR-IRAN-DOMAIN/azadi/bridge_cron.php?key=YOUR_CRON_KEY" >/dev/null 2>&1
```

---

# 14. چیزهایی که نباید عمومی شوند

این موارد را در GitHub، README، عکس، کانال یا گروه عمومی نگذارید:

```text
IP واقعی VPS
AUTH_KEY واقعی MHR
bridge_secret واقعی
cron_key واقعی
رمز ادمین واقعی
آدرس خصوصی Google Script اگر نمی‌خواهید عمومی شود
```

نمونه‌های داخل README فقط نمونه هستند و باید هنگام نصب عوض شوند.

---

# 15. چک‌لیست آخر

```text
[ ] نسخه Internal روی هاست ایران نصب شد
[ ] نسخه External روی هاست خارج نصب شد
[ ] db_check.php روی هر دو سبز است
[ ] Cloudflare Worker برای MHR ساخته شد
[ ] Google Apps Script به صورت Web app ساخته شد
[ ] فقط AKfycb... داخل config.json گذاشته شد
[ ] auth_key در config.json با AUTH_KEY در Code.gs یکی است
[ ] listen_host در config.json برابر 0.0.0.0 است
[ ] listen_port برابر 8085 است
[ ] MHR روی VPS اجرا شده و صفحه SSH باز است
[ ] bridge_proxy_url در Internal برابر http://YOUR_VPS_IP:8085 است
[ ] bridge_secret در Internal و External یکی است
[ ] cron_key در Internal و External یکی است
[ ] Cron روی هاست ایران فعال است
[ ] اگر دامنه خارجی باز نمی‌شد، Worker جدا برای Global ساخته شد
```

---

## Repository

```text
https://github.com/M0lavi/azadirelay
```

</div>
