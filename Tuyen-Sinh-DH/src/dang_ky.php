<?php
// dang_ky.php
// 2154800745_Nguyễn Thái Dương

session_start(); 

// Bao gồm các file kết nối CSDL và hàm chức năng
include "../connectdb.php";
include "../function.php";

// Khởi tạo biến thông báo lỗi và thành công
$error_message = "";
$success_message = "";
$result_html = ""; 

// Biến để lưu lại dữ liệu nhập nếu có lỗi
$ten = isset($_POST['username']) ? trim($_POST['username']) : '';
$tk = isset($_POST['account']) ? trim($_POST['account']) : '';
$role = isset($_POST['role']) ? $_POST['role'] : 'student'; // Mặc định là student

if (isset($_POST['btn_dang_ky'])) {
    // 1. Lấy dữ liệu mật khẩu (KHÔNG dùng trim() cho mật khẩu thô)
    $mk = $_POST['password'] ?? ''; 
    $mk2 = $_POST['passwordConfirm'] ?? '';
    
    // 2. Kiểm tra tính hợp lệ của dữ liệu (Validation)
    if (empty($ten) || empty($tk) || empty($mk) || empty($mk2)) {
        $error_message = '<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> Vui lòng điền đầy đủ tất cả các trường.</div>';
    } elseif ($mk !== $mk2) {
        $error_message = '<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> Mật khẩu và xác nhận mật khẩu không khớp!</div>';
    } else {
        
        // 3. Dữ liệu hợp lệ, gọi hàm đăng ký (Truyền mật khẩu thô $mk)
        $result_html = dang_ky($ten, $tk, $mk, $role);
        
        // 4. Xử lý kết quả trả về từ hàm dang_ky()
        if (strpos($result_html, 'alert-success') !== false) {
             // Đăng ký thành công: $result_html chứa HTML alert VÀ JS chuyển hướng
             $success_message = $result_html;
             $error_message = ""; 
             
        } else {
            // Đăng ký thất bại (Lỗi đã tồn tại, lỗi CSDL, v.v.)
            $error_message = $result_html;
            
            // Đảm bảo thông báo lỗi từ function.php có icon nếu bị thiếu
            if (strpos($error_message, 'alert-danger') !== false && strpos($error_message, '<i class="fas') === false) {
                $error_message = str_replace('<div class="alert alert-danger" role="alert">', '<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> ', $error_message);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="./file_upload/logo.ico">
    <title>Đăng Ký Tài Khoản</title>
    <link rel="icon" type="image/x-icon" href="../image/logo.png">
    <style>
        :root {
            --primary-color: #004d99; /* Xanh đậm - màu chủ đạo (FBU) */
            --secondary-color: #ffcc00; /* Vàng - màu nhấn (FBU) */
            --light-bg: #f4f7f9;
            --dark-text: #333;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
            color: var(--dark-text);
            padding: 30px 0;
            min-height: 100vh;
        }

        /* --- Registration Form --- */
        .registration-form {
            max-width: 550px; 
            margin: 0 auto;
            padding: 35px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border-top: 5px solid var(--primary-color); 
        }

        .registration-form h2 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 25px;
            text-transform: uppercase;
            font-size: 1.8rem;
        }

        .form-group label {
            font-weight: 500;
            color: var(--dark-text);
        }
        
        .form-control {
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            transition: background-color 0.3s, transform 0.2s;
            font-weight: 600;
            margin-top: 15px;
        }

        .btn-primary:hover {
            background-color: #003366; 
            border-color: #003366;
            transform: translateY(-1px);
        }
        
        .registration-form p a {
            color: var(--primary-color);
            font-weight: 500;
        }
        
        /* --- Custom Radio Buttons for Role Selection --- */
        input[type="radio"] {
            display: none;
        }

        input[type="radio"]+label {
            display: inline-block;
            padding: 10px 20px;
            border: 2px solid #ced4da;
            border-radius: 7px;
            cursor: pointer;
            transition: all 0.3s;
            margin-right: 10px;
            font-weight: 500;
            color: #555;
            background-color: #f8f9fa;
        }
        
        input[type="radio"]+label:hover {
            border-color: var(--secondary-color);
            background-color: #eee;
        }

        input[type="radio"]:checked+label {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
            box-shadow: 0 0 5px rgba(0, 77, 153, 0.5);
        }

        /* Ẩn radio admin (vốn đã bị hidden trong HTML gốc) */
        input[type="radio"][value="admin"] + label {
            display: none !important;
        }
        
        .role-group {
            display: flex;
            gap: 10px;
            margin-top: 5px;
            justify-content: center; /* Căn giữa các lựa chọn */
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="registration-form">
            <h2 class="text-center"><i class="fas fa-user-plus"></i> Đăng Ký Tài Khoản</h2>
            
            <?php 
            
            // 1. Hiển thị thông báo thành công (Chứa HTML và JS chuyển hướng)
            if (!empty($success_message)) {
                echo $success_message;
            } 
            
            // 2. Hiển thị thông báo lỗi (Validation hoặc lỗi đăng ký)
            elseif (!empty($error_message)) {
                echo $error_message;
            }
            ?>
            
            <form id="registrationForm" method="post">
                <div class="form-group">
                    <label for="username"><i class="fas fa-address-card"></i> Tên người dùng (Hiển thị)</label>
                    <input type="text" name="username" class="form-control" id="username" placeholder="Nhập tên người dùng" required value="<?php echo htmlspecialchars($ten); ?>">
                </div>
                <div class="form-group">
                    <label for="account"><i class="fas fa-user"></i> Tên đăng nhập (Tài khoản)</label>
                    <input type="text" name="account" class="form-control" id="account" placeholder="Tên đăng nhập" required value="<?php echo htmlspecialchars($tk); ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="password"><i class="fas fa-lock"></i> Mật khẩu</label>
                        <input type="password" name="password" class="form-control" id="password" placeholder="Mật khẩu" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="passwordConfirm"><i class="fas fa-redo-alt"></i> Gõ lại mật khẩu</label>
                        <input type="password" name="passwordConfirm" class="form-control" id="passwordConfirm" placeholder="Gõ lại mật khẩu" required> 
                    </div>
                </div>
                
                <div class="form-group text-center">
                    <label for="role" style="display: block; margin-bottom: 10px;"><i class="fas fa-graduation-cap"></i> Chọn đối tượng đăng ký</label>
                    <div class="role-group">
                        <input type="radio" name="role" id="student" value="student" <?php echo ($role == 'student') ? 'checked' : ''; ?>>
                        <label for="student">HỌC SINH</label>
                        
                        <input type="radio" name="role" id="teacher" value="teacher" <?php echo ($role == 'teacher') ? 'checked' : ''; ?>>
                        <label for="teacher">GIÁO VIÊN</label>
                        
                        <input type="radio" name="role" id="admin" value="admin" hidden <?php echo ($role == 'admin') ? 'checked' : ''; ?>>
                    </div>
                </div>
                
                <button type="submit" name="btn_dang_ky" class="btn btn-primary btn-block"><i class="fas fa-user-plus"></i> Đăng Ký Tài Khoản</button>
                
                <div class="form-group text-center mt-3">
                    <p class="text-center">Đã có tài khoản? <a href="dang_nhap.php">Quay trở lại Trang đăng nhập</a></p>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>