<?php
include 'navigaLib.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Did You Know</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&family=Ubuntu:wght@300;400;500;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f8f8;
        }
        .header {
            background-image: url('pic/gd-group-discussion-tips.jpg');
            background-size: cover;
            background-position: center;
            text-align: center;
            color: white;
            padding: 250px 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 48px;
            font-weight: bold;
        }
        .header p {
            margin: 20px 0;
            font-size: 24px;
        }
        .sub-header {
            padding: 180px 20px;
            text-align: center;
        }
        .sub-header p {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            max-width: 800px;
            margin: auto;
        }
        .fact-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            max-width: 600px;
            margin: auto;
        }
        .fact-card {
            border-radius:20px;
            overflow: hidden;
            text-align: center;
            padding: 0;
            transition: background-color 0.3s;
            position: relative;
        }
        .fact-card:nth-child(1) {
            background-color: #6495ED;
        }
        .fact-card:nth-child(2) {
            background-color: #6699CC;
        }
        .fact-card:nth-child(3) {
            background-color: #00308F;
        }
        .fact-card:nth-child(4) {
            background-color: #00BFFF;
        }
        .fact-card:nth-child(5) {
            background-color: #0066b2;
        }
        .fact-card img {
            width: 100%;
            height: auto;
            border-radius: 10px 10px 0 0;
        }
        .fact-card-content {
            padding: 20px;
        }
        .fact-card h1 {
            font-size: 24px;
            color: white;
            margin-bottom: 10px;
        }
        .fact-card p {
            font-size: 18px;
            color: white;
            margin-bottom: 20px;
        }
        .fact-card button {
            background-color: white;
            color: black;
            border: none;
            border-radius: 20px;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        .fact-card button:hover {
            background-color: #ddd;
        }
        .fact-popup {
            display: none;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-width: 90%;
            z-index: 1000;
        }
        .fact-popup h2 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .fact-popup p {
            font-size: 16px;
        }
        .fact-popup .close-button {
            background-color: red;
            color: white;
            border: none;
            border-radius: 20px;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 16px;
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
    </style>
    <script>
        function showPopup(id) {
            document.getElementById(id).style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
        }

        function closePopup(id) {
            document.getElementById(id).style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }
    </script>
</head>
<body>

<div class="header">
    <h1>Did You Know?</h1>
    <p>Fun facts and trivia about books, authors, and the library.</p>
</div>

<div class="sub-header">
    <p>Greater access to education gives everyone more ways to realize their potential. That’s why SK Kamunting is committed to providing people around the world with more opportunities to learn through partnerships in over 100 countries and regions.</p>
</div>

<div class="fact-container">
    <div class="fact-card">
        <img src="pic/Library-of-Alexandria-Full-of-Books-1024x574.png" alt="Library of Alexandria">
        <div class="fact-card-content">
            <h1>Library of Alexandria</h1>
            <p>The Library of Alexandria was the largest library in the ancient world.</p>
            <button onclick="showPopup('popup1')">Read more</button>
        </div>
    </div>
    <div class="fact-card">
        <img src="pic/000_II637-1024x640.jpg" alt="Al-Qarawiyyin Library">
        <div class="fact-card-content">
            <h1>Al-Qarawiyyin Library</h1>
            <p>The world’s oldest library in continuous operation is in Morocco.</p>
            <button onclick="showPopup('popup2')">Read more</button>
        </div>
    </div>
    <div class="fact-card">
        <img src="pic/visit-our-buildings.jpg" alt="British Library">
        <div class="fact-card-content">
            <h1>British Library</h1>
            <p>The British Library is the second-largest library in the world.</p>
            <button onclick="showPopup('popup3')">Read more</button>
        </div>
    </div>
    <div class="fact-card">
        <img src="pic/1612639204-image3.webp" alt="Number of Books">
        <div class="fact-card-content">
            <h1>Number of Books</h1>
            <p>There are around 130 million published books.</p>
            <button onclick="showPopup('popup4')">Read more</button>
        </div>
    </div>
    <div class="fact-card">
        <img src="pic/file-20230328-368-uasp13.avif" alt="The Codex Leicester">
        <div class="fact-card-content">
            <h1>The Codex Leicester</h1>
            <p>The most expensive book in the world is ‘the Codex Leicester’.</p>
            <button onclick="showPopup('popup5')">Read more</button>
        </div>
    </div>
</div>

<div id="overlay" class="overlay" onclick="closePopup('popup1'); closePopup('popup2'); closePopup('popup3'); closePopup('popup4'); closePopup('popup5');"></div>

<div id="popup1" class="fact-popup">
    <button class="close-button" onclick="closePopup('popup1')">×</button>
    <h2>Library of Alexandria</h2>
    <p>The Library of Alexandria, located in Egypt, was renowned for its vast collection of scrolls and manuscripts, making it a center for knowledge and learning in the ancient world. It is estimated to have housed anywhere between 40,000 to 700,000 scrolls.</p>
</div>

<div id="popup2" class="fact-popup">
    <button class="close-button" onclick="closePopup('popup2')">×</button>
    <h2>Al-Qarawiyyin Library</h2>
    <p>The Al-Qarawiyyin Library, located in Fez, Morocco, holds the title of the oldest library in continuous operation since its establishment in the 9th century. It has played a significant role in preserving Arabic manuscripts and promoting Islamic education.</p>
</div>

<div id="popup3" class="fact-popup">
    <button class="close-button" onclick="closePopup('popup3')">×</button>
    <h2>British Library</h2>
    <p>With over 170 million items in its collection, the British Library ranks as the second-largest library globally, surpassed only by the Library of Congress. It houses a vast array of books, manuscripts, maps, and historical documents.</p>
</div>

<div id="popup4" class="fact-popup">
    <button class="close-button" onclick="closePopup('popup4')">×</button>
    <h2>Number of Books</h2>
    <p>Although it’s impossible for us to come up with an exact number, according to Google, they believe it is around 130 million books! All those books definitely won’t be fitting on the book shelf.</p>
</div>

<div id="popup5" class="fact-popup">
    <button class="close-button" onclick="closePopup('popup5')">×</button>
    <h2>The Codex Leicester</h2>
    <p>The Codex Leicester is the most expensive book in the world. The science book sold for 30.8 million dollars in 1994. It was Leonardo da Vinci’s science diary!</p>
</div>

</body>
</html>
