-- Struktur tabel users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nama VARCHAR(100),
    email VARCHAR(100),
    role VARCHAR(20) DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);


INSERT INTO users (username, password_hash, nama, email, role) VALUES
('admin', '$2y$10$clCyEKorqDRB97NAC1Adzugcxh1te5VQNcmCq.eMV7jnreSP7fTZC', 'Admin', 'admin@email.com', 'admin');
INSERT INTO users (username, password_hash, nama, email, role) VALUES
('user1', '$2y$10$KllD9/azBpFSuzVBSGD9C.PtYTmabeEXpuTPHQN4o0NTXsO/Oerwa', 'User Satu', 'user1@email.com', 'user');

-- user 
-- user123
-- admin
-- admin123


-- Struktur tabel stories
CREATE TABLE IF NOT EXISTS stories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userId INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    category VARCHAR(50),
    coverImage VARCHAR(255),
    status VARCHAR(20) DEFAULT 'published',
    admin_comment TEXT NULL,
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (userId) REFERENCES users(id) ON DELETE CASCADE
); 

-- Sample stories data
INSERT INTO stories (userId, title, content, category, status, createdAt) VALUES
(2, 'Perjuangan Skripsi di Tengah Pandemi', 'Menghadapi skripsi saat pandemi memang penuh tantangan. Tapi aku belajar banyak tentang manajemen waktu dan mental. Dari yang awalnya bingung mau mulai dari mana, hingga akhirnya bisa menyelesaikan dengan baik. Prosesnya memang tidak mudah, tapi pengalaman ini mengajarkan banyak hal berharga tentang kedisiplinan dan konsistensi.', 'akademik', 'published', '2024-01-15 10:30:00'),
(2, 'Tips Mendapat Magang Impian', 'Dari ratusan lamaran, akhirnya aku diterima magang di perusahaan impian. Ini tips yang bisa diterapkan untuk meningkatkan peluang diterima magang. Pertama, riset mendalam tentang perusahaan. Kedua, sesuaikan CV dengan job description. Ketiga, latih soft skill terutama komunikasi.', 'karir', 'published', '2024-01-10 14:20:00'),
(2, 'Pengalaman Organisasi Kemahasiswaan', 'Bagaimana organisasi mengubah hidupku? Dari yang pemalu jadi lebih percaya diri dan punya banyak teman. Leadership skill juga berkembang pesat. Mulai dari event organizer kecil-kecilan sampai memimpin divisi dengan puluhan anggota.', 'kehidupan', 'published', '2024-01-05 09:15:00'),
(2, 'Belajar Programming dari Nol', 'Journey belajar programming memang penuh lika-liku. Mulai dari HTML, CSS, JavaScript, hingga framework modern. Yang penting konsisten latihan coding setiap hari dan jangan takut untuk bertanya atau mencari bantuan di komunitas developer.', 'teknologi', 'published', '2024-01-12 16:45:00'); 

-- Tabel untuk data tim admin
CREATE TABLE IF NOT EXISTS team (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    jabatan VARCHAR(100) NOT NULL,
    foto VARCHAR(255) DEFAULT NULL
); 
