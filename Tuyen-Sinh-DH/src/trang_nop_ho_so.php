<?php
// trang_nop_ho_so.php
// 2154800745_Nguyễn Thái Dương

session_start(); // Cần thiết để sử dụng $_SESSION

include "../connectdb.php";
include "../function.php";

// Biến lưu trữ thông báo lỗi/thành công
$error_message = "";
$success_message = "";
$faculty = "";
$admission_group = "";

// Giá trị môn học để giữ lại sau khi lỗi
$mon1 = null;
$mon2 = null;
$mon3 = null;


// 1. Kiểm tra đăng nhập và lấy dữ liệu GET
if (!isset($_SESSION['username'])) {
    header("Location: dang_nhap.php");
    exit;
}

// Lấy và làm sạch dữ liệu từ GET
if (isset($_GET['faculty']) && isset($_GET['admission_group'])) {
    $faculty = htmlspecialchars($_GET['faculty']);
    $admission_group = htmlspecialchars($_GET['admission_group']);
} else {
    // Nếu thiếu thông tin, chuyển hướng người dùng
    header("Location: trang_chu.php");
    exit;
}

// 2. Xử lý Logic Nộp Hồ Sơ (POST)
if (isset($_POST['btn_nop_ho_so'])) {
    // Lấy và làm sạch dữ liệu từ POST
    $studentName = htmlspecialchars($_POST['studentName']);
    $faculty_post = htmlspecialchars($_POST['faculty']);
    $admission_group_post = htmlspecialchars($_POST['admission_group']);
    
    // Lấy điểm và giữ lại giá trị nếu có lỗi
    $mon1 = isset($_POST['mon1']) ? (float)$_POST['mon1'] : 0;
    $mon2 = isset($_POST['mon2']) ? (float)$_POST['mon2'] : 0;
    $mon3 = isset($_POST['mon3']) ? (float)$_POST['mon3'] : 0;
    
    // Khai báo thư mục gốc để lưu file
    $base_dir = '../file_folder/';
    $file_anh_folder = $studentName . '_' . $faculty_post . '_' . $admission_group_post;
    $dest_dir = $base_dir . $file_anh_folder . '/';

    $files = $_FILES['upload'];
    $total_size_img = array_sum($files['size']);
    $file_count = count($files['name']);
    $allowed_extensions = ['jpg', 'png', 'jpeg'];
    $max_total_size = 100 * 1024 * 1024; // 100MB

    // 2.1. Kiểm tra Validation chung
    if ($file_count === 0 || empty($files['name'][0])) {
        $error_message = '<div class="alert alert-danger" role="alert">Vui lòng chọn ít nhất một tệp ảnh học bạ.</div>';
    } elseif ($total_size_img > $max_total_size) {
        $error_message = '<div class="alert alert-danger" role="alert">Tổng dung lượng ảnh không được vượt quá 100MB.</div>';
    } else {
        // 2.2. Xử lý Tệp Tin
        
        // Tạo thư mục nếu chưa tồn tại
        if (!file_exists($dest_dir)) {
            if (!mkdir($dest_dir, 0777, true)) {
                $error_message = '<div class="alert alert-danger" role="alert">Lỗi: Không thể tạo thư mục lưu trữ.</div>';
            }
        }
        
        if (empty($error_message)) {
            $name_save_img = ""; // Dùng để lưu tên file vào CSDL
            $upload_ok = true;

            for ($i = 0; $i < $file_count; $i++) {
                $file_name = $files['name'][$i];
                $file_tmp = $files['tmp_name'][$i];
                $file_error = $files['error'][$i];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                if ($file_error !== UPLOAD_ERR_OK) {
                    $error_message = '<div class="alert alert-danger" role="alert">Lỗi tải tệp: Mã lỗi ' . $file_error . '</div>';
                    $upload_ok = false;
                    break;
                }
                
                if (!in_array($file_ext, $allowed_extensions)) {
                    $error_message = '<div class="alert alert-danger" role="alert">Chỉ chấp nhận tệp JPG, JPEG, PNG.</div>';
                    $upload_ok = false;
                    break;
                }
                
                // Tăng cường bảo mật: Đổi tên file ngẫu nhiên
                $safe_file_name = uniqid($studentName . '_', true) . '.' . $file_ext;
                $dest_file = $dest_dir . $safe_file_name;
                
                if (move_uploaded_file($file_tmp, $dest_file)) {
                    $name_save_img .= $safe_file_name . '|'; 
                } else {
                    $error_message = '<div class="alert alert-danger" role="alert">Lỗi: Không thể di chuyển tệp đã tải lên.</div>';
                    $upload_ok = false;
                    break;
                }
            }

            // 2.3. Chèn dữ liệu vào CSDL và chuyển hướng nếu thành công
            if ($upload_ok) {
                $name_save_img = substr($name_save_img, 0, -1); // Loại bỏ dấu '|' cuối cùng
                
                // Giả định hàm nopHoSo() xử lý việc chèn an toàn
                $result_insert = nopHoSo($studentName, $faculty_post, $admission_group_post, $mon1, $mon2, $mon3, $file_anh_folder, $name_save_img);
                
                // Chuyển hướng sau khi xử lý POST thành công (PRG Pattern)
                if ($result_insert) {
                    $_SESSION['message'] = '<div class="alert alert-success" role="alert">Nộp hồ sơ thành công! Hồ sơ của bạn đang chờ duyệt.</div>';
                } else {
                    $_SESSION['message'] = '<div class="alert alert-danger" role="alert">Lỗi: Không thể lưu hồ sơ vào hệ thống (Lỗi CSDL hoặc trùng lặp).</div>';
                }
                
                header("Location: trang_chu.php"); 
                exit;
            } else {
                // Nếu có lỗi upload, các biến $mon1, $mon2, $mon3 đã giữ giá trị cũ
            }
        }
    }
    
    // Thêm icon cho thông báo lỗi (nếu có)
    if (!empty($error_message)) {
        $error_message = str_replace('<div class="alert alert-danger" role="alert">', '<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> ', $error_message);
    }
}

// Lấy dữ liệu môn tổ hợp để hiển thị tên môn học
$ds_mth = getMonToHop($admission_group);
$mth = !empty($ds_mth) ? $ds_mth[0] : null;

// Lấy giá trị môn học ban đầu nếu không có lỗi POST
if ($mon1 === null && $mth) {
    // Đặt giá trị mặc định cho input là rỗng (hoặc 0) nếu chưa submit
    $mon1_val = ''; 
    $mon2_val = '';
    $mon3_val = '';
} else {
    // Giữ lại giá trị từ POST khi có lỗi
    $mon1_val = $mon1;
    $mon2_val = $mon2;
    $mon3_val = $mon3;
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
    <title>Trang Nộp Hồ Sơ</title>
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
        
        /* --- Form Container --- */
        .form-container {
            max-width: 600px;
            margin: 20px auto 50px auto;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border-top: 5px solid var(--primary-color);
        }
        
        .form-container h2 {
            color: var(--primary-color);
            font-weight: 700;
            padding-bottom: 10px;
            margin-bottom: 25px;
            border-bottom: 3px solid var(--secondary-color);
            text-align: center;
        }

        .form-group strong {
            color: var(--dark-text);
        }
        
        .form-control[readonly] {
            background-color: #f1f1f1;
            font-weight: 500;
        }
        
        .score-row {
            display: flex;
            gap: 15px;
            justify-content: space-between;
        }
        .score-row .form-group {
            flex-grow: 1;
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
        
        .file-input-group {
            border: 1px dashed #ced4da;
            padding: 15px;
            border-radius: 5px;
            background-color: #f9f9f9;
            margin-top: 10px;
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
                        <i class="fas fa-user-circle"></i> Xin chào, <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item text-danger" href="dang_xuat.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <div class="hero">
        <h1>NỘP HỒ SƠ XÉT TUYỂN</h1>
        <p style="font-size: 1.1rem; opacity: 0.9;">
            Chuyên Ngành: <strong><?php echo $faculty; ?></strong> - Khối: <strong><?php echo $admission_group; ?></strong>
        </p>
    </div>

    <div class="container">
        <a href="trang_chu.php" class="btn btn-secondary" style="margin-bottom: 20px;"><i class="fas fa-arrow-left"></i> Trở lại</a>
    </div>
    
    <div class="form-container">
        <h2><i class="fas fa-edit"></i> Điền Thông Tin Xét Tuyển</h2>
        
        <?php echo $error_message; ?>
        
        <form id="submissionForm" method="post" enctype="multipart/form-data">
            
            <div class="form-group">
                <strong><label for="studentName">Tên học sinh</label></strong>
                <input type="text" name="studentName" class="form-control" id="studentName" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" readonly>
            </div>
            
            <div class="form-group">
                <strong><label for="faculty">Chuyên ngành</label></strong>
                <input type="text" name="faculty" class="form-control" id="faculty" value="<?php echo $faculty; ?>" readonly>
            </div>
            
            <div class="form-group">
                <strong><label for="admission_group">Tổ hợp xét tuyển</label></strong>
                <input type="text" name="admission_group" class="form-control" id="admission_group" value="<?php echo $admission_group; ?>" readonly>
            </div>

            <hr>
            
            <?php
            // Hiển thị Input Điểm Môn Học
            if ($mth) {
                // Làm sạch tên môn học
                $mon1_name = htmlspecialchars($mth['mon1']);
                $mon2_name = htmlspecialchars($mth['mon2']);
                $mon3_name = htmlspecialchars($mth['mon3']);
                
                echo '<strong><label>Nhập Điểm Học Bạ (Thang 10)</label></strong>';
                echo '<div class="score-row mb-3">';
                
                // Trường 1
                echo '<div class="form-group">';
                echo '<strong><label for="mon1">' . $mon1_name . '</label></strong>';
                echo '<input type="number" step="0.01" min="0" max="10" name="mon1" id="mon1" class="form-control" placeholder="Điểm môn ' . $mon1_name . '" required value="' . $mon1_val . '">';
                echo '</div>';
                
                // Trường 2
                echo '<div class="form-group">';
                echo '<strong><label for="mon2">' . $mon2_name . '</label></strong>';
                echo '<input type="number" step="0.01" min="0" max="10" name="mon2" id="mon2" class="form-control" placeholder="Điểm môn ' . $mon2_name . '" required value="' . $mon2_val . '">';
                echo '</div>';
                
                // Trường 3
                echo '<div class="form-group">';
                echo '<strong><label for="mon3">' . $mon3_name . '</label></strong>';
                echo '<input type="number" step="0.01" min="0" max="10" name="mon3" id="mon3" class="form-control" placeholder="Điểm môn ' . $mon3_name . '" required value="' . $mon3_val . '">';
                echo '</div>';
                
                echo '</div>';
                
            } else {
                echo '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Không có dữ liệu môn tổ hợp cho khối xét tuyển này.</div>';
            }
            ?>
            
            <div class="form-group file-input-group">
                <strong><label for="upload"><i class="fas fa-cloud-upload-alt"></i> Ảnh Học Bạ/Chứng từ</label></strong>
                <input type="file" name="upload[]" multiple class="form-control-file" id="upload" accept=".jpg, .png, .jpeg" required>
                <small class="form-text text-muted">Chỉ chấp nhận tệp JPG, JPEG, PNG. Tải lên nhiều tệp cùng lúc. Tổng dung lượng tối đa 100MB.</small>
            </div>

            <button type="submit" name="btn_nop_ho_so" class="btn btn-primary btn-block mt-4"><i class="fas fa-paper-plane"></i> Nộp Hồ Sơ Xét Tuyển</button>
        </form>
    </div>

    <footer>
        <p>&copy; 2025 Tuyển sinh FBU | Phát triển bởi Nguyễn Thái Dương - 2154800745</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>