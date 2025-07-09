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
    createdAt DATETIME,
    updatedAt DATETIME
); 
