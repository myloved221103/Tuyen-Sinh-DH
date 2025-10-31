<?php
// dang_xuat.php
// 2154800745_Nguyễn Thái Dương

// 1. Bắt đầu phiên (cần thiết để truy cập/hủy session)
session_start();

// 2. Hủy tất cả các biến session
$_SESSION = array();

// 3. Hủy phiên làm việc
session_destroy();

// 4. Điều hướng về trang đăng nhập
header("Location: dang_nhap.php");

// 5. Kết thúc script
// LƯU Ý QUAN TRỌNG: Lệnh exit/die; là cần thiết sau header() để đảm bảo
// rằng không có mã nào khác được thực thi trước khi chuyển hướng xảy ra.
exit;