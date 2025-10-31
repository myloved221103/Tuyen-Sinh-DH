<?php
// trang_thong_ke_ho_so.php
// 2154800745_Nguyễn Thái Dương

session_start(); // 1. Bắt đầu Session

include "../connectdb.php";
include "../function.php";

// Biến lưu trữ thông báo
$message = "";

// 2. Kiểm tra đăng nhập và lấy dữ liệu GET
if (!isset($_SESSION['username'])) {
    header("Location: dang_nhap.php");
    exit;
}

// Lấy và làm sạch dữ liệu từ GET
if (!isset($_GET['faculty']) || !isset($_GET['admission_group'])) {
    header("Location: trang_chu.php");
    exit;
}

$faculty_get = htmlspecialchars($_GET['faculty']);
$admission_group_get = htmlspecialchars($_GET['admission_group']);
$username_safe = htmlspecialchars($_SESSION['username']);
$role = htmlspecialchars($_SESSION['role']);

// 3. Xử lý logic Duyệt/Xóa (POST) - Áp dụng PRG
if (isset($_POST['duyet']) || isset($_POST['khong_duyet']) || isset($_POST['xoa'])) {
    // Chỉ Admin và Teacher mới có quyền thao tác
    if ($role === 'admin' || $role === 'teacher') {
        $ho_so_id = isset($_POST['duyet']) ? (int)$_POST['duyet'] : (
            isset($_POST['khong_duyet']) ? (int)$_POST['khong_duyet'] : (
                isset($_POST['xoa']) ? (int)$_POST['xoa'] : 0
            )
        );

        if ($ho_so_id > 0) {
            if (isset($_POST['duyet'])) {
                $message = duyetHoSo($ho_so_id, $username_safe);
            } elseif (isset($_POST['khong_duyet'])) {
                $message = khongDuyetHoSo($ho_so_id, $username_safe);
            } elseif ($role === 'admin' && isset($_POST['xoa'])) { // Chỉ Admin mới có quyền Xóa
                $tmp = getHoSoById($ho_so_id);
                // Giả định hàm xoaThuMuc an toàn
                xoaThuMuc('../file_folder/' . htmlspecialchars($tmp['file_anh'])); 
                $message = xoaHoSo($ho_so_id);
            }
            
            // Chuyển hướng sau khi xử lý POST thành công
            $_SESSION['message'] = $message;
            $redirect_url = "trang_thong_ke_ho_so.php?faculty=" . urlencode($faculty_get) . "&admission_group=" . urlencode($admission_group_get);
            header("Location: " . $redirect_url);
            exit;
        }
    } else {
        $_SESSION['message'] = '<div class="alert alert-danger" role="alert">Bạn không có quyền thực hiện thao tác này.</div>';
        header("Location: trang_chu.php");
        exit;
    }
}

// 4. Xử lý thông báo sau khi chuyển hướng
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Xóa thông báo sau khi hiển thị
    
    // Thêm icon cho thông báo
    if (strpos($message, 'alert-success') !== false) {
        $message = str_replace('<div class="alert alert-success" role="alert">', '<div class="alert alert-success" role="alert"><i class="fas fa-check-circle"></i> ', $message);
    } elseif (strpos($message, 'alert-danger') !== false) {
        $message = str_replace('<div class="alert alert-danger" role="alert">', '<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> ', $message);
    } elseif (strpos($message, 'alert-warning') !== false) {
        $message = str_replace('<div class="alert alert-warning" role="alert">', '<div class="alert alert-warning" role="alert"><i class="fas fa-info-circle"></i> ', $message);
    }
}


// 5. Lấy danh sách hồ sơ để hiển thị
$ds_hs = getHoSo($faculty_get, $admission_group_get);

// Hàm tiện ích hiển thị trạng thái
function displayStatus($trang_thai) {
    switch ($trang_thai) {
        case -1:
            return '<span class="badge badge-danger" style="font-size: 1em;">❌ Không Duyệt</span>';
        case 0:
            return '<span class="badge badge-warning" style="font-size: 1em;">⏳ Chưa Duyệt</span>';
        case 1:
            return '<span class="badge badge-success" style="font-size: 1em;">✅ Đã Duyệt</span>';
        default:
            return '<span class="badge badge-secondary" style="font-size: 1em;">❓ Không rõ</span>';
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
    <link rel="icon" type="image/x-icon" href="../image/logo.png">
    <title>Trang Thống Kê Hồ Sơ</title>
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
        
        .hero p {
            font-size: 1.2rem;
            font-weight: 300;
            opacity: 0.9;
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

        /* --- Hồ Sơ Card --- */
        .card {
            border-left: 5px solid var(--primary-color);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s;
            margin-bottom: 15px;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }

        .card-body h5 {
            color: var(--primary-color);
            font-weight: 700;
            border-bottom: 2px solid #eee;
            padding-bottom: 5px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-body p {
            margin-bottom: 5px;
            font-size: 1rem;
        }

        .card-body p strong {
            font-weight: 600;
        }
        
        .btn-group-action {
            margin-top: 10px;
            display: flex;
            gap: 10px;
        }
        
        /* Footer */
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
                        if ($role === 'admin') {
                            echo '<a href="trang_them_chuyen_nganh.php" class="dropdown-item"><i class="fas fa-plus-circle"></i> Thêm Chuyên Ngành TS</a>';
                            echo '<a href="trang_phan_quyen.php" class="dropdown-item"><i class="fas fa-user-shield"></i> Phân Quyền Tài Khoản GV</a>';
                            echo '<a href="trang_thong_ke_tong_ho_so.php" class="dropdown-item"><i class="fas fa-chart-bar"></i> Thống Kê Tổng Số Hồ Sơ</a>';
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
        <h1>THỐNG KÊ HỒ SƠ TUYỂN SINH</h1>
        <p>Chuyên ngành: <strong><?php echo $faculty_get; ?></strong> - Khối: <strong><?php echo $admission_group_get; ?></strong></p>
    </div>

    <div class="container">
        <a href="trang_chu.php" class="btn btn-secondary" style="margin-bottom: 20px;"><i class="fas fa-arrow-left"></i> Trở lại Trang Chủ</a>
        
        <?php echo $message; ?> 
        
        <div class="row">
            <?php
            if ($ds_hs != 0 && !empty($ds_hs)) {
                $count = 1;
                foreach ($ds_hs as $hs) {
                    // Bảo mật XSS cho dữ liệu từ CSDL
                    $hs_id = (int)$hs['id'];
                    $hs_ten_hs = htmlspecialchars($hs['ten_hs']);
                    $hs_ten_cn = htmlspecialchars($hs['ten_cn']);
                    $hs_ten_kxt = htmlspecialchars($hs['ten_kxt']);
                    $trang_thai_text = displayStatus((int)$hs['trang_thai']);
                    
                    echo '<div class="col-md-6 col-lg-4 mb-4">'; // Thay đổi bố cục thành card 2-3 cột
                    echo '<form method="post">';
                    echo '<div class="card">';
                    echo '<div class="card-body">';
                    
                    // Tiêu đề card và Trạng thái
                    echo '<h5 class="card-title">';
                    echo 'Hồ sơ <span class="badge badge-info">' . $count . '</span>';
                    echo '</h5>';
                    
                    echo '<p class="card-text"><strong>Họ và Tên:</strong> ' . $hs_ten_hs . '</p>';
                    echo '<p class="card-text"><strong>Chuyên ngành:</strong> ' . $hs_ten_cn . '</p>';
                    echo '<p class="card-text"><strong>Tổ hợp xét tuyển:</strong> ' . $hs_ten_kxt . '</p>';
                    echo '<p class="card-text"><strong>Trạng thái:</strong> ' . $trang_thai_text . '</p>';
                    
                    echo '<hr>';

                    echo '<div class="btn-group-action">';

                    // Nút Xem chi tiết
                    echo '<a href="trang_xem_ho_so.php?id=' . $hs_id . '&faculty=' . urlencode($faculty_get) . '&admission_group=' . urlencode($admission_group_get) . '" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i> Xem chi tiết</a>';
                    
                    // Logic hiển thị nút Duyệt/Không duyệt
                    if ($role === 'admin' || $role === 'teacher') {
                        if ((int)$hs['trang_thai'] == 0) { // Chưa duyệt
                            echo    '<button type="submit" name="duyet" value="' . $hs_id . '" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Duyệt</button>';
                            echo    '<button type="submit" name="khong_duyet" value="' . $hs_id . '" class="btn btn-warning btn-sm"><i class="fas fa-times"></i> Không duyệt</button>';
                        } elseif ((int)$hs['trang_thai'] == 1) { // Đã duyệt
                            echo    '<button type="submit" name="khong_duyet" value="' . $hs_id . '" class="btn btn-warning btn-sm"><i class="fas fa-times"></i> Hủy duyệt</button>';
                        } else { // Không duyệt (-1)
                            echo    '<button type="submit" name="duyet" value="' . $hs_id . '" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Duyệt</button>';
                        }
                    }
                    
                    // Chỉ Admin mới có nút Xóa
                    if ($role === 'admin') {
                        echo '<button type="submit" name="xoa" value="' . $hs_id . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Bạn có chắc chắn muốn xóa hồ sơ này không? Hành động này không thể hoàn tác.\')"><i class="fas fa-trash"></i> Xóa</button>';
                    }
                    
                    echo    '</div>'; // End btn-group-action
                    echo    '</div>'; // End card-body
                    echo    '</div>'; // End card
                    echo '</form>';
                    echo '</div>'; // close col-md-6
                    $count++;
                }
            } else {
                echo '<div class="col-md-12"><div class="alert alert-warning" role="alert"><i class="fas fa-info-circle"></i> Chưa có dữ liệu hồ sơ tuyển sinh cho chuyên ngành này!</div></div>';
            }
            ?>
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