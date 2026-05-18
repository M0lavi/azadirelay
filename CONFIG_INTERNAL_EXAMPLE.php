<?php
# =====================================================================
# تنظیمات AzadiRelay - نسخه داخلی / ایران
# =====================================================================
# این فایل را روی هاست ایران باز کنید و فقط مقدارهای مشخص‌شده را تغییر دهید.
# فایل chat_mw.db باید کنار index.php باشد؛ نام پوشه نصب مهم نیست.
# هیچ رمز واقعی، IP واقعی یا آدرس خصوصی را داخل GitHub منتشر نکنید.

return [
    # نقش این نصب. برای هاست ایران همیشه internal بماند.
    'role' => 'internal',

    # نام برنامه که در صفحه و عنوان‌ها نمایش داده می‌شود.
    'app_name' => 'AzadiRelay',
    'app_short_name' => 'AzadiRelay',

    # لینک مخزن GitHub که در پایین برنامه نمایش داده می‌شود.
    # اگر مخزن را تغییر دادید، فقط همین خط را عوض کنید.
    'repository_url' => 'https://github.com/M0lavi/azadirelay',

    # ورود پنل مدیریت. برای نصب واقعی بهتر است بعداً تغییر کند.
    'admin_username' => 'admin',
    'admin_password' => 'admin',

    # آدرس نسخه داخلی روی هاست ایران، بدون / آخر.
    # مثال: https://example.ir/azadi
    'internal_base_url' => 'https://YOUR-IRAN-HOST/azadi',

    # آدرس نسخه خارجی / Global، بدون / آخر.
    # اگر دامنه خارجی از ایران باز نمی‌شود و برای آن Worker جدا ساختید،
    # اینجا آدرس همان Worker جدا را بگذارید.
    # مثال مستقیم: https://global-example.com/azadi
    # مثال با Worker جدا: https://azadi-global-proxy.USER.workers.dev
    'foreign_base_url' => 'https://YOUR-GLOBAL-HOST/azadi',

    # رمز مشترک Bridge.
    # این مقدار باید در نسخه داخلی و نسخه خارجی دقیقاً یکی باشد.
    # نمونه واقعی خودتان باید طولانی و غیرقابل حدس باشد.
    'bridge_secret' => 'CHANGE_THIS_SAME_SECRET_ON_INTERNAL_AND_EXTERNAL',

    # کلید Cron.
    # این مقدار را هم در هر دو نسخه یکی بگذارید تا مدیریت ساده باشد.
    # این کلید را عمومی نکنید.
    'cron_key' => 'CHANGE_THIS_PRIVATE_CRON_KEY',

    # آدرس پروکسی MHR روی VPS.
    # فقط در نسخه داخلی پر می‌شود.
    # پورت پیش‌فرض MHR در این راهنما 8085 است.
    # مثال: http://123.123.123.123:8085
    'bridge_proxy_url' => 'http://YOUR_VPS_IP:8085',

    # Bridge روشن باشد.
    'bridge_enabled' => true,

    # رمزنگاری انتقال Bridge روشن باشد.
    'transport_encryption' => true,

    # تعداد پیام‌هایی که هر بار Cron جابه‌جا می‌کند.
    'max_batch_messages' => 25,

    # محدودیت حجم فایل برای پیام‌های Bridge.
    'max_file_bytes' => 2 * 1024 * 1024,

    # محدودیت روزانه فایل برای هر کاربر در Bridge.
    'max_daily_file_bytes' => 10 * 1024 * 1024,

    # زمان انتظار درخواست‌ها بر حسب ثانیه.
    'request_timeout' => 35,

    # فقط اگر پروکسی شما TLS دستکاری‌شده یا self-signed داشت true کنید.
    # در حالت عادی false بماند.
    'trust_proxy_self_signed' => false,
];
