-- Database for the PHP CRUD project.
-- Run it with:  mysql -u root -p < schema.sql

CREATE DATABASE IF NOT EXISTS crud CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE crud;

CREATE TABLE IF NOT EXISTS users
(
    id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name     VARCHAR(100) NOT NULL,
    username VARCHAR(30)  NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- password_hash() output, never the real password
    mail     VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS categories
(
    id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name    VARCHAR(100) NOT NULL,
    fk_user INT UNSIGNED NOT NULL,
    FOREIGN KEY (fk_user) REFERENCES users (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS posts
(
    id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name    VARCHAR(255) NOT NULL,
    text    TEXT         NOT NULL,
    image   VARCHAR(255) NOT NULL DEFAULT '',
    fk_user INT UNSIGNED NOT NULL,
    FOREIGN KEY (fk_user) REFERENCES users (id) ON DELETE CASCADE
);

-- Many-to-many: one post has many categories, one category has many posts.
CREATE TABLE IF NOT EXISTS categories_posts
(
    id_category INT UNSIGNED NOT NULL,
    id_post     INT UNSIGNED NOT NULL,
    PRIMARY KEY (id_category, id_post),
    FOREIGN KEY (id_category) REFERENCES categories (id) ON DELETE CASCADE,
    FOREIGN KEY (id_post) REFERENCES posts (id) ON DELETE CASCADE
);
