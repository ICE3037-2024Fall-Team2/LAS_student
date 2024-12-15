-- MySQL Script generated by MySQL Workbench
-- Sun Dec 15 21:41:13 2024
-- Model: New Model    Version: 1.0
-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
-- -----------------------------------------------------
-- Schema las_db
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema las_db
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `las_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci ;
USE `las_db` ;

-- -----------------------------------------------------
-- Table `las_db`.`admin`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `las_db`.`admin` (
  `admin_id` VARCHAR(10) NOT NULL,
  `admin_name` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) NULL DEFAULT NULL,
  PRIMARY KEY (`admin_id`),
  UNIQUE INDEX `admin_name` (`admin_name` ASC) VISIBLE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `las_db`.`users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `las_db`.`users` (
  `id` VARCHAR(10) NOT NULL,
  `username` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `username` (`username` ASC) VISIBLE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `las_db`.`labs`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `las_db`.`labs` (
  `lab_id` VARCHAR(5) NOT NULL,
  `lab_name` VARCHAR(255) NOT NULL,
  `address` VARCHAR(255) NOT NULL,
  `img_path` VARCHAR(255) NULL DEFAULT NULL,
  `capacity` INT NOT NULL,
  PRIMARY KEY (`lab_id`),
  UNIQUE INDEX `lab_name` (`lab_name` ASC) VISIBLE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `las_db`.`lab_stu`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `las_db`.`lab_stu` (
  `lab_id` VARCHAR(5) NOT NULL,
  `user_id` VARCHAR(10) NOT NULL,
  PRIMARY KEY (`lab_id`, `user_id`),
  INDEX `user_id` (`user_id` ASC) VISIBLE,
  CONSTRAINT `lab_stu_ibfk_1`
    FOREIGN KEY (`user_id`)
    REFERENCES `las_db`.`users` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `lab_stu_ibfk_2`
    FOREIGN KEY (`lab_id`)
    REFERENCES `las_db`.`labs` (`lab_id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `las_db`.`rejected_messages`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `las_db`.`rejected_messages` (
  `reservation_id` VARCHAR(15) CHARACTER SET 'utf8mb4' NOT NULL,
  `rejected_message` VARCHAR(220) NULL DEFAULT NULL,
  PRIMARY KEY (`reservation_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `las_db`.`reservations`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `las_db`.`reservations` (
  `reservation_id` VARCHAR(15) NOT NULL,
  `lab_id` VARCHAR(5) NOT NULL,
  `user_id` VARCHAR(10) NOT NULL,
  `date` DATE NOT NULL,
  `time` VARCHAR(5) NULL DEFAULT NULL,
  `verified` TINYINT(1) NOT NULL DEFAULT '0',
  `checked` TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`reservation_id`),
  INDEX `user_id` (`user_id` ASC) VISIBLE,
  INDEX `lab_id` (`lab_id` ASC) VISIBLE,
  CONSTRAINT `reservations_ibfk_1`
    FOREIGN KEY (`user_id`)
    REFERENCES `las_db`.`users` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `reservations_ibfk_2`
    FOREIGN KEY (`lab_id`)
    REFERENCES `las_db`.`labs` (`lab_id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `las_db`.`user_img`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `las_db`.`user_img` (
  `id` VARCHAR(10) NOT NULL,
  `photo_path` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `user_img_ibfk_1`
    FOREIGN KEY (`id`)
    REFERENCES `las_db`.`users` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


-- -----------------------------------------------------
-- Table `las_db`.`user_info`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `las_db`.`user_info` (
  `id` VARCHAR(10) NOT NULL,
  `username` VARCHAR(50) NULL DEFAULT NULL,
  `phonenumber` VARCHAR(20) NULL DEFAULT NULL,
  `email` VARCHAR(100) NULL DEFAULT NULL,
  `photo_path` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `username` (`username` ASC) VISIBLE,
  CONSTRAINT `user_info_ibfk_1`
    FOREIGN KEY (`id`)
    REFERENCES `las_db`.`users` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `user_info_ibfk_2`
    FOREIGN KEY (`username`)
    REFERENCES `las_db`.`users` (`username`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
