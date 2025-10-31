<?php
// trang_them_chuyen_nganh.php


session_start(); // 1. Bắt đầu Session

include "../connectdb.php";
include "../function.php";

// Biến lưu trữ thông báo
$message = "";

// 2. Kiểm tra quyền truy cập và chuyển hướng
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: dang_nhap.php");
    exit; // Dừng ngay lập tức sau khi chuyển hướng
}

// 3. Xử lý logic Thêm Chuyên Ngành (POST)
// Lưu lại giá trị nhập vào nếu có lỗi
$faculty_input = isset($_POST['faculty']) ? trim($_POST['faculty']) : '';
$admission_group_input = isset($_POST['admission_group']) ? trim($_POST['admission_group']) : '';
$begin_date_input = isset($_POST['begin_date']) ? $_POST['begin_date'] : '';
$end_date_input = isset($_POST['end_date']) ? $_POST['end_date'] : '';


if (isset($_POST['btn_them_chuyen_nganh'])) {
    // 3.1. Lấy và làm sạch dữ liệu
    $faculty = $faculty_input;
    $admission_group = $admission_group_input;
    $begin_date = $begin_date_input;
    $end_date = $end_date_input;
    
    // Khởi tạo thông báo lỗi cục bộ
    $error = [];

    // 3.2. Validation
    if (empty($faculty) || empty($admission_group) || empty($begin_date) || empty($end_date)) {
        $error[] = "Vui lòng điền đầy đủ tất cả các trường.";
    }
    
    // Kiểm tra ngày tháng hợp lệ
    if (!empty($begin_date) && !empty($end_date) && strtotime($begin_date) >= strtotime($end_date)) {
        $error[] = "Ngày bắt đầu phải trước ngày hết hạn tuyển sinh.";
    }

    // 3.3. Thực thi nếu không có lỗi
    if (empty($error)) {
        // Chuẩn hóa tên chuyên ngành (ucwords: Viết hoa chữ cái đầu mỗi từ)
        $faculty_formatted = ucwords($faculty);
        
        // Giả định hàm them_chuyen_nganh() trả về thông báo HTML hoặc mã lỗi
        $result = them_chuyen_nganh($faculty_formatted, $admission_group, $begin_date, $end_date);
        
        // Sử dụng PRG: Lưu thông báo và chuyển hướng
        $_SESSION['message'] = $result;
        header("Location: trang_them_chuyen_nganh.php");
        exit;
    } else {
        // Nếu có lỗi, xây dựng thông báo lỗi
        $message = '<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> ' . implode('<br>', $error) . '</div>';
    }
}

// 4. Xử lý thông báo sau khi chuyển hướng
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Xóa thông báo sau khi hiển thị
}

// 5. Lấy danh sách tổ hợp (cho select box)
$ds_th = getToHop();
$username_safe = htmlspecialchars($_SESSION['username']);

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="../image/logo.png">
    <title>Trang Thêm Chuyên Ngành</title>
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
            border: 3px solid var(--secondary-color) !important;
            object-fit: cover;
        }

        /* --- Form Container --- */
        .admin-form-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border-top: 5px solid var(--primary-color);
        }
        
        .form-group label {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 5px;
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
                        <i class="fas fa-user-circle"></i> Xin chào, <?php echo $username_safe; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                        <?php 
                        if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
                            echo '<a href="trang_them_chuyen_nganh.php" class="dropdown-item active"><i class="fas fa-plus-circle"></i> Thêm Chuyên Ngành TS</a>';
                            echo '<a href="trang_phan_quyen.php" class="dropdown-item"><i class="fas fa-user-shield"></i> Phân Quyền Tài Khoản GV</a>';
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
        <img src="../image/logo.png" alt="Logo Trường Đại Học Tài Chính - Ngân Hàng Hà Nội" class="site-logo" style="width: 80px; height: 80px; border-radius: 50%; display: block; margin: 0 auto 15px auto; background-color: white;">
        <h1>QUẢN LÝ TUYỂN SINH</h1>
        <p style="font-size: 1.1rem; opacity: 0.9;">Thêm Chuyên Ngành Tuyển Sinh Mới</p>
    </div>
    
    <div class="container">
        <a href="trang_chu.php" class="btn btn-secondary" style="margin-bottom: 20px"><i class="fas fa-arrow-left"></i> Trở lại Trang Chủ</a>
        
        <div class="admin-form-container">
            <h3 class="text-center" style="color: var(--primary-color); font-weight: 700; margin-bottom: 25px;">
                <i class="fas fa-folder-plus"></i> Thêm Chuyên Ngành
            </h3>
            
            <?php 
            // Hiển thị thông báo (thành công hoặc lỗi)
            if (!empty($message)) {
                echo '<div class="alert ' . (strpos($message, 'alert-success') !== false ? 'alert-success' : 'alert-danger') . '" role="alert">' . $message . '</div>';
            }
            ?> 
            
            <form id="addFacultyForm" method="post">
                <div class="form-group">
                    <label for="faculty"><i class="fas fa-book"></i> Tên chuyên ngành</label>
                    <input type="text" name="faculty" class="form-control" id="faculty" placeholder="Ví dụ: Kế toán, Tài chính Ngân hàng..." required value="<?php echo htmlspecialchars($faculty_input); ?>">
                </div>
                <div class="form-group">
                    <label for="admission_group"><i class="fas fa-list-ul"></i> Khối xét tuyển</label>
                    <select class="form-control" name="admission_group" id="admission_group" required>
                        <option value="">-- Chọn Khối xét tuyển --</option>
                        <?php
                        // Logic hiển thị danh sách tổ hợp
                        if ($ds_th == 0 || empty($ds_th)) {
                            echo "<option value='' disabled>Không có dữ liệu tổ hợp</option>";
                        } else {
                            foreach ($ds_th as $th) {
                                $tentohop_safe = htmlspecialchars($th['tentohop']);
                                // Kiểm tra để giữ lại lựa chọn nếu form có lỗi
                                $selected = ($tentohop_safe == $admission_group_input) ? 'selected' : '';
                                echo "<option value='" . $tentohop_safe . "' " . $selected . ">" . $tentohop_safe . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="begin_date"><i class="fas fa-calendar-alt"></i> Ngày bắt đầu tuyển sinh</label>
                        <input type="date" name="begin_date" class="form-control" id="begin_date" required value="<?php echo htmlspecialchars($begin_date_input); ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="end_date"><i class="fas fa-calendar-times"></i> Ngày hết hạn tuyển sinh</label>
                        <input type="date" name="end_date" class="form-control" id="end_date" required value="<?php echo htmlspecialchars($end_date_input); ?>">
                    </div>
                </div>
                <button type="submit" name="btn_them_chuyen_nganh" class="btn btn-primary btn-block"><i class="fas fa-save"></i> Thêm Chuyên Ngành</button>
            </form>
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