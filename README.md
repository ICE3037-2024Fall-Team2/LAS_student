# LAS_student
Lab reservation website for students.

## How to Run:
1. **Download XAMPP**:  
   - You can download XAMPP from [this link](https://www.apachefriends.org/).
   
2. **Clone the Repository**:  
   - Clone this repository into the `C:\xampp\htdocs\` folder.

3. **Start XAMPP**:
   - Open the XAMPP Control Panel.
   - Press **Start** for both **Apache** and **MySQL** services.

4. **Troubleshooting**:
   - If either **Apache** or **MySQL** does not start, check the ports they are using (often due to conflicts).
   - For **MySQL**, check if it's already running by looking in the **Task Manager**.
     - If MySQL is running, end the process and try starting it again.

5. **Access the Website**:
   - Once both services are running, open your browser and go to:
     ```
     http://localhost/<folder-name>
     ```
     Replace `<folder-name>` with the actual folder name where the repo is in the `htdocs` directory.



# LAS Database Structure and Example Data

This guide provides an overview of the database structure and example SQL commands for populating the tables.

## Table Structures

### 1. `labs`
This table contains information about the labs available for reservation.
```sql
CREATE TABLE `labs` (
  `lab_id` int(5) NOT NULL,
  `lab_name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `img_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### 2. `reservations`
This table stores information about lab reservations made by users.
```sql
CREATE TABLE `reservations` (
  `reservation_id` int(11) NOT NULL,
  `lab_id` int(11) NOT NULL,
  `user_id` varchar(10) NOT NULL,
  `date` date NOT NULL,
  `time` varchar(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### 3. `users`
This table stores user information, including their ID, username, and password.
```sql
CREATE TABLE `users` (
  `id` varchar(10) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### 4. `user_info`
This table stores additional information about the users, such as phone number, email, and profile photo.
```sql
CREATE TABLE `user_info` (
  `id` varchar(10) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `phonenumber` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

## Inserting Example Data

### 1. `labs` table:
```sql
INSERT INTO `labs` (`lab_id`, `lab_name`, `address`, `img_path`) VALUES
(33187, 'Physics Lab', 'Building 33', 'img/physics.jpg'),
(56123, 'Chemistry Lab', 'Building 56', 'img/chem.jpg'),
(66145, 'Biology Lab', 'Building 66', 'img/biology.jpg');
```

### 2. `reservations` table:
```sql
INSERT INTO `reservations` (`reservation_id`, `lab_id`, `user_id`, `date`, `time`) VALUES
(13, 33187, '2022315699', '2024-10-17', '10:00'),
(14, 33187, '2022315699', '2024-10-17', '11:00'),
(15, 33187, '2022315699', '2024-10-18', '12:00'),
(16, 56123, '2022315699', '2024-10-17', '13:00'),
(17, 56123, '2022315699', '2024-10-17', '14:00'),
(18, 56123, '2022315699', '2024-10-18', '15:00'),
(19, 66145, '2022315699', '2024-10-17', '16:00'),
(20, 66145, '2022315699', '2024-10-17', '17:00'),
(21, 66145, '2022315699', '2024-10-18', '18:00'),
(22, 33187, '2022312312', '2024-10-18', '11:00'),
(23, 33187, '2022315699', '2024-10-19', '11:00');
```

### 3. users table:
This inserts user records, including hashed passwords.
```sql
INSERT INTO `users` (`id`, `username`, `password`) VALUES
('2022312312', 'lee', '$2y$10$hetL5okks7UmXE88kjxbbupn7c.zExV9IouXqF/j/ABLrwqrzEwpy'),
('2022315699', 'igor', '$2y$10$S217uMA6SncHN4oofdt5peF3rM0O.3KTsRzieFBePPXVL335X2W7K');
```

### 4. `user_info` table:
```sql
INSERT INTO `user_info` (`id`, `username`, `phonenumber`, `email`, `photo_path`) VALUES
('2022123456', 'john', '1234567890', 'john@ex.com', 'img/photo.jpg');
```

## Indexes and Constraints

### Indexes for `labs`:
```sql
ALTER TABLE `labs`
  ADD PRIMARY KEY (`lab_id`);
```
### Indexes for `reservations`:
```sql
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `lab_id` (`lab_id`),
  ADD KEY `user_id` (`user_id`);
```
### Indexes for `users`:
```sql
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);
```
### Indexes for `user_info`:
```sql
ALTER TABLE `user_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`);
```

## Constraints
### Constraints for `reservations`:
```sql
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`lab_id`) REFERENCES `labs` (`lab_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
```
### Constraints for `user_info`:
```sql
ALTER TABLE `user_info`
  ADD CONSTRAINT `user_info_ibfk_1` FOREIGN KEY (`id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_info_ibfk_2` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE CASCADE;
```
### Auto-Increment for `reservations` table:
```sql
ALTER TABLE `reservations`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
```


