<?php
// function.php
// 2154800745_Nguyễn Thái Dương

// CHÚ Ý: Loại bỏ include "connectdb.php" và session_start()
// vì connectdb.php đã làm việc đó và nó được include vào đây.

// Thiết lập múi giờ mặc định
date_default_timezone_set('Asia/Ho_Chi_Minh'); 

// =========================================================================
// HÀM TIỆN ÍCH
// =========================================================================

/**
 * Hàm trả về một chuỗi HTML thông báo
 * @param string $message Nội dung thông báo
 * @param string $type Kiểu thông báo (success, danger, warning)
 * @return string HTML div alert
 */
function create_alert($message, $type = 'success') {
    return '<div class="alert alert-' . htmlspecialchars($type) . '" role="alert">' . htmlspecialchars($message) . '</div>';
}

// =========================================================================
// TRANG ĐĂNG KÝ, ĐĂNG NHẬP
// =========================================================================

/**
 * Lấy ra danh sách tất cả tài khoản
 * @return array|int Mảng dữ liệu hoặc 0 nếu không có kết quả
 */
function getDSTK()
{
    global $conn;
    $ds = [];
    // Sử dụng OOP
    $result = $conn->query("SELECT * FROM `danh_sach_tai_khoan`");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $ds[] = $row;
        }
        $result->free();
        return $ds;
    }
    return 0;
}

/**
 * Kiểm tra xem tên tài khoản đã tồn tại hay chưa?
 * @param string $tk Tên tài khoản cần kiểm tra
 * @return bool True nếu tài khoản không tồn tại, False nếu đã tồn tại
 */
function isAccountNotExist($tk)
{
    global $conn;
    // Sử dụng Prepared Statement
    $stmt = $conn->prepare("SELECT `account` FROM `danh_sach_tai_khoan` WHERE `account` = ?");
    $stmt->bind_param("s", $tk);
    $stmt->execute();
    $stmt->store_result();
    $is_not_exist = $stmt->num_rows === 0;
    $stmt->close();
    return $is_not_exist;
}

// =========================================================================
// HÀM ĐĂNG KÝ (function.php)
// (Chỉ bao gồm hàm dang_ky đã sửa)
// =========================================================================

/**
 * Đăng ký tài khoản mới (đã sửa: dùng Prepared Statements và password_hash)
 * @param string $ten Tên người dùng
 * @param string $tk Tên tài khoản
 * @param string $mk Mật khẩu chưa hash
 * @param string $role Vai trò
 * @return string HTML thông báo (bao gồm script chuyển hướng nếu thành công)
 */
function dang_ky($ten, $tk, $mk, $role)
{
    global $conn;
    
    // 1. Kiểm tra tồn tại
    if (!isAccountNotExist($tk)) {
        return create_alert("Tài khoản đã tồn tại!", 'danger');
    }
    
    // 2. Hash mật khẩu (AN TOÀN)
    $hashed_password = password_hash($mk, PASSWORD_DEFAULT);

    // 3. Insert vào CSDL bằng Prepared Statement (AN TOÀN)
    $query = "INSERT INTO `danh_sach_tai_khoan`(`username`, `account`, `password`, `role`) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    
    if ($stmt === false) {
        return create_alert("Lỗi hệ thống khi chuẩn bị truy vấn.", 'danger');
    }
    
    $stmt->bind_param("ssss", $ten, $tk, $hashed_password, $role);
    
    if ($stmt->execute()) {
        $stmt->close();
        
        // 🌟 TẠO THÔNG BÁO VÀ SCRIPT CHUYỂN HƯỚNG
        
        // 1. Tạo thông báo thành công
        $success_message_html = '<div class="alert alert-success" role="alert"><i class="fas fa-check-circle"></i> Đăng ký thành công! Bạn sẽ được chuyển đến trang đăng nhập sau 3 giây.</div>';

        // 2. Tạo script chuyển hướng
        $redirect_script = '
            <script>
                setTimeout(function() {
                    window.location.replace("dang_nhap.php");
                }, 3000); 
            </script>';
        
        // Trả về chuỗi HTML + Script
        return $success_message_html . $redirect_script; 
        
    } else {
        $stmt->close();
        return create_alert("Đăng ký không thành công! Lỗi CSDL.", 'danger');
    }
}

// =========================================================================
// HÀM ĐĂNG NHẬP (function.php)
// (Chỉ bao gồm hàm dang_ky đã sửa)
// =========================================================================
/**
 * Đăng nhập tài khoản (đã sửa: dùng Prepared Statements và password_verify)
 * @param string $tk Tên tài khoản
 * @param string $mk Mật khẩu
 * @return string|void HTML thông báo hoặc chuyển hướng
 */
function dang_nhap($tk, $mk)
{
    global $conn;
    
    // 1. Tìm tài khoản bằng Prepared Statement (AN TOÀN)
    $query = "SELECT `username`, `password`, `role`, `mission` FROM `danh_sach_tai_khoan` WHERE `account` = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $tk);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $stmt->close();
        
        // 2. Kiểm tra mật khẩu đã hash (AN TOÀN)
        if (password_verify($mk, $row['password'])) {
            $_SESSION['username'] = $row['username'];
            $_SESSION['account'] = $tk; // Lưu lại account
            $_SESSION['role'] = $row['role'];
            $_SESSION['mission'] = $row['mission'];
            header("Location: trang_chu.php");
            exit; // Đảm bảo dừng script sau khi chuyển hướng
        } else {
            return create_alert("Sai mật khẩu!", 'danger');
        }
    } else {
        $stmt->close();
        return create_alert("Tài khoản không tồn tại!", 'danger');
    }
}

// =========================================================================
// TRANG CHỦ & QUẢN LÝ CHUYÊN NGÀNH
// =========================================================================

/**
 * Lấy ra danh sách chuyên ngành (Admin)
 * @return array|int Mảng dữ liệu hoặc 0
 */
function adminChuyenNganh()
{
    global $conn;
    $ds = [];
    $result = $conn->query("SELECT * FROM `chuyen_nganh_tuyen_sinh`");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $ds[] = $row;
        }
        $result->free();
        return $ds;
    }
    return 0;
}

/**
 * Ẩn chuyên ngành (đã sửa: dùng Prepared Statements)
 * @param int $num ID chuyên ngành
 */
function hideChuyenNganh($num)
{
    global $conn;
    $num = (int)$num; // Ép kiểu an toàn

    // Dùng Prepared Statement
    $query = "UPDATE `chuyen_nganh_tuyen_sinh` SET `status` = 0 WHERE `num` = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $num); // "i" là integer
    $stmt->execute();
    $stmt->close();
}

/**
 * Hiện chuyên ngành (đã sửa: dùng Prepared Statements)
 * @param int $num ID chuyên ngành
 */
function showChuyenNganh($num)
{
    global $conn;
    $num = (int)$num; // Ép kiểu an toàn

    // Dùng Prepared Statement
    $query = "UPDATE `chuyen_nganh_tuyen_sinh` SET `status` = 1 WHERE `num` = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $num);
    $stmt->execute();
    $stmt->close();
}

/**
 * Xóa chuyên ngành (đã sửa: dùng Prepared Statements)
 * @param int $num ID chuyên ngành
 * @return string|void HTML thông báo hoặc void nếu thành công
 */
function deleteChuyenNganh($num)
{
    global $conn;
    $num = (int)$num;

    // 1. Lấy thông tin chuyên ngành bằng Prepared Statement
    $query_select = "SELECT `faculty`, `admission_group` FROM `chuyen_nganh_tuyen_sinh` WHERE `num` = ?";
    $stmt_select = $conn->prepare($query_select);
    $stmt_select->bind_param("i", $num);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    
    if ($result->num_rows === 0) {
        $stmt_select->close();
        return create_alert("Không tìm thấy Chuyên Ngành!", 'warning');
    }
    
    $row = $result->fetch_assoc();
    $stmt_select->close();
    
    $faculty = $row['faculty'];
    $admission_group = $row['admission_group'];
    
    // 2. Xóa các hồ sơ liên quan
    if (!deleteHoSoByCNTS($faculty, $admission_group)) { // Đổi tên hàm để tránh nhầm lẫn
        return create_alert("Xóa Hồ Sơ Thất Bại!", 'danger');
    }
    
    // 3. Xóa chuyên ngành bằng Prepared Statement
    $query_delete = "DELETE FROM `chuyen_nganh_tuyen_sinh` WHERE `num` = ?";
    $stmt_delete = $conn->prepare($query_delete);
    $stmt_delete->bind_param("i", $num);

    if ($stmt_delete->execute()) {
        $stmt_delete->close();
        // Không trả về thông báo nếu thành công, chỉ trả về nếu thất bại
    } else {
        $stmt_delete->close();
        return create_alert("Xóa Chuyên Ngành Thất Bại!", 'danger');
    }
}

/**
 * Lấy hồ sơ bằng ID chuyên ngành (Đã sửa: dùng Prepared Statements)
 * CHÚ Ý: Hàm này lấy hồ sơ theo ID CNTS, không dùng ID hồ sơ
 * @param int $num ID chuyên ngành
 * @return array|int Mảng hồ sơ hoặc 0
 */
function getHoSoByCNTS($num)
{
    global $conn;
    $num = (int)$num;
    $ds = [];

    // 1. Lấy tên chuyên ngành và khối xét tuyển
    $query_select = "SELECT `faculty`, `admission_group` FROM `chuyen_nganh_tuyen_sinh` WHERE `num` = ?";
    $stmt_select = $conn->prepare($query_select);
    $stmt_select->bind_param("i", $num);
    $stmt_select->execute();
    $result = $stmt_select->get_result();

    if ($result->num_rows === 0) {
        $stmt_select->close();
        return 0;
    }
    
    $cn_info = $result->fetch_assoc();
    $stmt_select->close();
    
    $faculty = $cn_info['faculty'];
    $admission_group = $cn_info['admission_group'];

    // 2. Lấy danh sách hồ sơ bằng Prepared Statement
    $query = "SELECT * FROM `ho_so` WHERE `ten_cn` = ? AND `ten_kxt` = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $faculty, $admission_group);
    $stmt->execute();
    $result_hs = $stmt->get_result();

    if ($result_hs->num_rows > 0) {
        while ($row = $result_hs->fetch_assoc()) {
            $ds[] = $row;
        }
        $stmt->close();
        return $ds;
    }
    $stmt->close();
    return 0;
}

/**
 * Xóa các hồ sơ của chuyên ngành bị xóa (Đã sửa: dùng Prepared Statements)
 * Đổi tên hàm thành deleteHoSoByCNTS để tránh nhầm lẫn với xoaHoSo($id)
 * @param string $ten_cn Tên chuyên ngành
 * @param string $ten_kxt Tên khối xét tuyển
 * @return bool True nếu thành công, False nếu thất bại
 */
function deleteHoSoByCNTS($ten_cn, $ten_kxt)
{
    global $conn;
    $query = "DELETE FROM `ho_so` WHERE `ten_cn` = ? AND `ten_kxt` = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $ten_cn, $ten_kxt);

    if ($stmt->execute()) {
        $stmt->close();
        return true;
    }
    $stmt->close();
    return false;
}

/**
 * Lấy danh sách chuyên ngành (Giáo viên) (Đã sửa: dùng Prepared Statements)
 * @param string $mission Nhiệm vụ (Tên chuyên ngành) của giáo viên
 * @return array|int Mảng dữ liệu hoặc 0
 */
function teacherChuyenNganh($mission)
{
    global $conn;
    $ds = [];
    $query = "SELECT * FROM `chuyen_nganh_tuyen_sinh` WHERE `status` = 1 AND `faculty` = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $mission);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $ds[] = $row;
        }
        $stmt->close();
        return $ds;
    }
    $stmt->close();
    return 0;
}

/**
 * Lấy danh sách chuyên ngành (Học sinh)
 * @return array|int Mảng dữ liệu hoặc 0
 */
function studentChuyenNganh()
{
    global $conn;
    $ds = [];
    // Không cần Prepared Statement vì không có biến người dùng
    $result = $conn->query("SELECT * FROM `chuyen_nganh_tuyen_sinh` WHERE `status` = 1");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $ds[] = $row;
        }
        $result->free();
        return $ds;
    }
    return 0;
}

/**
 * Kiểm tra xem học sinh đã nộp hồ sơ chuyên ngành hay chưa? (Đã sửa: dùng Prepared Statements)
 * @param string $ten_hs Tên học sinh
 * @param string $ten_cn Tên chuyên ngành
 * @return array|int Mảng dữ liệu hồ sơ hoặc 0
 */
function isHoSoExist($ten_hs, $ten_cn)
{
    global $conn;
    $ds = [];
    $query = "SELECT * FROM `ho_so` WHERE `ten_hs` = ? AND `ten_cn` = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $ten_hs, $ten_cn);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $ds[] = $row;
        }
        $stmt->close();
        return $ds;
    }
    $stmt->close();
    return 0;
}

/**
 * Kiểm tra học sinh nộp hồ sơ chuyên ngành theo khối xét tuyển nào? (Đã sửa: dùng Prepared Statements)
 * @param string $username Tên học sinh
 * @param string $faculty Tên chuyên ngành
 * @return string|int Khối xét tuyển hoặc 0
 */
function checkAdmissionGroup($username, $faculty)
{
    global $conn;
    $query = "SELECT ten_kxt FROM ho_so WHERE ten_hs = ? AND ten_cn = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $username, $faculty);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['ten_kxt'];
    }
    $stmt->close();
    return 0;
}

// =========================================================================
// TRANG THÊM CHUYÊN NGÀNH
// =========================================================================

/**
 * Lấy ra danh tên tổ hợp xét tuyển
 * @return array|int Mảng dữ liệu hoặc 0
 */
function getToHop()
{
    global $conn;
    $ds = [];
    // Không cần Prepared Statement
    $result = $conn->query("SELECT tentohop FROM `to_hop`");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $ds[] = $row;
        }
        $result->free();
        return $ds;
    }
    return 0;
}

/**
 * Thêm chuyên ngành xét tuyển mới (Đã sửa: dùng Prepared Statements)
 * @param string $faculty Tên chuyên ngành
 * @param string $admission_group Khối xét tuyển
 * @param string $begin_date Ngày bắt đầu
 * @param string $end_date Ngày kết thúc
 * @return string HTML thông báo
 */
function them_chuyen_nganh($faculty, $admission_group, $begin_date, $end_date)
{
    global $conn;
    
    // Kiểm tra đã được thực hiện ở trang gọi hàm, nhưng nên kiểm tra lại để đảm bảo
    if (strtotime($begin_date) > strtotime($end_date)) {
        return create_alert("Vui lòng chọn ngày tháng hợp lệ.", 'danger');
    }
    
    // Sử dụng Prepared Statement
    $query = "INSERT INTO `chuyen_nganh_tuyen_sinh`(`faculty`, `admission_group`, `begin_date`, `end_date`) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $faculty, $admission_group, $begin_date, $end_date);
    
    if ($stmt->execute()) {
        $stmt->close();
        return create_alert("Thêm Chuyên Ngành Thành Công!", 'success');
    } else {
        $stmt->close();
        // Cung cấp chi tiết lỗi để debug (ví dụ: duplicate entry)
        // return create_alert("Thêm Chuyên Ngành Thất Bại! Lỗi: " . $conn->error, 'danger'); 
        return create_alert("Thêm Chuyên Ngành Thất Bại!", 'danger'); 
    }
}

// =========================================================================
// TRANG NỘP HỒ SƠ
// =========================================================================

/**
 * Lấy ra tên của 3 môn thi trong tổ hợp xét tuyển (Đã sửa: dùng Prepared Statements)
 * @param string $admission_group Tên tổ hợp
 * @return array|int Mảng dữ liệu hoặc 0
 */
function getMonToHop($admission_group)
{
    global $conn;
    $ds = [];
    $query = "SELECT `mon1`, `mon2`, `mon3` FROM `to_hop` WHERE `tentohop` = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $admission_group);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $ds[] = $row;
        }
        $stmt->close();
        return $ds;
    }
    $stmt->close();
    return 0;
}

/**
 * Lưu hồ sơ mới vào cơ sở dữ liệu (Đã sửa: dùng Prepared Statements)
 * @param string $ten_hs Tên học sinh
 * @param string $ten_cn Tên chuyên ngành
 * @param string $ten_kxt Khối xét tuyển
 * @param float $mon1 Điểm môn 1
 * @param float $mon2 Điểm môn 2
 * @param float $mon3 Điểm môn 3
 * @param string $file_anh Tên thư mục chứa ảnh
 * @return string HTML thông báo
 */
function nopHoSo($ten_hs, $ten_cn, $ten_kxt, $mon1, $mon2, $mon3, $file_anh)
{
    global $conn;
    $query = "INSERT INTO `ho_so`(`ten_hs`, `ten_cn`, `ten_kxt`, `mon1`, `mon2`, `mon3`, `file_anh`) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    
    // "sssddds" tương ứng với 7 tham số: 3 string (s), 3 double/float (d), 1 string (s)
    $stmt->bind_param("sssddds", $ten_hs, $ten_cn, $ten_kxt, $mon1, $mon2, $mon3, $file_anh);
    
    if ($stmt->execute()) {
        $stmt->close();
        return create_alert("Nộp Hồ Sơ Thành Công! Vui Lòng Trở Về Trang Chủ", 'success');
    } else {
        $stmt->close();
        return create_alert("Nộp Hồ Sơ Thất Bại! Vui Lòng Trở Về Trang Chủ", 'danger');
    }
}

// =========================================================================
// TRANG PHÂN QUYỀN
// =========================================================================

/**
 * Lấy ra danh sách tài khoản giáo viên
 * @return array|int Mảng dữ liệu hoặc 0
 */
function getDSTKGV()
{
    global $conn;
    $ds = [];
    // Không cần Prepared Statement
    $result = $conn->query("SELECT * FROM `danh_sach_tai_khoan` WHERE `role` = 'teacher'");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $ds[] = $row;
        }
        $result->free();
        return $ds;
    }
    return 0;
}

/**
 * Lấy ra tên chuyên ngành xét tuyển để phân nhiệm vụ cho giáo viên
 * @return array|int Mảng dữ liệu hoặc 0
 */
function getTenChuyenNganh()
{
    global $conn;
    $ds = [];
    // Không cần Prepared Statement
    $query = "SELECT DISTINCT `faculty` FROM `chuyen_nganh_tuyen_sinh`";
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $ds[] = $row;
        }
        $result->free();
        return $ds;
    }
    return 0;
}

/**
 * Kiểm tra nhiệm vụ được phân công của giáo viên (Đã sửa: dùng Prepared Statements)
 * @param string $username Tên người dùng
 * @param string $account Tên tài khoản
 * @return array|null Mảng dữ liệu hoặc null
 */
function checkMissionOfTeacher($username, $account)
{
    global $conn;
    $query = "SELECT `mission` FROM `danh_sach_tai_khoan` WHERE `username` = ? AND `account` = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $username, $account);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row;
    }
    $stmt->close();
    return null;
}

/**
 * Lưu nhiệm vụ mới của giáo viên (Đã sửa: dùng Prepared Statements)
 * @param string $username Tên người dùng
 * @param string $account Tên tài khoản
 * @param string $mission Nhiệm vụ
 * @return string|void HTML thông báo hoặc void nếu thành công
 */
function saveMissionOfTeacher($username, $account, $mission)
{
    global $conn;
    $query = "UPDATE `danh_sach_tai_khoan` SET `mission` = ? WHERE `username` = ? AND `account` = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $mission, $username, $account);

    if (!$stmt->execute()) {
        $stmt->close();
        return create_alert("Lưu Nhiệm Vụ Thất Bại!", 'danger');
    }
    $stmt->close();
}

// =========================================================================
// TRANG THỐNG KÊ HỒ SƠ & XEM CHI TIẾT
// =========================================================================

/**
 * Lấy ra danh sách hồ sơ theo tên chuyên ngành và khối xét tuyển (Đã sửa: dùng Prepared Statements)
 * @param string $faculty Tên chuyên ngành
 * @param string $admission_group Khối xét tuyển
 * @return array|int Mảng dữ liệu hoặc 0
 */
function getHoSo($faculty, $admission_group)
{
    global $conn;
    $ds = [];
    $query = "SELECT * FROM `ho_so` WHERE `ten_cn` = ? AND `ten_kxt` = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $faculty, $admission_group);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $ds[] = $row;
        }
        $stmt->close();
        return $ds;
    }
    $stmt->close();
    return 0;
}

/**
 * Duyệt hồ sơ (Đã sửa: dùng Prepared Statements)
 * @param int $id ID hồ sơ
 * @param string $ten_nguoi_duyet Tên người duyệt
 * @return string|void HTML thông báo hoặc void nếu thành công
 */
function duyetHoSo($id, $ten_nguoi_duyet)
{
    global $conn;
    $id = (int)$id;
    $date = date('Y-m-d');
    
    $query = "UPDATE `ho_so` SET `trang_thai` = 1, `ten_nguoi_duyet` = ?, `ngay_duyet` = ? WHERE `id` = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $ten_nguoi_duyet, $date, $id); // 2 string, 1 integer

    if (!$stmt->execute()) {
        $stmt->close();
        return create_alert("Duyệt Hồ Sơ Thất Bại!", 'danger');
    }
    $stmt->close();
}

/**
 * Không duyệt hồ sơ (Đã sửa: dùng Prepared Statements)
 * @param int $id ID hồ sơ
 * @param string $ten_nguoi_duyet Tên người duyệt
 * @return string|void HTML thông báo hoặc void nếu thành công
 */
function khongDuyetHoSo($id, $ten_nguoi_duyet)
{
    global $conn;
    $id = (int)$id;
    $date = date('Y-m-d');
    
    $query = "UPDATE `ho_so` SET `trang_thai` = -1, `ten_nguoi_duyet` = ?, `ngay_duyet` = ? WHERE `id` = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $ten_nguoi_duyet, $date, $id); // 2 string, 1 integer

    if (!$stmt->execute()) {
        $stmt->close();
        return create_alert("Không Duyệt Hồ Sơ Thất Bại!", 'danger');
    }
    $stmt->close();
}

/**
 * Xóa hồ sơ (Đã sửa: dùng Prepared Statements)
 * @param int $id ID hồ sơ
 * @return string|void HTML thông báo hoặc void nếu thành công
 */
function xoaHoSo($id)
{
    global $conn;
    $id = (int)$id;
    $query = "DELETE FROM `ho_so` WHERE `id` = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);

    if (!$stmt->execute()) {
        $stmt->close();
        return create_alert("Xóa Hồ Sơ Thất Bại!", 'danger');
    }
    $stmt->close();
}

/**
 * Xóa thư mục chứa ảnh bằng unlink và rmdir (không thay đổi logic)
 * @param string $dir Đường dẫn thư mục
 * @return bool
 */
function xoaThuMuc($dir) {
    if (!is_dir($dir)) {
      return false;
    }
  
    $files = array_diff(scandir($dir), array('.', '..'));
  
    foreach ($files as $file) {
      (is_dir("$dir/$file")) ? xoaThuMuc("$dir/$file") : unlink("$dir/$file");
    }
  
    return rmdir($dir);
}

/**
 * Lấy ra hồ sơ của học sinh theo id (Đã sửa: dùng Prepared Statements)
 * @param int $id ID hồ sơ
 * @return array|int Mảng dữ liệu hồ sơ (1 dòng) hoặc 0
 */
function getHoSoById($id)
{
    global $conn;
    $id = (int)$id;
    $query = "SELECT * FROM `ho_so` WHERE `id` = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row; // Trả về 1 dòng (mảng kết hợp)
    }
    $stmt->close();
    return 0;
}

// =========================================================================
// TRANG THỐNG KÊ TỔNG SỐ HỒ SƠ
// =========================================================================

/**
 * Lấy ra tất cả tên chuyên ngành xét tuyển (Không lấy trùng lặp)
 * @return array|int Mảng dữ liệu hoặc 0
 */
function getAllCNTS()
{
    global $conn;
    $ds = [];
    // Không cần Prepared Statement
    $query = "SELECT DISTINCT `ten_cn` FROM `ho_so`";
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $ds[] = $row;
        }
        $result->free();
        return $ds;
    }
    return 0;
}

/**
 * Đếm số hồ sơ không duyệt (Đã sửa: dùng Prepared Statements)
 * @param string $ten_cn Tên chuyên ngành
 * @return int Số lượng
 */
function countHoSoKhongDuyet($ten_cn)
{
    global $conn;
    $query = "SELECT COUNT(*) FROM `ho_so` WHERE `ten_cn` = ? AND `trang_thai` = -1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $ten_cn);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return (int)$count;
}

/**
 * Đếm số hồ sơ chưa duyệt (Đã sửa: dùng Prepared Statements)
 * @param string $ten_cn Tên chuyên ngành
 * @return int Số lượng
 */
function countHoSoChuaDuyet($ten_cn)
{
    global $conn;
    $query = "SELECT COUNT(*) FROM `ho_so` WHERE `ten_cn` = ? AND `trang_thai` = 0";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $ten_cn);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return (int)$count;
}

/**
 * Đếm số hồ sơ đã duyệt (Đã sửa: dùng Prepared Statements)
 * @param string $ten_cn Tên chuyên ngành
 * @return int Số lượng
 */
function countHoSoDaDuyet($ten_cn)
{
    global $conn;
    $query = "SELECT COUNT(*) FROM `ho_so` WHERE `ten_cn` = ? AND `trang_thai` = 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $ten_cn);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return (int)$count;
}