<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// تأكد من أن الطلب هو POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // بيانات الاتصال بقاعدة البيانات
    $servername = "localhost";
    $username = "root"; // اسم المستخدم الافتراضي لـ XAMPP
    $password = "";     // كلمة المرور الافتراضية لـ XAMPP
    $dbname = "saaid_db";

    // إنشاء الاتصال
    $conn = new mysqli($servername, $username, $password, $dbname);

    // التحقق من الاتصال
    if ($conn->connect_error) {
        die("فشل الاتصال: " . $conn->connect_error);
    }

    // استلام البيانات من النموذج
    $full_name = $_POST['fullName'];
    $email = $_POST['email'];
    $national_id = $_POST['nationalId'];
    $password = $_POST['password'];

    // التحقق من أن جميع الحقول مملوءة
    if (empty($full_name) || empty($email) || empty($national_id) || empty($password)) {
        echo "<script>alert('الرجاء تعبئة جميع الحقول.'); window.history.back();</script>";
        exit();
    }

    // التحقق مما إذا كان البريد الإلكتروني أو رقم الهوية موجود مسبقاً
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR national_id = ?");
    $stmt->bind_param("ss", $email, $national_id);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        echo "<script>alert('البريد الإلكتروني أو رقم الهوية الوطنية مسجل بالفعل.'); window.history.back();</script>";
        exit();
    }
    $stmt->close();

    // تشفير كلمة المرور قبل حفظها في قاعدة البيانات
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // إدخال البيانات في قاعدة البيانات
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, national_id, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $full_name, $email, $national_id, $hashed_password);

    if ($stmt->execute()) {
        echo "<script>alert('تم إنشاء الحساب بنجاح. سيتم تحويلك إلى صفحة تسجيل الدخول.'); window.location.href='../login.html';</script>";
    } else {
        echo "<script>alert('حدث خطأ أثناء إنشاء الحساب. الرجاء المحاولة مرة أخرى.'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>