CREATE DATABASE IF NOT EXISTS tracker;
USE tracker;

CREATE TABLE users (
                       id INT AUTO_INCREMENT PRIMARY KEY,
                       username VARCHAR(50) UNIQUE NOT NULL,
                       password VARCHAR(255) NOT NULL,
                       role ENUM('client', 'admin') DEFAULT 'client',
                       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                       updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE statuses (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          name VARCHAR(50) UNIQUE NOT NULL,
                          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                          updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE tags (
                      id INT AUTO_INCREMENT PRIMARY KEY,
                      name VARCHAR(50) UNIQUE NOT NULL,
                      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE tickets (
                         id INT AUTO_INCREMENT PRIMARY KEY,
                         user_id INT NOT NULL,
                         title VARCHAR(255) NOT NULL,
                         description TEXT NOT NULL,
                         status_id INT DEFAULT 1,
                         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                         updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                         FOREIGN KEY (user_id) REFERENCES users(id),
                         FOREIGN KEY (status_id) REFERENCES statuses(id)
);

CREATE TABLE ticket_tags (
                             id INT AUTO_INCREMENT PRIMARY KEY,
                             ticket_id INT NOT NULL,
                             tag_id INT NOT NULL,
                             FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
                             FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE,
                             UNIQUE KEY unique_ticket_tag (ticket_id, tag_id)
);

CREATE TABLE comments (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          ticket_id INT NOT NULL,
                          user_id INT NOT NULL,
                          message TEXT NOT NULL,
                          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                          FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
                          FOREIGN KEY (user_id) REFERENCES users(id)
);

INSERT INTO statuses (name) VALUES ('ToDo'), ('InProgress'), ('Ready For Review'), ('Done');

INSERT IGNORE INTO users (username, password, role) VALUES 
('admin', '$2y$10$gcA8jR1QiokjATS3rWfY0eqDZBgt5E08Un8EpG/DMRcTV7KzWtbxC', 'admin');
