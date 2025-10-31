-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 04, 2024 at 05:25 PM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `k72_nhom4`
--

-- --------------------------------------------------------

--
-- Table structure for table `chuyen_nganh_tuyen_sinh`
--

CREATE TABLE `chuyen_nganh_tuyen_sinh` (
  `num` int(11) NOT NULL,
  `faculty` varchar(255) NOT NULL,
  `admission_group` varchar(30) NOT NULL,
  `begin_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1 COMMENT '0: Ẩn\r\n1: Hiện'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `chuyen_nganh_tuyen_sinh`
--

INSERT INTO `chuyen_nganh_tuyen_sinh` (`num`, `faculty`, `admission_group`, `begin_date`, `end_date`, `status`) VALUES
(1, 'Công Nghệ Thông Tin', 'A01', '2024-12-01', '2024-12-31', 1),
(2, 'Văn Học', 'C00', '2024-12-01', '2024-12-31', 1),
(3, 'Toán Học', 'A00', '2024-12-01', '2024-12-31', 0),
(4, 'Sinh Học', 'B00', '2024-12-01', '2024-12-31', 0),
(5, 'Việt Nam Học', 'D01', '2024-12-01', '2024-12-31', 0),
(6, 'Công Nghệ Thông Tin', 'A00', '2024-12-01', '2024-12-31', 1);

-- --------------------------------------------------------

--
-- Table structure for table `danh_sach_tai_khoan`
--

CREATE TABLE `danh_sach_tai_khoan` (
  `username` varchar(255) NOT NULL,
  `account` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL,
  `mission` varchar(255) DEFAULT NULL COMMENT 'Only for teacher'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `danh_sach_tai_khoan`
--

INSERT INTO `danh_sach_tai_khoan` (`username`, `account`, `password`, `role`, `mission`) VALUES
('Admin', 'admin', '$2y$10$4gUUSkDn2taKKaR5otqhReR9jEZSIRwQEWL2Sh0KX7mVRILbiTCYe', 'admin', NULL),
('Nguyễn Thái Dương', 'duongtn', '$2y$10$GxQpHmmzqxFROV11oLy4WOcAbu.OeQtMc0GHky1EtcV99HCap2eoa', 'student', NULL),
('Trần Trung Hiếu', 'hieutt', '$2y$10$aFBj/M/sds8L8iQxgecMeep/3.n2KyawBEaNRMT.qbbgyUhchUwKu', 'teacher', 'Văn Học'),
('Nguyễn Thế Mạnh Huỳnh', 'huynhntm', '$2y$10$S3uSztcs75pXv9q.yGL3gOjR5mnwHeN.HdYEe6oeRjR7n9OoouyC6', 'teacher', 'Công Nghệ Thông Tin'),
('Ngô Thị Phương Thảo', 'thaontp', '$2y$10$TOahoaCx3GqUGU16SLTsmeJ0oiZSqX6fjSehEid0kKipsCSbfEacq', 'student', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ho_so`
--

CREATE TABLE `ho_so` (
  `id` int(11) NOT NULL,
  `ten_hs` varchar(255) NOT NULL,
  `ten_cn` varchar(255) NOT NULL,
  `ten_kxt` varchar(10) NOT NULL,
  `mon1` float NOT NULL,
  `mon2` float NOT NULL,
  `mon3` float NOT NULL,
  `file_anh` varchar(255) NOT NULL,
  `ngay_nop` date NOT NULL DEFAULT current_timestamp(),
  `ten_nguoi_duyet` varchar(255) DEFAULT NULL,
  `ngay_duyet` date DEFAULT NULL,
  `trang_thai` int(11) NOT NULL DEFAULT 0 COMMENT '-1: Không duyệt\r\n0: Chưa duyệt\r\n1: Đã duyệt'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `ho_so`
--

INSERT INTO `ho_so` (`id`, `ten_hs`, `ten_cn`, `ten_kxt`, `mon1`, `mon2`, `mon3`, `file_anh`, `ngay_nop`, `ten_nguoi_duyet`, `ngay_duyet`, `trang_thai`) VALUES
(1, 'Nguyễn Thái Dương', 'Công Nghệ Thông Tin', 'A01', 10, 9, 10, 'Nguyễn Thái Dương_Công Nghệ Thông Tin_A01', '2024-12-04', 'Nguyễn Thái Sơn', '2024-12-04', 1),
(2, 'Ngô Thị Phương Thảo', 'Quản Trị Kinh Doanh', 'A00', 10, 10, 10, 'Ngô Thị Phương Thảo', '2024-12-04', NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `to_hop`
--

CREATE TABLE `to_hop` (
  `tentohop` varchar(30) NOT NULL,
  `mon1` varchar(30) NOT NULL,
  `mon2` varchar(30) NOT NULL,
  `mon3` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `to_hop`
--

INSERT INTO `to_hop` (`tentohop`, `mon1`, `mon2`, `mon3`) VALUES
('A00', 'Toán', 'Vật Lý', 'Hóa Học'),
('A01', 'Toán', 'Vật Lý', 'Tiếng Anh'),
('A02', 'Toán', 'Vật Lý', 'Sinh Học'),
('A03', 'Toán', 'Vật Lý', 'Lịch Sử'),
('A04', 'Toán', 'Vật Lý', 'Địa Lý'),
('A05', 'Toán', 'Hóa Học', 'Lịch Sử'),
('B00', 'Toán', 'Hóa Học', 'Sinh Học'),
('B01', 'Toán', 'Sinh Học', 'Lịch Sử'),
('B02', 'Toán', 'Sinh Học', 'Địa Lý'),
('B03', 'Toán', 'Sinh Học', 'Văn'),
('C00', 'Văn', 'Lịch Sử', 'Địa Lý'),
('C01', 'Văn', 'Toán', 'Vật Lý'),
('C02', 'Văn', 'Toán', 'Hóa Học'),
('C03', 'Văn', 'Toán', 'Lịch Sử'),
('C04', 'Văn', 'Toán', 'Địa Lý'),
('C05', 'Văn', 'Vật Lý', 'Hóa Học'),
('D01', 'Văn', 'Toán', 'Tiếng Anh'),
('D07', 'Toán', 'Hóa Học', 'Tiếng Anh'),
('D08', 'Toán', 'Sinh Học', 'Tiếng Anh'),
('D09', 'Toán', 'Lịch Sử', 'Tiếng Anh'),
('D10', 'Toán', 'Địa Lý', 'Tiếng Anh');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chuyen_nganh_tuyen_sinh`
--
ALTER TABLE `chuyen_nganh_tuyen_sinh`
  ADD PRIMARY KEY (`num`),
  ADD KEY `admission_group` (`admission_group`);

--
-- Indexes for table `danh_sach_tai_khoan`
--
ALTER TABLE `danh_sach_tai_khoan`
  ADD PRIMARY KEY (`account`);

--
-- Indexes for table `ho_so`
--
ALTER TABLE `ho_so`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `to_hop`
--
ALTER TABLE `to_hop`
  ADD PRIMARY KEY (`tentohop`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chuyen_nganh_tuyen_sinh`
--
ALTER TABLE `chuyen_nganh_tuyen_sinh`
  MODIFY `num` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `ho_so`
--
ALTER TABLE `ho_so`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chuyen_nganh_tuyen_sinh`
--
ALTER TABLE `chuyen_nganh_tuyen_sinh`
  ADD CONSTRAINT `chuyen_nganh_tuyen_sinh_ibfk_1` FOREIGN KEY (`admission_group`) REFERENCES `to_hop` (`tentohop`) ON DELETE CASCADE ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
