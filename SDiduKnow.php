<?php
include 'navigaStu.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Did You Know?</title>
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
            color: white;
            text-align: center;
            padding: 150px 20px;
        }
        .header h1 {
            font-size: 56px;
            font-weight: bold;
        }
        .quote-section {
            text-align: center;
            padding: 50px 20px;
            background-color: #f8f8f8;
        }
        .quote-section h2 {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .quote-section p {
            font-size: 20px;
            color: #333;
            font-style: italic;
            font-weight: bold;
        }
        .quote-section .author {
            margin-top: 5px;
            font-size: 16px;
            color: #333;
            font-style: normal;
            font-weight: normal;
        }
        .fun-facts-container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .fun-fact {
            display: flex;
            justify-content: space-between;
            background-color: #2e2185;
            color: white;
            border-radius: 10px;
            overflow: hidden;
            transition: height 0.3s ease-in-out, background-color 0.3s ease-in-out;
        }
        .fun-fact .left-side {
            padding: 20px;
            width: 50%;
            background-color: #2e2185;
        }
        .fun-fact .left-side h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .fun-fact .left-side p {
            font-size: 16px;
        }
        .fun-fact .right-side {
            width: 50%;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            transition: all 0.3s ease-in-out;
            height: 200px;
            background-color: #2e2185;
        }
        .fun-fact .right-side img {
            width: 100%;
            height: auto;
            display: block;
        }
        .fun-fact .info {
            padding: 20px;
            display: none;
            height: 100%;
            overflow-y: auto;
            color: black;
            background-color: #F0F8FF;
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
            var fact = document.getElementById(id);
            var img = fact.querySelector('img');
            var info = fact.querySelector('.info');
            var button = fact.querySelector('.toggle-button');
            
            if (info.style.display === 'none') {
                img.style.display = 'none';
                info.style.display = 'block';
                button.textContent = '×';
                fact.querySelector('.right-side').style.backgroundColor = '#F0F8FF';
            } else {
                img.style.display = 'block';
                info.style.display = 'none';
                button.textContent = '+';
                fact.querySelector('.right-side').style.backgroundColor = '#2e2185';
            }
        }
    </script>
</head>
<body>

<div class="header">
    <h1>Did You Know?</h1>
</div>

<div class="quote-section">
    <h2>Education moves learners forward.</h2>
    <p>Greater access to education gives everyone more ways to realize their potential. That’s why Apple is committed to providing people around the world with more opportunities to learn through partnerships in over 100 countries and regions.</p>
</div>

<div class="fun-facts-container">
    <div class="fun-fact" id="fact1">
        <div class="left-side">
            <h1>Library of Alexandria</h1>
            <p>The Library of Alexandria was the largest library in the ancient world.</p>
            <button class="toggle-button" onclick="toggleContent('fact1')">+</button>
        </div>
        <div class="right-side">
            <img src="pic/Library-of-Alexandria-Full-of-Books-1024x574.png" alt="Library of Alexandria">
            <div class="info">
                <h2>Library of Alexandria</h2>
                <p>The Library of Alexandria, located in Egypt, was renowned for its vast collection of scrolls and manuscripts, making it a center for knowledge and learning in the ancient world. It is estimated to have housed anywhere between 40,000 to 700,000 scrolls.</p>
            </div>
        </div>
    </div>
    
    <div class="fun-fact" id="fact2">
        <div class="left-side">
            <h1>Al-Qarawiyyin Library</h1>
            <p>The world’s oldest library in continuous operation is in Morocco.</p>
            <button class="toggle-button" onclick="toggleContent('fact2')">+</button>
        </div>
        <div class="right-side">
            <img src="pic/000_II637-1024x640.jpg" alt="Al-Qarawiyyin Library">
            <div class="info">
                <h2>Al-Qarawiyyin Library</h2>
                <p>The Al-Qarawiyyin Library, located in Fez, Morocco, holds the title of the oldest library in continuous operation since its establishment in the 9th century. It has played a significant role in preserving Arabic manuscripts and promoting Islamic education.</p>
            </div>
        </div>
    </div>
    
    <div class="fun-fact" id="fact3">
        <div class="left-side">
            <h1>British Library</h1>
            <p>The British Library is the second-largest library in the world.</p>
            <button class="toggle-button" onclick="toggleContent('fact3')">+</button>
        </div>
        <div class="right-side">
            <img src="pic/visit-our-buildings.jpg" alt="British Library">
            <div class="info">
                <h2>British Library</h2>
                <p>With over 170 million items in its collection, the British Library ranks as the second-largest library globally, surpassed only by the Library of Congress. It houses a vast array of books, manuscripts, maps, and historical documents.</p>
            </div>
        </div>
    </div>
    
    <div class="fun-fact" id="fact4">
        <div class="left-side">
            <h1>130 Million Books</h1>
            <p>There are around 130 million published books.</p>
            <button class="toggle-button" onclick="toggleContent('fact4')">+</button>
        </div>
        <div class="right-side">
            <img src="pic/1612639204-image3.webp" alt="130 Million Books">
            <div class="info">
                <h2>130 Million Books</h2>
                <p>Although it’s impossible for us to come up with an exact number, according to Google, they believe it is around 130 million books! All those books definitely won’t be fitting on the bookshelf.</p>
            </div>
        </div>
    </div>
    
    <div class="fun-fact" id="fact5">
        <div class="left-side">
            <h1>The Codex Leicester</h1>
            <p>The most expensive book in the world is ‘the Codex Leicester’.</p>
            <button class="toggle-button" onclick="toggleContent('fact5')">+</button>
        </div>
        <div class="right-side">
            <img src="pic/file-20230328-368-uasp13.avif" alt="The Codex Leicester">
            <div class="info">
                <h2>The Codex Leicester</h2>
                <p>The Codex Leicester is the most expensive book in the world. You’re not going to believe how much it sold for… The science book sold for 30.8 million dollars in 1994. It was Leonardo da Vinci’s science diary!</p>
            </div>
        </div>
    </div>
</div>

</body>
</html>
