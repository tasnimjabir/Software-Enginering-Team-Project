-- ============================================
-- MAT MEE - Bangladeshi Demo Data
-- ============================================

SET FOREIGN_KEY_CHECKS=0;

-- =====================
-- USERS
-- =====================
INSERT INTO users (name, email, password, email_verified, role) VALUES
('Tasnim Jabir', 'tasnim@gmail.com', '$2y$10$hashedpass1', 1, 'admin'),
('Rahim Uddin', 'rahimuddin@gmail.com', '$2y$10$hashedpass2', 1, 'user'),
('Nusrat Jahan', 'nusratjahan@gmail.com', '$2y$10$hashedpass3', 1, 'user'),
('Sabbir Hossain', 'sabbir.bd@gmail.com', '$2y$10$hashedpass4', 0, 'user'),
('Farhana Akter', 'farhanaakter@gmail.com', '$2y$10$hashedpass5', 1, 'user');

-- =====================
-- CATEGORIES
-- =====================
INSERT INTO categories (name, slug, image, description) VALUES
('Men Collection', 'men-collection', 'men.jpg', 'Stylish and comfortable men clothing collection'),
('Women Collection', 'women-collection', 'women.jpg', 'Elegant and trendy outfits for women'),
('Panjabi Collection', 'panjabi', 'panjabi.jpg', 'Premium Panjabi for festivals and events'),
('Kids Collection', 'kids-collection', 'kids.jpg', 'Cute and comfy outfits for kids');

-- =====================
-- SIZES
-- =====================
INSERT INTO sizes (category_id, size_name) VALUES
(1, 'S'), (1, 'M'), (1, 'L'), (1, 'XL'),
(2, 'S'), (2, 'M'), (2, 'L'),
(3, '38'), (3, '40'), (3, '42'), (3, '44'),
(4, '2-3Y'), (4, '4-5Y'), (4, '6-7Y');

-- =====================
-- PRODUCTS
-- =====================
INSERT INTO products (name, slug, description, price, category_id, main_image, views) VALUES
('Classic Black T-Shirt', 'classic-black-tshirt', 'Premium cotton black t-shirt for daily wear', 650.00, 1, 'black-tshirt.jpg', 120),
('Slim Fit Blue Jeans', 'slim-fit-blue-jeans', 'Comfortable slim fit jeans for men', 1450.00, 1, 'blue-jeans.jpg', 90),
('Floral Printed Three Piece', 'floral-three-piece', 'Beautiful floral printed three piece for women', 2850.00, 2, 'three-piece.jpg', 150),
('Premium White Panjabi', 'premium-white-panjabi', 'Elegant white panjabi for Eid and weddings', 1990.00, 3, 'white-panjabi.jpg', 200),
('Kids Cartoon T-Shirt', 'kids-cartoon-tshirt', 'Soft cotton cartoon printed t-shirt for kids', 550.00, 4, 'kids-shirt.jpg', 75);

-- =====================
-- PRODUCT IMAGES
-- =====================
INSERT INTO product_images (product_id, image) VALUES
(1, 'black1.jpg'),
(1, 'black2.jpg'),
(2, 'jeans1.jpg'),
(3, 'three1.jpg'),
(4, 'panjabi1.jpg'),
(5, 'kids1.jpg');

-- =====================
-- CART
-- =====================
INSERT INTO cart (user_id, session_id) VALUES
(2, 'sess_rahim_123'),
(3, 'sess_nusrat_456');

-- =====================
-- CART ITEMS
-- =====================
INSERT INTO cart_items (cart_id, product_id, size_id, quantity) VALUES
(1, 1, 2, 2),
(1, 2, 3, 1),
(2, 3, 6, 1);

-- =====================
-- ORDERS
-- =====================
INSERT INTO orders (user_id, session_id, total, shipping_charge, status, customer_name, customer_email, shipping_address, phone, notes) VALUES
(2, 'sess_rahim_123', 2750.00, 80.00, 'processing', 'Rahim Uddin', 'rahimuddin@gmail.com', 'House 12, Road 5, Mirpur DOHS, Dhaka', '01711223344', 'Call before delivery'),
(3, 'sess_nusrat_456', 2850.00, 100.00, 'shipped', 'Nusrat Jahan', 'nusratjahan@gmail.com', 'Flat 3B, Zindabazar, Sylhet', '01844556677', '');

-- =====================
-- ORDER ITEMS
-- =====================
INSERT INTO order_items (order_id, product_id, size_id, quantity, price_at_purchase) VALUES
(1, 1, 2, 2, 650.00),
(1, 2, 3, 1, 1450.00),
(2, 3, 6, 1, 2850.00);

-- =====================
-- METADATA
-- =====================
INSERT INTO metadata (name, value) VALUES
('site_name', 'MAT MEE'),
('currency', 'BDT'),
('contact_email', 'support@matmee.com'),
('phone', '01700000000'),
('facebook', 'https://facebook.com/matmee');

-- =====================
-- CAROUSEL
-- =====================
INSERT INTO carousels (image, big_text, small_text, button_text, button_link, sort_order, active) VALUES
('slider1.jpg', 'New Eid Collection 2026', 'Discover premium panjabi and three piece collection', 'Shop Now', '/shop', 1, 1),
('slider2.jpg', 'Flat 20% Off on Men Collection', 'Limited time offer. Grab yours today!', 'Explore', '/men-collection', 2, 1),
('slider3.jpg', 'Kids Fashion Fiesta', 'Colorful and comfortable outfits for kids', 'Buy Now', '/kids-collection', 3, 1);

SET FOREIGN_KEY_CHECKS=1;

-- ============================================
-- END OF MAT MEE DEMO DATA
-- ============================================