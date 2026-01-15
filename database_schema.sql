CREATE DATABASE IF NOT EXISTS travel_agency;
USE travel_agency;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    role ENUM('customer', 'agent', 'admin') NOT NULL DEFAULT 'customer',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Destinations
CREATE TABLE IF NOT EXISTS destinations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image_url VARCHAR(500)
);

-- Tour Packages
CREATE TABLE IF NOT EXISTS packages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    agent_id INT NOT NULL,
    destination_id INT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    status ENUM('available', 'soldout', 'inactive') DEFAULT 'available',
    image_url VARCHAR(500),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE SET NULL
);

-- Services (Flights, Hotels)
CREATE TABLE IF NOT EXISTS services (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type ENUM('flight', 'hotel') NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(500)
);

-- Bookings
CREATE TABLE IF NOT EXISTS bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    package_id INT,
    service_id INT,
    travel_date DATE,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE SET NULL,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL
);

-- Payments
CREATE TABLE IF NOT EXISTS payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    status ENUM('success', 'failed') DEFAULT 'success',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

-- System Reports
CREATE TABLE IF NOT EXISTS reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    type VARCHAR(50),
    content TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id)
);

-- Default Admin User (password: admin123)
INSERT INTO users (email, password, full_name, role) VALUES 
('admin@travel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Admin', 'admin');

-- Default Agent (password: agent123)
INSERT INTO users (email, password, full_name, role) VALUES 
('agent@travel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Travel Expert', 'agent');

-- Sample Destinations (Countries)
INSERT INTO destinations (name, description, image_url) VALUES
('Paris, France', 'The city of lights and love', 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=800'),
('Tokyo, Japan', 'A blend of traditional and modern', 'https://images.unsplash.com/photo-1540959733332-eab4deabeeaf?w=800'),
('Bali, Indonesia', 'Tropical paradise with beautiful beaches', 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?w=800'),
('New York, USA', 'The city that never sleeps', 'https://images.unsplash.com/photo-1496442226666-8d4d0e62e6e9?w=800'),
('London, UK', 'Historic landmarks and royal heritage', 'https://images.unsplash.com/photo-1513635269975-59663e0ac1ad?w=800'),
('Dubai, UAE', 'Luxury shopping and ultramodern architecture', 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?w=800'),
('Sydney, Australia', 'Stunning harbour and iconic Opera House', 'https://images.unsplash.com/photo-1506973035872-a4ec16b8e8d9?w=800'),
('Rome, Italy', 'Ancient ruins and world-class cuisine', 'https://images.unsplash.com/photo-1552832230-c0197dd311b5?w=800'),
('Cape Town, South Africa', 'Breathtaking mountains and beaches', 'https://images.unsplash.com/photo-1580060839134-75a5edca2e99?w=800'),
('Rio de Janeiro, Brazil', 'Carnival, beaches, and Christ the Redeemer', 'https://images.unsplash.com/photo-1483729558449-99ef09a8c325?w=800');

-- Mock Tour Packages (agent_id = 2 refers to agent@travel.com)
INSERT INTO packages (agent_id, destination_id, name, description, price, status, image_url) VALUES
(2, 1, 'Romantic Paris Getaway', '5-night stay in Paris with Eiffel Tower dinner and Louvre tour', 2499.00, 'available', 'https://images.unsplash.com/photo-1499856871958-5b9627545d1a?w=800'),
(2, 2, 'Tokyo Adventure Package', '7-day Tokyo experience with temples, sushi class, and Mt. Fuji trip', 3299.00, 'available', 'https://images.unsplash.com/photo-1503899036084-c55cdd92da26?w=800'),
(2, 3, 'Bali Wellness Retreat', '6-night spa and yoga retreat in tropical Bali', 1899.00, 'available', 'https://images.unsplash.com/photo-1573790387438-4da905039392?w=800'),
(2, 4, 'New York City Explorer', '4-night NYC package with Broadway show and Statue of Liberty', 2199.00, 'available', 'https://images.unsplash.com/photo-1485871981521-5b1fd3805eee?w=800'),
(2, 5, 'Royal London Experience', '5-night London stay with palace tours and afternoon tea', 2599.00, 'available', 'https://images.unsplash.com/photo-1529655683826-aba9b3e77383?w=800'),
(2, 6, 'Dubai Luxury Escape', '4-night luxury Dubai with desert safari and Burj tour', 3499.00, 'available', 'https://images.unsplash.com/photo-1518684079-3c830dcef090?w=800'),
(2, 7, 'Sydney Harbour Adventure', '6-night Sydney with Opera House and Great Barrier Reef', 2899.00, 'available', 'https://images.unsplash.com/photo-1624138784614-87fd1b6528f8?w=800'),
(2, 8, 'Ancient Rome Discovery', '5-night Rome tour with Colosseum, Vatican, and cooking class', 2299.00, 'available', 'https://images.unsplash.com/photo-1515542622106-78bda8ba0e5b?w=800'),
(2, 9, 'Cape Town Safari & Wine', '7-night Cape Town with safari, wine tasting, and Table Mountain', 2799.00, 'available', 'https://images.unsplash.com/photo-1576485375217-d6a95e34d043?w=800'),
(2, 10, 'Rio Carnival Experience', '6-night Rio package during Carnival with beach and Sugarloaf', 2699.00, 'available', 'https://images.unsplash.com/photo-1516306580123-e6e52b1b7b5f?w=800');

-- Mock Flights (Services)
INSERT INTO services (type, name, description, price, image_url) VALUES
('flight', 'New York → Paris', 'Direct flight from JFK to Charles de Gaulle, 7h 30m', 649.00, 'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=800'),
('flight', 'Paris → Tokyo', 'Flight Paris CDG to Tokyo Narita with 1 stop, 14h', 899.00, 'https://images.unsplash.com/photo-1569629743817-70d8db6c323b?w=800'),
('flight', 'London → Dubai', 'Direct flight Heathrow to Dubai International, 7h', 549.00, 'https://images.unsplash.com/photo-1529074963764-98f45c47344b?w=800'),
('flight', 'Tokyo → Sydney', 'Flight from Narita to Sydney Kingsford Smith, 9h 30m', 799.00, 'https://images.unsplash.com/photo-1570710891163-6d3b5c47248b?w=800'),
('flight', 'Dubai → Cape Town', 'Flight Dubai to Cape Town with 1 stop, 12h', 699.00, 'https://images.unsplash.com/photo-1464037866556-6812c9d1c72e?w=800'),
('flight', 'Sydney → Bali', 'Direct flight Sydney to Bali Ngurah Rai, 6h', 399.00, 'https://images.unsplash.com/photo-1559268950-2d7ceb2efa3a?w=800'),
('flight', 'Rome → London', 'Direct flight Fiumicino to Heathrow, 2h 30m', 199.00, 'https://images.unsplash.com/photo-1556388158-158ea5ccacbd?w=800'),
('flight', 'Cape Town → Rio', 'Flight Cape Town to Rio Galeao with 1 stop, 11h', 849.00, 'https://images.unsplash.com/photo-1474302770737-173ee21bab63?w=800'),
('flight', 'Rio → New York', 'Direct flight Rio to JFK, 10h', 599.00, 'https://images.unsplash.com/photo-1517479149777-5f3b1511d5ad?w=800'),
('flight', 'Bali → Tokyo', 'Flight Bali to Tokyo Narita with 1 stop, 9h', 449.00, 'https://images.unsplash.com/photo-1542296332-2e4473faf563?w=800');

-- Mock Hotels (Services)
INSERT INTO services (type, name, description, price, image_url) VALUES
('hotel', 'Le Grand Paris Hotel', '5-star luxury hotel near Champs-Élysées, per night', 350.00, 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800'),
('hotel', 'Tokyo Sakura Inn', 'Traditional ryokan in Shinjuku, per night', 280.00, 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=800'),
('hotel', 'Bali Beach Resort', 'Beachfront villa in Seminyak, per night', 180.00, 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=800'),
('hotel', 'Manhattan Skyline Hotel', 'Midtown hotel with Central Park views, per night', 420.00, 'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?w=800'),
('hotel', 'London Royal Suites', 'Historic hotel near Buckingham Palace, per night', 380.00, 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=800'),
('hotel', 'Dubai Palm Resort', 'Luxury resort on Palm Jumeirah, per night', 550.00, 'https://images.unsplash.com/photo-1582719508461-905c673771fd?w=800');
