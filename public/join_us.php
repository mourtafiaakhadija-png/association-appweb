<?php
require_once 'config/db.php';
require_once 'includes/csrf.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$pageTitle = 'انضم إلينا';

include 'includes/header_public.php';
?>

<section class="page-hero orange">
    <div class="container">
        <h1>انضم إلينا كمتطوع <i class="fa-solid fa-handshake" ></i></h1>
        <p>ساهم معنا في صنع الأمل، وقتك ومهاراتك يمكن أن تصنع التغيير</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="don-form-card" style="max-width:700px; margin:0 auto;">
            <h2>استمارة الترشح للتطوع</h2>
            <?php if (isset($_GET['success'])): ?>
                <p class="info-note">تم إرسال ترشحكم بنجاح، سنتواصل معكم قريبا!</p>
            <?php endif; ?>
            <?php if (isset($_GET['error'])): ?>
                <p class="error">يرجى ملء جميع الحقول المطلوبة بشكل صحيح.</p>
            <?php endif; ?>
            <form method="POST" action="join_us_process.php" class="don-form" enctype="multipart/form-data">
                <input type="text" name="website" style="position:absolute; left:-9999px;" tabindex="-1" autocomplete="off">
                <label>الاسم الشخصي</label>
                <input type="text" name="prenom" required placeholder="اسمك الشخصي">

                <label>الاسم العائلي</label>
                <input type="text" name="nom" required placeholder="اسمك العائلي">

                <label>البريد الإلكتروني</label>
                <input type="email" name="email" required placeholder="بريدك الإلكتروني">

                <label>رقم الهاتف</label>
                <input type="text" name="telephone" placeholder="رقم هاتفك">

                <label>تاريخ الازدياد</label>
                <input type="date" name="date_naissance">

                <label>المدينة</label>
                <input type="text" name="ville" placeholder="مدينة السكن">

                <label>المهنة</label>
                <input type="text" name="profession" placeholder="مهنتك الحالية (اختياري)">

                <label>المستوى الدراسي</label>
                <input type="text" name="niveau_etude" placeholder="آخر شهادة أو مستوى دراسي">

                <label>الصورة الشخصية (اختياري)</label>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp">

                <label>المهارات</label>
                <textarea name="competences" rows="3" placeholder="مهاراتك (تنظيم، تصميم، تدريس...)" style="width:100%; padding:0.65rem; margin-top:0.35rem; border:1px solid var(--border); border-radius:8px; font-family:var(--font-body);"></textarea>

                <label>التجارب السابقة</label>
                <textarea name="experiences" rows="3" placeholder="تجارب تطوعية سابقة (اختياري)" style="width:100%; padding:0.65rem; margin-top:0.35rem; border:1px solid var(--border); border-radius:8px; font-family:var(--font-body);"></textarea>

                <label>لماذا تريد الانضمام إلينا؟</label>
                <textarea name="motivation" rows="4" required placeholder="حدثنا عن دوافعك للتطوع معنا" style="width:100%; padding:0.65rem; margin-top:0.35rem; border:1px solid var(--border); border-radius:8px; font-family:var(--font-body);"></textarea>
                <input type="hidden" name="csrf_token" value="<?= genererJetonCsrf() ?>">
                <button type="submit">إرسال الترشح</button>
                <p class="form-note">سيتم دراسة ترشحكم من طرف فريقنا، وستتوصلون بجواب عبر البريد الإلكتروني.</p>
            </form>
        </div>
    </div>
</section>

<?php include 'includes/footer_public.php'; ?>