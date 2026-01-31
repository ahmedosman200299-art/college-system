<?php
$db_host = 'localhost';
$db_name = 'college_system';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // أمر إضافة العمود الناقص
    $sql = "ALTER TABLE students ADD COLUMN is_confirmed INT DEFAULT 0";
    
    $pdo->exec($sql);
    echo "<h1 style='color:green; text-align:center;'>تم إصلاح قاعدة البيانات بنجاح! ✅</h1>";
    echo "<h3 style='text-align:center;'>تم إضافة عمود is_confirmed. يمكنك الآن حذف هذا الملف والعودة للنظام.</h3>";

} catch (PDOException $e) {
    // إذا كان العمود موجوداً مسبقاً أو حدث خطأ آخر
    if(strpos($e->getMessage(), "Duplicate column name") !== false){
         echo "<h1 style='color:blue; text-align:center;'>العمود موجود بالفعل، لا توجد مشكلة. ✅</h1>";
    } else {
        echo "<h1 style='color:red; text-align:center;'>خطأ: " . $e->getMessage() . "</h1>";
    }
}
?>