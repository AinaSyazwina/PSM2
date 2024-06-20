<?php include 'navigaLib.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Life at Nasyaz Company</title>
 
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
          
        }
        .header-video-container {
            position: relative;
            width: 100%;
            height: 95vh; 
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
        .modal {
            display: none; 
            
            position: fixed; 
            z-index: 1; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgb(0,0,0); 
            background-color: rgba(0,0,0,0.4); 
            padding-top: 60px; 
        }
        .modal-content {
            border-radius: 18px;
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 700px; 
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>


<div class="header-video-container">
    <video autoplay muted loop>
        <source src="pic/6334253-uhd_4096_2160_25fps.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>
    <div class="header-content">
        <h1>SK Kamunting </h1>
        <p>Embrace a culture of respect, curiosity, and generosity.</p>
    </div>
</div>

<div class="title-section">
    <h2>History of SK Kamunting</h2>
    <p>SK Kamunting was established on 3 January 1987. Encik Ahmad Ahtar bin Abdul Latiff is the first principal of the school. The library was built later in October 1988. The total number of students is 669, whereas the total number of teachers is 54.</p>
</div>

<hr class="custom-hr">

<div class="values-section">
    <h1>Our Motto and Mission</h1>
    <div class="values">
        <div class="value">
            <h3>Knowledgeable</h3>
            <p>Knowledge is essential. We hope students acquire a broad range of knowledge, becoming highly knowledgeable in all fields upon graduation.</p>
        </div>
        <div class="value">
            <h3>Ethical</h3>
            <p>Possessing knowledge without noble character is insufficient. Therefore, our students are encouraged to develop both intellectual and moral excellence.</p>
        </div>
        <div class="value">
            <h3>Mission</h3>
            <p>Dedicated Teachers and Support Staff, Active PTA, Competitive Students, Pillars of School Educational Success</p>
        </div>
        <div class="value">
            <h3>Successful</h3>
            <p>With a strong foundation of knowledge and noble character, students are encouraged to strive diligently to achieve higher levels of success.</p>
        </div>
    </div>
</div>

<hr class="custom-hr">

<div class="about-company">
    <div class="about-company-text">
        <h2>About SK Kamunting</h2>
        <p>En. Ahmad Ahtar bin Abd. Latif, born on January 7, 1942, in Taiping, Perak, completed his secondary education at All Saints Secondary School, Kamunting. He began his teaching career in 1959 and became a trained teacher in 1962. He served in various schools until 1979 and then as Senior Assistant Teacher at Sekolah Kebangsaan Taiping until 1986. On January 1, 1987, he was appointed Headmaster of Sekolah Kebangsaan Kamunting, where he worked diligently to advance the school.</p>
        <a href="#" class="download-button" id="readMoreBtn">Read More</a>
    </div>
    <img src="pic/75642435_2788265944537543_932626017745371136_n.jpg" alt="Corporate Meeting">
</div>

<div id="myModal" class="modal">
<div class="modal-content">
    <span class="close">&times;</span>
    <p>
        The school was established on January 3, 1987. Before the building was completed, operations were held at SRJK(T) Kamunting. Eight teachers and five staff members reported for duty, with 169 students from Year 1 to Year 4. The number of students increased, and additional teachers were sent by PPD Larut Matang and Selama.
    </p>
    <br>
    <p>
        Activities included Children's Day on October 27, 1987. The first headmaster, En. Ahmad Ahtar Bin Abd. Latiff, was succeeded by En. Mohd Yusof Bin Hj. Ahmad on November 16, 1987.
    </p>
    <p>
        In 1988, the student number increased to 369, and a 'Gotong-Royong' school building with four classrooms was constructed. By October 1988, two blocks of two-story buildings with 12 classrooms, a teacher's room, an office, a prayer room, a resource center, a multipurpose room, a canteen, and a bicycle parking area were completed.
    </p>
    <br>
    <p>
        By 1989, the student number increased to 556, and Year 6 classes began. By 1998, the number of students reached 1650, with additional teachers and staff.
    </p>
    <p>
        An annex preschool class started on January 2, 1992, with 25 children. The school was upgraded to Grade A on September 1, 1992.
    </p>
    <br>
    <p>
        In 1994, the school received a single-story block with four classrooms and a science lab. A three-story block was completed the following year.
    </p>
    <p>
        In mid-1997, Puan Zakiah Bt Ghazali became Headmistress, succeeding Tuan Hj. Mohamad Elias Bin Hj. Mohd Sidek.
    </p>
</div>

</div>

<script>
    var modal = document.getElementById("myModal");

    var btn = document.getElementById("readMoreBtn");

    var span = document.getElementsByClassName("close")[0];

    btn.onclick = function() {
        modal.style.display = "block";
    }

    span.onclick = function() {
        modal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>

</body>
</html>
