-- =====================================================
-- SQL SCRIPT ĐỂ TẠO DATABASE VÀ DỮ LIỆU MẪU
-- Hospital Purchase Management System
-- =====================================================

-- Tạo database
CREATE DATABASE IF NOT EXISTS hospital_vpp_2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hospital_vpp_2;

-- =====================================================
-- 1. TẠO CÁC BẢNG
-- =====================================================

-- Bảng Departments
CREATE TABLE IF NOT EXISTS departments (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng Users
CREATE TABLE IF NOT EXISTS users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('SuperAdmin', 'Admin', 'Department') NOT NULL,
    department_id BIGINT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);

-- Bảng Categories
CREATE TABLE IF NOT EXISTS categories (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    parent_id BIGINT NULL,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Bảng Products
CREATE TABLE IF NOT EXISTS products (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    category_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    unit VARCHAR(50) NOT NULL,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
);

-- Bảng Monthly Orders
CREATE TABLE IF NOT EXISTS monthly_orders (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    department_id BIGINT NOT NULL,
    product_id BIGINT NOT NULL,
    month VARCHAR(7) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_order (department_id, product_id, month),
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
);

-- Bảng Activity Logs
CREATE TABLE IF NOT EXISTS activity_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    action VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bảng Sessions (cho Laravel)
CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    INDEX sessions_user_id_index (user_id),
    INDEX sessions_last_activity_index (last_activity)
);

-- Bảng Password Reset Tokens
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
);

-- =====================================================
-- 2. THÊM DỮ LIỆU MẪU
-- =====================================================

-- Departments
INSERT INTO departments (code, name, is_active) VALUES
('CDHA', 'Chẩn đoán hình ảnh', TRUE),
('XN', 'Xét nghiệm', TRUE),
('PK', 'Phòng khám', TRUE),
('NGOAI', 'Khoa Ngoại', TRUE),
('NOI', 'Khoa Nội', TRUE),
('SANPK', 'Sản phụ khoa', TRUE),
('NHI', 'Khoa Nhi', TRUE),
('DUOC', 'Khoa Dược', TRUE),
('HCTH', 'Hành chính tổng hợp', TRUE);

-- Users (Password: password - đã hash bằng bcrypt)
INSERT INTO users (name, email, password, role, department_id, is_active) VALUES
('Super Admin', 'superadmin@hospital.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'SuperAdmin', NULL, TRUE),
('Admin User', 'admin@hospital.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', NULL, TRUE),
('User Chẩn đoán hình ảnh', 'cdha@hospital.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Department', 1, TRUE),
('User Xét nghiệm', 'xn@hospital.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Department', 2, TRUE),
('User Phòng khám', 'pk@hospital.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Department', 3, TRUE),
('User Khoa Ngoại', 'ngoai@hospital.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Department', 4, TRUE),
('User Khoa Nội', 'noi@hospital.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Department', 5, TRUE),
('User Sản phụ khoa', 'sanpk@hospital.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Department', 6, TRUE),
('User Khoa Nhi', 'nhi@hospital.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Department', 7, TRUE),
('User Khoa Dược', 'duoc@hospital.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Department', 8, TRUE),
('User Hành chính tổng hợp', 'hcth@hospital.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Department', 9, TRUE);

-- Categories
INSERT INTO categories (name, parent_id, display_order, is_active) VALUES
('Văn phòng phẩm - Nhà sách Thành Vân', NULL, 1, TRUE),
('Quảng cáo Rạng', NULL, 2, TRUE),
('Văn phòng phẩm khác', NULL, 3, TRUE),
('Thiết bị y tế', NULL, 4, TRUE);

-- Products
INSERT INTO products (category_id, name, unit, display_order, is_active) VALUES
-- Văn phòng phẩm - Nhà sách Thành Vân
(1, 'Bìa còng 10p', 'Cái', 1, TRUE),
(1, 'Bìa giấy 10p', 'Cái', 2, TRUE),
(1, 'Sổ caro A4', 'Quyển', 3, TRUE),
(1, 'Bút bi xanh', 'Cây', 4, TRUE),
(1, 'Bút bi đỏ', 'Cây', 5, TRUE),
(1, 'Tập 100 tờ', 'Quyển', 6, TRUE),
(1, 'Giấy A4', 'Ream', 7, TRUE),
(1, 'Kẹp giấy', 'Hộp', 8, TRUE),
(1, 'Ghim bấm', 'Hộp', 9, TRUE),
(1, 'Băng keo trong', 'Cuộn', 10, TRUE),
(1, 'Thước kẻ 30cm', 'Cái', 11, TRUE),
(1, 'Kéo văn phòng', 'Cái', 12, TRUE),
-- Quảng cáo Rạng
(2, 'Form siêu âm', 'Tờ', 1, TRUE),
(2, 'Sổ theo dõi bệnh án', 'Cuốn', 2, TRUE),
(2, 'Phiếu khám bệnh', 'Tờ', 3, TRUE),
(2, 'Sổ theo dõi xét nghiệm', 'Cuốn', 4, TRUE),
(2, 'Giấy in nhiệt', 'Cuộn', 5, TRUE),
-- Văn phòng phẩm khác
(3, 'Bìa đựng hồ sơ', 'Cái', 1, TRUE),
(3, 'Hộp đựng bút', 'Cái', 2, TRUE),
(3, 'Bảng tên để bàn', 'Cái', 3, TRUE),
-- Thiết bị y tế
(4, 'Găng tay y tế', 'Hộp', 1, TRUE),
(4, 'Khẩu trang y tế', 'Hộp', 2, TRUE),
(4, 'Bông y tế', 'Gói', 3, TRUE);

-- Monthly Orders (Dữ liệu mẫu cho tháng 09/2025)
INSERT INTO monthly_orders (department_id, product_id, month, quantity) VALUES
-- CDHA
(1, 1, '09/2025', 2),  -- Bìa còng 10p
(1, 3, '09/2025', 5),  -- Sổ caro A4
(1, 13, '09/2025', 10), -- Form siêu âm
-- XN
(2, 1, '09/2025', 5),  -- Bìa còng 10p
(2, 4, '09/2025', 10), -- Bút bi xanh
(2, 7, '09/2025', 2),  -- Giấy A4
-- PK
(3, 1, '09/2025', 3),  -- Bìa còng 10p
(3, 15, '09/2025', 20), -- Phiếu khám bệnh
(3, 21, '09/2025', 5); -- Găng tay y tế

-- =====================================================
-- HOÀN THÀNH
-- =====================================================
