CREATE DATABASE mikrotik_logs;

USE mikrotik_logs;

CREATE TABLE pppoe_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_type ENUM('login','logout') NOT NULL,
    username VARCHAR(100) NOT NULL,
    ip_address VARCHAR(100) DEFAULT NULL,
    caller_id VARCHAR(100) DEFAULT NULL,
    uptime VARCHAR(100) DEFAULT NULL,
    service VARCHAR(50) DEFAULT NULL,
    last_disconnect_reason VARCHAR(255) DEFAULT NULL,
    last_logout VARCHAR(255) DEFAULT NULL,
    last_caller_id VARCHAR(255) DEFAULT NULL,
    active_client INT DEFAULT 0,
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
