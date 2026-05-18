<div dir="rtl">

# AzadiRelay — راهنمای کامل راه‌اندازی ارتباط ایران و خارج کشور

[![GitHub](https://img.shields.io/badge/GitHub-AzadiRelay-8B1E2D?logo=github)](https://github.com/M0lavi/azadirelay)

---

## فهرست مطالب

- [AzadiRelay چیست؟](#azadirelay-چیست)
- [مسیر کلی ارتباط](#مسیر-کلی-ارتباط)
- [چیزهایی که قبل از شروع لازم دارید](#چیزهایی-که-قبل-از-شروع-لازم-دارید)
- [مرحله ۱ — نصب نسخه Internal روی هاست ایران](#مرحله-۱--نصب-نسخه-internal-روی-هاست-ایران)
- [مرحله ۲ — نصب نسخه External روی هاست خارج کشور](#مرحله-۲--نصب-نسخه-external-روی-هاست-خارج-کشور)
- [مرحله ۳ — ساخت Cloudflare Worker برای MHR](#مرحله-۳--ساخت-cloudflare-worker-برای-mhr)
- [مرحله ۴ — ساخت Google Apps Script برای MHR](#مرحله-۴--ساخت-google-apps-script-برای-mhr)
- [مرحله ۵ — نصب MHR-CFW روی VPS Ubuntu](#مرحله-۵--نصب-mhr-cfw-روی-vps-ubuntu)
- [مرحله ۶ — تنظیم فایل config.json روی VPS](#مرحله-۶--تنظیم-فایل-configjson-روی-vps)
- [مرحله ۷ — اجرای MHR روی VPS](#مرحله-۷--اجرای-mhr-روی-vps)
- [مرحله ۸ — اتصال AzadiRelay Internal به MHR](#مرحله-۸--اتصال-azadirelay-internal-به-mhr)
- [مرحله ۹ — ساخت Cron Job برای Bridge](#مرحله-۹--ساخت-cron-job-برای-bridge)
- [مرحله ۱۰ — تست نهایی ارتباط](#مرحله-۱۰--تست-نهایی-ارتباط)
- [اگر دامنه خارج کشور باز نشد](#اگر-دامنه-خارج-کشور-باز-نشد)
- [عیب‌یابی](#عیب‌یابی)
- [چک‌لیست نهایی](#چک‌لیست-نهایی)
- [نکات امنیتی](#نکات-امنیتی)

---

## AzadiRelay چیست؟

**AzadiRelay** یک پیام‌رسان self-hosted برای برقراری ارتباط امن بین ایران و خارج کشور است.

با این پروژه شما دو نسخه جدا نصب می‌کنید:

- یک نسخه روی **هاست ایران** برای کاربران داخل ایران
- یک نسخه روی **هاست خارج کشور** برای کاربران خارج کشور
- یک مسیر واسط با **MHR-CFW روی VPS Ubuntu** برای عبور درخواست‌های Bridge

هدف اصلی:

```text
ارسال و دریافت پیام بین ایران و خارج کشور از مسیر Relay
```

---

## مسیر کلی ارتباط

```text
کاربر داخل ایران
    │
    ▼
AzadiRelay Internal
    │
    ▼
MHR-CFW روی VPS Ubuntu
    │
    ▼
Google Apps Script
    │
    ▼
Cloudflare Worker
    │
    ▼
AzadiRelay External / Global
    │
    ▼
کاربر خارج کشور
```

پورت پیش‌فرض MHR در این آموزش:

```text
8085
```

یعنی در پایان، نسخه Internal باید به این آدرس وصل شود:

```php
'bridge_proxy_url' => 'http://YOUR_VPS_IP:8085',
```

---

# چیزهایی که قبل از شروع لازم دارید

## ۱. هاست ایران

برای نصب نسخه Internal.

قبل از نصب مطمئن شوید هاست ایران این موارد را دارد:

| مورد | لازم است؟ |
|---|---|
| PHP 8 یا بالاتر | بله |
| SQLite | بله |
| pdo_sqlite | بله |
| Cron Job | بله |
| File Manager | بله |
| SSL | بهتر است داشته باشد |

برای اطمینان از پشتیبانی هاست بپرسید:

```text
سلام، آیا روی هاست من PHP pdo_sqlite و sqlite3 فعال است؟
آیا امکان Cron Job دارم؟
```

اگر `pdo_sqlite` فعال نباشد، برنامه به دیتابیس وصل نمی‌شود.

---

## ۲. هاست خارج کشور

برای نصب نسخه External / Global.

این هاست هم باید این موارد را داشته باشد:

| مورد | لازم است؟ |
|---|---|
| PHP 8 یا بالاتر | بله |
| SQLite | بله |
| pdo_sqlite | بله |
| SSL | بله |
| دامنه یا ساب‌دامنه | بله |

اگر دامنه خارج کشور در بعضی کشورها باز نشد، در بخش [اگر دامنه خارج کشور باز نشد](#اگر-دامنه-خارج-کشور-باز-نشد) راهکار گفته شده است.

---

## ۳. VPS Ubuntu

برای اجرای MHR-CFW.

مشخصات پیشنهادی:

| مورد | مقدار پیشنهادی |
|---|---|
| سیستم‌عامل | Ubuntu 22.04 یا 24.04 |
| RAM | حداقل 1GB |
| CPU | حداقل 1 Core |
| IP | IPv4 عمومی |
| دسترسی | root یا sudo |
| پورت | TCP 8085 باز باشد |

---

## ۴. حساب Cloudflare

برای ساخت Worker.

آدرس:

```text
https://dash.cloudflare.com
```

---

## ۵. حساب Google

برای ساخت Google Apps Script.

آدرس:

```text
https://script.google.com
```

---

## ۶. فایل‌های دانلود

در این آموزش فایل‌ها از لینک داخلی زیر دانلود می‌شوند:

```text
https://nakhl.sbs
```

لینک‌های مورد استفاده در آموزش:

```text
https://nakhl.sbs/mhr/mhr-cfw-main.zip
https://nakhl.sbs/mhr/worker.js
https://nakhl.sbs/mhr/Code.gs
https://nakhl.sbs/releases/azadirelay-internal.zip
https://nakhl.sbs/releases/azadirelay-external.zip
https://nakhl.sbs/cloudflare/azadirelay-global-domain-proxy-worker.js
```

---

# مرحله ۱ — نصب نسخه Internal روی هاست ایران

نسخه Internal همان نسخه‌ای است که روی هاست ایران نصب می‌شود.

## ۱. ورود به File Manager

وارد کنترل‌پنل هاست ایران شوید.

معمولاً از این مسیر:

```text
cPanel → File Manager
```

یا در DirectAdmin:

```text
File Manager
```

---

## ۲. ساخت پوشه نصب

داخل `public_html` یک پوشه بسازید.

مثال:

```text
azadi
```

مسیر نهایی می‌شود:

```text
public_html/azadi
```

---

## ۳. دانلود یا آپلود فایل نسخه Internal

فایل نسخه Internal از این لینک است:

```text
https://nakhl.sbs/releases/azadirelay-internal.zip
```

اگر هاست شما امکان دانلود مستقیم با URL دارد، از همان استفاده کنید.

اگر ندارد:

1. فایل را با مرورگر دانلود کنید
2. وارد File Manager شوید
3. داخل پوشه `azadi` آپلود کنید
4. روی فایل ZIP بزنید
5. گزینه **Extract** را بزنید

---

## ۴. چک کردن جای فایل‌ها

بعد از Extract باید فایل‌ها شبیه این باشند:

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

مهم‌ترین نکته:

```text
index.php و chat_mw.db باید کنار هم باشند.
```

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

اسم پوشه مهم نیست. فقط `chat_mw.db` باید کنار `index.php` باشد.

---

## ۵. تست دیتابیس نسخه Internal

این آدرس را باز کنید:

```text
https://YOUR-IRAN-DOMAIN/azadi/db_check.php
```

اگر همه چیز درست باشد باید چنین چیزهایی ببینید:

```text
pdo_sqlite: فعال ✅
فایل دیتابیس موجود است: بله ✅
پوشه قابل نوشتن است: بله ✅
فایل دیتابیس readable: بله ✅
فایل دیتابیس writable: بله ✅
اتصال و نوشتن دیتابیس موفق بود ✅
```

اگر `pdo_sqlite` غیرفعال بود، در cPanel بروید به:

```text
Select PHP Version → Extensions
```

بعد این‌ها را فعال کنید:

```text
pdo_sqlite
sqlite3
```

اگر این گزینه‌ها نبودند، به پشتیبانی هاست پیام بدهید:

```text
لطفاً افزونه‌های PHP pdo_sqlite و sqlite3 را برای هاست من فعال کنید.
```

---

## ۶. تنظیم config.php نسخه Internal

در File Manager فایل زیر را باز کنید:

```text
config.php
```

در نسخه Internal این موارد را تنظیم کنید.

نمونه:

```php
<?php

return [

    # نوع این نسخه
    # برای هاست ایران باید internal باشد
    'role' => 'internal',

    # آدرس نسخه داخلی روی هاست ایران
    # آخر آدرس / نگذارید
    'internal_base_url' => 'https://YOUR-IRAN-DOMAIN/azadi',

    # آدرس نسخه خارجی روی هاست خارج کشور
    # آخر آدرس / نگذارید
    'foreign_base_url' => 'https://YOUR-FOREIGN-DOMAIN/azadi',

    # رمز مشترک Bridge
    # این مقدار باید در Internal و External دقیقاً یکی باشد
    # این رمز را عمومی نکنید
    'bridge_secret' => 'PUT_SAME_LONG_BRIDGE_SECRET_HERE',

    # رمز اجرای Cron
    # این مقدار را طولانی و خصوصی بگذارید
    'cron_key' => 'PUT_LONG_RANDOM_CRON_KEY_HERE',

    # آدرس MHR روی VPS
    # فقط در نسخه Internal پر می‌شود
    # YOUR_VPS_IP را با IP واقعی VPS عوض کنید
    'bridge_proxy_url' => 'http://YOUR_VPS_IP:8085',

    # لینک ریپازیتوری پروژه
    'repository_url' => 'https://github.com/M0lavi/azadirelay',

    # یوزرنیم و رمز پنل ادمین
    # بعد از نصب واقعی بهتر است تغییر دهید
    'admin_username' => 'admin',
    'admin_password' => 'admin',

];
```

نکته مهم:

```text
bridge_secret در Internal و External باید دقیقاً یکی باشد.
```

مثال:

```php
'bridge_secret' => 'AzadiRelay_Bridge_2026_X9pQ_private_key',
```

در هر دو فایل همین را بگذارید.

---

# مرحله ۲ — نصب نسخه External روی هاست خارج کشور

نسخه External همان نسخه‌ای است که روی هاست خارج کشور نصب می‌شود.

## ۱. ورود به File Manager هاست خارج

وارد کنترل‌پنل هاست خارج کشور شوید.

داخل `public_html` یک پوشه بسازید.

مثال:

```text
azadi
```

---

## ۲. دانلود یا آپلود نسخه External

فایل نسخه External از این لینک است:

```text
https://nakhl.sbs/releases/azadirelay-external.zip
```

روش نصب:

1. فایل ZIP را داخل پوشه `azadi` آپلود کنید
2. روی ZIP بزنید
3. گزینه **Extract** را بزنید

بعد از Extract فایل‌ها باید شبیه این باشند:

```text
azadi/
├── index.php
├── config.php
├── chat_mw.db
├── bridge_endpoint.php
├── db_check.php
└── ...
```

---

## ۳. تست دیتابیس نسخه External

این آدرس را باز کنید:

```text
https://YOUR-FOREIGN-DOMAIN/azadi/db_check.php
```

باید وضعیت دیتابیس سبز باشد.

اگر `pdo_sqlite` فعال نبود، مثل هاست ایران باید از کنترل‌پنل هاست یا پشتیبانی هاست فعال شود.

---

## ۴. تنظیم config.php نسخه External

فایل زیر را باز کنید:

```text
config.php
```

نمونه تنظیم درست برای External:

```php
<?php

return [

    # نوع این نسخه
    # برای هاست خارج کشور باید foreign باشد
    'role' => 'foreign',

    # آدرس نسخه داخلی روی هاست ایران
    # آخر آدرس / نگذارید
    'internal_base_url' => 'https://YOUR-IRAN-DOMAIN/azadi',

    # آدرس نسخه خارجی روی همین هاست خارج کشور
    # آخر آدرس / نگذارید
    'foreign_base_url' => 'https://YOUR-FOREIGN-DOMAIN/azadi',

    # رمز مشترک Bridge
    # باید دقیقاً همان مقداری باشد که در Internal گذاشتید
    'bridge_secret' => 'PUT_SAME_LONG_BRIDGE_SECRET_HERE',

    # رمز اجرای Cron
    # بهتر است همان مقداری باشد که در Internal گذاشتید
    'cron_key' => 'PUT_LONG_RANDOM_CRON_KEY_HERE',

    # در نسخه External معمولاً خالی می‌ماند
    # چون مسیر پروکسی برای Internal است
    'bridge_proxy_url' => '',

    # لینک ریپازیتوری پروژه
    'repository_url' => 'https://github.com/M0lavi/azadirelay',

    # یوزرنیم و رمز پنل ادمین
    # بعد از نصب واقعی بهتر است تغییر دهید
    'admin_username' => 'admin',
    'admin_password' => 'admin',

];
```

مهم:

```text
در External مقدار bridge_proxy_url را خالی بگذارید.
```

---

# مرحله ۳ — ساخت Cloudflare Worker برای MHR

Cloudflare Worker بخشی از مسیر MHR است.

## ۱. ورود به Cloudflare

وارد این آدرس شوید:

```text
https://dash.cloudflare.com
```

اگر حساب ندارید، ثبت‌نام کنید.

---

## ۲. رفتن به Workers

از منوی سمت چپ بروید به:

```text
Compute (Workers)
```

بعد روی:

```text
Workers & Pages
```

کلیک کنید.

---

## ۳. ساخت Worker جدید

روی دکمه:

```text
Create
```

کلیک کنید.

بعد گزینه:

```text
Start with Hello World
```

را انتخاب کنید.

یک نام برای Worker بگذارید.

مثال:

```text
azadi-mhr-worker
```

بعد روی:

```text
Deploy
```

بزنید.

---

## ۴. ویرایش کد Worker

بعد از Deploy، روی:

```text
Edit code
```

کلیک کنید.

کد پیش‌فرض را کامل پاک کنید.

در ویندوز:

```text
Ctrl + A
Delete
```

---

## ۵. قرار دادن کد worker.js

کد Worker از این لینک است:

```text
https://nakhl.sbs/mhr/worker.js
```

فایل را باز کنید، کل کد را کپی کنید و داخل Cloudflare Worker paste کنید.

---

## ۶. تنظیم آدرس Worker داخل کد

داخل کد این خط را پیدا کنید:

```javascript
const WORKER_URL = "myworker.workers.dev";
```

مقدار `myworker.workers.dev` را با آدرس Worker خودتان عوض کنید.

مثال:

```javascript
const WORKER_URL = "azadi-mhr-worker.YOURNAME.workers.dev";
```

نکته:

```text
اینجا معمولاً https:// نمی‌گذارید، فقط دامنه Worker را می‌گذارید.
```

بعد روی:

```text
Deploy
```

کلیک کنید.

---

## ۷. نگه داشتن آدرس Worker

آدرس Worker شما شبیه این است:

```text
https://azadi-mhr-worker.YOURNAME.workers.dev
```

این آدرس را نگه دارید؛ در Google Apps Script لازم می‌شود.

---

# مرحله ۴ — ساخت Google Apps Script برای MHR

Google Apps Script بخش بعدی مسیر MHR است.

## ۱. ورود به Google Apps Script

وارد این آدرس شوید:

```text
https://script.google.com
```

با حساب Google خود وارد شوید.

---

## ۲. ساخت پروژه جدید

روی:

```text
New project
```

کلیک کنید.

کد پیش‌فرض را پاک کنید.

کد پیش‌فرض معمولاً این است:

```javascript
function myFunction() {

}
```

همه آن را حذف کنید.

---

## ۳. قرار دادن کد Code.gs

کد Apps Script از این لینک است:

```text
https://nakhl.sbs/mhr/Code.gs
```

فایل را باز کنید، کل کد را کپی کنید و داخل Google Apps Script paste کنید.

---

## ۴. تنظیم AUTH_KEY و WORKER_URL

داخل کد این دو خط را پیدا کنید:

```javascript
const AUTH_KEY = "STRONG_SECRET_KEY";
const WORKER_URL = "https://example.workers.dev";
```

حالا `AUTH_KEY` را با یک رمز طولانی عوض کنید.

مثال:

```javascript
const AUTH_KEY = "Azadi_MHR_AUTH_2026_x9Pq_very_secret";
```

این رمز را نگه دارید، چون در `config.json` روی VPS هم باید همین را بگذارید.

بعد `WORKER_URL` را با آدرس Worker خودتان عوض کنید.

مثال:

```javascript
const WORKER_URL = "https://azadi-mhr-worker.YOURNAME.workers.dev";
```

بعد ذخیره کنید:

```text
Ctrl + S
```

---

## ۵. Deploy کردن به صورت Web app

از بالای صفحه روی:

```text
Deploy
```

کلیک کنید.

بعد:

```text
New deployment
```

را بزنید.

کنار گزینه:

```text
Select type
```

روی آیکون چرخ‌دنده بزنید.

بعد انتخاب کنید:

```text
Web app
```

تنظیمات را این‌طور بگذارید:

```text
Description     : AzadiRelay MHR
Execute as      : Me
Who has access  : Anyone
```

بعد روی:

```text
Deploy
```

کلیک کنید.

---

## ۶. تأیید دسترسی Google

اگر Google اجازه خواست:

1. روی **Authorize access** بزنید
2. حساب Google خود را انتخاب کنید
3. اگر پیام امنیتی آمد، روی **Advanced** بزنید
4. روی **Go to project** بزنید
5. روی **Allow** بزنید

---

## ۷. کپی کردن Deployment ID

بعد از Deploy، یک لینک شبیه این می‌بینید:

```text
https://script.google.com/macros/s/AKfycbxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx/exec
```

برای MHR کل لینک لازم نیست.

فقط قسمت وسط را کپی کنید:

```text
AKfycbxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

یعنی این بخش:

```text
https://script.google.com/macros/s/[این قسمت]/exec
```

این مقدار را نگه دارید. در `config.json` باید داخل `script_id` قرار بگیرد.

---

# مرحله ۵ — نصب MHR-CFW روی VPS Ubuntu

در این مرحله MHR روی VPS نصب می‌شود.

## ۱. ورود به VPS

از کامپیوتر خود وارد VPS شوید:

```bash
ssh root@YOUR_VPS_IP
```

به جای `YOUR_VPS_IP` آی‌پی VPS خودتان را بگذارید.

اگر کاربر شما root نیست، با کاربر خود وارد شوید و قبل دستورها `sudo` بگذارید.

---

## ۲. آپدیت VPS

```bash
apt update && apt upgrade -y
```

---

## ۳. نصب ابزارهای لازم

```bash
apt install -y curl wget unzip nano ufw python3 python3-pip python3-venv ca-certificates
```

اگر خطا داد:

```bash
apt --fix-broken install -y
apt update
```

بعد دوباره دستور نصب را بزنید.

---

## ۴. رفتن به پوشه opt

```bash
cd /opt
```

---

## ۵. دانلود MHR از لینک داخلی

```bash
wget https://nakhl.sbs/mhr/mhr-cfw-main.zip -O mhr-cfw-main.zip
```

اگر `wget` کار نکرد:

```bash
curl -L https://nakhl.sbs/mhr/mhr-cfw-main.zip -o mhr-cfw-main.zip
```

---

## ۶. استخراج فایل

```bash
rm -rf /opt/mhr-cfw /opt/mhr-cfw-main
unzip /opt/mhr-cfw-main.zip -d /opt
mv /opt/mhr-cfw-main /opt/mhr-cfw
cd /opt/mhr-cfw
```

---

## ۷. چک کردن فایل‌ها

```bash
ls -la
```

باید فایل‌هایی شبیه این ببینید:

```text
main.py
requirements.txt
config.example.json
run.sh
deploy/
src/
```

اگر این فایل‌ها نبودند، یعنی ZIP درست استخراج نشده یا مسیر پوشه فرق دارد.

---

## ۸. ساخت محیط Python

```bash
python3 -m venv .venv
```

---

## ۹. نصب نیازمندی‌ها

```bash
.venv/bin/python -m pip install --upgrade pip
.venv/bin/python -m pip install -r requirements.txt
```

اگر نصب packageها مشکل داشت:

```bash
.venv/bin/python -m pip install -r requirements.txt -i https://mirror-pypi.runflare.com/simple/ --trusted-host mirror-pypi.runflare.com
```

---

# مرحله ۶ — تنظیم فایل config.json روی VPS

## ۱. ساخت config.json

داخل پوشه MHR باشید:

```bash
cd /opt/mhr-cfw
```

از روی نمونه، فایل config بسازید:

```bash
cp config.example.json config.json
```

---

## ۲. باز کردن config.json

```bash
nano config.json
```

---

## ۳. تنظیم script_id

داخل فایل دنبال این قسمت بگردید:

```json
"script_id": ""
```

یا اگر مقدار نمونه داشت، همان را تغییر دهید.

مقدار `AKfycb...` که از Google Apps Script گرفتید را اینجا بگذارید.

مثال:

```json
"script_id": "AKfycbxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
```

نکته:

```text
اینجا فقط AKfycb... را بگذارید، نه لینک کامل Google Script.
```

درست:

```json
"script_id": "AKfycbxxxxxxxxxxxxxxxx"
```

اشتباه:

```json
"script_id": "https://script.google.com/macros/s/AKfycbxxxxxxxxxxxxxxxx/exec"
```

---

## ۴. تنظیم auth_key

داخل فایل دنبال این قسمت بگردید:

```json
"auth_key": ""
```

مقدار آن باید دقیقاً همان `AUTH_KEY` باشد که داخل `Code.gs` گذاشتید.

مثال:

```json
"auth_key": "Azadi_MHR_AUTH_2026_x9Pq_very_secret"
```

اگر حتی یک حرف فرق داشته باشد، MHR کار نمی‌کند.

---

## ۵. تنظیم listen_host

داخل فایل دنبال این قسمت بگردید:

```json
"listen_host": "127.0.0.1"
```

آن را به این تغییر دهید:

```json
"listen_host": "0.0.0.0"
```

مهم:

```text
اگر 127.0.0.1 باشد، فقط خود VPS می‌تواند به MHR وصل شود.
اگر 0.0.0.0 باشد، هاست ایران هم می‌تواند به آن وصل شود.
```

---

## ۶. تنظیم listen_port

داخل فایل دنبال این قسمت بگردید:

```json
"listen_port": 8085
```

اگر عدد دیگری بود، آن را روی 8085 بگذارید:

```json
"listen_port": 8085
```

---

## ۷. ذخیره فایل در nano

برای ذخیره:

```text
Ctrl + O
Enter
Ctrl + X
```

---

## ۸. باز کردن پورت 8085 در فایروال VPS

```bash
ufw allow OpenSSH
ufw allow 8085/tcp
ufw --force enable
ufw status
```

باید چیزی شبیه این ببینید:

```text
8085/tcp ALLOW Anywhere
```

اگر پنل شرکت VPS هم Firewall جدا دارد، داخل پنل هم پورت TCP `8085` را باز کنید.

---

# مرحله ۷ — اجرای MHR روی VPS

در این آموزش MHR را ساده اجرا می‌کنیم.

یعنی همان صفحه SSH باز می‌ماند و MHR داخل همان صفحه روشن می‌ماند.

## ۱. رفتن به پوشه MHR

```bash
cd /opt/mhr-cfw
```

---

## ۲. اجرای MHR

```bash
.venv/bin/python main.py --config /opt/mhr-cfw/config.json
```

---

## ۳. خروجی موفق

اگر همه چیز درست باشد باید متنی شبیه این ببینید:

```text
HTTP proxy listening on 0.0.0.0:8085
```

یا در لاگ‌ها ببینید که پروکسی روی پورت `8085` اجرا شده است.

---

## ۴. این صفحه را نبندید

تا وقتی این صفحه SSH باز باشد، MHR روشن می‌ماند.

اگر این کارها را انجام دهید، MHR خاموش می‌شود:

```text
بستن صفحه SSH
زدن Ctrl + C
ریستارت شدن VPS
خاموش شدن VPS
```

پس اگر می‌خواهید Relay فعال بماند، این صفحه را باز نگه دارید.

---

## ۵. چک کردن پورت از یک ترمینال دیگر

اگر یک SSH دیگر باز کردید، می‌توانید چک کنید:

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

یعنی `listen_host` را درست تغییر نداده‌اید.

دوباره config را باز کنید:

```bash
nano /opt/mhr-cfw/config.json
```

و مقدار را بگذارید:

```json
"listen_host": "0.0.0.0"
```

بعد MHR را دوباره اجرا کنید.

---

# مرحله ۸ — اتصال AzadiRelay Internal به MHR

حالا باید نسخه Internal روی هاست ایران را به MHR وصل کنید.

## ۱. باز کردن config.php نسخه Internal

در هاست ایران وارد File Manager شوید.

فایل زیر را باز کنید:

```text
public_html/azadi/config.php
```

---

## ۲. پیدا کردن bridge_proxy_url

این خط را پیدا کنید:

```php
'bridge_proxy_url' => '',
```

یا اگر مقدار نمونه دارد:

```php
'bridge_proxy_url' => 'http://YOUR_VPS_IP:8085',
```

---

## ۳. گذاشتن IP واقعی VPS

به جای `YOUR_VPS_IP` آی‌پی VPS خودتان را بگذارید.

مثال:

```php
'bridge_proxy_url' => 'http://123.123.123.123:8085',
```

مهم:

```text
در نسخه Internal این مقدار باید پر باشد.
در نسخه External این مقدار باید خالی باشد.
```

---

# مرحله ۹ — ساخت Cron Job برای Bridge

Bridge باید مرتب اجرا شود تا پیام‌ها بین Internal و External رد و بدل شوند.

## ۱. ورود به Cron Jobs

در cPanel بروید به:

```text
Cron Jobs
```

---

## ۲. انتخاب اجرای هر یک دقیقه

زمان اجرا را روی هر یک دقیقه بگذارید.

معمولاً شکلش این است:

```text
Minute: *
Hour: *
Day: *
Month: *
Weekday: *
```

---

## ۳. دستور Cron با PHP

مسیر فایل `bridge_cron.php` را مطابق هاست خودتان بگذارید.

نمونه:

```bash
php -q /home/USERNAME/public_html/azadi/bridge_cron.php >/dev/null 2>&1
```

اگر مسیر PHP روی هاست شما فرق داشت، ممکن است این باشد:

```bash
/usr/local/bin/php -q /home/USERNAME/public_html/azadi/bridge_cron.php >/dev/null 2>&1
```

اگر مطمئن نیستید، از پشتیبانی هاست بپرسید:

```text
مسیر کامل PHP CLI برای Cron Job روی هاست من چیست؟
```

---

## ۴. روش URL اگر PHP CLI کار نکرد

اگر هاست اجازه اجرای PHP CLI نداد، از curl استفاده کنید:

```bash
curl -fsS "https://YOUR-IRAN-DOMAIN/azadi/bridge_cron.php?key=YOUR_CRON_KEY" >/dev/null 2>&1
```

`YOUR_CRON_KEY` همان مقداری است که در `config.php` گذاشتید.

مثال:

```php
'cron_key' => 'Azadi_Cron_2026_Private_Key',
```

پس URL می‌شود:

```text
https://YOUR-IRAN-DOMAIN/azadi/bridge_cron.php?key=Azadi_Cron_2026_Private_Key
```

---

# مرحله ۱۰ — تست نهایی ارتباط

## ۱. تست نسخه Internal

این آدرس را باز کنید:

```text
https://YOUR-IRAN-DOMAIN/azadi/
```

یک اکانت بسازید.

---

## ۲. تست نسخه External

این آدرس را باز کنید:

```text
https://YOUR-FOREIGN-DOMAIN/azadi/
```

یک اکانت بسازید.

---

## ۳. تست Bridge

از نسخه Internal تلاش کنید به کاربر نسخه External پیام بدهید.

اگر Cron فعال باشد و MHR روشن باشد، پیام باید از مسیر Bridge ارسال شود.

---

## ۴. اگر پیام فوری نرسید

چند دقیقه صبر کنید.

Cron ممکن است هر ۱ دقیقه اجرا شود.

اگر باز هم پیام نرسید، بخش [عیب‌یابی](#عیب‌یابی) را بررسی کنید.

---

# اگر دامنه خارج کشور باز نشد

گاهی ممکن است دامنه نسخه External در بعضی کشورها باز نشود.

در این حالت یک Worker جدا برای دامنه خارجی بسازید.

این Worker برای MHR نیست.  
این Worker فقط برای کمک به باز شدن دامنه خارجی است.

---

## ۱. ساخت Worker جدید

وارد Cloudflare شوید:

```text
https://dash.cloudflare.com
```

بروید به:

```text
Compute (Workers) → Workers & Pages
```

روی:

```text
Create
```

بزنید.

گزینه:

```text
Start with Hello World
```

را انتخاب کنید.

یک نام بگذارید:

```text
azadi-global-proxy
```

روی:

```text
Deploy
```

بزنید.

بعد:

```text
Edit code
```

را بزنید.

---

## ۲. گذاشتن کد Worker مخصوص دامنه Global

کد از این لینک است:

```text
https://nakhl.sbs/cloudflare/azadirelay-global-domain-proxy-worker.js
```

کد پیش‌فرض را کامل پاک کنید و کد بالا را paste کنید.

---

## ۳. تنظیم آدرس دامنه اصلی Global داخل Worker

داخل کد Worker دنبال این قسمت بگردید:

```javascript
const TARGET = "https://YOUR-FOREIGN-DOMAIN/azadi";
```

آن را با آدرس واقعی نسخه External عوض کنید.

مثال:

```javascript
const TARGET = "https://your-global-domain.com/azadi";
```

بعد روی:

```text
Deploy
```

بزنید.

---

## ۴. استفاده از آدرس Worker به جای دامنه خارجی

آدرس Worker شبیه این می‌شود:

```text
https://azadi-global-proxy.YOURNAME.workers.dev
```

حالا در `config.php` هر دو نسخه، مقدار `foreign_base_url` را به آدرس Worker تغییر دهید.

در Internal:

```php
'foreign_base_url' => 'https://azadi-global-proxy.YOURNAME.workers.dev',
```

در External هم اگر لازم بود همین مقدار را هماهنگ کنید.

نکته:

```text
اگر آدرس Worker مستقیم به مسیر /azadi وصل شده، آخرش دوباره /azadi اضافه نکنید.
```

---

# عیب‌یابی

## مشکل ۱ — db_check.php خطای pdo_sqlite می‌دهد

علت:

```text
افزونه pdo_sqlite روی هاست فعال نیست.
```

راه‌حل:

```text
cPanel → Select PHP Version → Extensions → pdo_sqlite
```

یا به پشتیبانی هاست پیام بدهید:

```text
لطفاً pdo_sqlite و sqlite3 را فعال کنید.
```

---

## مشکل ۲ — فایل دیتابیس writable نیست

علت:

```text
هاست اجازه نوشتن روی chat_mw.db یا پوشه نصب را نمی‌دهد.
```

راه‌حل:

در File Manager سطح دسترسی را بررسی کنید:

```text
پوشه نصب: 755
chat_mw.db: 664
```

اگر باز مشکل داشتید، برای تست موقت:

```text
chat_mw.db: 666
```

---

## مشکل ۳ — MHR روشن است ولی AzadiRelay وصل نمی‌شود

روی VPS این دستور را بزنید:

```bash
ss -lntp | grep 8085
```

خروجی درست:

```text
0.0.0.0:8085
```

اگر خروجی این بود:

```text
127.0.0.1:8085
```

یعنی `listen_host` اشتباه است.

باید در `config.json` این باشد:

```json
"listen_host": "0.0.0.0"
```

---

## مشکل ۴ — پورت VPS بسته است

روی VPS بزنید:

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

اگر شرکت VPS پنل Firewall دارد، آنجا هم پورت `8085` را باز کنید.

---

## مشکل ۵ — AUTH_KEY اشتباه است

علت:

```text
AUTH_KEY در Code.gs با auth_key در config.json یکی نیست.
```

باید دقیقاً یکی باشند.

در Code.gs:

```javascript
const AUTH_KEY = "Azadi_MHR_AUTH_2026_x9Pq_very_secret";
```

در config.json:

```json
"auth_key": "Azadi_MHR_AUTH_2026_x9Pq_very_secret"
```

---

## مشکل ۶ — script_id اشتباه است

اگر MHR خطای script داد، دوباره Google Apps Script را باز کنید.

بروید به:

```text
Deploy → Manage deployments
```

لینک Web app را ببینید.

شکل لینک:

```text
https://script.google.com/macros/s/AKfycbxxxxxxxxxxxxxxxx/exec
```

فقط این بخش را کپی کنید:

```text
AKfycbxxxxxxxxxxxxxxxx
```

و در `config.json` بگذارید:

```json
"script_id": "AKfycbxxxxxxxxxxxxxxxx"
```

---

## مشکل ۷ — Worker درست Deploy نشده

در Cloudflare بروید به:

```text
Workers & Pages → Worker → Edit code
```

کد را چک کنید.

بعد دوباره:

```text
Deploy
```

را بزنید.

---

## مشکل ۸ — پیام‌ها رد و بدل نمی‌شوند

این موارد را بررسی کنید:

```text
MHR روی VPS روشن است
صفحه SSH بسته نشده است
bridge_secret در Internal و External یکی است
foreign_base_url درست است
bridge_proxy_url در Internal درست است
bridge_proxy_url در External خالی است
Cron Job فعال است
db_check.php روی هر دو هاست سبز است
```

---

## مشکل ۹ — صفحه قدیمی یا لوگوی قبلی دیده می‌شود

مرورگر یا PWA ممکن است کش کرده باشد.

راه‌حل:

```text
Clear site data
```

اگر PWA نصب کرده‌اید:

```text
Uninstall
```

بعد دوباره سایت را باز کنید.

---

# چک‌لیست نهایی

قبل از استفاده واقعی، این‌ها را چک کنید:

```text
[ ] نسخه Internal روی هاست ایران نصب شده
[ ] نسخه External روی هاست خارج نصب شده
[ ] db_check.php روی هر دو هاست سبز است
[ ] pdo_sqlite روی هر دو هاست فعال است
[ ] bridge_secret در هر دو config.php یکی است
[ ] cron_key تنظیم شده است
[ ] Cloudflare Worker برای MHR ساخته شده است
[ ] Google Apps Script به صورت Web app ساخته شده است
[ ] فقط AKfycb... داخل script_id گذاشته شده است
[ ] AUTH_KEY در Code.gs و config.json یکی است
[ ] MHR روی VPS نصب شده است
[ ] listen_host روی 0.0.0.0 است
[ ] listen_port روی 8085 است
[ ] پورت 8085 روی VPS باز است
[ ] MHR اجرا شده و صفحه SSH باز مانده است
[ ] bridge_proxy_url در Internal روی http://YOUR_VPS_IP:8085 است
[ ] bridge_proxy_url در External خالی است
[ ] Cron Job فعال است
[ ] پیام از Internal به External تست شده است
[ ] پیام از External به Internal تست شده است
```

---

# نکات امنیتی

این موارد را عمومی نکنید:

```text
IP واقعی VPS
bridge_secret واقعی
cron_key واقعی
AUTH_KEY واقعی MHR
رمز ادمین واقعی
آدرس‌های خصوصی نصب
```

برای نصب واقعی، بعد از تست بهتر است رمز ادمین را تغییر دهید:

```php
'admin_username' => 'admin',
'admin_password' => 'یک رمز قوی',
```

نمونه رمز خوب:

```text
Azadi_Admin_2026_X9pQ_private
```

---

# لینک‌های اصلی

```text
AzadiRelay:
https://github.com/M0lavi/azadirelay

فایل‌های دانلود:
https://nakhl.sbs
```

---

## سلب مسئولیت

این پروژه برای استفاده self-hosted، آموزشی و ارتباط خصوصی ارائه شده است.

مسئولیت استفاده، تنظیمات سرور، قوانین محلی، امنیت حساب‌ها، امنیت کلیدها و نگهداری سرویس بر عهده استفاده‌کننده است.

</div>
