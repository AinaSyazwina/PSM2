<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Nasyaz Company</title>
    <link rel="stylesheet" href="Cssfile/style3.css">
    <link rel="stylesheet" href="Cssfile/style4.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f8f8;
        }
        .contact-header {
            display: flex;
            align-items: center;
            background-color: #fff;
            height: 550px;
            background-image: url('pic/1303-e-business.png'); /* Background image */
            background-size: cover;
            background-position: center;
            position: relative;
            color: white;
            text-align: center;
            justify-content: center;
        }
        .contact-header h2 {
            font-size: 48px; 
            font-weight: bold;
            margin: 0;
        }
        .contact-header p {
            font-size: 20px; 
            margin: 0;
        }
        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5); 
        }
        .contact-header-content {
            position: relative;
            z-index: 1;
        }
        .contact-methods {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            padding: 40px 20px;
            background-color: #fff;
            margin: 0 65px; 
        }
        .contact-method {
            flex: 1 1 calc(33% - 40px);
            margin: 20px;
            max-width: calc(33% - 40px);
            text-align: left;
        }
        .contact-method h3 {
            font-size: 20px;
            margin-bottom: 10px;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            font-weight: normal;
        }
        .contact-method h3 i {
            margin-right: 10px;
            font-size: 24px;
        }
        .contact-method p {
            font-size: 16px;
            line-height: 1.6;
            color: #333;
        }
        .contact-method a {
            text-decoration: none;
            color: #000;
            display: block;
            margin-top: 10px;
            font-size: 16px;
            text-decoration: underline;
        }
        .contact-method a:hover {
            text-decoration: underline;
        }
        .contact-method:nth-child(3n+1) {
            margin-left: 0;
        }
        .contact-method:nth-child(3n) {
            margin-right: 0;
        }
    </style>
</head>
<body>
<?php include 'navigation.php'; ?>

<div class="contact-header">
    <div class="overlay"></div>
    <div class="contact-header-content">
    <h2>Contact Us</h2>
<p>If you need any assistance, our team is here to help with all your library-related inquiries: <br>
from finding books and resources to getting help with your admin account. <br>
 Feel free to contact us, we are here for you.</p>

    </div>
</div>

<div class="contact-methods">
    <div class="contact-method">
        <h3><i class="fas fa-phone"></i>Call Us</h3>
        <p>General Enquiries<br>8 a.m. - 5 p.m. from Monday to Thursday and 8 a.m. - 12 p.m. on Friday</p>
        <a href="tel:+60196002402">Tel. +60 19 600 2402</a>
    </div>
    <div class="contact-method">
        <h3><i class="fab fa-whatsapp"></i>WhatsApp Us</h3>
        <p>A Nasyaz employee will assist you on WhatsApp</p>
        <a href="http://Www.wassap.my/0196002402/Hello,Aina" target="_blank">Send a message</a>
    </div>
    <div class="contact-method">
        <h3><i class="fas fa-envelope"></i>E-mail Us</h3>
        <p>A Nasyaz employee will respond as soon as possible</p>
        <a href="mailto:nasyazz242@gmail.com">Send an e-mail</a>
    </div>
    <div class="contact-method">
        <h3><i class="fab fa-instagram"></i>Instagram</h3>
        <p>A Nasyaz ambassador will reply to you via Instagram Messenger</p>
        <a href="https://www.instagram.com/nasyaz.z?igsh=ZDNxc3kzZ3h1N3Yx&utm_source=qr" target="_blank">Send a message</a>
    </div>
    <div class="contact-method">
        <h3><i class="fab fa-telegram-plane"></i>Telegram</h3>
        <p>A Nasyaz employee will reply to you via Telegram</p>
        <a href="https://t.me/nasyazzz" target="_blank">Request help</a>
    </div>
    <div class="contact-method">
        <h3><i class="fab fa-facebook-messenger"></i>Message Us</h3>
        <p>A Nasyaz employee will reply to you via Facebook Messenger</p>
        <a href="https://www.facebook.com/share/Mituw1PmjfJcr1CC/?mibextid=LQQJ4d" target="_blank">Send a message</a>
    </div>
</div>

</body>
</html>
