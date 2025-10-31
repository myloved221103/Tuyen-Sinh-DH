<?php
// trang_xem_ho_so.php
// 2154800745_Nguyễn Thái Dương

session_start(); // 1. Bắt đầu Session

include "../connectdb.php";
include "../function.php";

$message = "";

// 2. Kiểm tra đăng nhập và lấy dữ liệu GET
if (!isset($_SESSION['username'])) {
    header("Location: dang_nhap.php");
    exit;
}

// Lấy và làm sạch dữ liệu từ GET
$ho_so_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$faculty_get = isset($_GET['faculty']) ? htmlspecialchars($_GET['faculty']) : '';
$admission_group_get = isset($_GET['admission_group']) ? htmlspecialchars($_GET['admission_group']) : '';
$username_safe = htmlspecialchars($_SESSION['username']);
$role = htmlspecialchars($_SESSION['role']);

// Nếu thiếu ID, chuyển hướng
if ($ho_so_id === 0) {
    header("Location: trang_chu.php");
    exit;
}

// 3. Xử lý logic Duyệt/Xóa (POST) - Áp dụng PRG
if (isset($_POST['duyet']) || isset($_POST['khong_duyet']) || isset($_POST['xoa'])) {
    $post_id = isset($_POST['duyet']) ? (int)$_POST['duyet'] : (
        isset($_POST['khong_duyet']) ? (int)$_POST['khong_duyet'] : (
            isset($_POST['xoa']) ? (int)$_POST['xoa'] : 0
        )
    );

    if ($post_id === $ho_so_id) { // Đảm bảo ID trong POST khớp với ID đang xem
        $redirect_url = "trang_xem_ho_so.php?id=" . $ho_so_id . "&faculty=" . urlencode($faculty_get) . "&admission_group=" . urlencode($admission_group_get);

        if (isset($_POST['duyet']) && ($role === 'admin' || $role === 'teacher')) {
            $message = duyetHoSo($post_id, $username_safe);
        } elseif (isset($_POST['khong_duyet']) && ($role === 'admin' || $role === 'teacher')) {
            $message = khongDuyetHoSo($post_id, $username_safe);
        } elseif (isset($_POST['xoa']) && $role === 'admin') {
            $tmp = getHoSoById($post_id);
            // Xóa file liên quan trước
            if ($tmp && !empty($tmp['file_anh'])) {
                // Đường dẫn thư mục cần xóa được xây dựng lại
                $folder_to_delete = '../file_folder/' . htmlspecialchars($tmp['ten_hs']) . '_' . htmlspecialchars($tmp['ten_cn']) . '_' . htmlspecialchars($tmp['ten_kxt']);
                xoaThuMuc($folder_to_delete);
            }
            $message = xoaHoSo($post_id);
            // Sau khi xóa, chuyển hướng về trang thống kê
            $redirect_url = "trang_thong_ke_ho_so.php?faculty=" . urlencode($faculty_get) . "&admission_group=" . urlencode($admission_group_get);
        } else {
            $message = '<div class="alert alert-danger" role="alert">Bạn không có quyền thực hiện thao tác này.</div>';
            $redirect_url = "trang_chu.php"; // Nếu quyền không hợp lệ
        }

        // Chuyển hướng
        $_SESSION['message'] = $message;
        header("Location: " . $redirect_url);
        exit;
    }
}

// 4. Lấy dữ liệu hiển thị sau khi đã xử lý POST
$hs = getHoSoById($ho_so_id);

// Nếu hồ sơ không tồn tại, chuyển hướng về trang thống kê
if ($hs === 0) {
    header("Location: trang_thong_ke_ho_so.php?faculty=" . urlencode($faculty_get) . "&admission_group=" . urlencode($admission_group_get));
    exit;
}

// Lấy thông tin tổ hợp môn
$kxt = getMonToHop($hs['ten_kxt']);

// 5. Xử lý thông báo sau khi chuyển hướng
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
    
    // Thêm icon cho thông báo
    if (strpos($message, 'alert-success') !== false) {
        $message = str_replace('<div class="alert alert-success" role="alert">', '<div class="alert alert-success" role="alert"><i class="fas fa-check-circle"></i> ', $message);
    } elseif (strpos($message, 'alert-danger') !== false) {
        $message = str_replace('<div class="alert alert-danger" role="alert">', '<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> ', $message);
    } elseif (strpos($message, 'alert-warning') !== false) {
        $message = str_replace('<div class="alert alert-warning" role="alert">', '<div class="alert alert-warning" role="alert"><i class="fas fa-info-circle"></i> ', $message);
    }
}

// Hàm tiện ích hiển thị trạng thái
function displayStatus($trang_thai) {
    switch ((int)$trang_thai) {
        case -1:
            return '<span class="badge badge-danger" style="font-size: 1em;"><i class="fas fa-times-circle"></i> Không duyệt</span>';
        case 0:
            return '<span class="badge badge-warning" style="font-size: 1em;"><i class="fas fa-clock"></i> Chưa duyệt</span>';
        case 1:
            return '<span class="badge badge-success" style="font-size: 1em;"><i class="fas fa-check-circle"></i> Đã duyệt</span>';
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
    <title>Trang Xem Hồ Sơ</title>
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
            width: 80px; 
            height: 80px; 
            border-radius: 50%; 
            display: block; 
            margin: 0 auto 15px auto; 
            background-color: white;
            border: 3px solid var(--secondary-color) !important;
            object-fit: cover;
        }
        
        /* --- Nội dung chi tiết --- */
        .detail-card {
            max-width: 900px; /* Tăng chiều rộng tổng thể */
            margin: 20px auto 50px auto;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border-top: 5px solid var(--primary-color);
        }
        
        .detail-card h3 {
            color: var(--primary-color);
            font-weight: 700;
            padding-bottom: 5px;
            margin-bottom: 25px;
            border-bottom: 3px solid var(--secondary-color);
        }

        /* Form Controls Styling */
        .form-group p {
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 5px;
        }
        
        .form-control[readonly] {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            font-weight: 500;
            color: var(--dark-text);
        }
        
        /* Flex Score/Date */
        .score-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        .score-row > div {
            flex: 1;
        }
        
        /* Ảnh học bạ */
        .image-section {
            margin-top: 30px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            background-color: #fcfcfc;
        }
        
        .image-section h4 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 15px;
        }

        .image-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .image-grid img {
            max-width: 100%;
            height: auto;
            max-height: 400px; 
            object-fit: contain;
            border: 2px solid var(--secondary-color);
            padding: 5px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        /* Nút thao tác */
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            padding: 15px;
            border-top: 1px solid #eee;
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
        <h1>XEM HỒ SƠ TUYỂN SINH CHI TIẾT</h1>
        <p style="font-size: 1.1rem; opacity: 0.9;">Mã hồ sơ: #<?php echo $ho_so_id; ?></p>
    </div>
    
    <div class="container">
        <?php
        $back_url = "trang_chu.php";
        // Teacher/Admin trở lại trang thống kê
        if ($role !== 'student') { 
            $back_url = 'trang_thong_ke_ho_so.php?faculty=' . urlencode($faculty_get) . '&admission_group=' . urlencode($admission_group_get);
        }
        echo '<a href="' . $back_url . '" class="btn btn-secondary" style="margin-bottom: 20px;"><i class="fas fa-arrow-left"></i> Trở lại</a>';
        
        // Hiển thị thông báo (nếu có)
        echo $message;
        ?>
    </div>

    <form method="post">
        <div class="container detail-card">
            <h3 class="text-center"><i class="fas fa-file-alt"></i> Thông Tin Hồ Sơ</h3>
            <?php
            // Hiển thị thông tin hồ sơ
            if ($hs !== 0) {
                // Lấy và làm sạch dữ liệu hồ sơ
                $hs_ten_hs = htmlspecialchars($hs['ten_hs']);
                $hs_ten_cn = htmlspecialchars($hs['ten_cn']);
                $hs_ten_kxt = htmlspecialchars($hs['ten_kxt']);
                $hs_ngay_nop = htmlspecialchars($hs['ngay_nop']);
                $hs_nguoi_duyet = htmlspecialchars($hs['ten_nguoi_duyet']);
                $hs_ngay_duyet = htmlspecialchars($hs['ngay_duyet']);
                $hs_mon1 = htmlspecialchars($hs['mon1']);
                $hs_mon2 = htmlspecialchars($hs['mon2']);
                $hs_mon3 = htmlspecialchars($hs['mon3']);

                $mon1_name = ($kxt !== 0 && isset($kxt[0]['mon1'])) ? htmlspecialchars($kxt[0]['mon1']) : 'Môn 1';
                $mon2_name = ($kxt !== 0 && isset($kxt[0]['mon2'])) ? htmlspecialchars($kxt[0]['mon2']) : 'Môn 2';
                $mon3_name = ($kxt !== 0 && isset($kxt[0]['mon3'])) ? htmlspecialchars($kxt[0]['mon3']) : 'Môn 3';
                
                $trang_thai = displayStatus($hs['trang_thai']);
                
                // 1. Hiển thị thông tin chính
                echo    '<div class="row">
                            <div class="col-12">
                                <div class="form-group"><p>Họ và tên</p><input type="text" class="form-control" value="' . $hs_ten_hs . '" readonly></div>
                                <div class="form-group"><p>Chuyên ngành xét tuyển</p> <input type="text" class="form-control" value="' . $hs_ten_cn . '" readonly></div>
                                <div class="form-group"><p>Tổ hợp xét tuyển</p> <input type="text" class="form-control" value="' . $hs_ten_kxt . '" readonly></div>
                                
                                <label class="mt-3"><strong>Điểm Xét Tuyển:</strong></label>
                                <div class="score-row">
                                    <div class="form-group"><p>' . $mon1_name . '</p> <input type="number" step="0.01" class="form-control" value="' . $hs_mon1 . '" readonly></div>
                                    <div class="form-group"><p>' . $mon2_name . '</p> <input type="number" step="0.01" class="form-control" value="' . $hs_mon2 . '" readonly></div>
                                    <div class="form-group"><p>' . $mon3_name . '</p> <input type="number" step="0.01" class="form-control" value="' . $hs_mon3 . '" readonly></div>
                                </div>
                                
                                <div class="score-row">
                                    <div class="form-group"><p>Ngày nộp hồ sơ</p> <input type="date" class="form-control" value="' . $hs_ngay_nop . '" readonly></div>
                                    <div class="form-group"><p>Tình trạng hồ sơ</p> <div class="form-control" style="background-color: #f8f9fa; border: 1px solid #dee2e6; font-weight: 500; height: 38px; display: flex; align-items: center;">' . $trang_thai . '</div></div>
                                </div>

                                <div class="score-row">
                                    <div class="form-group"><p>Người duyệt hồ sơ</p> <input type="text" class="form-control" value="' . $hs_nguoi_duyet . '" readonly></div>
                                    <div class="form-group"><p>Ngày duyệt hồ sơ</p> <input type="date" class="form-control" value="' . $hs_ngay_duyet . '" readonly></div>
                                </div>
                            </div>
                        </div>';
                        
                // 2. Hiển thị Ảnh học bạ
                echo '<div class="image-section">';
                echo '<h4><i class="fas fa-images"></i> Ảnh Học Bạ Đính Kèm</h4>';
                
                // Lấy danh sách tệp
                // Lưu ý: Đảm bảo tên thư mục được mã hóa an toàn
                $folder_name_safe = htmlspecialchars($hs['ten_hs']) . '_' . htmlspecialchars($hs['ten_cn']) . '_' . htmlspecialchars($hs['ten_kxt']);
                $folder_path = '../file_folder/' . $folder_name_safe . '/';
                $img_files = glob($folder_path . '*');

                if (!empty($img_files)) {
                    echo '<div class="image-grid">';
                    foreach ($img_files as $anh) {
                         // Bảo mật: Đảm bảo đường dẫn an toàn trước khi in ra src
                        $anh_safe = htmlspecialchars($anh);
                        echo '<img src="' . $anh_safe . '" alt="Ảnh học bạ">';
                    }
                    echo '</div>';
                } else {
                    echo '<div class="alert alert-warning text-center"><i class="fas fa-exclamation-triangle"></i> Không tìm thấy ảnh học bạ nào trong thư mục.</div>';
                }
                echo '</div>'; // End image-section
            } else {
                echo '<div class="alert alert-danger" role="alert"><i class="fas fa-times-circle"></i> Không tìm thấy hồ sơ. Vui lòng kiểm tra lại ID hồ sơ.</div>';
            }
            ?>
        </div>
        
        <?php
        // 3. Hiển thị nút thao tác (chỉ cho Admin/Teacher)
        if ($hs !== 0 && ($role === 'admin' || $role === 'teacher')) {
            $current_status = (int)$hs['trang_thai'];
            $btn_html = '';

            // Nút Duyệt / Không duyệt
            if ($current_status == -1 || $current_status == 0) { // Không duyệt hoặc Chưa duyệt
                $btn_html .= '<button type="submit" name="duyet" value="' . $ho_so_id . '" class="btn btn-success"><i class="fas fa-check"></i> Duyệt Hồ Sơ</button>';
            }
            if ($current_status == 1 || $current_status == 0) { // Đã duyệt hoặc Chưa duyệt
                $btn_html .= '<button type="submit" name="khong_duyet" value="' . $ho_so_id . '" class="btn btn-warning"><i class="fas fa-times"></i> Không Duyệt</button>';
            }

            // Nút Xóa (Chỉ dành cho Admin)
            if ($role === 'admin') {
                $btn_html .= '<button type="submit" name="xoa" value="' . $ho_so_id . '" class="btn btn-danger" onclick="return confirm(\'Bạn có chắc chắn muốn xóa hồ sơ này không? Hành động này không thể hoàn tác và sẽ xóa cả thư mục ảnh.\')"><i class="fas fa-trash"></i> Xóa Hồ Sơ</button>';
            }
            
            // In nút ra màn hình
            if (!empty($btn_html)) {
                echo '<div class="container action-buttons">';
                echo $btn_html;
                echo '</div>';
            }
        }
        ?>
    </form>
    
    <footer>
        <p>&copy; 2025 Tuyển sinh FBU | Phát triển bởi Nguyễn Thái Dương - 2154800745</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>