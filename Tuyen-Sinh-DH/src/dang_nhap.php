<?php
// dang_nhap.php
// Bắt đầu session để lưu trạng thái đăng nhập
session_start(); 

include "../connectdb.php";
include "../function.php";

// Khởi tạo biến thông báo lỗi
$error_message = "";
$account_input = ""; // Biến để lưu lại Tên đăng nhập đã nhập

// 1. Xử lý logic đăng nhập
if (isset($_POST['btn_dang_nhap'])) {
    // 1.1. Lấy và làm sạch dữ liệu đầu vào
    $tk = trim($_POST['account']);
    $mk = $_POST['password']; // Không dùng trim() cho mật khẩu
    
    // Lưu lại tên đăng nhập để hiển thị lại
    $account_input = htmlspecialchars($tk); 
    
    // 1.2. Kiểm tra tính hợp lệ
    if (empty($tk) || empty($mk)) {
        // Sử dụng alert của Bootstrap
        $error_message = '<div class="alert alert-danger" role="alert">Vui lòng điền đầy đủ Tên đăng nhập và Mật khẩu.</div>';
    } else {
        // 1.3. Gọi hàm đăng nhập
        $result_html = dang_nhap($tk, $mk);
        
        // Kiểm tra xem hàm có trả về thông báo lỗi không
        if (strpos($result_html, 'alert-danger') !== false) {
             // Lỗi đăng nhập, lưu thông báo
             $error_message = $result_html;
        } else {
             // Đăng nhập thành công, hàm dang_nhap() nên xử lý chuyển hướng
             // Không làm gì thêm ở đây nếu hàm đã xử lý chuyển hướng thành công.
        }
    }
    
    // Thêm icon cho thông báo lỗi (nếu có)
    if (strpos($error_message, 'alert-danger') !== false) {
        $error_message = str_replace('<div class="alert alert-danger" role="alert">', '<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> ', $error_message);
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
    <title>Đăng Nhập Hệ Thống</title>
    <link rel="icon" type="image/x-icon" href="../image/logo.png">
    <style>
        :root {
            --primary-color: #004d99; /* Xanh đậm - màu chủ đạo (FBU) */
            --secondary-color: #ffcc00; /* Vàng - màu nhấn (FBU) */
            --dark-text: #333;
        }

        /* ⚠️ Quan trọng: Thêm/Thay đổi CSS cho HTML và BODY */
        html, body {
            height: 100%; /* Đảm bảo chiều cao đầy đủ */
            margin: 0;
            padding: 0;
            /* Giữ nguyên overflow: hidden; nếu bạn muốn banner full màn hình cố định */
            overflow: hidden; 
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark-text);
            padding-top: 0; 
        }

        /* --- Full Screen Hero Banner (Giữ nguyên) --- */
        .hero-banner { 
            position: fixed; 
            top: 0;
            left: 0;
            width: 100vw; 
            height: 100vh; 
            background-image: url('../image/truongdh.png'); 
            background-size: cover; 
            background-position: center; 
            z-index: 0; 
        }

        .hero-banner::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.25); 
            z-index: 1; 
        }
        
        /* --- Wrapper cho toàn bộ nội dung trang (Giữ nguyên) --- */
        .page-wrapper {
            position: relative;
            z-index: 2; 
            min-height: 100vh; 
            overflow-y: auto; 
            padding-top: 50px; 
            padding-bottom: 50px; 
        }

        /* --- Header/Logo (Giữ nguyên) --- */
        .site-logo {
            width: 100px; 
            height: 100px; 
            border-radius: 50%; 
            display: block; 
            margin: 0 auto 15px auto; 
            background-color: white;
            border: 5px solid var(--secondary-color) !important; 
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .main-heading {
            font-weight: 800; 
            color: white; 
            text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.7); 
            font-size: 2.5rem; 
            margin-bottom: 10px;
        }
        
        .sub-heading {
            color: white;
            font-size: 1.2rem;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
            margin-bottom: 30px; 
        }

        /* --- Login Form (Giữ nguyên) --- */
        .login-form {
            max-width: 420px; 
            margin: 0 auto; 
            padding: 30px;
            background-color: rgba(255, 255, 255, 0.95); 
            border-radius: 10px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.25);
            border-top: 5px solid var(--primary-color);
        }

        .login-form h2 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 25px;
            text-transform: uppercase;
            font-size: 1.5rem;
        }
        
        /* --- Account Table (ẨN ĐI BẰNG CSS) --- */
        .account-table {
            /* THÊM DÒNG NÀY ĐỂ ẨN TOÀN BỘ PHẦN BẢNG */
            display: none; 
            /* Phần còn lại của style giữ lại cho mục đích tài liệu nếu cần */
             max-width: 900px;
             margin: 50px auto; 
             padding: 20px;
             background-color: rgba(255, 255, 255, 0.9); 
             border-radius: 10px;
             box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
             border-top: 5px solid var(--primary-color);
        }

        /* --- Footer (Giữ nguyên) --- */
        footer {
            background-color: var(--primary-color);
            color: white;
            text-align: center;
            padding: 15px 0;
            margin-top: 30px; 
        }
    </style>
</head>

<body>
    
<div class="hero-banner"></div>

    
<div class="page-wrapper">
        <div class="container text-center pt-5">
            <img src="../image/logo.png" alt="Logo Trường Đại Học Tài Chính - Ngân Hàng Hà Nội" class="site-logo">
            <h1 class="main-heading">HỆ THỐNG QUẢN LÝ TUYỂN SINH TRỰC TUYẾN</h1>
            <p class="sub-heading">TRƯỜNG ĐẠI HỌC TÀI CHÍNH NGÂN HÀNG HÀ NỘI</p>
        </div>
        
        <div class="container">
            <div class="login-form">
                <h2 class="text-center"><i class="fas fa-sign-in-alt"></i> Đăng Nhập Hệ Thống</h2>
                
                <?php 
                echo $error_message;
                ?>
                
                <form id="loginForm" method="post">
                    <div class="form-group">
                        <label for="account"><i class="fas fa-user"></i> Tên đăng nhập</label>
                        <input type="text" name="account" class="form-control" id="account" placeholder="Nhập tên đăng nhập" required value="<?php echo $account_input; ?>">
                    </div>
                    <div class="form-group">
                        <label for="password"><i class="fas fa-lock"></i> Mật khẩu</label>
                        <input type="password" name="password" class="form-control" id="password" placeholder="Nhập mật khẩu" required>
                    </div>
                    <button type="submit" name="btn_dang_nhap" class="btn btn-primary btn-block">Đăng Nhập</button>
                    <div class="form-group text-center mt-3">
                        <p class="text-center">Chưa có tài khoản? <a href="dang_ky.php">Đăng ký ngay</a></p>
                    </div>
                </form>
            </div>
        </div>

        <div class="container">
            <div class="account-table">
                <h3 class="text-center uppercase"><i class="fas fa-list-alt"></i> Danh Sách Tài Khoản Mẫu</h3>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mt-4">
                        <thead>
                            <tr>
                                <th style="width: 5%;">STT</th>
                                <th>Tên người dùng</th>
                                <th>Tài khoản</th>
                                <th style="width: 15%;">Loại tài khoản</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $ds_tk = getDSTK();
                            if ($ds_tk == 0 || empty($ds_tk)) { 
                                echo "<tr><td colspan = '4' class='text-center text-muted'>Không có dữ liệu tài khoản mẫu nào.</td></tr>";
                            } else {
                                $stt = 1;
                                foreach ($ds_tk as $tk) {
                                    $role_class = (htmlspecialchars($tk['role']) == 'admin') ? 'text-danger font-weight-bold' : '';
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($stt) . "</td>";
                                    echo "<td>" . htmlspecialchars($tk['username']) . "</td>";
                                    echo "<td>" . htmlspecialchars($tk['account']) . "</td>";
                                    echo "<td class='uppercase " . $role_class . "'>" . htmlspecialchars($tk['role']) . "</td>";
                                    echo "</tr>";
                                    $stt++;
                                }
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-center text-danger font-weight-bold" style="background-color: #fff3cd; border-color: #ffeeba;">
                                    <i class="fas fa-exclamation-circle"></i> LƯU Ý: Vui lòng sử dụng tài khoản mẫu hoặc đăng ký mới để trải nghiệm hệ thống.
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <footer>
            <p>&copy; 2025 Tuyển sinh FBU | Phát triển bởi Nguyễn Thái Dương - 2154800745</p>
        </footer>
        
    </div> <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>