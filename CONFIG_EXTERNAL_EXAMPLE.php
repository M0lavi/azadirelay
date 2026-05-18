<?php
# =====================================================================
# تنظیمات AzadiRelay - نسخه خارجی / Global
# =====================================================================
# این فایل را روی هاست خارج کشور باز کنید و فقط مقدارهای مشخص‌شده را تغییر دهید.
# فایل chat_mw.db باید کنار index.php باشد؛ نام پوشه نصب مهم نیست.
# هیچ رمز واقعی، IP واقعی یا آدرس خصوصی را داخل GitHub منتشر نکنید.

return [
    # نقش این نصب. برای هاست خارج کشور همیشه foreign بماند.
    'role' => 'foreign',

    # نام برنامه که در صفحه و عنوان‌ها نمایش داده می‌شود.
    'app_name' => 'AzadiRelay Global',
    'app_short_name' => 'AzadiRelay',

    # لینک مخزن GitHub که در پایین برنامه نمایش داده می‌شود.
    'repository_url' => 'https://github.com/M0lavi/azadirelay',

    # ورود پنل مدیریت. برای نصب واقعی بهتر است بعداً تغییر کند.
    'admin_username' => 'admin',
    'admin_password' => 'admin',

    # آدرس نسخه داخلی روی هاست ایران، بدون / آخر.
    'internal_base_url' => 'https://YOUR-IRAN-HOST/azadi',

    # آدرس همین نسخه خارجی / Global، بدون / آخر.
    # اینجا معمولاً آدرس واقعی هاست خارجی را می‌گذارید.
    'foreign_base_url' => 'https://YOUR-GLOBAL-HOST/azadi',

    # رمز مشترک Bridge.
    # باید دقیقاً همان مقداری باشد که در نسخه داخلی گذاشتید.
    'bridge_secret' => 'CHANGE_THIS_SAME_SECRET_ON_INTERNAL_AND_EXTERNAL',

    # کلید Cron.
    # بهتر است همان مقداری باشد که در نسخه داخلی گذاشتید.
    'cron_key' => 'CHANGE_THIS_PRIVATE_CRON_KEY',

    # در نسخه خارجی معمولاً خالی می‌ماند.
    # چون درخواست اصلی از سمت نسخه داخلی و از مسیر MHR ارسال می‌شود.
    'bridge_proxy_url' => '',

    # Bridge روشن باشد.
    'bridge_enabled' => true,

    # رمزنگاری انتقال Bridge روشن باشد.
    'transport_encryption' => true,

    # تعداد پیام‌هایی که هر بار جابه‌جا می‌شود.
    'max_batch_messages' => 25,

    # محدودیت حجم فایل برای پیام‌های Bridge.
    'max_file_bytes' => 2 * 1024 * 1024,

    # محدودیت روزانه فایل برای هر کاربر در Bridge.
    'max_daily_file_bytes' => 10 * 1024 * 1024,

    # زمان انتظار درخواست‌ها بر حسب ثانیه.
    'request_timeout' => 35,

    # در حالت عادی false بماند.
    'trust_proxy_self_signed' => false,
];
