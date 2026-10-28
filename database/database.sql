-- ==========================================================
-- Skema Basis Data: Lapor Purbalingga
-- Panduan Mahasiswa:
-- 1. Buka phpMyAdmin (http://localhost/phpmyadmin)
-- 2. Buat database baru bernama `lapor_purbalingga` atau impor file ini.
-- 3. Password akun pengguna disimpan menggunakan PHP password_hash (bcrypt).
-- ==========================================================

CREATE DATABASE IF NOT EXISTS lapor_purbalingga CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lapor_purbalingga;

-- 1. Tabel Pengguna (Warga & Administrator)
CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('citizen', 'central_admin', 'agency_admin', 'regent') NOT NULL DEFAULT 'citizen',
    agency_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Tabel Pengaduan Masalah Pelayanan Publik
CREATE TABLE IF NOT EXISTS complaints (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_number VARCHAR(24) NOT NULL UNIQUE,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(150) NOT NULL,
    category VARCHAR(100) NOT NULL,
    incident_date DATE NOT NULL,
    description TEXT NOT NULL,
    district VARCHAR(100) NOT NULL,
    village VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    latitude DECIMAL(10,7) NULL,
    longitude DECIMAL(10,7) NULL,
    status ENUM('submitted', 'verified', 'assigned', 'processing', 'awaiting_confirmation', 'completed', 'needs_information', 'rejected', 'referred') NOT NULL DEFAULT 'submitted',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_complaints_user FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_complaints_user (user_id),
    INDEX idx_complaints_status (status),
    INDEX idx_complaints_created (created_at),
    INDEX idx_complaints_coordinates (latitude, longitude)
) ENGINE=InnoDB;

-- 3. Tabel Lampiran Foto Bukti Pengaduan
CREATE TABLE IF NOT EXISTS complaint_attachments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    complaint_id BIGINT UNSIGNED NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(50) NOT NULL,
    size_bytes INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_attachments_complaint FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
    INDEX idx_attachments_complaint (complaint_id)
) ENGINE=InnoDB;

-- 4. Tabel Riwayat Perubahan Status & Audit Log
CREATE TABLE IF NOT EXISTS complaint_status_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    complaint_id BIGINT UNSIGNED NOT NULL,
    actor_id BIGINT UNSIGNED NOT NULL,
    old_status VARCHAR(40) NULL,
    new_status VARCHAR(40) NOT NULL,
    note TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_history_complaint FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
    CONSTRAINT fk_history_actor FOREIGN KEY (actor_id) REFERENCES users(id),
    INDEX idx_history_complaint (complaint_id, created_at)
) ENGINE=InnoDB;
