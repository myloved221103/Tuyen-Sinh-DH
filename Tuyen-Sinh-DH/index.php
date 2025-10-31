<?php
// index.php
// 2154800745_Nguyễn Thái Dương

// Thực hành tốt nhất: Đảm bảo không có mã HTML nào được gửi trước khi chuyển hướng.
// Thêm lệnh exit/die sau header() là bắt buộc để ngăn chặn mã PHP tiếp tục chạy 
// trong trường hợp lệnh chuyển hướng header() không thành công.

header("Location: ./src/dang_nhap.php");
exit; 
?>