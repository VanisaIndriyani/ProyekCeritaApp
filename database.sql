-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 18, 2025 at 11:09 AM
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
-- Database: `cerita_app`
--

-- --------------------------------------------------------

--
-- Table structure for table `stories`
--

CREATE TABLE `stories` (
  `id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `coverImage` varchar(255) DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL,
  `updatedAt` datetime DEFAULT NULL,
  `status` varchar(20) DEFAULT 'draft',
  `admin_comment` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stories`
--

INSERT INTO `stories` (`id`, `userId`, `title`, `content`, `category`, `coverImage`, `createdAt`, `updatedAt`, `status`, `admin_comment`, `created_at`) VALUES
(9, 2, 'Pengalaman Magang di Startup', 'Magang di startup membuat saya belajar banyak hal baru, mulai dari kerja tim, manajemen waktu, hingga coding real project. Walau awalnya gugup, akhirnya saya bisa beradaptasi dan mendapat banyak teman baru.', 'Karir', NULL, NULL, NULL, 'published', NULL, '2024-07-05 14:30:00'),
(11, 1, 'Tips Bertahan Hidup di Kost', 'Hidup di kost jauh dari orang tua mengajarkan saya mandiri. Mulai dari masak sendiri, atur keuangan, sampai cari teman baru. Semua pengalaman ini bikin saya lebih dewasa.', 'Kehidupan', NULL, NULL, NULL, 'published', NULL, '2024-07-12 18:00:00'),
(12, 2, 'Lolos Beasiswa ke Luar Negeri', 'Saya tidak menyangka bisa lolos beasiswa ke Jepang! Prosesnya panjang, mulai dari seleksi berkas, wawancara, hingga tes bahasa. Tapi semua terbayar saat akhirnya saya berangkat ke Tokyo.', 'Prestasi', NULL, NULL, NULL, 'published', NULL, '2024-07-15 08:45:00'),
(14, 6, 'cerita mahasiswa ', 'ini cerita teknologi sistem informasi', 'teknologi', '5c6b7fb8a6f7734e.jpeg', '2025-07-17 10:27:26', '2025-07-17 10:28:22', 'published', NULL, '2025-07-17 15:27:26');

-- --------------------------------------------------------

--
-- Table structure for table `team`
--

CREATE TABLE `team` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jabatan` varchar(100) NOT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `team`
--

INSERT INTO `team` (`id`, `nama`, `jabatan`, `foto`) VALUES
(1, 'Vanisa Indriyanidshksj', 'Anggota', ''),
(2, 'Triyas Nurilita Nurul Adha', 'Anggota', NULL),
(3, 'Gede Aryamulya Putra Kumara', 'Anggota', NULL),
(4, 'Khairunnnas', 'Anggota', NULL),
(5, 'Abdul Rahman Wahid', 'Anggota', NULL),
(6, 'Bagus Nur Sulaiman', 'Anggota', NULL),
(10, 'tata azzani', 'ketua', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'user',
  `created_at` datetime DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `nama`, `email`, `role`, `created_at`, `reset_token`, `reset_expires`) VALUES
(5, 'admin', '$2y$10$clCyEKorqDRB97NAC1Adzugcxh1te5VQNcmCq.eMV7jnreSP7fTZC', 'Admin', 'admin@email.com', 'admin', '2025-07-09 10:21:00', NULL, NULL),
(6, 'user1', '$2y$10$KllD9/azBpFSuzVBSGD9C.PtYTmabeEXpuTPHQN4o0NTXsO/Oerwa', 'User Satu', 'user1@email.com', 'user', '2025-07-09 10:21:00', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `stories`
--
ALTER TABLE `stories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `team`
--
ALTER TABLE `team`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `stories`
--
ALTER TABLE `stories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `team`
--
ALTER TABLE `team`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
