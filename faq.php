<?php include 'navigation.php'?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ Dropdown</title>
    <style>
        
        .faq-header {
            background-color: #F0F8FF; 
            padding: 60px 20px;
            width: 100%; 
            margin: 0 auto; 
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
        <span>How to register students?</span>
        <span class="arrow"></span>
    </div>
    <div class="faq-answer">
    Navigate to the sidebar, select "Registration," and fill in all required fields 
    marked with an asterisk (*) in the correct format. Click "Submit," and a success message will appear upon completion.
    </div>
</div>

<div class="faq-container">
    <div class="faq-question">
        <span>How to add new books into a box?</span>
        <span class="arrow"></span>
    </div>
    <div class="faq-answer">
    Go to "Manage Book" in the sidebar, click the "Edit" button for the desired book, enter the 
    Box ID and book copy count, and click "Update."
    </div>
</div>

<div class="faq-container">
    <div class="faq-question">
        <span>How to check book availability?</span>
        <span class="arrow"></span>
    </div>
    <div class="faq-answer">
    Click the dropdown menu under "Book Operation" in the sidebar and select "Book Availability."
    </div>
</div>

<div class="faq-container">
    <div class="faq-question">
        <span>What are the categories in box status availability?</span>
        <span class="arrow"></span>
    </div>
    <div class="faq-answer">
    There are four categories: available (green), closed for issue (grey), unavailable (green), and unavailable (red). Red indicates the box 
    has been borrowed and not yet returned, grey means the box has no books and is unavailable for borrowing, closed for issue means the box
     is not available for issue, and green available indicates the box is available for issue.
    </div>
</div>

<div class="faq-container">
    <div class="faq-question">
        <span>How to issue a missing book or box to students or library prefects?</span>
        <span class="arrow"></span>
    </div>
    <div class="faq-answer">
    Select "Issue Book Fine" or "Issue Box Fine" from the sidebar, enter the student ID, and provide the ISBN/Box serial number. Only
     items not yet returned can be marked as missing.
    </div>
</div>

<div class="faq-container">
    <div class="faq-question">
        <span>How to see which students are still borrowing or have exceeded the due date?</span>
        <span class="arrow"></span>
    </div>
    <div class="faq-answer">
    Navigate to the dashboard, where the "Box Issue Records" and "Book Issue Records" sections display all student borrowing statuses, including exceed, pending, overdue, and returned.
    </div>
</div>

<div class="faq-container">
    <div class="faq-question">
        <span> How to print a report?</span>
        <span class="arrow"></span>
    </div>
    <div class="faq-answer">
    Choose the session, month, report category, and click "Submit." If information is displayed, click "Print PDF." Do not print if no information is shown.
    </div>
</div>

<div class="faq-container">
    <div class="faq-question">
        <span> What is the function of notifications?</span>
        <span class="arrow"></span>
    </div>
    <div class="faq-answer">
    Notification icons display two-week overdue books and students whose book or box due date is tomorrow. This enhancement provides timely updates on library activities, ensuring users stay informed about new additions and upcoming deadlines.
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
