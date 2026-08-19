<?php
require_once '../config/db.php';
$pageTitle = 'اتصل بنا';

include '../includes/header_public.php';
?>

<section class="page-hero blue">
    <div class="container">
        <h1>اتصل بنا <i class="fa-solid fa-envelope-open-text" style="color: #fff;"></i></h1>
        <p>لديكم سؤال أو اقتراح؟ راسلونا وسنجيبكم في أقرب وقت</p>
    </div>
</section>

<section class="section">
    <div class="container don-layout">

        <div class="don-form-card">
            <h2>راسلنا</h2>
            <?php if (isset($_GET['error'])): ?>
                <p class="error">يرجى ملء جميع الحقول المطلوبة بشكل صحيح.</p>
            <?php endif; ?>
            <?php if (isset($_GET['success'])): ?>
                <p class="info-note">تم إرسال رسالتكم بنجاح، شكرا لتواصلكم معنا!</p>
            <?php endif; ?>
            <form method="POST" action="contact_process.php" class="don-form">
                <label>الاسم الكامل</label>
                <input type="text" name="nom" required placeholder="اسمك الكامل">

                <label>البريد الإلكتروني</label>
                <input type="email" name="email" required placeholder="بريدك الإلكتروني">

                <label>الموضوع</label>
                <input type="text" name="sujet" placeholder="موضوع الرسالة (اختياري)">

                <label>الرسالة</label>
                <textarea name="message" rows="6" required placeholder="اكتب رسالتك هنا..." style="width:100%; padding:0.65rem; margin-top:0.35rem; border:1px solid var(--border); border-radius:8px; font-family:var(--font-body);"></textarea>

                <button type="submit">إرسال الرسالة</button>
            </form>
        </div>

        <div class="don-info-card">
            <h3> معلومات التواصل</h3>
            <p><i class="fa-solid fa-location-dot" style="color: red;"></i> دوار أولاد عيسى، جماعة أولاد عيسى، قيادة إكلي، إقليم تارودانت</p>
            <hr>
            <p>
                <i class="fa-solid fa-phone-flip" style="color: #000;"></i>
                <a href="https://wa.me/212648656411" target="_blank" style="color:inherit; text-decoration:none;"  class="contact-phone">212648656411+</a>
                /
                <a href="https://wa.me/212611249905" target="_blank" style="color:inherit; text-decoration:none;" class="contact-phone">212611249905+</a>
            </p>
            <p>
                <i class="fa-brands fa-whatsapp" style="color:#25D366;"></i>
                <a href="https://wa.me/212648656411" target="_blank" style="color:#25D366; text-decoration:none; font-weight:600;">تواصلوا معنا عبر واتساب</a>
            </p>
            <p><i class="fa-solid fa-envelope" style="color: #000;"></i> ass.generation.creative@gmail.com</p>
            <hr>
            <h3> تريدون الانضمام إلينا؟</h3>
            <p>يمكنكم التقدم بطلب للانضمام كمتطوعين.</p>
            <a href="join_us.php" class="btn-outline-blue">انضم إلينا</a>
        </div>

    </div>
</section>

<?php include '../includes/footer_public.php'; ?>