<?php 
$page_title = 'Contact Us';
require_once 'components/config-page.php';

$message_status = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    
    $sql = "INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)";
    
    // $conn is a DatabaseConnection instance from components/config-page.php
    $inserted = $conn->execute($sql, [$name, $email, $phone, $subject, $message]);
    
    if ($inserted !== false) {
        $message_status = '<div class="alert alert-success" style="padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px; color: #155724; background-color: #d4edda; border-color: #c3e6cb;">Thank you for your message! We will get back to you as soon as possible.</div>';
    } else {
        $message_status = '<div class="alert alert-danger" style="padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px; color: #721c24; background-color: #f8d7da; border-color: #f5c6cb;">Error saving message. Please try again.</div>';
    }
}
?>
<link rel="stylesheet" href="asset/css/about-contact.css">
<body>

    <!-- Contact Content -->
    <section class="contact-content">
        <div class="container">
            <!-- Section Header -->
            <div class="section-header">
                <h2>Contact Information</h2>
                <p>Reach out to us through any of these channels</p>
            </div>

            <!-- Contact Grid -->
            <div class="contact-grid">
                <!-- Contact Info -->
                <div class="contact-info">
                    <!-- Location -->
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <div class="contact-details">
                            <h4>Our Location</h4>
                            <p>২য় তলা (চলন্ত সিড়ি থেকে হাতের ডানে)<br>
                               দোকান নং- ২৪৬, ২৪৭৪<br>
                               জাহাজ কোম্পানি শপিং কমপ্লেক্স<br>
                               রংপুর, বাংলাদেশ</p>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="bi bi-envelope-fill"></i>
                        </div>
                        <div class="contact-details">
                            <h4>Email Address</h4>
                            <p><a href="mailto:matmee.official@gmail.com">matmee.official@gmail.com</a></p>
                            <p style="margin-top: 0.5rem; font-size: 0.9rem;">We typically respond within 24 hours</p>
                        </div>
                    </div>

                    <!-- Phone -->
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="bi bi-telephone-fill"></i>
                        </div>
                        <div class="contact-details">
                            <h4>Phone Number</h4>
                            <p><a href="tel:01746590658">01746590658</a></p>
                            <p style="margin-top: 0.5rem; font-size: 0.9rem;">Available Monday - Saturday, 9am - 6pm</p>
                        </div>
                    </div>

                    <!-- WhatsApp -->
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="bi bi-chat-dots-fill" style="color: #25d366;"></i>
                        </div>
                        <div class="contact-details">
                            <h4>WhatsApp</h4>
                            <p><a href="https://wa.me/01746590658" target="_blank">Chat with us on WhatsApp</a></p>
                            <p style="margin-top: 0.5rem; font-size: 0.9rem;">Instant messaging available 24/7</p>
                        </div>
                    </div>

                    <!-- Social Media -->
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="bi bi-facebook"></i>
                        </div>
                        <div class="contact-details">
                            <h4>Follow Us</h4>
                            <p><a href="https://fb.com/matmeerangpur" target="_blank">Facebook: fb.com/matmeerangpur</a></p>
                            <p style="margin-top: 0.5rem; font-size: 0.9rem;">Follow for updates and special offers</p>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="contact-form">
                    <h3>Send us a Message</h3>
                    <?php if($message_status) echo $message_status; ?>
                    <form id="contactForm" action="" method="POST">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" placeholder="your.email@example.com" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" placeholder="Your phone number" required>
                        </div>

                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" placeholder="Message subject" required>
                        </div>

                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" placeholder="Write your message here..." required></textarea>
                        </div>

                        <button type="submit" name="submit_contact" class="submit-btn" style="border: none; cursor: pointer;">
                            <i class="bi bi-send"></i>
                            Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="about-content" style="background: #f9fafb;">
        <div class="container">
            <div class="section-header">
                <h2>Frequently Asked Questions</h2>
                <p>Find answers to common questions</p>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div style="padding: 1.5rem; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); border-left: 4px solid var(--primary-color);">
                        <h5 style="color: var(--primary-color); font-weight: 700; margin-bottom: 0.75rem;">What are your delivery times?</h5>
                        <p style="color: var(--text-light); margin: 0;">We offer same-day and next-day delivery options in Rangpur. Delivery times may vary for other areas. Contact us for specific details.</p>
                    </div>
                </div>


                <div class="col-lg-6 mb-4">
                    <div style="padding: 1.5rem; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); border-left: 4px solid var(--primary-color);">
                        <h5 style="color: var(--primary-color); font-weight: 700; margin-bottom: 0.75rem;">Do you offer exchanges?</h5>
                        <p style="color: var(--text-light); margin: 0;">Yes! If an item doesn't fit or you're not satisfied, we offer exchanges within 7 days of purchase. Conditions apply.</p>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div style="padding: 1.5rem; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); border-left: 4px solid var(--primary-color);">
                        <h5 style="color: var(--primary-color); font-weight: 700; margin-bottom: 0.75rem;">What payment methods do you accept?</h5>
                        <p style="color: var(--text-light); margin: 0;">We accept cash on delivery, bKash, Nagad, Rocket, and bank transfers. Choose the payment method that's most convenient for you.</p>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div style="padding: 1.5rem; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); border-left: 4px solid var(--primary-color);">
                        <h5 style="color: var(--primary-color); font-weight: 700; margin-bottom: 0.75rem;">How can I track my order?</h5>
                        <p style="color: var(--text-light); margin: 0;">After placing your order, you'll receive a confirmation email with tracking details. You can call us anytime for order updates.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section (Optional) -->
    <section class="about-content">
        <div class="container">
            <div class="section-header">
                <h2>Visit Our Store</h2>
                <p>Located in the heart of Rangpur</p>
            </div>

            <div style="width: 100%; height: 400px; background: linear-gradient(135deg, rgba(128, 0, 0, 0.1) 0%, rgba(153, 27, 27, 0.1) 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);">
                <div style="text-align: center;">
                    <i class="bi bi-geo-alt" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                    <p style="color: var(--text-light); font-size: 1.1rem;">
                        Jahaj Company Shopping Complex, Rangpur<br>
                        <small style="font-size: 0.9rem;">Open Monday - Saturday, 9am - 7pm</small>
                    </p>
                </div>
            </div>
        </div>
    </section>

</body>

<?php require_once 'components/page_close.php'; ?>
