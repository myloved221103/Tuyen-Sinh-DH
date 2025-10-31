<?php
// trang_chu.php
// 2154800745_Nguyễn Thái Dương

// 1. Bắt đầu phiên và kiểm tra đăng nhập
session_start(); // Cần thiết để sử dụng $_SESSION
include "../connectdb.php";
include "../function.php";

// Kiểm tra đăng nhập (đã có nhưng cần thêm exit)
if (!isset($_SESSION['username'])) {
    header("Location: dang_nhap.php");
    exit; // Dừng ngay lập tức sau khi chuyển hướng
}

// Lấy thông tin session
// Đã được làm sạch HTML trong code gốc, giữ nguyên
$username = htmlspecialchars($_SESSION['username']);
$role = htmlspecialchars($_SESSION['role']);

// 2. Xử lý các hành động POST (Admin)
// Đưa logic xử lý lên đầu file để dễ quản lý và thực hiện trước khi gửi bất kỳ nội dung nào
if ($role == 'admin') {
    if (isset($_POST['xoa'])) {
        $num = (int)$_POST['xoa']; // Ép kiểu về số nguyên để bảo mật hơn
        $tmp = getHoSoByCN($num);
        
        // Cần đảm bảo hàm xoaThuMuc và deleteChuyenNganh xử lý an toàn
        foreach ($tmp as $hs) {
            // Giả định $hs['file_anh'] là tên file/thư mục cần xóa
            xoaThuMuc('../file_folder/' . $hs['file_anh']); 
        }
        
        // Giả định hàm deleteChuyenNganh() trả về thông báo lỗi/thành công (HTML)
        $message = deleteChuyenNganh($num); 
        
        // Nếu bạn muốn hiển thị thông báo, hãy lưu vào session và chuyển hướng
        $_SESSION['message'] = $message; 
        header("Location: trang_chu.php");
        exit;
    }
    
    if (isset($_POST['an'])) {
        $num = (int)$_POST['an'];
        $_SESSION['message'] = hideChuyenNganh($num);
        header("Location: trang_chu.php");
        exit;
    }
    
    if (isset($_POST['hien'])) {
        $num = (int)$_POST['hien'];
        $_SESSION['message'] = showChuyenNganh($num);
        header("Location: trang_chu.php");
        exit;
    }
}

// 3. Xử lý thông báo sau khi chuyển hướng
$message = '';
if (isset($_SESSION['message'])) {
    // Để đảm bảo tính bảo mật, bạn nên xử lý thông báo HTML từ PHP một cách cẩn thận.
    // Nếu $message đến từ các hàm PHP của bạn và chứa HTML, bạn cần đảm bảo nó an toàn.
    // Ở đây tôi giữ nguyên để không làm thay đổi logic hiển thị thông báo của bạn.
    $message = $_SESSION['message']; 
    unset($_SESSION['message']); // Xóa thông báo sau khi hiển thị
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

    <style>
        :root {
            --primary-color: #004d99; /* Xanh đậm - màu chủ đạo */
            --secondary-color: #ffcc00; /* Vàng - màu nhấn */
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

        .dropdown-menu {
            border: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .dropdown-item {
            color: var(--dark-text);
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
        
        /* --- Slider (Giữ nguyên cấu trúc gốc nhưng làm đẹp hơn) --- */
        .slider {
            width: 100%;
            height: 300px; 
            overflow: hidden;
            margin: 20px auto;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .slider ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            width: calc(100% * var(--quantity));
            height: 100%;
            animation: slideShow var(--time) infinite linear;
        }

        .slider ul li {
            width: calc(100% / var(--quantity));
            height: 100%;
            flex-shrink: 0;
        }

        .slider ul li img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        @keyframes slideShow {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(calc(-100% + (100% / var(--quantity)))); /* Di chuyển 11 ảnh */
            }
        }
        
        /* Điều chỉnh nếu số lượng ảnh thay đổi */
        .slider ul[style*="--quantity: 12"] {
            /* 12 ảnh, di chuyển 11 ảnh để lặp lại */
            animation: slideShow12 var(--time) infinite linear;
        }
        @keyframes slideShow12 {
            0% { transform: translateX(0); }
            100% { transform: translateX(calc(-100% * 11 / 12)); }
        }
        
        /* --- Content và Cards --- */
        .content {
            padding-top: 20px;
            padding-bottom: 40px;
        }

        .content h3 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 30px;
            border-bottom: 3px solid var(--secondary-color);
            display: inline-block;
            padding-bottom: 5px;
        }

        .card {
            border: 1px solid #ddd;
            border-radius: 10px;
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 25px;
            background-color: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            height: 100%; /* Đảm bảo chiều cao bằng nhau */
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .card-title {
            color: var(--primary-color);
            font-weight: 600;
            border-left: 4px solid var(--secondary-color);
            padding-left: 10px;
            margin-bottom: 15px;
        }

        .card-text {
            font-size: 0.95rem;
            color: #555;
            margin-bottom: 8px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            transition: background-color 0.3s;
        }

        .btn-primary:hover {
            background-color: #003366; 
            border-color: #003366;
        }
        
        .btn-danger, .btn-warning, .btn-success {
            margin-top: 5px; /* Để các nút đều nằm trên cùng một dòng hoặc có khoảng cách nhất định */
        }
        
        /* --- Thông báo --- */
        .alert-success, .alert-danger, .alert-warning {
            border-radius: 5px;
            font-weight: 500;
            /* Cần đảm bảo các style inline trong PHP không phá vỡ bố cục */
        }
        
        /* Fix cho thông báo "Đã nộp hồ sơ" */
        .card .row .alert {
            margin-bottom: 0px !important;
        }
        
        /* --- Footer --- */
        footer {
            background-color: var(--primary-color);
            color: white;
            text-align: center;
            padding: 15px 0;
            margin-top: 30px;
        }

    </style>
    
    <title>Trang Chủ | Tuyển Sinh FBU</title>
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
                        <i class="fas fa-user-circle"></i> Xin chào, <?php echo $username; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                        <?php
                        // Sử dụng biến $role đã được làm sạch
                        if ($role == 'admin') {
                            echo '<a href="trang_them_chuyen_nganh.php" class="dropdown-item"><i class="fas fa-plus-circle"></i> Thêm Chuyên Ngành Tuyển Sinh</a>';
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
    
    <div class="container mt-3">
        <?php 
        // Thay thế echo $message; bằng cấu trúc alert của Bootstrap để đẹp hơn (giữ nguyên nếu $message đã chứa HTML alert)
        if (!empty($message)) {
            echo '<div class="alert alert-info alert-dismissible fade show" role="alert">
                      ' . $message . '
                      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                      </button>
                  </div>';
        }
        ?> 
    </div>

    <div class="hero">
        <img src="../image/logo.png" alt="Logo Trường Đại Học Tài Chính - Ngân Hàng Hà Nội" class="site-logo" style="width: 80px; height: 80px; border-radius: 50%; display: block; margin: 0 auto 15px auto; background-color: white;">
        <h1>CHÀO MỪNG ĐẾN VỚI TUYỂN SINH ĐH TÀI CHÍNH NGÂN HÀNG HÀ NỘI</h1>
        <p style="font-size: 1.1rem; opacity: 0.9;">Bước ra thế giới với tấm bằng đại học</p>
    </div>

    <div class="container">
        <div class="slider">
            <ul style="
            --time: 30s; /* Tăng thời gian để slider chậm hơn */
            --quantity: 12;
            ">
                <li style="--index: 1"><img src="../image/anh_1.jpg" alt="Ảnh 1"></li>
                <li style="--index: 2"><img src="../image/anh_2.jpg" alt="Ảnh 2"></li>
                <li style="--index: 3"><img src="../image/anh_3.jpg" alt="Ảnh 3"></li>
                <li style="--index: 4"><img src="../image/anh_4.jpg" alt="Ảnh 4"></li>
                <li style="--index: 5"><img src="../image/anh_5.jpg" alt="Ảnh 5"></li>
                <li style="--index: 6"><img src="../image/anh_6.jpg" alt="Ảnh 6"></li>
                <li style="--index: 7"><img src="../image/anh_7.jpg" alt="Ảnh 7"></li>
                <li style="--index: 8"><img src="../image/anh_8.jpg" alt="Ảnh 8"></li>
                <li style="--index: 9"><img src="../image/anh_9.jpg" alt="Ảnh 9"></li>
                <li style="--index: 10"><img src="../image/anh_10.jpg" alt="Ảnh 10"></li>
                <li style="--index: 11"><img src="../image/anh_11.jpg" alt="Ảnh 11"></li>
                <li style="--index: 12"><img src="../image/anh_12.jpg" alt="Ảnh 12"></li>
            </ul>
        </div>
    </div>
    
    <form method="post">
        <div class="container content">
            <?php
            // Hiển thị vai trò hoặc thông tin bổ sung (nếu cần)
            if ($role == 'admin') {
                echo '<p class="text-center text-muted"><i class="fas fa-crown text-warning"></i> Bạn đang đăng nhập với vai trò Quản trị viên.</p>';
            }
            ?>
            <h3 style="text-align: center; text-transform: uppercase">Danh Sách Chuyên Ngành Tuyển Sinh</h3>
            <?php
            if ($role == "teacher" && isset($_SESSION['mission'])) {
                // Bảo mật XSS cho $_SESSION['mission']
                $mission_display = str_replace("|", ", ", htmlspecialchars($_SESSION['mission']));
                echo '<p class="text-center text-info"><i class="fas fa-clipboard-list"></i> Chuyên ngành được phân công: <strong>' . $mission_display . '</strong></p>';
            }
            ?>
            <div class="row">
                <?php
                // --- Logic hiển thị danh sách chuyên ngành (GIỮ NGUYÊN) ---
                
                switch ($role) {
                    case 'admin':
                        $ds_cn = adminChuyenNganh();
                        if (!empty($ds_cn)) {
                            foreach ($ds_cn as $cn) {
                                // Bảo mật XSS cho tất cả dữ liệu từ CSDL
                                $faculty = htmlspecialchars($cn['faculty']);
                                $admission_group = htmlspecialchars($cn['admission_group']);
                                $num = (int)$cn['num'];
                                
                                echo '<div class="col-lg-4 col-md-6">';
                                echo '<div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title">' . $faculty . '</h5>
                                                <p class="card-text"><i class="fas fa-file-signature text-secondary"></i> Tổ hợp xét tuyển: <strong>' . $admission_group . '</strong></p>
                                                <p class="card-text"><i class="fas fa-calendar-check text-success"></i> Bắt đầu: ' . date('d-m-Y', strtotime($cn['begin_date'])) . '</p>
                                                <p class="card-text"><i class="fas fa-calendar-times text-danger"></i> Hết hạn: ' . date('d-m-Y', strtotime($cn['end_date'])) . '</p>
                                                <div class="mt-3">
                                                    <a href="trang_thong_ke_ho_so.php?faculty=' . urlencode($faculty) . '&admission_group=' . urlencode($admission_group) . '" class="btn btn-primary btn-sm"><i class="fas fa-chart-line"></i> Thống kê</a>
                                                &nbsp;';
                                echo    $cn['status'] == 1 ? '<button type="submit" name="an" value="' . $num . '" class="btn btn-warning btn-sm"><i class="fas fa-eye-slash"></i> Ẩn</button>'
                                    : '<button type="submit" name="hien" value="' . $num . '" class="btn btn-success btn-sm"><i class="fas fa-eye"></i> Hiện</button>';
                                echo            '&nbsp;<button type="submit" name="xoa" value="' . $num . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Bạn có chắc chắn muốn xóa chuyên ngành này không?\')"><i class="fas fa-trash-alt"></i> Xóa</button>
                                                </div>
                                            </div>
                                        </div>';
                                echo '</div>';
                            }
                        } else {
                            echo '<div class="col-md-12"><div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> Chưa có dữ liệu chuyên ngành tuyển sinh!</div></div>';
                        }
                        break;
                    case 'teacher':
                        if (isset($_SESSION['mission'])) {
                            $missions = explode("|", $_SESSION['mission']);
                            $has_data = false;
                            foreach ($missions as $mission) {
                                $ds_cn = teacherChuyenNganh(trim($mission)); // trim để làm sạch
                                if (!empty($ds_cn)) {
                                    $has_data = true;
                                    foreach ($ds_cn as $cn) {
                                        $faculty = htmlspecialchars($cn['faculty']);
                                        $admission_group = htmlspecialchars($cn['admission_group']);
                                        
                                        echo '<div class="col-lg-4 col-md-6">';
                                        echo '<div class="card">
                                                    <div class="card-body">
                                                        <h5 class="card-title">' . $faculty . '</h5>
                                                        <p class="card-text"><i class="fas fa-file-signature text-secondary"></i> Tổ hợp xét tuyển: <strong>' . $admission_group . '</strong></p>
                                                        <p class="card-text"><i class="fas fa-calendar-check text-success"></i> Bắt đầu: ' . date('d-m-Y', strtotime($cn['begin_date'])) . '</p>
                                                        <p class="card-text"><i class="fas fa-calendar-times text-danger"></i> Hết hạn: ' . date('d-m-Y', strtotime($cn['end_date'])) . '</p>
                                                        <div class="mt-3">
                                                            <a href="trang_thong_ke_ho_so.php?faculty=' . urlencode($faculty) . '&admission_group=' . urlencode($admission_group) . '" class="btn btn-primary"><i class="fas fa-chart-line"></i> Thống kê</a>
                                                        </div>
                                                    </div>
                                                </div>';
                                        echo '</div>';
                                    }
                                }
                            }
                            if (!$has_data) {
                                echo '<div class="col-md-12"><div class="alert alert-warning" role="alert"><i class="fas fa-exclamation-circle"></i> Bạn chưa được phân công hoặc các chuyên ngành được phân công chưa có dữ liệu.</div></div>';
                            }
                        }
                        break;
                    case 'student':
                        $ds_cn = studentChuyenNganh();
                        if (!empty($ds_cn)) {
                            foreach ($ds_cn as $cn) {
                                $faculty = htmlspecialchars($cn['faculty']);
                                $admission_group = htmlspecialchars($cn['admission_group']);
                                
                                echo    '<div class="col-lg-4 col-md-6">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h5 class="card-title">' . $faculty . '</h5>
                                                    <p class="card-text"><i class="fas fa-file-signature text-secondary"></i> Tổ hợp xét tuyển: <strong>' . $admission_group . '</strong></p>
                                                    <p class="card-text"><i class="fas fa-calendar-check text-success"></i> Bắt đầu: ' . date('d-m-Y', strtotime($cn['begin_date'])) . '</p>
                                                    <p class="card-text"><i class="fas fa-calendar-times text-danger"></i> Hết hạn: ' . date('d-m-Y', strtotime($cn['end_date'])) . '</p>
                                                    <div class="mt-3">';
                                                    
                                // Kiểm tra ngày nộp hồ sơ
                                $ho_so_action = '';
                                $current_date = date('Y-m-d');
                                
                                if ($cn['begin_date'] > $current_date) {
                                    $ho_so_action = '<div class="alert alert-danger p-2 mb-0" role="alert"><small><i class="fas fa-clock"></i> Chưa bắt đầu nộp hồ sơ!</small></div>';
                                } elseif ($cn['end_date'] <= $current_date) {
                                    $ho_so_action = '<div class="alert alert-danger p-2 mb-0" role="alert"><small><i class="fas fa-lock"></i> Đã hết hạn nộp hồ sơ!</small></div>';
                                } else {
                                    // Kiểm tra hồ sơ đã tồn tại chưa
                                    $ho_so_exist = isHoSoExist($username, $cn['faculty']);
                                    if ($ho_so_exist == 0) {
                                        $ho_so_action = '<a href="trang_nop_ho_so.php?faculty=' . urlencode($faculty) . '&admission_group=' . urlencode($admission_group) . '" class="btn btn-primary"><i class="fas fa-upload"></i> Nộp hồ sơ</a>';
                                    } else {
                                        // Kiểm tra tổ hợp xét tuyển
                                        $checked_admission_group = checkAdmissionGroup($username, $cn['faculty']);
                                        
                                        if ($admission_group == htmlspecialchars($checked_admission_group)) {
                                            $hs = $ho_so_exist; // $hs lúc này là mảng hồ sơ
                                            $hs_id = (int)$hs[0]['id'];
                                            $hs_ten_cn = htmlspecialchars($hs[0]['ten_cn']);
                                            $hs_ten_kxt = htmlspecialchars($hs[0]['ten_kxt']);
                                            
                                            $ho_so_action = '<div class="d-flex align-items-center">
                                                                <span class="alert alert-success p-2 mb-0 mr-2"><small><i class="fas fa-check-circle"></i> Đã nộp!</small></span>
                                                                <a href="trang_xem_ho_so.php?id=' . $hs_id . '&faculty=' . urlencode($hs_ten_cn) . '&admission_group=' . urlencode($hs_ten_kxt) . '" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> Xem chi tiết</a>
                                                            </div>';
                                        } else {
                                            // Trường hợp đã nộp chuyên ngành nhưng khác tổ hợp (khá hiếm, tùy logic hệ thống)
                                            $ho_so_action = '<div class="alert alert-danger p-2 mb-0" role="alert"><small><i class="fas fa-exclamation-triangle"></i> Đã nộp hồ sơ chuyên ngành!</small></div>';
                                        }
                                    }
                                }
                                echo $ho_so_action; // Hiển thị hành động/trạng thái
                                echo            '</div>
                                                </div>
                                            </div>
                                        </div>';
                            }
                        } else {
                            echo '<div class="col-md-12"><div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> Chưa có dữ liệu chuyên ngành tuyển sinh!</div></div>';
                        }
                        break;
                    default:
                        break;
                }
                ?>
            </div>
        </div>
    </form>
    
    <footer>
        <p>&copy; 2025 Tuyển sinh FBU | Phát triển bởi Nguyễn Thái Dương - 2154800745</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>