<?php
// trang_phan_quyen.php
// 2154800745_Nguyễn Thái Dương

session_start(); // 1. Bắt đầu Session

include "../connectdb.php";
include "../function.php";

// 2. Kiểm tra quyền truy cập và chuyển hướng
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: dang_nhap.php");
    exit; // Dừng ngay lập tức sau khi chuyển hướng
}
// 
// 3. Xử lý logic Phân quyền (POST)
if (isset($_POST['save'])) {
    // Lấy và làm sạch dữ liệu
    $username = $_POST['username'];
    $account = $_POST['account'];
    $missions = isset($_POST['missions']) ? $_POST['missions'] : []; // Đảm bảo là mảng
    
    // Nối các nhiệm vụ lại bằng dấu phân cách "|"
    $mission_string = implode("|", $missions);

    // Gọi hàm lưu nhiệm vụ (Giả định hàm saveMissionOfTeacher xử lý an toàn)
    $message = saveMissionOfTeacher($username, $account, $mission_string);
    
    // Sử dụng PRG: Lưu thông báo và chuyển hướng
    $_SESSION['message'] = $message;
    header("Location: trang_phan_quyen.php");
    exit;
}

// 4. Xử lý thông báo sau khi chuyển hướng
$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Xóa thông báo sau khi hiển thị
    
    // Thêm icon cho thông báo
    if (strpos($message, 'alert-success') !== false) {
        $message = str_replace('<div class="alert alert-success" role="alert">', '<div class="alert alert-success" role="alert"><i class="fas fa-check-circle"></i> ', $message);
    } elseif (strpos($message, 'alert-danger') !== false) {
        $message = str_replace('<div class="alert alert-danger" role="alert">', '<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> ', $message);
    }
}

// 5. Lấy danh sách chuyên ngành (cần cho việc hiển thị checkbox)
$ds_tcn = getTenChuyenNganh();

// 6. Lấy danh sách tài khoản giáo viên
$ds_tk = getDSTKGV();

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="../image/logo.png">
    <title>Trang Phân Quyền Tài Khoản</title>
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
            padding-top: 56px; /* Bù cho navbar fixed-top */
        }

        /* --- Navbar --- */
        .navbar {
            background-color: var(--primary-color) !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
        }

        .navbar-brand, .navbar-nav .nav-link {
            color: white !important;
            transition: color 0.3s;
        }

        .navbar-nav .nav-link:hover {
            color: var(--secondary-color) !important;
        }
        
        .dropdown-item:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .dropdown-item.active {
            background-color: #007bff; /* Màu xanh sáng hơn cho mục đang active */
            color: white;
        }

        /* --- Hero Section --- */
        .hero {
            background: linear-gradient(135deg, var(--primary-color) 0%, #007bff 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
            margin-bottom: 30px;
            border-bottom-left-radius: 15px;
            border-bottom-right-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .hero h1 {
            font-size: 2.2rem;
            margin-top: 10px;
            font-weight: 600;
        }

        .site-logo {
            width: 80px; 
            height: 80px; 
            border-radius: 50%; 
            display: block; 
            margin: 0 auto 15px auto; 
            background-color: white;
            border: 3px solid var(--secondary-color) !important;
            object-fit: cover;
        }
        
        /* --- Account Table (Form Container) --- */
        .admin-table-container {
            width: 100%;
            max-width: 1200px; /* Tăng chiều rộng để chứa cột phân quyền */
            margin: 20px auto 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border-top: 5px solid var(--primary-color);
        }

        .admin-table-container h3 {
            color: var(--primary-color);
            font-weight: 700;
            padding-bottom: 5px;
            margin-bottom: 20px;
            border-bottom: 3px solid var(--secondary-color);
            display: inline-block;
        }
        
        .table thead th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            vertical-align: middle;
            border-color: #003366;
        }

        .table tbody tr:nth-child(even) {
            background-color: #f8f8f8;
        }
        
        .table td {
            vertical-align: middle;
            font-size: 0.95rem;
        }

        /* --- Phân Quyền Form --- */
        .phan-quyen-form {
            display: flex;
            flex-direction: column; /* Đặt checkbox và nút lưu theo chiều dọc */
            align-items: center;
            padding: 5px;
        }
        
        .checkbox-container {
             display: flex;
             flex-wrap: wrap; /* Cho phép checkbox xuống dòng trong 1 ô */
             gap: 8px 15px;
             margin-bottom: 10px;
             max-height: 150px;
             overflow-y: auto; /* Thêm thanh cuộn nếu quá nhiều chuyên ngành */
             padding: 5px;
             border: 1px dashed #ddd;
             border-radius: 5px;
             background-color: #fafafa;
        }

        .checkbox-item {
            white-space: nowrap; /* Ngăn chuyên ngành bị ngắt dòng */
        }
        
        .phan-quyen-form .btn {
            white-space: nowrap;
            width: 100%;
            max-width: 150px;
            font-weight: 500;
        }
        
        /* --- Footer --- */
        footer {
            background-color: var(--primary-color);
            color: white;
            text-align: center;
            padding: 15px 0;
            margin-top: 50px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <a class="navbar-brand" href="trang_chu.php">
            <i class="fas fa-home"></i> Trang Chủ
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-user-circle"></i> Xin chào, <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                        <?php 
                        if (htmlspecialchars($_SESSION['role']) == 'admin') {
                            echo '<a href="trang_them_chuyen_nganh.php" class="dropdown-item"><i class="fas fa-plus-circle"></i> Thêm Chuyên Ngành TS</a>';
                            echo '<a href="trang_phan_quyen.php" class="dropdown-item active"><i class="fas fa-user-shield"></i> Phân Quyền Tài Khoản GV</a>';
                            echo '<a href="trang_thong_ke_tong_ho_so.php" class="dropdown-item"><i class="fas fa-chart-bar"></i> Thống Kê Tổng Hồ Sơ</a>';
                            echo '<div class="dropdown-divider"></div>';
                        }
                        ?>
                        <a class="dropdown-item text-danger" href="dang_xuat.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <div class="hero">
        <img src="../image/logo.png" alt="Logo Trường Đại Học Tài Chính - Ngân Hàng Hà Nội" class="site-logo">
        <h1>QUẢN LÝ TUYỂN SINH</h1>
        <p style="font-size: 1.1rem; opacity: 0.9;">Phân Quyền Duyệt Hồ Sơ cho Giáo Viên</p>
    </div>
    
    <div class="container">
        <a href="trang_chu.php" class="btn btn-secondary" style="margin-bottom: 20px"><i class="fas fa-arrow-left"></i> Trở lại Trang Chủ</a>
        
        <?php echo $message; ?> 
    </div>

    <div class="admin-table-container">
        <h3 class="text-center uppercase"><i class="fas fa-tasks"></i> Danh Sách Tài Khoản Giáo Viên</h3>
        <div class="table-responsive">
            <table class="table table-bordered table-hover mt-4">
                <thead>
                    <tr>
                        <th style="width: 5%;">STT</th>
                        <th style="width: 15%;">Tên người dùng</th>
                        <th style="width: 10%;">Tài khoản</th>
                        <th style="width: 10%;">Loại TK</th>
                        <th style="width: 20%;">Nhiệm vụ đã phân công</th>
                        <th style="width: 40%;">Phân quyền (Chọn Chuyên ngành)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($ds_tk == 0 || empty($ds_tk)) {
                        echo "<tr><td colspan = '6' class='text-center text-muted'>Không có dữ liệu tài khoản giáo viên nào.</td></tr>";
                    } else {
                        $stt = 1;
                        foreach ($ds_tk as $tk) {
                            // Bảo mật XSS cho dữ liệu từ CSDL
                            $username_safe = htmlspecialchars($tk['username']);
                            $account_safe = htmlspecialchars($tk['account']);
                            $role_safe = htmlspecialchars($tk['role']);
                            $mission_raw = $tk['mission'];
                            $missions_assigned = explode("|", $mission_raw);
                            
                            // Định dạng hiển thị Nhiệm vụ đã phân công
                            $mission_display = !empty($mission_raw) ? str_replace("|", ", ", $mission_raw) : '<span class="text-danger">Chưa phân công</span>';
                            
                            echo "<tr>";
                            echo "<td>$stt</td>";
                            echo "<td>" . $username_safe . "</td>";
                            echo "<td>" . $account_safe . "</td>";
                            echo "<td class='uppercase'>" . $role_safe . "</td>";
                            echo "<td>" . htmlspecialchars($mission_display) . "</td>";
                            echo "<td>";
                            
                            // Form phân quyền
                            echo '<form method="post" class="phan-quyen-form">';
                            echo '<input type="hidden" name="username" value="' . $username_safe . '">';
                            echo '<input type="hidden" name="account" value="' . $account_safe . '">';
                            
                            echo '<div class="checkbox-container">';
                            
                            // Hiển thị danh sách chuyên ngành dưới dạng checkbox
                            if (!empty($ds_tcn)) {
                                foreach ($ds_tcn as $tcn) {
                                    $faculty_name = htmlspecialchars($tcn['faculty']);
                                    // Kiểm tra xem chuyên ngành này đã được phân quyền chưa
                                    $checked = in_array($faculty_name, $missions_assigned) ? 'checked' : '';
                                    
                                    echo '<div class="checkbox-item">';
                                    echo '<input type="checkbox" name="missions[]" id="mission_' . $stt . '_' . str_replace(' ', '_', $faculty_name) . '" value="' . $faculty_name . '" ' . $checked . '>';
                                    echo '<label for="mission_' . $stt . '_' . str_replace(' ', '_', $faculty_name) . '" style="margin-left: 5px; font-weight: 400;">' . $faculty_name . '</label>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<p class="text-muted w-100 text-center">Vui lòng thêm chuyên ngành trước khi phân quyền.</p>';
                            }
                            
                            echo '</div>'; // End checkbox-container
                            
                            echo '<button type="submit" name="save" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Lưu Phân Quyền</button>';
                            echo "</form>";
                            echo "</td>";
                            echo "</tr>";
                            $stt++;
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <footer>
        <p>&copy; 2025 Tuyển sinh FBU | Phát triển bởi Nguyễn Thái Dương - 2154800745</p>
    </footer>


    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>