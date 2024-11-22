CREATE DATABASE petsave_db1;

USE petsave_db1;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('user','admin') DEFAULT 'user'
);

CREATE TABLE pets_info (
    pet_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    breed VARCHAR(100) NOT NULL,
    bday DATE NOT NULL,
    vaccinated TINYINT(1) NOT NULL,
    image VARCHAR(255) NOT NULL,
    description text NULL,
    adopted tinyint(1) NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE adoption_applications (
    adopt_id INT AUTO_INCREMENT PRIMARY KEY,
    pet_id INT NOT NULL,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    id_image VARCHAR(100) NOT NULL,
    message TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    phone VARCHAR(15) NOT NULL,
    address VARCHAR(255) NOT NULL,
    occupation VARCHAR(100),
    previous_pet ENUM('yes', 'no') DEFAULT 'no',
    marital_status ENUM('single', 'married') NOT NULL;
    contact_access TINYINT(1) DEFAULT 0;
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (pet_id) REFERENCES pets_info(pet_id) ON DELETE CASCADE
);

CREATE TABLE contacts (
    contact_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    services VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    feedback_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL;
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE user_contacts (
    contact_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    phone VARCHAR(15) NULL,
    email VARCHAR(255) NULL,
    telegram VARCHAR(100) NULL,
    facebook VARCHAR(100) NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE adopted_pets (
    adopted_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pet_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    breed VARCHAR(100) NOT NULL,
    bday date NOT NULL,
    vaccinated TINYINT(1) NOT NULL,
    image VARCHAR(255) NOT NULL,
    adoption_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (pet_id) REFERENCES pets_info(pet_id) ON DELETE CASCADE
);

CREATE TABLE processed_application (
  id INT AUTO_INCREMENT PRIMARY KEY,
  adopt_id int NOT NULL,
  user_id int NOT NULL,
  pet_id int NOT NULL,
  status enum('approved','canceled') NOT NULL,
  name varchar(255)  DEFAULT NULL,
  email varchar(255)  DEFAULT NULL,
  phone varchar(255)  DEFAULT NULL,
  address text NULL,
  occupation varchar(255) NULL,
  previous_pet varchar(255) NULL,
  marital_status varchar(255) NULL,
  message text NULL,
  id_image varchar(255) NULL,
  processed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

