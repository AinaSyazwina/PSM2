<?php
include 'navigaLib.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Regulations</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&family=Ubuntu:wght@300;400;500;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f8f8;
        }
        .header {
            background-color: #F0F8FF;
            text-align: center;
            padding: 60px 20px;
            margin-top: 60px; 
        }
        .header h1 {
            margin: 0;
            font-size: 48px;
            font-weight: bold;
        }
        .header p {
            margin: 20px 0;
            font-size: 18px;
            color: #555;
        }
        .header .quote {
            margin-top: 20px;
            font-size: 20px;
            color: #333;
            font-style: italic;
            font-weight: bold;
        }
        .header .author {
            margin-top: 5px;
            font-size: 16px;
            color: #333;
            font-style: normal;
            font-weight: normal;
        }
        .regulation-container {
            padding: 20px;
            max-width: 1200px;
            margin: auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .regulation {
            display: flex;
            justify-content: space-between;
            background-color: #2e2185;
            border-radius: 10px;
            overflow: hidden;
            width: 100%;
            max-width: 900px; 
            margin: auto;
            transition: height 0.3s ease-in-out, background-color 0.3s ease-in-out;
        }
        .regulation .left-side {
            padding: 20px;
            width: 50%;
            position: relative;
            background-color: #2e2185;
        }
        .regulation .left-side h1 {
            font-size: 28px;
            margin-bottom: 10px;
            color: white; 
        }
        .regulation .left-side p {
            font-size: 16px;
            color: white; 
        }
        .regulation .right-side {
            width: 50%;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            transition: all 0.3s ease-in-out;
            height: 300px; 
            background-color: #2e2185; 
        }
        .regulation .right-side img {
            width: 100%;
            height: auto;
            display: block;
        }
        .regulation .info {
            padding: 20px;
            display: none;
            height: 100%; 
            overflow-y: auto; 
            color: black; 
        }
        .toggle-button {
            position: absolute;
            bottom: 10px;
            left: 10px;
            background-color: #fff;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            font-size: 24px;
            line-height: 30px;
            text-align: center;
            cursor: pointer;
            outline: none;
            transition: background-color 0.3s;
        }
        .toggle-button:hover {
            background-color: #ddd;
        }
    </style>
    <script>
        function toggleContent(id) {
            var regulation = document.getElementById(id);
            var img = regulation.querySelector('img');
            var info = regulation.querySelector('.info');
            var button = regulation.querySelector('.toggle-button');
            
            if (info.style.display === 'none') {
                img.style.display = 'none';
                info.style.display = 'block';
                button.textContent = '×';
                regulation.querySelector('.right-side').style.backgroundColor = '#F0F8FF'; 
            } else {
                img.style.display = 'block';
                info.style.display = 'none';
                button.textContent = '+';
                regulation.querySelector('.right-side').style.backgroundColor = '#2e2185'; 
            }
        }
    </script>
</head>
<body>

<div class="header">
    <h1>Library Regulations</h1>
    <p>Welcome to the SK Kamunting Library. We are committed to maintaining an environment of integrity, honesty, and respect.<br>
    Our policies and guidelines are designed to ensure a smooth and enjoyable experience for all users,<br>
    reflecting our core values and commitment to ethical conduct.</p>
    <p class="quote">"A library is not a luxury but one of the necessities of life."<br>
    <span class="author">Henry Ward Beecher</span></p>
</div>

<div class="regulation-container">
    <div class="regulation" id="regulation1">
        <div class="left-side">
            <h1>Library Hours</h1>
            <p>Our library is open during the following hours:</p>
            <button class="toggle-button" onclick="toggleContent('regulation1')">+</button>
        </div>
        <div class="right-side">
            <img src="pic/IMG_7560-1024x768.jpg" alt="Library Hours">
            <div class="info">
              
            <p>The library is open from 8:30 AM until 1:00 PM, providing ample time for students and faculty to utilize our resources, study spaces, and services. However, please note that on Fridays, the library closes earlier at 11:30 AM to accommodate special schedules and events. We encourage all users to plan their visits accordingly to make the most of the library's offerings within these hours.</p>
            </div>
        </div>
    </div>
    <div class="regulation" id="regulation2">
        <div class="left-side">
            <h1>Returning Materials</h1>
            <p>Please ensure timely return of materials.</p>
            <button class="toggle-button" onclick="toggleContent('regulation2')">+</button>
        </div>
        <div class="right-side">
            <img src="pic/primary-school-2.webp" alt="Returning Materials">
            <div class="info">
               
                <p>Materials must be returned by the due date to avoid overdue fines, which may be incurred for late returns or damaged material.</p>
            </div>
        </div>
    </div>
    <div class="regulation" id="regulation3">
        <div class="left-side">
            <h1>Borrowing Privileges</h1>
            <p>Adhere to borrowing guidelines.</p>
            <button class="toggle-button" onclick="toggleContent('regulation3')">+</button>
        </div>
        <div class="right-side">
            <img src="pic/TP-System-for-Primary-School-students-post-1024x716.jpeg" alt="Borrowing Privileges">
            <div class="info">
                
                <p>Failure to comply with library regulations may result in suspension of borrowing privileges.</p>
            </div>
        </div>
    </div>
    <div class="regulation" id="regulation4">
        <div class="left-side">
            <h1>General Conduct</h1>
            <p>Maintain a respectful environment.</p>
            <button class="toggle-button" onclick="toggleContent('regulation4')">+</button>
        </div>
        <div class="right-side">
            <img src="pic/unnamed.png" alt="General Conduct">
            <div class="info">
               
                <p>Please maintain silence to ensure a conducive reading environment for all students. Handle all books and materials with care.</p>
            </div>
        </div>
    </div>
    <div class="regulation" id="regulation5">
    <div class="left-side">
        <h1>Guidelines for Reviews and Quotes</h1>
        <p>Ensuring respectful and appropriate communication.</p>
        <button class="toggle-button" onclick="toggleContent('regulation5')">+</button>
    </div>
    <div class="right-side">
        <img src="pic/IMG_5623.jpg" alt="Guidelines for Reviews and Quotes">
        <div class="info">
            
            <p>Students must use respectful and appropriate language in reviews and quotes, avoiding offensive or discriminatory remarks. Failure to comply may result in warnings, temporary suspension of library privileges, or other disciplinary actions. We appreciate your cooperation in fostering a positive environment for all users..</p>
        </div>
    </div>
</div>

    <div class="regulation" id="regulation6">
        <div class="left-side">
            <h1>Library Events and Activities</h1>
            <p>Participation guidelines for events.</p>
            <button class="toggle-button" onclick="toggleContent('regulation6')">+</button>
        </div>
        <div class="right-side">
            <img src="pic/event6.jpg" alt="Library Events and Activities">
            <div class="info">
              
                <p>The library hosts various events and activities to engage the community. Participation in these events is encouraged, and attendees are expected to follow the library's code of conduct during these events.</p>
            </div>
        </div>
    </div>
</div>

</body>
</html>
