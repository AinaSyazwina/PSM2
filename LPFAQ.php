<?php include 'navigaLib.php'?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ Dropdown</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding-top: 20px; /* Adjust based on the height of your navigation bar */
            background-color: #f4f4f4;
            margin: 0;
        }
        .faq-header {
            background-color: #F0F8FF; /* Light pink background */
            padding: 60px 20px;
            width: 100%; /* Full width */
            margin: 0 auto; /* Center aligned */
            box-sizing: border-box;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }
        .faq-header-text {
            flex: 1;
            text-align: center;
        }
        .faq-header img {
            height: 205px;
            position: absolute;
            bottom: 0;
            right: 20px;
        }
        .faq-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        .faq-header p {
            font-size: 18px;
            color: #555;
            max-width: 60%;
            margin: 0 auto;
        }
        .faq-container {
            width: 70%;
            margin: 20px auto;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        .faq-question {
            background-color: #f9f9f9;
            border-bottom: 1px solid #ddd;
            padding: 30px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 18px;
            font-weight: bold;
        }
        .faq-answer {
            display: none;
            padding: 30px;
            border-bottom: 1px solid #ddd;
            font-size: 16px;
            background-color: #fff;
        }
        .faq-container.active .faq-answer {
            display: block;
        }
        .faq-container.active .faq-question {
            background-color: #e9e9e9;
        }
        .faq-container .arrow {
            transition: transform 0.3s ease;
        }
        .faq-container.active .arrow {
            transform: rotate(180deg);
        }
        .arrow::after {
            content: '\25BC';
            font-size: 16px;
        }
    </style>
</head>
<body>

<div class="faq-header">
    <div class="faq-header-text">
        <h1>Frequently Asked Questions</h1>
        <p>BookPanda and GrabBook management system hope the below answers solve your 
            queries. We strive to provide you with the best possible assistance and ensure 
            you have a smooth experience. Feel free to reach out to us for any further questions or clarifications you may need.</p>
    </div>
    <img src="uploads/young-student-woman-wearing-denim-jacket-eyeglasses-holding-colorful-folders-showing-thumb-up-pink_176532-13861-removebg-preview.png" alt="Student Image">
</div>

<div class="faq-container">
    <div class="faq-question">
        <span>How to check my borrow record</span>
        <span class="arrow"></span>
    </div>
    <div class="faq-answer">
        At the navigation bar, choose the Issue Records option. A dropdown box and book will show. Click the preferred category and the borrow records will be displayed.
    </div>
</div>

<div class="faq-container">
    <div class="faq-question">
        <span>How to check my fine record</span>
        <span class="arrow"></span>
    </div>
    <div class="faq-answer">
        At the navigation bar, choose the Fine Records option. A dropdown box and book will show. Click the preferred category and the fine records will be displayed.
    </div>
</div>

<div class="faq-container">
    <div class="faq-question">
        <span>How to search for a book or box</span>
        <span class="arrow"></span>
    </div>
    <div class="faq-answer">
        At the navigation bar, choose the book or bo option. At the serach bar enter the book or box prefered and click enter.
    </div>
</div>

<div class="faq-container">
    <div class="faq-question">
        <span>How to add new students or library prefects into the system</span>
        <span class="arrow"></span>
    </div>
    <div class="faq-answer">
     Direct to the register option at navigation bar and entered all required information with the correct format. After that click enter
    </div>
</div>

<div class="faq-container">
    <div class="faq-question">
        <span>How to review a book</span>
        <span class="arrow"></span>
    </div>
    <div class="faq-answer">
        At the navigation bar, choose the review option, click book dropdown. The system will display the book that is available to review. Click the plus sign, after inserting rate and review click submit. A success message popup will pop up upon a successful review.
    </div>
</div>

<div class="faq-container">
    <div class="faq-question">
        <span>How to add book or box into my wishlist</span>
        <span class="arrow"></span>
    </div>
    <div class="faq-answer">
        Navigate to the book or box option. Click any preferred book or box, after choosing click the heart icon. Then go to profile, choose wishlist at the dropdown to see the favorite book or box.
    </div>
</div>

<div class="faq-container">
    <div class="faq-question">
        <span>How to use chatbot</span>
        <span class="arrow"></span>
    </div>
    <div class="faq-answer">
        Click the blue icon at the top of the page, enter a chat to start a conversation.
    </div>
</div>

<div class="faq-container">
    <div class="faq-question">
        <span>What is the function of notifications</span>
        <span class="arrow"></span>
    </div>
    <div class="faq-answer">
    The notification icons will display recently added books or boxes for approximately two weeks. Additionally, 
    they will notify users of incoming overdue book returns or borrowed items. This enhancement aims to provide users 
    with timely updates on their library activities, ensuring they stay informed about new additions and upcoming deadlines
    </div>
</div>

<script>
    document.querySelectorAll('.faq-question').forEach(item => {
        item.addEventListener('click', () => {
            const parent = item.parentElement;
            parent.classList.toggle('active');
        });
    });
</script>

</body>
</html>
