<?php
// trang_thong_ke_tong_ho_so.php
// 2154800745_Nguyễn Thái Dương

session_start(); // Bắt đầu Session

include "../connectdb.php";
include "../function.php";

// 1. Kiểm tra đăng nhập và quyền Admin
if (!isset($_SESSION['username']) || (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin')) {
    header("Location: dang_nhap.php");
    exit; // Dừng ngay lập tức sau khi chuyển hướng
}

// Lấy dữ liệu cần thiết
$username_safe = htmlspecialchars($_SESSION['username']);
// Giả định hàm getAllCNTS() trả về mảng chuyên ngành
$ds_cn = getAllCNTS(); 

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="../image/logo.png">
    <title>Trang Thống Kê Tổng Số Hồ Sơ</title>
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
        
        /* --- Account Table Container (Bảng Thống kê) --- */
        .admin-table-container {
            width: 100%;
            max-width: 1000px; 
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
            font-size: 1rem;
            font-weight: 500;
        }

        /* Highlight số lượng hồ sơ */
        .count-danger { color: #dc3545; font-weight: 700; }
        .count-warning { color: #ffc107; font-weight: 700; }
        .count-success { color: #28a745; font-weight: 700; }
        
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
                        if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
                            echo '<a href="trang_them_chuyen_nganh.php" class="dropdown-item"><i class="fas fa-plus-circle"></i> Thêm Chuyên Ngành TS</a>';
                            echo '<a href="trang_phan_quyen.php" class="dropdown-item"><i class="fas fa-user-shield"></i> Phân Quyền Tài Khoản GV</a>';
                            echo '<a href="trang_thong_ke_tong_ho_so.php" class="dropdown-item active"><i class="fas fa-chart-bar"></i> Thống Kê Tổng Hồ Sơ</a>';
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
        <p style="font-size: 1.1rem; opacity: 0.9;">Thống Kê Tổng Số Hồ Sơ Theo Chuyên Ngành</p>
    </div>

    <div class="container">
        <a href="trang_chu.php" class="btn btn-secondary" style="margin-bottom: 20px;"><i class="fas fa-arrow-left"></i> Trở lại Trang Chủ</a>
    </div>

    <div class="admin-table-container">
        <h3 class="text-center uppercase"><i class="fas fa-chart-pie"></i> Bảng Thống Kê Tổng Hồ Sơ Tuyển Sinh</h3>
        <div class="table-responsive">
            <table class="table table-bordered table-hover mt-4">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 5%; vertical-align: middle;">STT</th>
                        <th rowspan="2" style="width: 50%; vertical-align: middle;">Chuyên ngành</th>
                        <th colspan="3" class="text-center">Loại hồ sơ</th>
                    </tr>
                    <tr>
                        <th style="width: 15%; background-color: #dc3545 !important; border-color: #dc3545;"><i class="fas fa-times"></i> Không duyệt</th>
                        <th style="width: 15%; background-color: #ffc107 !important; border-color: #ffc107;"><i class="fas fa-clock"></i> Chưa duyệt</th>
                        <th style="width: 15%; background-color: #28a745 !important; border-color: #28a745;"><i class="fas fa-check"></i> Đã duyệt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($ds_cn != 0 && !empty($ds_cn)) {
                        $stt = 1;
                        foreach ($ds_cn as $cn) {
                            $ten_cn_safe = htmlspecialchars($cn['ten_cn']);
                            
                            // Giả định các hàm countHoSo... trả về số nguyên
                            $khong_duyet = countHoSoKhongDuyet($ten_cn_safe);
                            $chua_duyet = countHoSoChuaDuyet($ten_cn_safe);
                            $da_duyet = countHoSoDaDuyet($ten_cn_safe);
                            
                            echo "<tr>";
                            echo "<td>$stt</td>";
                            echo "<td class='text-left font-weight-bold' style='color: var(--primary-color);'>" . $ten_cn_safe . "</td>";
                            echo "<td class='count-danger'>" . $khong_duyet . "</td>";
                            echo "<td class='count-warning'>" . $chua_duyet . "</td>";
                            echo "<td class='count-success'>" . $da_duyet . "</td>";
                            echo "</tr>";
                            $stt++;
                        }
                    } else {
                        echo '<tr><td colspan="5" class="text-center"><div class="alert alert-warning m-0" role="alert"><i class="fas fa-info-circle"></i> Chưa có dữ liệu chuyên ngành tuyển sinh để thống kê!</div></td></tr>';
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