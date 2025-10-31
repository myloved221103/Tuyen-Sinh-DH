<?php
// connectdb.php
// 2154800745_Nguyễn Thái Dương

// Bắt đầu session (Đây là thực tế tốt vì hầu hết các trang đều cần session)
session_start();

// 1. Thông tin kết nối CSDL
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'nhom_16';



// 2. Kết nối CSDL bằng MySQLi Hướng đối tượng
// Sử dụng @ để ngăn chặn lỗi mặc định, thay vào đó dùng cơ chế xử lý lỗi rõ ràng hơn
$conn = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

// 3. Xử lý lỗi kết nối
if ($conn->connect_error) {
    // Ghi log lỗi kết nối (tùy chọn, để phục vụ debug)
    error_log("Kết nối CSDL thất bại: " . $conn->connect_error); 
    
    // Hiển thị thông báo lỗi thân thiện (Không nên hiển thị $conn->connect_error cho người dùng cuối)
    die("Lỗi kết nối CSDL. Vui lòng thử lại sau.");
}

// 4. Thiết lập bộ ký tự (Charset)
// Đặt bộ ký tự UTF-8 cho kết nối
$conn->set_charset("utf8mb4"); // Sử dụng utf8mb4 là chuẩn mới hơn cho Emoji/ký tự đặc biệt

// Biến $conn hiện tại là đối tượng kết nối đã sẵn sàng sử dụng.

?>