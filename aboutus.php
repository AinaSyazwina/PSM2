<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Life at Nasyaz Company</title>
    <link rel="stylesheet" href="Cssfile/style3.css">
    <link rel="stylesheet" href="Cssfile/style4.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f8f8;
        }
        .header-video-container {
            position: relative;
            width: 100%;
            height: 75vh; /* Adjust the height as needed */
            overflow: hidden;
        }
        .header-video-container video {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: translate(-50%, -50%);
        }
        .header-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            text-align: center;
            padding: 20px;
        }
        .title-section {
            text-align: center;
            padding: 60px 20px;
            background-color: #fff;
            margin: 20px 0;
        }
        .title-section h2 {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .title-section p {
            font-size: 18px;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: auto;
        }
        .custom-hr {
            border: 0;
            height: 1px;
            background: #333;
            margin: 40px 0;
        }
        .values-section {
            text-align: center;
            padding: 60px 20px;
            background-color: #fff;
            margin: 20px 0;
        }
        .values-section h1 {
            margin-bottom: 40px;
        }
        .values {
            display: flex;
            justify-content: space-around;
            margin: 40px 0;
        }
        .value {
            flex: 1;
            margin: 0 20px;
            max-width: 250px;
        }
        .value h3 {
            font-size: 24px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .value p {
            font-size: 16px;
            line-height: 1.6;
            color: #333;
        }
        .about-company {
            display: flex;
            align-items: center;
            padding: 60px 20px;
            background-color: #fff;
            margin: 20px 0;
        }
        .about-company img {
            max-width: 50%;
            border-radius: 10px;
        }
        .about-company-text {
            padding: 20px;
            max-width: 50%;
        }
        .about-company-text h2 {
            font-size: 36px;
            margin-bottom: 20px;
        }
        .about-company-text p {
            font-size: 18px;
            line-height: 1.6;
            color: #333;
        }
        .about-company-text .download-button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #000;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
<?php include 'navigation.php'; ?>

<div class="header-video-container">
    <video autoplay muted loop>
        <source src="pic/3205624-hd_1920_1080_25fps.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>
    <div class="header-content">
        <h1>Life at Nasyaz Company</h1>
        <p>Embrace a culture of respect, curiosity, and generosity.</p>
    </div>
</div>

<div class="title-section">
    <h2>Life at Nasyaz Company</h2>
    <p>As a new company, Nasyaz is quickly building a reputation for excellence by embracing a culture of respect, curiosity, and generosity. Committed to innovation and continuous improvement, we strive to stay ahead in a rapidly evolving industry. By fostering a collaborative and inclusive environment, we empower our employees to achieve their full potential and contribute to our shared success.</p>
</div>


<hr class="custom-hr">

<div class="values-section">
    <h1>Our Vision & Mission</h1>
    <div class="values">
        <div class="value">
            <h3>Curiosity</h3>
            <p>Curiosity drove our founders and has been at the heart of Nasyaz since the very beginning.</p>
        </div>
        <div class="value">
            <h3>Open-Mindedness</h3>
            <p>Free-spirited and in tune with the contemporary world, Nasyaz pushes the boundaries of creativity and taste.</p>
        </div>
        <div class="value">
            <h3>Generosity</h3>
            <p>Generosity, and in turn, philanthropy, embody the kind of impact we want to have on the world.
            </p>
        </div>
        <div class="value">
            <h3>Sharing</h3>
            <p>We foster a welcoming and collaborative style: always gracious, warm, and considerate towards individuals and communities.</p>
        </div>
    </div>
</div>

<hr class="custom-hr">

<div class="about-company">
    <div class="about-company-text">
        <h2>About Nasyaz Company</h2>
        <p>Nasyaz Company is based in Taiping, Perak, Malaysia. We are dedicated to excellence in every aspect of our work, from innovation and creativity to philanthropy and community engagement. Our commitment to our values drives us to push boundaries and create a lasting impact.</p>
        <a href="#" class="download-button">Read More</a>
    </div>
    <img src="pic/corporate-meeting_30308a0fc5.jpg" alt="Corporate Meeting">
</div>

</body>
</html>
