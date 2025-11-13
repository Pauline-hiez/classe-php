CREATE DATABASE classes CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE classes;

CREATE TABLE utilisateurs (
    id INT(11) NOT NULL AUTO_INCREMENT,
    login VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    firstname VARCHAR(50),
    lastname VARCHAR(50),
    PRIMARY KEy (id),
    UNIQUE (login),
    UNIQUE (email)
    );