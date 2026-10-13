-- phpMyAdmin SQL Dump
-- version 5.2.1
-- Host: 127.0.0.1:3307
-- Database: defaultdb

USE defaultdb;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

START TRANSACTION;

-- --------------------------------------------------------
-- Table structure for table users
-- --------------------------------------------------------

CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    recovery_key VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) DEFAULT NULL,
    contact_email VARCHAR(255) DEFAULT NULL,
    gender VARCHAR(20) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    tag VARCHAR(20) DEFAULT 'normal',
    profile_photo VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO users
(id, username, password, recovery_key, full_name, contact_email, gender, bio, tag, profile_photo)
VALUES
(1, 'jirox_fx', '$2y$10$UQK1gKa42BVI/ggqhWynF.HuH2bsqn07fkBn/pKnnXQsaqnoVpOKO', '$2y$10$WfKNL/Lg.M/HJ2kxhojCJAOl.BXwZeOOXtpNmt2nC6XwdOHs4YbCMG', 'Jirox Fx', 'abc@gmail.com', 'male', 'yo', 'editor', NULL),
(2, 'Jeyasuriya', '$2y$10$hPa/6k3BRBLj6k3mF5cR2ur//Obi8xcHTwJyivBVxdB5B0y3QNj1m', '$2y$10$0.foEI3sgA56dKmzD9pBxeA2WrriAPVAvS1Wakc.agakSv2.GByhe', NULL, NULL, NULL, NULL, 'normal', NULL),
(3, 'suriya', '$2y$10$IdXLIdyHpz.cEN8cVMcN6ewAeRSKsp46daI1p/VAZ4XOQSMoOYi8a', '$2y$10$yLw.g00P5WH3U/.F/Tov3.XHEf7xyZRU41UjZMMTDLb1xknt457.O', NULL, NULL, NULL, NULL, 'normal', NULL);

-- --------------------------------------------------------
-- Table structure for table profile_reviews
-- --------------------------------------------------------

CREATE TABLE profile_reviews (
    id INT(11) NOT NULL AUTO_INCREMENT,
    profile_id INT(11) NOT NULL,
    reviewer_id INT(11) NOT NULL,
    rating INT(11) NOT NULL DEFAULT 5,
    comment TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    PRIMARY KEY (id),
    KEY profile_id (profile_id),
    KEY reviewer_id (reviewer_id),
    CONSTRAINT profile_reviews_ibfk_1
        FOREIGN KEY (profile_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT profile_reviews_ibfk_2
        FOREIGN KEY (reviewer_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table reviews
-- --------------------------------------------------------

CREATE TABLE reviews (
    id INT(11) NOT NULL AUTO_INCREMENT,
    target_type VARCHAR(20) NOT NULL,
    target_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    rating INT(11) NOT NULL DEFAULT 5,
    comment TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    PRIMARY KEY (id),
    KEY user_id (user_id),
    CONSTRAINT reviews_ibfk_1
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO reviews
(id, target_type, target_id, user_id, rating, comment, created_at)
VALUES
(1, 'material', 2, 1, 5, 'nicee', '2026-09-30 14:57:19'),
(2, 'service', 1, 1, 5, 'hmm', '2026-09-30 14:57:52'),
(3, 'service', 1, 2, 3, 's', '2026-09-30 14:59:45');

-- --------------------------------------------------------
-- Table structure for table social_links
-- --------------------------------------------------------

CREATE TABLE social_links (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    platform VARCHAR(50) NOT NULL,
    url VARCHAR(255) NOT NULL,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    CONSTRAINT social_links_ibfk_1
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table user_assets
-- --------------------------------------------------------

CREATE TABLE user_assets (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    title VARCHAR(150) NOT NULL,
    type VARCHAR(20) NOT NULL DEFAULT 'free',
    price DECIMAL(10,2) DEFAULT NULL,
    file_path VARCHAR(255) DEFAULT NULL,
    thumbnail VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    PRIMARY KEY (id),
    KEY user_id (user_id),
    CONSTRAINT user_assets_ibfk_1
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO user_assets
(id, user_id, title, type, price, file_path, thumbnail, created_at)
VALUES
(1, 1, 'vagabond', 'free', NULL, 'uploads/assets/asset_6abd22d1ba58b.jpg', 'uploads/assets/preview_6abd22d1ba6e2.jpg', '2026-09-30 14:55:13'),
(2, 1, 'gundam', 'paid', 106.00, 'uploads/assets/asset_6abd243a43a1a.jpg', 'uploads/assets/preview_6abd243a43f3b.jpg', '2026-09-30 15:01:14');

-- --------------------------------------------------------
-- Table structure for table user_materials
-- --------------------------------------------------------

CREATE TABLE user_materials (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    title VARCHAR(150) NOT NULL,
    category VARCHAR(50) DEFAULT 'PNG',
    file_path VARCHAR(255) DEFAULT NULL,
    thumbnail VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    PRIMARY KEY (id),
    KEY user_id (user_id),
    CONSTRAINT user_materials_ibfk_1
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO user_materials
(id, user_id, title, category, file_path, thumbnail, created_at)
VALUES
(1, 1, 'luffy', 'PNG', 'uploads/materials/material_6abd22976f903.jpg', 'uploads/materials/preview_6abd22976fe2b.jpg', '2026-09-30 14:54:15'),
(2, 1, 'luffy', 'PNG', 'uploads/materials/material_6abd22a502920.jpg', 'uploads/materials/preview_6abd22a50305b.jpg', '2026-09-30 14:54:29');

-- ---------------------------------------------------------
-- Table structure for table user_services
-- ---------------------------------------------------------

CREATE TABLE user_services (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT DEFAULT NULL,
    price DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    PRIMARY KEY (id),
    KEY user_id (user_id),
    CONSTRAINT user_services_ibfk_1
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO user_services
(id, user_id, title, description, price, created_at)
VALUES
(1, 1, 'long video', '', 69.00, '2026-09-30 14:54:54');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
