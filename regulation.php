<?php
include 'navigation.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
        .section-container {
            padding: 20px;
            max-width: 1200px;
            margin: auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .section {
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
        .section .left-side {
            padding: 20px;
            width: 50%;
            position: relative;
            background-color: #2e2185;
        }
        .section .left-side h1 {
            font-size: 28px;
            margin-bottom: 10px;
            color: white; 
        }
        .section .left-side p {
            font-size: 16px;
            color: white; 
        }
        .section .right-side {
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
        .section .right-side img {
            width: 100%;
            height: auto;
            display: block;
        }
        .section .info {
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
            var section = document.getElementById(id);
            var img = section.querySelector('img');
            var info = section.querySelector('.info');
            var button = section.querySelector('.toggle-button');
            
            if (info.style.display === 'none') {
                img.style.display = 'none';
                info.style.display = 'block';
                button.textContent = '×';
                section.querySelector('.right-side').style.backgroundColor = '#F0F8FF'; 
            } else {
                img.style.display = 'block';
                info.style.display = 'none';
                button.textContent = '+';
                section.querySelector('.right-side').style.backgroundColor = '#2e2185'; 
            }
        }
    </script>
</head>
<body>

<div class="header">
    <h1>Regulations</h1>
    <p>Welcome to the SK Kamunting Library Admin Regulations.<br>Admins must adhere to the listed regulations to ensure efficient library<br> management.These guidelines are essential for maintaining a high standard of service<br> and operational excellence.</p>
</div>


<div class="section-container">
    <div class="section" id="section1">
        <div class="left-side">
            <h1>Manage Users</h1>
            <p>View and manage user accounts and privileges.</p>
            <button class="toggle-button" onclick="toggleContent('section1')">+</button>
        </div>
        <div class="right-side">
            <img src="pic/IMG_7560-1024x768.jpg" alt="Manage Users">
            <div class="info">
                <p>As an admin, you can add and edit user accounts. Ensure that each user has the appropriate level of access and that their details are up-to-date. This helps in maintaining a secure and well-organized library system.</p>
            </div>
        </div>
    </div>
    <div class="section" id="section2">
        <div class="left-side">
            <h1>Catalog Management</h1>
            <p>Maintain the library catalog.</p>
            <button class="toggle-button" onclick="toggleContent('section2')">+</button>
        </div>
        <div class="right-side">
            <img src="pic/primary-school-2.webp" alt="Catalog Management">
            <div class="info">
                <p>Update the catalog with new books, remove outdated entries, and ensure that all information is accurate. Proper catalog management ensures that users can easily find and access the resources they need.</p>
            </div>
        </div>
    </div>
    <div class="section" id="section3">
        <div class="left-side">
            <h1>Report Generation</h1>
            <p>Create reports on library usage and resources.</p>
            <button class="toggle-button" onclick="toggleContent('section3')">+</button>
        </div>
        <div class="right-side">
            <img src="pic/TP-System-for-Primary-School-students-post-1024x716.jpeg" alt="Report Generation">
            <div class="info">
                <p>Generate detailed reports on various aspects of library operations, such as book borrowing trends, user activity, and resource utilization. These reports help in making informed decisions to improve library services.</p>
            </div>
        </div>
    </div>
    <div class="section" id="section4">
        <div class="left-side">
            <h1>System Maintenance</h1>
            <p>Ensure the library system is up-to-date.</p>
            <button class="toggle-button" onclick="toggleContent('section4')">+</button>
        </div>
        <div class="right-side">
            <img src="pic/unnamed.png" alt="System Maintenance">
            <div class="info">
                <p>Regularly check for software updates, backups, and security patches. Maintaining the system ensures that the library operates smoothly without any technical interruptions.</p>
            </div>
        </div>
    </div>
    <div class="section" id="section5">
        <div class="left-side">
            <h1>Event Coordination</h1>
            <p>Organize and manage library events.</p>
            <button class="toggle-button" onclick="toggleContent('section5')">+</button>
        </div>
        <div class="right-side">
            <img src="pic/IMG_5623.jpg" alt="Event Coordination">
            <div class="info">
                <p>Plan and coordinate events, workshops, and other activities within the library. Ensure that all events run smoothly and are well-publicized to maximize participation and engagement.</p>
            </div>
        </div>
    </div>
    <div class="section" id="section6">
        <div class="left-side">
            <h1>Policy Enforcement</h1>
            <p>Implement library policies and regulations.</p>
            <button class="toggle-button" onclick="toggleContent('section6')">+</button>
        </div>
        <div class="right-side">
            <img src="pic/event6.jpg" alt="Policy Enforcement">
            <div class="info">
                <p>Ensure that all users adhere to the library's rules and policies. This includes managing fines for overdue materials, addressing misconduct, and maintaining a respectful and conducive environment for all users.</p>
            </div>
        </div>
    </div>
</div>

</body>
</html>
