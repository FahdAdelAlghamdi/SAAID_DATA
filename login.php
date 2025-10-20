<?php
session_start();

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
    $identifier = $_POST['username']; // يمكن أن يكون بريد إلكتروني أو رقم هوية
    $password = $_POST['password'];

    // البحث عن المستخدم في قاعدة البيانات
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ? OR national_id = ?");
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $stmt->store_result();
    
    // التحقق مما إذا كان المستخدم موجود
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $hashed_password);
        $stmt->fetch();

        // التحقق من كلمة المرور
        if (password_verify($password, $hashed_password)) {
            // تسجيل الدخول ناجح
            $_SESSION['user_id'] = $user_id;
            echo "<script>alert('تم تسجيل الدخول بنجاح!'); window.location.href='../index.html';</script>";
        } else {
            // كلمة المرور غير صحيحة
            echo "<script>alert('كلمة المرور غير صحيحة.'); window.history.back();</script>";
        }
    } else {
        // المستخدم غير موجود
        echo "<script>alert('اسم المستخدم أو البريد الإلكتروني غير صحيح.'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>