-- Airline Management System Database Schema

-- USERS TABLE
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    photo_path VARCHAR(255),
    phone VARCHAR(20),
    dob DATE
);

-- FLIGHTS TABLE
CREATE TABLE flights (
    flight_id INT AUTO_INCREMENT PRIMARY KEY,
    airline VARCHAR(50) NOT NULL,
    flight_number VARCHAR(20) NOT NULL,
    from_city VARCHAR(50) NOT NULL,
    to_city VARCHAR(50) NOT NULL,
    depart_date DATE NOT NULL,
    depart_time TIME NOT NULL,
    arrive_date DATE NOT NULL,
    arrive_time TIME NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    economy_seats INT NOT NULL,
    business_seats INT NOT NULL,
    first_seats INT NOT NULL
);

-- SEATS TABLE
CREATE TABLE seats (
    seat_id INT AUTO_INCREMENT PRIMARY KEY,
    flight_id INT NOT NULL,
    seat_number VARCHAR(10) NOT NULL,
    seat_class ENUM('Economy', 'Business', 'First') NOT NULL,
    is_booked BOOLEAN DEFAULT 0,
    FOREIGN KEY (flight_id) REFERENCES flights(flight_id)
);

-- BOOKINGS TABLE
CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    flight_id INT NOT NULL,
    seat_number VARCHAR(10) NOT NULL,
    seat_class ENUM('Economy', 'Business', 'First') NOT NULL,
    booking_price DECIMAL(10,2) NOT NULL,
    booking_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    payment_status ENUM('Pending', 'Completed', 'Failed') DEFAULT 'Pending',
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (flight_id) REFERENCES flights(flight_id)
);

-- SAMPLE FLIGHTS DATA
INSERT INTO flights (airline, flight_number, from_city, to_city, depart_date, depart_time, arrive_date, arrive_time, price, total_seats) VALUES
('SkyWay', 'SW101', 'New York', 'London', '2025-04-20', '09:00:00', '2025-04-20', '21:00:00', 450.00, 120),
('SkyWay', 'SW102', 'New York', 'London', '2025-04-21', '15:00:00', '2025-04-21', '03:00:00', 470.00, 120),
('JetFly', 'JF201', 'London', 'Tokyo', '2025-04-22', '10:30:00', '2025-04-22', '23:30:00', 700.00, 100),
('JetFly', 'JF202', 'London', 'Tokyo', '2025-04-23', '20:00:00', '2025-04-24', '09:00:00', 720.00, 100),
('AirGlobal', 'AG301', 'Tokyo', 'New York', '2025-04-24', '08:00:00', '2025-04-24', '20:00:00', 800.00, 110),
('AirGlobal', 'AG302', 'Tokyo', 'New York', '2025-04-25', '14:00:00', '2025-04-25', '02:00:00', 820.00, 110),
('SkyWay', 'SW103', 'London', 'New York', '2025-04-26', '12:00:00', '2025-04-26', '22:00:00', 460.00, 120),
('JetFly', 'JF203', 'New York', 'Tokyo', '2025-04-27', '18:00:00', '2025-04-28', '08:00:00', 750.00, 100),
('AirGlobal', 'AG303', 'London', 'New York', '2025-04-28', '11:00:00', '2025-04-28', '21:00:00', 480.00, 110);

-- SAMPLE SEATS DATA (for flights 1-3, 10 seats each)
INSERT INTO seats (flight_id, seat_number, is_booked) VALUES
(1, 'A1', 0), (1, 'A2', 0), (1, 'A3', 0), (1, 'A4', 0), (1, 'A5', 0), (1, 'A6', 0), (1, 'A7', 0), (1, 'A8', 0), (1, 'A9', 0), (1, 'A10', 0),
(2, 'A1', 0), (2, 'A2', 0), (2, 'A3', 0), (2, 'A4', 0), (2, 'A5', 0), (2, 'A6', 0), (2, 'A7', 0), (2, 'A8', 0), (2, 'A9', 0), (2, 'A10', 0),
(3, 'A1', 0), (3, 'A2', 0), (3, 'A3', 0), (3, 'A4', 0), (3, 'A5', 0), (3, 'A6', 0), (3, 'A7', 0), (3, 'A8', 0), (3, 'A9', 0), (3, 'A10', 0);
