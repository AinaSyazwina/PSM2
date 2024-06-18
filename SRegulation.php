<?php
include 'navigaStu.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Regulations</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .header {
            background-color: #f4f4f4;
            text-align: center;
            padding: 40px 20px;
            margin-top: 60px; /* Adjust this if your navigation bar height is different */
        }
        .header h1 {
            margin: 0;
            font-size: 36px;
            font-weight: bold;
        }
        .header p {
            margin: 10px 0;
            font-size: 16px;
            color: #555;
        }
        .regulation-container {
            padding: 20px;
            max-width: 800px;
            margin: auto;
        }
        .regulation {
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #fafafa;
        }
        .regulation img {
            width: 100%;
            height: auto;
            border-radius: 5px;
        }
        .regulation .title {
            font-size: 18px;
            font-weight: bold;
            margin: 10px 0;
            cursor: pointer;
        }
        .regulation-content {
            display: none;
            margin-top: 10px;
        }
        .regulation-content p {
            margin: 0;
        }
    </style>
    <script>
        function toggleContent(id) {
            var content = document.getElementById(id);
            if (content.style.display === "none") {
                content.style.display = "block";
            } else {
                content.style.display = "none";
            }
        }
    </script>
</head>
<body>

<div class="header">
    <h1>Ethics and Compliance</h1>
    <p>Apple conducts business ethically, honestly, and in full compliance with the law. We believe that how we conduct ourselves is as critical to Apple's success as making the best products in the world. Our Compliance and Business Conduct policies are foundational to how we do business and how we put our values into practice every day.</p>
    <p><strong>"We do the right thing, even when it's not easy."</strong></p>
    <p><em>Tim Cook</em></p>
</div>

<div class="regulation-container">
    <div class="regulation">
        <img src="pic/IMG_7560-1024x768.jpg" alt="Library Image">
        <div class="title" onclick="toggleContent('regulation1')">Library Hours</div>
        <div class="regulation-content" id="regulation1">
            <p>The library is open from 8.30 am until 1.00 pm except on Fridays when it closes at 11.30 am.</p>
        </div>
    </div>
    <div class="regulation">
        <img src="pic/primary-school-2.webp" alt="Library Image">
        <div class="title" onclick="toggleContent('regulation2')">Returning Materials</div>
        <div class="regulation-content" id="regulation2">
            <p>Materials must be returned by the due date to avoid overdue fines, which may be incurred for late returns or damaged material.</p>
        </div>
    </div>
    <div class="regulation">
        <img src="pic/TP-System-for-Primary-School-students-post-1024x716.jpeg" alt="Library Image">
        <div class="title" onclick="toggleContent('regulation3')">Borrowing Privileges</div>
        <div class="regulation-content" id="regulation3">
            <p>Failure to comply with library regulations may result in suspension of borrowing privileges.</p>
        </div>
    </div>
    <div class="regulation">
        <img src="pic/unnamed.png" alt="Library Image">
        <div class="title" onclick="toggleContent('regulation4')">General Conduct</div>
        <div class="regulation-content" id="regulation4">
            <p>Please maintain silence to ensure a conducive reading environment for all students. Handle all books and materials with care.</p>
        </div>
    </div>
</div>

</body>
</html>
