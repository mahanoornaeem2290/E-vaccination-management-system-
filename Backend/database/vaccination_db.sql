-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 29, 2026 at 07:26 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vaccination_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  `hospital_id` int(11) NOT NULL,
  `vaccine_id` int(11) NOT NULL,
  `appt_date` date NOT NULL,
  `slot` varchar(20) NOT NULL,
  `admin_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `admin_note` varchar(255) NOT NULL DEFAULT '',
  `vax_status` enum('pending','vaccinated','not_vaccinated') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `children`
--

CREATE TABLE `children` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `dob` date NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `blood_group` varchar(5) NOT NULL DEFAULT '',
  `member_type` enum('child','adult') NOT NULL DEFAULT 'child',
  `passport_no` varchar(20) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `children`
--

INSERT INTO `children` (`id`, `parent_id`, `name`, `dob`, `gender`, `blood_group`, `member_type`, `passport_no`, `created_at`) VALUES
(1, 2, 'Zain Ahmed', '2026-07-12', 'Male', 'B+', 'child', '', '2026-10-19 19:36:11'),
(2, 8, 'Ibrahim', '2008-10-16', 'Male', 'A+', 'adult', '3489483', '2026-10-24 06:14:31'),
(3, 9, 'huda', '2024-07-22', 'Female', 'A+', 'child', '', '2026-10-26 17:47:58');

-- --------------------------------------------------------

--
-- Table structure for table `hospitals`
--

CREATE TABLE `hospitals` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(80) NOT NULL,
  `phone` varchar(20) NOT NULL DEFAULT '',
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hospitals`
--

INSERT INTO `hospitals` (`id`, `user_id`, `name`, `address`, `city`, `phone`, `status`) VALUES
(1, 3, 'City Care Hospital', 'Shahrah-e-Faisal', 'Karachi', '021-34000001', 'pending'),
(2, 4, 'Green Crescent Clinic', 'Saddar', 'Karachi', '021-34000002', 'pending'),
(3, 5, 'Demo Partner Hospital - Gulshan', 'Demo location, Gulshan-e-Iqbal', 'Karachi', '021-555-0101', 'pending'),
(4, 6, 'Demo Partner Clinic - Clifton', 'Demo location, Clifton', 'Karachi', '021-555-0102', 'pending'),
(5, 7, 'Demo Vaccination Centre - North Nazimabad', 'Demo location, North Nazimabad', 'Karachi', '021-555-0103', 'pending'),
(10, 20, 'Aga Khan University Hospital', 'Stadium Road, Karachi 74800', 'Karachi', '021-34930051', 'approved'),
(11, 21, 'Liaquat National Hospital', 'National Stadium Road, Karachi', 'Karachi', '021-34412442', 'approved'),
(12, 22, 'South City Hospital', 'Block-3, Clifton, Karachi', 'Karachi', '021-35871471', 'approved'),
(13, 23, 'Indus Hospital', 'Rafi Iqbal Road, Korangi, Karachi', 'Karachi', '021-35112709', 'approved'),
(14, 24, 'Ziauddin Hospital', 'North Nazimabad, Karachi', 'Karachi', '021-36699000', 'approved'),
(15, 25, 'Shaukat Khanum Memorial Cancer Hospital', '7A Block R-3, MA Johar Town, Lahore', 'Lahore', '042-35945100', 'approved'),
(16, 26, 'Services Hospital Lahore', 'Ghaus-ul-Azam Road, Lahore', 'Lahore', '042-99203390', 'approved'),
(17, 27, 'Doctors Hospital Lahore', '152-G/1 Canal Bank, Johar Town, Lahore', 'Lahore', '042-35761999', 'approved'),
(18, 28, 'Ittefaq Hospital', '17 Factory Area, Raiwind Road, Lahore', 'Lahore', '042-35761340', 'approved'),
(19, 29, 'Sir Ganga Ram Hospital', 'Shama Chowk, The Mall, Lahore', 'Lahore', '042-37233337', 'approved'),
(20, 30, 'Shifa International Hospital', 'H-8/4, Islamabad', 'Islamabad', '051-8464646', 'approved'),
(21, 31, 'Pakistan Institute of Medical Sciences (PIMS)', 'G-8/3, Islamabad', 'Islamabad', '051-9261170', 'approved'),
(22, 32, 'Holy Family Hospital Rawalpindi', 'Satellite Town, Rawalpindi', 'Rawalpindi', '051-9290311', 'approved'),
(23, 33, 'Benazir Bhutto Hospital', 'Benazir Bhutto Road, Rawalpindi', 'Rawalpindi', '051-9270341', 'approved'),
(24, 34, 'Lady Reading Hospital', 'Peshawar City, Peshawar', 'Peshawar', '091-9212301', 'approved'),
(25, 35, 'Hayatabad Medical Complex', 'Phase-5, Hayatabad, Peshawar', 'Peshawar', '091-9217001', 'approved'),
(26, 36, 'Khyber Teaching Hospital', 'University Road, Peshawar', 'Peshawar', '091-9216440', 'approved'),
(27, 37, 'Bolan Medical Complex Hospital', 'Brewery Road, Quetta', 'Quetta', '081-2821000', 'approved'),
(28, 38, 'Civil Hospital Quetta', 'M.A. Jinnah Road, Quetta', 'Quetta', '081-2822020', 'approved'),
(29, 39, 'Allied Hospital Faisalabad', 'Sargodha Road, Faisalabad', 'Faisalabad', '041-9200471', 'approved'),
(30, 40, 'Niazi Medical & Dental College Hospital', 'Canal Road, Faisalabad', 'Faisalabad', '041-8812111', 'approved'),
(31, 41, 'Nishtar Hospital Multan', 'Nishtar Medical University Road, Multan', 'Multan', '061-9200303', 'approved'),
(32, 42, 'Children Hospital Multan', 'Nishtar Road, Multan', 'Multan', '061-9200401', 'approved'),
(33, 43, 'Liaquat University Hospital Hyderabad', 'Jamshoro Road, Hyderabad', 'Hyderabad', '022-9200830', 'approved'),
(34, 44, 'GMMMC Hospital Sukkur', 'Sukkur-Shikarpur Road, Sukkur', 'Sukkur', '071-5630071', 'approved'),
(35, 45, 'Ayub Teaching Hospital', 'Mansehra Road, Abbottabad', 'Abbottabad', '0992-381136', 'approved'),
(36, 46, 'DHQ Hospital Gilgit', 'Jutial, Gilgit', 'Gilgit', '05811-920005', 'approved'),
(37, 47, 'Shaukat Khanum Hospital Peshawar', 'Regi Model Town, Peshawar', 'Peshawar', '091-5703000', 'approved'),
(38, 48, 'DHQ Teaching Hospital Gujranwala', 'GT Road, Gujranwala', 'Gujranwala', '055-9200102', 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `hospital_vaccines`
--

CREATE TABLE `hospital_vaccines` (
  `hospital_id` int(11) NOT NULL,
  `vaccine_id` int(11) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `price_pkr` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hospital_vaccines`
--

INSERT INTO `hospital_vaccines` (`hospital_id`, `vaccine_id`, `stock`, `price_pkr`) VALUES
(1, 1, 40, 0.00),
(1, 2, 40, 0.00),
(1, 3, 40, 0.00),
(1, 4, 40, 0.00),
(1, 5, 40, 0.00),
(1, 6, 40, 0.00),
(1, 7, 40, 0.00),
(1, 8, 40, 0.00),
(1, 9, 40, 0.00),
(1, 10, 40, 0.00),
(1, 11, 40, 0.00),
(1, 12, 40, 0.00),
(1, 13, 40, 0.00),
(1, 14, 40, 0.00),
(1, 15, 40, 0.00),
(1, 16, 40, 0.00),
(1, 17, 40, 0.00),
(1, 18, 40, 0.00),
(1, 19, 40, 0.00),
(1, 20, 40, 0.00),
(1, 21, 40, 0.00),
(1, 22, 40, 0.00),
(1, 23, 40, 0.00),
(2, 1, 40, 0.00),
(2, 2, 40, 0.00),
(2, 3, 40, 0.00),
(2, 4, 40, 0.00),
(2, 5, 40, 0.00),
(2, 6, 40, 0.00),
(2, 7, 40, 0.00),
(2, 8, 40, 0.00),
(2, 9, 40, 0.00),
(2, 10, 40, 0.00),
(2, 11, 40, 0.00),
(2, 12, 40, 0.00),
(2, 13, 40, 0.00),
(2, 14, 40, 0.00),
(2, 15, 40, 0.00),
(2, 16, 40, 0.00),
(2, 17, 40, 0.00),
(2, 18, 40, 0.00),
(2, 19, 40, 0.00),
(2, 20, 40, 0.00),
(2, 21, 40, 0.00),
(2, 22, 40, 0.00),
(2, 23, 40, 0.00),
(3, 1, 40, 0.00),
(3, 2, 40, 0.00),
(3, 3, 40, 0.00),
(3, 4, 40, 0.00),
(3, 5, 40, 0.00),
(3, 6, 40, 0.00),
(3, 7, 40, 0.00),
(3, 8, 40, 0.00),
(3, 9, 40, 0.00),
(3, 10, 40, 0.00),
(3, 11, 40, 0.00),
(3, 12, 40, 0.00),
(3, 13, 40, 0.00),
(3, 14, 40, 0.00),
(3, 15, 40, 0.00),
(3, 16, 40, 0.00),
(3, 17, 40, 0.00),
(3, 18, 40, 0.00),
(3, 19, 40, 0.00),
(3, 20, 40, 0.00),
(3, 21, 40, 0.00),
(3, 22, 40, 0.00),
(3, 23, 40, 0.00),
(4, 1, 40, 0.00),
(4, 2, 40, 0.00),
(4, 3, 40, 0.00),
(4, 4, 40, 0.00),
(4, 5, 40, 0.00),
(4, 6, 40, 0.00),
(4, 7, 40, 0.00),
(4, 8, 40, 0.00),
(4, 9, 40, 0.00),
(4, 10, 40, 0.00),
(4, 11, 40, 0.00),
(4, 12, 40, 0.00),
(4, 13, 40, 0.00),
(4, 14, 40, 0.00),
(4, 15, 40, 0.00),
(4, 16, 40, 0.00),
(4, 17, 40, 0.00),
(4, 18, 40, 0.00),
(4, 19, 40, 0.00),
(4, 20, 40, 0.00),
(4, 21, 40, 0.00),
(4, 22, 40, 0.00),
(4, 23, 40, 0.00),
(5, 1, 40, 0.00),
(5, 2, 40, 0.00),
(5, 3, 40, 0.00),
(5, 4, 40, 0.00),
(5, 5, 40, 0.00),
(5, 6, 40, 0.00),
(5, 7, 40, 0.00),
(5, 8, 40, 0.00),
(5, 9, 40, 0.00),
(5, 10, 40, 0.00),
(5, 11, 40, 0.00),
(5, 12, 40, 0.00),
(5, 13, 40, 0.00),
(5, 14, 40, 0.00),
(5, 15, 40, 0.00),
(5, 16, 40, 0.00),
(5, 17, 40, 0.00),
(5, 18, 40, 0.00),
(5, 19, 40, 0.00),
(5, 20, 40, 0.00),
(5, 21, 40, 0.00),
(5, 22, 40, 0.00),
(5, 23, 40, 0.00),
(10, 1, 50, 0.00),
(10, 2, 50, 0.00),
(10, 3, 50, 0.00),
(10, 4, 50, 0.00),
(10, 5, 50, 0.00),
(10, 6, 50, 0.00),
(10, 7, 50, 0.00),
(10, 8, 50, 0.00),
(10, 9, 50, 0.00),
(10, 10, 50, 0.00),
(10, 11, 50, 0.00),
(10, 12, 50, 0.00),
(10, 13, 50, 0.00),
(10, 14, 50, 0.00),
(10, 15, 50, 0.00),
(10, 16, 50, 0.00),
(10, 17, 50, 0.00),
(10, 18, 50, 0.00),
(10, 19, 50, 0.00),
(10, 20, 50, 0.00),
(10, 21, 50, 0.00),
(10, 22, 50, 0.00),
(10, 23, 50, 0.00),
(11, 1, 50, 0.00),
(11, 2, 50, 0.00),
(11, 3, 50, 0.00),
(11, 4, 50, 0.00),
(11, 5, 50, 0.00),
(11, 6, 50, 0.00),
(11, 7, 50, 0.00),
(11, 8, 50, 0.00),
(11, 9, 50, 0.00),
(11, 10, 50, 0.00),
(11, 11, 50, 0.00),
(11, 12, 50, 0.00),
(11, 13, 50, 0.00),
(11, 14, 50, 0.00),
(11, 15, 50, 0.00),
(11, 16, 50, 0.00),
(11, 17, 50, 0.00),
(11, 18, 50, 0.00),
(11, 19, 50, 0.00),
(11, 20, 50, 0.00),
(11, 21, 50, 0.00),
(11, 22, 50, 0.00),
(11, 23, 50, 0.00),
(12, 1, 50, 0.00),
(12, 2, 50, 0.00),
(12, 3, 50, 0.00),
(12, 4, 50, 0.00),
(12, 5, 50, 0.00),
(12, 6, 50, 0.00),
(12, 7, 50, 0.00),
(12, 8, 50, 0.00),
(12, 9, 50, 0.00),
(12, 10, 50, 0.00),
(12, 11, 50, 0.00),
(12, 12, 50, 0.00),
(12, 13, 50, 0.00),
(12, 14, 50, 0.00),
(12, 15, 50, 0.00),
(12, 16, 50, 0.00),
(12, 17, 50, 0.00),
(12, 18, 50, 0.00),
(12, 19, 50, 0.00),
(12, 20, 50, 0.00),
(12, 21, 50, 0.00),
(12, 22, 50, 0.00),
(12, 23, 50, 0.00),
(13, 1, 50, 0.00),
(13, 2, 50, 0.00),
(13, 3, 50, 0.00),
(13, 4, 50, 0.00),
(13, 5, 50, 0.00),
(13, 6, 50, 0.00),
(13, 7, 50, 0.00),
(13, 8, 50, 0.00),
(13, 9, 50, 0.00),
(13, 10, 50, 0.00),
(13, 11, 50, 0.00),
(13, 12, 50, 0.00),
(13, 13, 50, 0.00),
(13, 14, 50, 0.00),
(13, 15, 50, 0.00),
(13, 16, 50, 0.00),
(13, 17, 50, 0.00),
(13, 18, 50, 0.00),
(13, 19, 50, 0.00),
(13, 20, 50, 0.00),
(13, 21, 50, 0.00),
(13, 22, 50, 0.00),
(13, 23, 50, 0.00),
(14, 1, 50, 0.00),
(14, 2, 50, 0.00),
(14, 3, 50, 0.00),
(14, 4, 50, 0.00),
(14, 5, 50, 0.00),
(14, 6, 50, 0.00),
(14, 7, 50, 0.00),
(14, 8, 50, 0.00),
(14, 9, 50, 0.00),
(14, 10, 50, 0.00),
(14, 11, 50, 0.00),
(14, 12, 50, 0.00),
(14, 13, 50, 0.00),
(14, 14, 50, 0.00),
(14, 15, 50, 0.00),
(14, 16, 50, 0.00),
(14, 17, 50, 0.00),
(14, 18, 50, 0.00),
(14, 19, 50, 0.00),
(14, 20, 50, 0.00),
(14, 21, 50, 0.00),
(14, 22, 50, 0.00),
(14, 23, 50, 0.00),
(15, 1, 50, 0.00),
(15, 2, 50, 0.00),
(15, 3, 50, 0.00),
(15, 4, 50, 0.00),
(15, 5, 50, 0.00),
(15, 6, 50, 0.00),
(15, 7, 50, 0.00),
(15, 8, 50, 0.00),
(15, 9, 50, 0.00),
(15, 10, 50, 0.00),
(15, 11, 50, 0.00),
(15, 12, 50, 0.00),
(15, 13, 50, 0.00),
(15, 14, 50, 0.00),
(15, 15, 50, 0.00),
(15, 16, 50, 0.00),
(15, 17, 50, 0.00),
(15, 18, 50, 0.00),
(15, 19, 50, 0.00),
(15, 20, 50, 0.00),
(15, 21, 50, 0.00),
(15, 22, 50, 0.00),
(15, 23, 50, 0.00),
(16, 1, 50, 0.00),
(16, 2, 50, 0.00),
(16, 3, 50, 0.00),
(16, 4, 50, 0.00),
(16, 5, 50, 0.00),
(16, 6, 50, 0.00),
(16, 7, 50, 0.00),
(16, 8, 50, 0.00),
(16, 9, 50, 0.00),
(16, 10, 50, 0.00),
(16, 11, 50, 0.00),
(16, 12, 50, 0.00),
(16, 13, 50, 0.00),
(16, 14, 50, 0.00),
(16, 15, 50, 0.00),
(16, 16, 50, 0.00),
(16, 17, 50, 0.00),
(16, 18, 50, 0.00),
(16, 19, 50, 0.00),
(16, 20, 50, 0.00),
(16, 21, 50, 0.00),
(16, 22, 50, 0.00),
(16, 23, 50, 0.00),
(17, 1, 50, 0.00),
(17, 2, 50, 0.00),
(17, 3, 50, 0.00),
(17, 4, 50, 0.00),
(17, 5, 50, 0.00),
(17, 6, 50, 0.00),
(17, 7, 50, 0.00),
(17, 8, 50, 0.00),
(17, 9, 50, 0.00),
(17, 10, 50, 0.00),
(17, 11, 50, 0.00),
(17, 12, 50, 0.00),
(17, 13, 50, 0.00),
(17, 14, 50, 0.00),
(17, 15, 50, 0.00),
(17, 16, 50, 0.00),
(17, 17, 50, 0.00),
(17, 18, 50, 0.00),
(17, 19, 50, 0.00),
(17, 20, 50, 0.00),
(17, 21, 50, 0.00),
(17, 22, 50, 0.00),
(17, 23, 50, 0.00),
(18, 1, 50, 0.00),
(18, 2, 50, 0.00),
(18, 3, 50, 0.00),
(18, 4, 50, 0.00),
(18, 5, 50, 0.00),
(18, 6, 50, 0.00),
(18, 7, 50, 0.00),
(18, 8, 50, 0.00),
(18, 9, 50, 0.00),
(18, 10, 50, 0.00),
(18, 11, 50, 0.00),
(18, 12, 50, 0.00),
(18, 13, 50, 0.00),
(18, 14, 50, 0.00),
(18, 15, 50, 0.00),
(18, 16, 50, 0.00),
(18, 17, 50, 0.00),
(18, 18, 50, 0.00),
(18, 19, 50, 0.00),
(18, 20, 50, 0.00),
(18, 21, 50, 0.00),
(18, 22, 50, 0.00),
(18, 23, 50, 0.00),
(19, 1, 50, 0.00),
(19, 2, 50, 0.00),
(19, 3, 50, 0.00),
(19, 4, 50, 0.00),
(19, 5, 50, 0.00),
(19, 6, 50, 0.00),
(19, 7, 50, 0.00),
(19, 8, 50, 0.00),
(19, 9, 50, 0.00),
(19, 10, 50, 0.00),
(19, 11, 50, 0.00),
(19, 12, 50, 0.00),
(19, 13, 50, 0.00),
(19, 14, 50, 0.00),
(19, 15, 50, 0.00),
(19, 16, 50, 0.00),
(19, 17, 50, 0.00),
(19, 18, 50, 0.00),
(19, 19, 50, 0.00),
(19, 20, 50, 0.00),
(19, 21, 50, 0.00),
(19, 22, 50, 0.00),
(19, 23, 50, 0.00),
(20, 1, 50, 0.00),
(20, 2, 50, 0.00),
(20, 3, 50, 0.00),
(20, 4, 50, 0.00),
(20, 5, 50, 0.00),
(20, 6, 50, 0.00),
(20, 7, 50, 0.00),
(20, 8, 50, 0.00),
(20, 9, 50, 0.00),
(20, 10, 50, 0.00),
(20, 11, 50, 0.00),
(20, 12, 50, 0.00),
(20, 13, 50, 0.00),
(20, 14, 50, 0.00),
(20, 15, 50, 0.00),
(20, 16, 50, 0.00),
(20, 17, 50, 0.00),
(20, 18, 50, 0.00),
(20, 19, 50, 0.00),
(20, 20, 50, 0.00),
(20, 21, 50, 0.00),
(20, 22, 50, 0.00),
(20, 23, 50, 0.00),
(21, 1, 50, 0.00),
(21, 2, 50, 0.00),
(21, 3, 50, 0.00),
(21, 4, 50, 0.00),
(21, 5, 50, 0.00),
(21, 6, 50, 0.00),
(21, 7, 50, 0.00),
(21, 8, 50, 0.00),
(21, 9, 50, 0.00),
(21, 10, 50, 0.00),
(21, 11, 50, 0.00),
(21, 12, 50, 0.00),
(21, 13, 50, 0.00),
(21, 14, 50, 0.00),
(21, 15, 50, 0.00),
(21, 16, 50, 0.00),
(21, 17, 50, 0.00),
(21, 18, 50, 0.00),
(21, 19, 50, 0.00),
(21, 20, 50, 0.00),
(21, 21, 50, 0.00),
(21, 22, 50, 0.00),
(21, 23, 50, 0.00),
(22, 1, 50, 0.00),
(22, 2, 50, 0.00),
(22, 3, 50, 0.00),
(22, 4, 50, 0.00),
(22, 5, 50, 0.00),
(22, 6, 50, 0.00),
(22, 7, 50, 0.00),
(22, 8, 50, 0.00),
(22, 9, 50, 0.00),
(22, 10, 50, 0.00),
(22, 11, 50, 0.00),
(22, 12, 50, 0.00),
(22, 13, 50, 0.00),
(22, 14, 50, 0.00),
(22, 15, 50, 0.00),
(22, 16, 50, 0.00),
(22, 17, 50, 0.00),
(22, 18, 50, 0.00),
(22, 19, 50, 0.00),
(22, 20, 50, 0.00),
(22, 21, 50, 0.00),
(22, 22, 50, 0.00),
(22, 23, 50, 0.00),
(23, 1, 50, 0.00),
(23, 2, 50, 0.00),
(23, 3, 50, 0.00),
(23, 4, 50, 0.00),
(23, 5, 50, 0.00),
(23, 6, 50, 0.00),
(23, 7, 50, 0.00),
(23, 8, 50, 0.00),
(23, 9, 50, 0.00),
(23, 10, 50, 0.00),
(23, 11, 50, 0.00),
(23, 12, 50, 0.00),
(23, 13, 50, 0.00),
(23, 14, 50, 0.00),
(23, 15, 50, 0.00),
(23, 16, 50, 0.00),
(23, 17, 50, 0.00),
(23, 18, 50, 0.00),
(23, 19, 50, 0.00),
(23, 20, 50, 0.00),
(23, 21, 50, 0.00),
(23, 22, 50, 0.00),
(23, 23, 50, 0.00),
(24, 1, 50, 0.00),
(24, 2, 50, 0.00),
(24, 3, 50, 0.00),
(24, 4, 50, 0.00),
(24, 5, 50, 0.00),
(24, 6, 50, 0.00),
(24, 7, 50, 0.00),
(24, 8, 50, 0.00),
(24, 9, 50, 0.00),
(24, 10, 50, 0.00),
(24, 11, 50, 0.00),
(24, 12, 50, 0.00),
(24, 13, 50, 0.00),
(24, 14, 50, 0.00),
(24, 15, 50, 0.00),
(24, 16, 50, 0.00),
(24, 17, 50, 0.00),
(24, 18, 50, 0.00),
(24, 19, 50, 0.00),
(24, 20, 50, 0.00),
(24, 21, 50, 0.00),
(24, 22, 50, 0.00),
(24, 23, 50, 0.00),
(25, 1, 50, 0.00),
(25, 2, 50, 0.00),
(25, 3, 50, 0.00),
(25, 4, 50, 0.00),
(25, 5, 50, 0.00),
(25, 6, 50, 0.00),
(25, 7, 50, 0.00),
(25, 8, 50, 0.00),
(25, 9, 50, 0.00),
(25, 10, 50, 0.00),
(25, 11, 50, 0.00),
(25, 12, 50, 0.00),
(25, 13, 50, 0.00),
(25, 14, 50, 0.00),
(25, 15, 50, 0.00),
(25, 16, 50, 0.00),
(25, 17, 50, 0.00),
(25, 18, 50, 0.00),
(25, 19, 50, 0.00),
(25, 20, 50, 0.00),
(25, 21, 50, 0.00),
(25, 22, 50, 0.00),
(25, 23, 50, 0.00),
(26, 1, 50, 0.00),
(26, 2, 50, 0.00),
(26, 3, 50, 0.00),
(26, 4, 50, 0.00),
(26, 5, 50, 0.00),
(26, 6, 50, 0.00),
(26, 7, 50, 0.00),
(26, 8, 50, 0.00),
(26, 9, 50, 0.00),
(26, 10, 50, 0.00),
(26, 11, 50, 0.00),
(26, 12, 50, 0.00),
(26, 13, 50, 0.00),
(26, 14, 50, 0.00),
(26, 15, 50, 0.00),
(26, 16, 50, 0.00),
(26, 17, 50, 0.00),
(26, 18, 50, 0.00),
(26, 19, 50, 0.00),
(26, 20, 50, 0.00),
(26, 21, 50, 0.00),
(26, 22, 50, 0.00),
(26, 23, 50, 0.00),
(27, 1, 50, 0.00),
(27, 2, 50, 0.00),
(27, 3, 50, 0.00),
(27, 4, 50, 0.00),
(27, 5, 50, 0.00),
(27, 6, 50, 0.00),
(27, 7, 50, 0.00),
(27, 8, 50, 0.00),
(27, 9, 50, 0.00),
(27, 10, 50, 0.00),
(27, 11, 50, 0.00),
(27, 12, 50, 0.00),
(27, 13, 50, 0.00),
(27, 14, 50, 0.00),
(27, 15, 50, 0.00),
(27, 16, 50, 0.00),
(27, 17, 50, 0.00),
(27, 18, 50, 0.00),
(27, 19, 50, 0.00),
(27, 20, 50, 0.00),
(27, 21, 50, 0.00),
(27, 22, 50, 0.00),
(27, 23, 50, 0.00),
(28, 1, 50, 0.00),
(28, 2, 50, 0.00),
(28, 3, 50, 0.00),
(28, 4, 50, 0.00),
(28, 5, 50, 0.00),
(28, 6, 50, 0.00),
(28, 7, 50, 0.00),
(28, 8, 50, 0.00),
(28, 9, 50, 0.00),
(28, 10, 50, 0.00),
(28, 11, 50, 0.00),
(28, 12, 50, 0.00),
(28, 13, 50, 0.00),
(28, 14, 50, 0.00),
(28, 15, 50, 0.00),
(28, 16, 50, 0.00),
(28, 17, 50, 0.00),
(28, 18, 50, 0.00),
(28, 19, 50, 0.00),
(28, 20, 50, 0.00),
(28, 21, 50, 0.00),
(28, 22, 50, 0.00),
(28, 23, 50, 0.00),
(29, 1, 50, 0.00),
(29, 2, 50, 0.00),
(29, 3, 50, 0.00),
(29, 4, 50, 0.00),
(29, 5, 50, 0.00),
(29, 6, 50, 0.00),
(29, 7, 50, 0.00),
(29, 8, 50, 0.00),
(29, 9, 50, 0.00),
(29, 10, 50, 0.00),
(29, 11, 50, 0.00),
(29, 12, 50, 0.00),
(29, 13, 50, 0.00),
(29, 14, 50, 0.00),
(29, 15, 50, 0.00),
(29, 16, 50, 0.00),
(29, 17, 50, 0.00),
(29, 18, 50, 0.00),
(29, 19, 50, 0.00),
(29, 20, 50, 0.00),
(29, 21, 50, 0.00),
(29, 22, 50, 0.00),
(29, 23, 50, 0.00),
(30, 1, 50, 0.00),
(30, 2, 50, 0.00),
(30, 3, 50, 0.00),
(30, 4, 50, 0.00),
(30, 5, 50, 0.00),
(30, 6, 50, 0.00),
(30, 7, 50, 0.00),
(30, 8, 50, 0.00),
(30, 9, 50, 0.00),
(30, 10, 50, 0.00),
(30, 11, 50, 0.00),
(30, 12, 50, 0.00),
(30, 13, 50, 0.00),
(30, 14, 50, 0.00),
(30, 15, 50, 0.00),
(30, 16, 50, 0.00),
(30, 17, 50, 0.00),
(30, 18, 50, 0.00),
(30, 19, 50, 0.00),
(30, 20, 50, 0.00),
(30, 21, 50, 0.00),
(30, 22, 50, 0.00),
(30, 23, 50, 0.00),
(31, 1, 50, 0.00),
(31, 2, 50, 0.00),
(31, 3, 50, 0.00),
(31, 4, 50, 0.00),
(31, 5, 50, 0.00),
(31, 6, 50, 0.00),
(31, 7, 50, 0.00),
(31, 8, 50, 0.00),
(31, 9, 50, 0.00),
(31, 10, 50, 0.00),
(31, 11, 50, 0.00),
(31, 12, 50, 0.00),
(31, 13, 50, 0.00),
(31, 14, 50, 0.00),
(31, 15, 50, 0.00),
(31, 16, 50, 0.00),
(31, 17, 50, 0.00),
(31, 18, 50, 0.00),
(31, 19, 50, 0.00),
(31, 20, 50, 0.00),
(31, 21, 50, 0.00),
(31, 22, 50, 0.00),
(31, 23, 50, 0.00),
(32, 1, 50, 0.00),
(32, 2, 50, 0.00),
(32, 3, 50, 0.00),
(32, 4, 50, 0.00),
(32, 5, 50, 0.00),
(32, 6, 50, 0.00),
(32, 7, 50, 0.00),
(32, 8, 50, 0.00),
(32, 9, 50, 0.00),
(32, 10, 50, 0.00),
(32, 11, 50, 0.00),
(32, 12, 50, 0.00),
(32, 13, 50, 0.00),
(32, 14, 50, 0.00),
(32, 15, 50, 0.00),
(32, 16, 50, 0.00),
(32, 17, 50, 0.00),
(32, 18, 50, 0.00),
(32, 19, 50, 0.00),
(32, 20, 50, 0.00),
(32, 21, 50, 0.00),
(32, 22, 50, 0.00),
(32, 23, 50, 0.00),
(33, 1, 50, 0.00),
(33, 2, 50, 0.00),
(33, 3, 50, 0.00),
(33, 4, 50, 0.00),
(33, 5, 50, 0.00),
(33, 6, 50, 0.00),
(33, 7, 50, 0.00),
(33, 8, 50, 0.00),
(33, 9, 50, 0.00),
(33, 10, 50, 0.00),
(33, 11, 50, 0.00),
(33, 12, 50, 0.00),
(33, 13, 50, 0.00),
(33, 14, 50, 0.00),
(33, 15, 50, 0.00),
(33, 16, 50, 0.00),
(33, 17, 50, 0.00),
(33, 18, 50, 0.00),
(33, 19, 50, 0.00),
(33, 20, 50, 0.00),
(33, 21, 50, 0.00),
(33, 22, 50, 0.00),
(33, 23, 50, 0.00),
(34, 1, 50, 0.00),
(34, 2, 50, 0.00),
(34, 3, 50, 0.00),
(34, 4, 50, 0.00),
(34, 5, 50, 0.00),
(34, 6, 50, 0.00),
(34, 7, 50, 0.00),
(34, 8, 50, 0.00),
(34, 9, 50, 0.00),
(34, 10, 50, 0.00),
(34, 11, 50, 0.00),
(34, 12, 50, 0.00),
(34, 13, 50, 0.00),
(34, 14, 50, 0.00),
(34, 15, 50, 0.00),
(34, 16, 50, 0.00),
(34, 17, 50, 0.00),
(34, 18, 50, 0.00),
(34, 19, 50, 0.00),
(34, 20, 50, 0.00),
(34, 21, 50, 0.00),
(34, 22, 50, 0.00),
(34, 23, 50, 0.00),
(35, 1, 50, 0.00),
(35, 2, 50, 0.00),
(35, 3, 50, 0.00),
(35, 4, 50, 0.00),
(35, 5, 50, 0.00),
(35, 6, 50, 0.00),
(35, 7, 50, 0.00),
(35, 8, 50, 0.00),
(35, 9, 50, 0.00),
(35, 10, 50, 0.00),
(35, 11, 50, 0.00),
(35, 12, 50, 0.00),
(35, 13, 50, 0.00),
(35, 14, 50, 0.00),
(35, 15, 50, 0.00),
(35, 16, 50, 0.00),
(35, 17, 50, 0.00),
(35, 18, 50, 0.00),
(35, 19, 50, 0.00),
(35, 20, 50, 0.00),
(35, 21, 50, 0.00),
(35, 22, 50, 0.00),
(35, 23, 50, 0.00),
(36, 1, 50, 0.00),
(36, 2, 50, 0.00),
(36, 3, 50, 0.00),
(36, 4, 50, 0.00),
(36, 5, 50, 0.00),
(36, 6, 50, 0.00),
(36, 7, 50, 0.00),
(36, 8, 50, 0.00),
(36, 9, 50, 0.00),
(36, 10, 50, 0.00),
(36, 11, 50, 0.00),
(36, 12, 50, 0.00),
(36, 13, 50, 0.00),
(36, 14, 50, 0.00),
(36, 15, 50, 0.00),
(36, 16, 50, 0.00),
(36, 17, 50, 0.00),
(36, 18, 50, 0.00),
(36, 19, 50, 0.00),
(36, 20, 50, 0.00),
(36, 21, 50, 0.00),
(36, 22, 50, 0.00),
(36, 23, 50, 0.00),
(37, 1, 50, 0.00),
(37, 2, 50, 0.00),
(37, 3, 50, 0.00),
(37, 4, 50, 0.00),
(37, 5, 50, 0.00),
(37, 6, 50, 0.00),
(37, 7, 50, 0.00),
(37, 8, 50, 0.00),
(37, 9, 50, 0.00),
(37, 10, 50, 0.00),
(37, 11, 50, 0.00),
(37, 12, 50, 0.00),
(37, 13, 50, 0.00),
(37, 14, 50, 0.00),
(37, 15, 50, 0.00),
(37, 16, 50, 0.00),
(37, 17, 50, 0.00),
(37, 18, 50, 0.00),
(37, 19, 50, 0.00),
(37, 20, 50, 0.00),
(37, 21, 50, 0.00),
(37, 22, 50, 0.00),
(37, 23, 50, 0.00),
(38, 1, 50, 0.00),
(38, 2, 50, 0.00),
(38, 3, 50, 0.00),
(38, 4, 50, 0.00),
(38, 5, 50, 0.00),
(38, 6, 50, 0.00),
(38, 7, 50, 0.00),
(38, 8, 50, 0.00),
(38, 9, 50, 0.00),
(38, 10, 50, 0.00),
(38, 11, 50, 0.00),
(38, 12, 50, 0.00),
(38, 13, 50, 0.00),
(38, 14, 50, 0.00),
(38, 15, 50, 0.00),
(38, 16, 50, 0.00),
(38, 17, 50, 0.00),
(38, 18, 50, 0.00),
(38, 19, 50, 0.00),
(38, 20, 50, 0.00),
(38, 21, 50, 0.00),
(38, 22, 50, 0.00),
(38, 23, 50, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(1, 2, 'Welcome to VaxCare. Zain\'s vaccination schedule is ready.', 0, '2026-10-19 19:36:11');

-- --------------------------------------------------------

--
-- Table structure for table `parents`
--

CREATE TABLE `parents` (
  `user_id` int(11) NOT NULL,
  `address` varchar(255) NOT NULL DEFAULT '',
  `city` varchar(80) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `parents`
--

INSERT INTO `parents` (`user_id`, `address`, `city`) VALUES
(2, 'House 12, Block B, Gulshan', 'Karachi'),
(8, '3c 3/7 nazimabad no.3', 'karachi'),
(9, '3c 3/8 nazimabad no.3', 'karachi');

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE `schedule` (
  `id` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  `vaccine_id` int(11) NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('pending','booked','vaccinated') NOT NULL DEFAULT 'pending',
  `given_on` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `schedule`
--

INSERT INTO `schedule` (`id`, `child_id`, `vaccine_id`, `due_date`, `status`, `given_on`) VALUES
(1, 1, 1, '2026-07-12', 'vaccinated', '2026-07-12'),
(2, 1, 2, '2026-07-12', 'vaccinated', '2026-07-12'),
(3, 1, 3, '2026-07-12', 'vaccinated', '2026-07-12'),
(4, 1, 4, '2026-08-23', 'vaccinated', '2026-08-23'),
(5, 1, 5, '2026-08-23', 'vaccinated', '2026-08-23'),
(6, 1, 6, '2026-08-23', 'vaccinated', '2026-08-23'),
(7, 1, 7, '2026-09-20', 'pending', NULL),
(8, 1, 8, '2026-09-20', 'pending', NULL),
(9, 1, 9, '2026-10-18', 'pending', NULL),
(10, 1, 10, '2026-10-18', 'pending', NULL),
(11, 1, 11, '2027-04-08', 'pending', NULL),
(12, 1, 12, '2027-10-05', 'pending', NULL),
(13, 1, 13, '2028-01-03', 'pending', NULL),
(14, 2, 14, '2026-10-24', 'pending', NULL),
(15, 2, 15, '2026-10-24', 'pending', NULL),
(16, 2, 16, '2026-10-24', 'pending', NULL),
(17, 2, 17, '2026-10-24', 'pending', NULL),
(18, 2, 18, '2026-10-24', 'pending', NULL),
(19, 2, 19, '2026-10-24', 'pending', NULL),
(20, 2, 20, '2026-10-24', 'pending', NULL),
(21, 2, 21, '2026-10-24', 'pending', NULL),
(22, 2, 22, '2026-10-24', 'pending', NULL),
(23, 2, 23, '2026-10-24', 'pending', NULL),
(24, 3, 1, '2024-07-22', 'pending', NULL),
(25, 3, 2, '2024-07-22', 'pending', NULL),
(26, 3, 3, '2024-07-22', 'pending', NULL),
(27, 3, 4, '2024-09-02', 'pending', NULL),
(28, 3, 5, '2024-09-02', 'pending', NULL),
(29, 3, 6, '2024-09-02', 'pending', NULL),
(30, 3, 7, '2024-09-30', 'pending', NULL),
(31, 3, 8, '2024-09-30', 'pending', NULL),
(32, 3, 9, '2024-10-28', 'pending', NULL),
(33, 3, 10, '2024-10-28', 'pending', NULL),
(34, 3, 11, '2025-04-18', 'pending', NULL),
(35, 3, 12, '2025-10-15', 'pending', NULL),
(36, 3, 13, '2026-01-13', 'pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `travel_clearance`
--

CREATE TABLE `travel_clearance` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `passport_no` varchar(50) NOT NULL,
  `destination` varchar(150) NOT NULL,
  `travel_date` date NOT NULL,
  `purpose` varchar(255) NOT NULL DEFAULT '',
  `vaccines_proof` text NOT NULL DEFAULT '',
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `admin_note` varchar(500) NOT NULL DEFAULT '',
  `clearance_code` varchar(32) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `travel_plans`
--

CREATE TABLE `travel_plans` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `travel_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL DEFAULT '',
  `date_of_birth` date DEFAULT NULL,
  `is_adult` tinyint(1) NOT NULL DEFAULT 0,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','parent','hospital') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `date_of_birth`, `is_adult`, `password`, `role`, `created_at`) VALUES
(1, 'System Admin', 'admin@vaxcare.test', '0300-0000000', NULL, 0, '$2y$10$okLazmmHEFP6fygqwr9VGOGq/RDtyje2ixP7NdJWtUuXPKgKw7/K2', 'admin', '2026-10-19 19:36:11'),
(2, 'Ayesha Khan', 'parent@vaxcare.test', '0301-1234567', NULL, 0, '$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i', 'parent', '2026-10-19 19:36:11'),
(3, 'City Care Hospital', 'hospital@vaxcare.test', '021-34000001', NULL, 0, '$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i', 'hospital', '2026-10-19 19:36:11'),
(4, 'Green Crescent Clinic', 'clinic@vaxcare.test', '021-34000002', NULL, 0, '$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i', 'hospital', '2026-10-19 19:36:11'),
(5, 'Demo Partner Hospital - Gulshan', 'demo.gulshan@vaxcare.test', '021-555-0101', NULL, 0, '$2y$10$TDfCSLmLPcljrrVBwZv5ouS5PtI6XJsq6lEDjeItCIdUfZAgrTIlW', 'hospital', '2026-10-23 18:49:21'),
(6, 'Demo Partner Clinic - Clifton', 'demo.clifton@vaxcare.test', '021-555-0102', NULL, 0, '$2y$10$I7wH7QWSNIy0IX0qgx6rte/531Z6rLRSM2faSkjxmLLa6H1HXU/.y', 'hospital', '2026-10-23 18:49:21'),
(7, 'Demo Vaccination Centre - North Nazimabad', 'demo.north@vaxcare.test', '021-555-0103', NULL, 0, '$2y$10$pKMAwHqGPopGh5VgQTzZU.IGSTY2RUZGkPxp7.FeFS8w/WnIhUHDG', 'hospital', '2026-10-23 18:49:21'),
(8, 'Noman', 'muhammadnomansiddiqui794@gmai.l.com', '03196582415', NULL, 0, '$2y$10$UJTuFmS/vHKg3rcpZLsKyu6rmc1XFt5cudS4Eo/oYilVPuacp9wRy', 'parent', '2026-10-24 06:12:42'),
(9, 'daniya', 'daniy123@gmail.com', '03002576890', NULL, 0, '$2y$10$U0R6.KW8sWowDBe5aTG2t.lOxkK744HsEZGjX0zSo96bjkLgcFi2O', 'parent', '2026-10-26 17:47:22'),
(20, 'Aga Khan University Hospital', 'akuh@vaxcare.test', '021-34930051', NULL, 0, '$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i', 'hospital', '2026-10-26 18:00:17'),
(21, 'Liaquat National Hospital', 'lnh@vaxcare.test', '021-34412442', NULL, 0, '$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i', 'hospital', '2026-10-26 18:00:17'),
(22, 'South City Hospital', 'southcity@vaxcare.test', '021-35871471', NULL, 0, '$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i', 'hospital', '2026-10-26 18:00:17'),
(23, 'Indus Hospital', 'indus@vaxcare.test', '021-35112709', NULL, 0, '$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i', 'hospital', '2026-10-26 18:00:17'),
(24, 'Ziauddin Hospital', 'ziauddin@vaxcare.test', '021-36699000', NULL, 0, '$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i', 'hospital', '2026-10-26 18:00:17'),
(25, 'Shaukat Khanum Memorial Cancer Hospital', 'skmch.lhr@vaxcare.test', '042-35945100', NULL, 0, '$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i', 'hospital', '2026-10-26 18:00:17'),
(26, 'Services Hospital Lahore', 'services.lhr@vaxcare.test', '042-99203390', NULL, 0, '$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i', 'hospital', '2026-10-26 18:00:17'),
(27, 'Doctors Hospital Lahore', 'doctors.lhr@vaxcare.test', '042-35761999', NULL, 0, '$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i', 'hospital', '2026-10-26 18:00:17'),
(28, 'Ittefaq Hospital', 'ittefaq.lhr@vaxcare.test', '042-35761340', NULL, 0, '$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i', 'hospital', '2026-10-26 18:00:17'),
(29, 'Ganga Ram Hospital', 'gangarum.lhr@vaxcare.test', '042-37233337', NULL, 0, '$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i', 'hospital', '2026-10-26 18:00:17'),
(30, 'Shifa International Hospital', 'shifa.isb@vaxcare.test', '051-8464646', NULL, 0, '$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i', 'hospital', '2026-10-26 18:00:17'),
(31, 'Pakistan Institute of Medical Sciences', 'pims.isb@vaxcare.test', '051-9261170', NULL, 0, '$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i', 'hospital', '2026-10-26 18:00:17'),
(32, 'Holy Family Hospital Rawalpindi', 'holyfamily.rwp@vaxcare.test', '051-9290311', NULL, 0, '$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i', 'hospital', '2026-10-26 18:00:17'),
(33, 'Benazir Bhutto Hospital', 'bgh.rwp@vaxcare.test', '051-9270341', NULL, 0, '$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i', 'hospital', '2026-10-26 18:00:17'),
(34, 'Lady Reading Hospital', 'lrh.psh@vaxcare.test', '091-9212301', NULL, 0, '$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i', 'hospital', '2026-10-26 18:00:17'),
(35, 'Hayatabad Medical Complex', 'hmc.psh@vaxcare.test', '091-9217001', NULL, 0, '$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i', 'hospital', '2026-10-26 18:00:17'),
(36, 'Khyber Teaching Hospital', 'kth.psh@vaxcare.test', '091-9216440', NULL, 0, '$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i', 'hospital', '2026-10-26 18:00:17'),
(37, 'Bolan Medical Complex Hospital', 'bmc.qta@vaxcare.test', '081-2821000', NULL, 0, '$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i', 'hospital', '2026-10-26 18:00:17'),
(38, 'Civil Hospital Quetta', 'civil.qta@vaxcare.test', '081-2822020', NULL, 0, '$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i', 'hospital', '2026-10-26 18:00:17'),
(39, 'Allied Hospital Faisalabad', 'allied.fsd@vaxcare.test', '041-9200471', NULL, 0, '$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i', 'hospital', '2026-10-26 18:00:17'),
(40, 'Niazi Medical & Dental College Hospital', 'niazi.fsd@vaxcare.test', '041-8812111', NULL, 0, '$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i', 'hospital', '2026-10-26 18:00:17'),
(41, 'Nishtar Hospital Multan', 'nishtar.mtn@vaxcare.test', '061-9200303', NULL, 0, '$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i', 'hospital', '2026-10-26 18:00:17'),
(42, 'Children Hospital Multan', 'children.mtn@vaxcare.test', '061-9200401', NULL, 0, '$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i', 'hospital', '2026-10-26 18:00:17'),
(43, 'Liaquat University Hospital Hyderabad', 'luh.hyd@vaxcare.test', '022-9200830', NULL, 0, '$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i', 'hospital', '2026-10-26 18:00:17'),
(44, 'Ghulam Muhammad Mahar Medical College Hospital', 'gmmmc.skr@vaxcare.test', '071-5630071', NULL, 0, '$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i', 'hospital', '2026-10-26 18:00:17'),
(45, 'Ayub Teaching Hospital', 'ayub.abb@vaxcare.test', '0992-381136', NULL, 0, '$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i', 'hospital', '2026-10-26 18:00:17'),
(46, 'District Headquarters Hospital Gilgit', 'dhq.gilt@vaxcare.test', '05811-920005', NULL, 0, '$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i', 'hospital', '2026-10-26 18:00:17'),
(47, 'Shaukat Khanum Hospital Peshawar (Regi)', 'skmch.psh@vaxcare.test', '091-5703000', NULL, 0, '$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i', 'hospital', '2026-10-26 18:00:17'),
(48, 'DHQ Teaching Hospital Gujranwala', 'dhq.gjw@vaxcare.test', '055-9200102', NULL, 0, '$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i', 'hospital', '2026-10-26 18:00:17');

-- --------------------------------------------------------

--
-- Table structure for table `vaccines`
--

CREATE TABLE `vaccines` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL DEFAULT '',
  `age_days` int(11) NOT NULL DEFAULT 0,
  `available` tinyint(1) NOT NULL DEFAULT 1,
  `audience` enum('child','adult') NOT NULL DEFAULT 'child',
  `travel_required` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vaccines`
--

INSERT INTO `vaccines` (`id`, `name`, `description`, `age_days`, `available`, `audience`, `travel_required`) VALUES
(1, 'BCG', 'Protects against tuberculosis', 0, 1, 'child', 0),
(2, 'Hepatitis B (birth dose)', 'Protects against hepatitis B', 0, 1, 'child', 0),
(3, 'OPV-0', 'Oral polio vaccine, birth dose', 0, 1, 'child', 0),
(4, 'Pentavalent-1', 'DPT, Hepatitis B and Hib, dose 1', 42, 1, 'child', 0),
(5, 'PCV-1', 'Pneumococcal vaccine, dose 1', 42, 1, 'child', 0),
(6, 'Rotavirus-1', 'Protects against severe diarrhoea, dose 1', 42, 1, 'child', 0),
(7, 'Pentavalent-2', 'DPT, Hepatitis B and Hib, dose 2', 70, 1, 'child', 0),
(8, 'PCV-2', 'Pneumococcal vaccine, dose 2', 70, 1, 'child', 0),
(9, 'Pentavalent-3', 'DPT, Hepatitis B and Hib, dose 3', 98, 1, 'child', 0),
(10, 'IPV', 'Injectable polio vaccine', 98, 1, 'child', 0),
(11, 'Measles-Rubella 1', 'Measles and rubella, dose 1', 270, 1, 'child', 0),
(12, 'Measles-Rubella 2', 'Measles and rubella, dose 2', 450, 1, 'child', 0),
(13, 'Typhoid conjugate', 'Protects against typhoid fever', 540, 1, 'child', 0),
(14, 'Polio (IPV/OPV)', 'Polio proof is checked for international travellers', 0, 1, 'adult', 1),
(15, 'Meningococcal ACWY', 'Needed for Hajj, Umrah and travel to some regions', 0, 1, 'adult', 1),
(16, 'COVID-19 vaccine', 'Primary course or booster, checked by some destinations', 0, 1, 'adult', 1),
(17, 'Typhoid conjugate (adult)', 'Recommended for South Asia and Africa trips', 0, 1, 'adult', 0),
(18, 'Hepatitis A', 'Recommended for most overseas trips', 0, 1, 'adult', 0),
(19, 'Hepatitis B (adult)', 'Protects against hepatitis B', 0, 1, 'adult', 0),
(20, 'Tetanus-diphtheria (Td)', 'Booster every 10 years', 0, 1, 'adult', 0),
(21, 'MMR', 'Measles, mumps and rubella catch-up', 0, 1, 'adult', 0),
(22, 'Yellow fever', 'Required when travelling to or from endemic countries', 0, 1, 'adult', 0),
(23, 'Seasonal influenza', 'Yearly flu shot', 0, 1, 'adult', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appt_date` (`appt_date`),
  ADD KEY `hospital_id` (`hospital_id`,`appt_date`),
  ADD KEY `schedule_id` (`schedule_id`),
  ADD KEY `child_id` (`child_id`),
  ADD KEY `vaccine_id` (`vaccine_id`);

--
-- Indexes for table `children`
--
ALTER TABLE `children`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `hospitals`
--
ALTER TABLE `hospitals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `hospital_vaccines`
--
ALTER TABLE `hospital_vaccines`
  ADD PRIMARY KEY (`hospital_id`,`vaccine_id`),
  ADD KEY `vaccine_id` (`vaccine_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`,`is_read`);

--
-- Indexes for table `parents`
--
ALTER TABLE `parents`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `schedule`
--
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `due_date` (`due_date`),
  ADD KEY `child_id` (`child_id`),
  ADD KEY `vaccine_id` (`vaccine_id`);

--
-- Indexes for table `travel_clearance`
--
ALTER TABLE `travel_clearance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tc_user` (`user_id`);

--
-- Indexes for table `travel_plans`
--
ALTER TABLE `travel_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`,`travel_date`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vaccines`
--
ALTER TABLE `vaccines`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `children`
--
ALTER TABLE `children`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `hospitals`
--
ALTER TABLE `hospitals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `schedule`
--
ALTER TABLE `schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `travel_clearance`
--
ALTER TABLE `travel_clearance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `travel_plans`
--
ALTER TABLE `travel_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `vaccines`
--
ALTER TABLE `vaccines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `schedule` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`child_id`) REFERENCES `children` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_4` FOREIGN KEY (`vaccine_id`) REFERENCES `vaccines` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `children`
--
ALTER TABLE `children`
  ADD CONSTRAINT `children_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hospitals`
--
ALTER TABLE `hospitals`
  ADD CONSTRAINT `hospitals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hospital_vaccines`
--
ALTER TABLE `hospital_vaccines`
  ADD CONSTRAINT `hospital_vaccines_ibfk_1` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hospital_vaccines_ibfk_2` FOREIGN KEY (`vaccine_id`) REFERENCES `vaccines` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `parents`
--
ALTER TABLE `parents`
  ADD CONSTRAINT `parents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `schedule`
--
ALTER TABLE `schedule`
  ADD CONSTRAINT `schedule_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `children` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `schedule_ibfk_2` FOREIGN KEY (`vaccine_id`) REFERENCES `vaccines` (`id`);

--
-- Constraints for table `travel_clearance`
--
ALTER TABLE `travel_clearance`
  ADD CONSTRAINT `fk_tc_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `travel_plans`
--
ALTER TABLE `travel_plans`
  ADD CONSTRAINT `travel_plans_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `children` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
