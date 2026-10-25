-- ===============================================================
-- KirimKita: Sistem Informasi Manajemen Pengiriman Barang
-- MySQL 8+ / MariaDB (InnoDB dan utf8mb4)
-- ===============================================================

CREATE DATABASE IF NOT EXISTS kirimkita_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE kirimkita_db;

-- Pelanggan / pengirim paket
CREATE TABLE IF NOT EXISTS customers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    email VARCHAR(190) NOT NULL DEFAULT '',
    address VARCHAR(500) NOT NULL DEFAULT '',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB;

-- Petugas kurir
CREATE TABLE IF NOT EXISTS couriers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    vehicle VARCHAR(80) NOT NULL DEFAULT '',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB;

-- Akun login administrator dan kurir
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'courier') NOT NULL DEFAULT 'admin',
    courier_id INT UNSIGNED NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_user_courier
        FOREIGN KEY (courier_id) REFERENCES couriers(id)
        ON DELETE RESTRICT
) ENGINE = InnoDB;

-- Data utama pengiriman paket
CREATE TABLE IF NOT EXISTS shipments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tracking_number VARCHAR(32) NOT NULL UNIQUE,
    customer_id INT UNSIGNED NOT NULL,
    courier_id INT UNSIGNED NULL,
    recipient_name VARCHAR(120) NOT NULL,
    recipient_phone VARCHAR(30) NOT NULL,
    origin_address VARCHAR(500) NOT NULL,
    destination_address VARCHAR(500) NOT NULL,
    destination_city VARCHAR(100) NOT NULL,
    service_type ENUM('REG', 'EXPRESS') NOT NULL DEFAULT 'REG',
    weight_kg DECIMAL(10, 2) NOT NULL,
    shipping_cost DECIMAL(13, 2) NOT NULL,
    status ENUM('pending', 'picked_up', 'in_transit', 'delivered', 'cancelled')
        NOT NULL DEFAULT 'pending',
    notes VARCHAR(500) NOT NULL DEFAULT '',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_ship_customer
        FOREIGN KEY (customer_id) REFERENCES customers(id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_ship_courier
        FOREIGN KEY (courier_id) REFERENCES couriers(id)
        ON DELETE RESTRICT,

    INDEX idx_ship_status (status),
    INDEX idx_ship_created (created_at)
) ENGINE = InnoDB;

-- Riwayat status dari setiap paket
CREATE TABLE IF NOT EXISTS tracking_events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    shipment_id INT UNSIGNED NOT NULL,
    status ENUM('pending', 'picked_up', 'in_transit', 'delivered', 'cancelled')
        NOT NULL,
    description VARCHAR(500) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_event_ship
        FOREIGN KEY (shipment_id) REFERENCES shipments(id)
        ON DELETE CASCADE,

    INDEX idx_event_ship (shipment_id, created_at)
) ENGINE = InnoDB;

-- Depo/gudang, dikelola administrator.
CREATE TABLE IF NOT EXISTS depots (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    city VARCHAR(100) NOT NULL,
    address VARCHAR(500) NOT NULL,
    phone VARCHAR(30) NOT NULL DEFAULT '',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB;
