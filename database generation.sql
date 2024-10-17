CREATE DATABASE las_db;

USE las_db;

CREATE TABLE `users` (
  `id` varchar(10) NOT NULL PRIMARY KEY,
  `username` varchar(50) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `user_info` (
  `id` varchar(10) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `phonenumber` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (id),         			 -- PK
  FOREIGN KEY (id) REFERENCES users(id) ON DELETE CASCADE,  
  FOREIGN KEY (username) REFERENCES users(username) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `labs` (
  `lab_id` varchar(5) NOT NULL PRIMARY KEY,
  `lab_name` varchar(255) NOT NULL UNIQUE,
  `address` varchar(255) NOT NULL,
  `img_path` varchar(255) DEFAULT NULL,
  `capacity` int NOT NULL CHECK (capacity > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `reservations` (
  `reservation_id` varchar(12) NOT NULL,
  `lab_id` varchar(5) NOT NULL,
  `user_id` varchar(10) NOT NULL,
  `date` date NOT NULL,
  `time` varchar(5) DEFAULT NULL,
  `verified` BOOLEAN NOT NULL DEFAULT FALSE,
  PRIMARY KEY (reservation_id),         			 -- PK
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,  
  FOREIGN KEY (lab_id) REFERENCES labs(lab_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `lab_stu` (
  `lab_id` varchar(5) NOT NULL,
  `user_id` varchar(10) NOT NULL,
  PRIMARY KEY (lab_id,user_id),         			 -- PK
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,  
  FOREIGN KEY (lab_id) REFERENCES labs(lab_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

