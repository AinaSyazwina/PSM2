<?php 
ob_start();
session_start();
if (!isset($_SESSION['logged_in'])) {
    $_SESSION['show_flashcard'] = true;
    $_SESSION['logged_in'] = true;
}
include 'config.php';  
include 'navigaStu.php'; 

// Ensure the user is logged in as a student
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    header('Location: index.php');
    exit;
}

// Use memberID from the session
$memberID = $_SESSION['memberID'] ?? 'default_member_id';

// Function to fetch recommendations using the memberID
function fetchRecommendations($memberID) {
    $url = "http://localhost:5000/recommend/" . $memberID;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) {
        return ["Error" => "Failed to fetch data"];
    }

    return json_decode($response, true) ?: ["Error" => "No recommendations available"];
}

// Fetch or update recommendations if they are not set or if the memberID has changed
if (!isset($_SESSION['recommendations']) || $memberID != $_SESSION['last_checked_member_id']) {
    $_SESSION['recommendations'] = fetchRecommendations($memberID);
    $_SESSION['last_checked_member_id'] = $memberID;
}

$recommendations = $_SESSION['recommendations'] ?? [];

// Function to fetch book details from the database
function fetchBookDetails($title) {
    global $conn;
    $stmt = $conn->prepare("SELECT book_acquisition, title, picture, genre FROM books WHERE title = ?");
    $stmt->bind_param("s", $title);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0 ? $result->fetch_assoc() : null;
}
// Determine if the current user is the top reviewer of the month for books
$current_user_id = $_SESSION['memberID'];
$isTopReviewerBooks = false;
$reviewerQueryBooks = "SELECT memberID, COUNT(*) as review_count
                       FROM reviews
                       WHERE MONTH(DateReview) = MONTH(CURDATE()) AND 
                             YEAR(DateReview) = YEAR(CURDATE()) AND 
                             isReview = 1
                       GROUP BY memberID
                       ORDER BY review_count DESC
                       LIMIT 1";

$stmtBooks = $conn->prepare($reviewerQueryBooks);
$stmtBooks->execute();
$resultBooks = $stmtBooks->get_result();
if ($topReviewerBooks = $resultBooks->fetch_assoc()) {
    $isTopReviewerBooks = ($topReviewerBooks['memberID'] === $current_user_id);
}

// Determine if the current user is the top reviewer of the month for boxes
$isTopReviewerBoxes = false;
$reviewerQueryBoxes = "SELECT memberID, COUNT(*) as review_count
                       FROM reviewbox
                       WHERE MONTH(DateReview) = MONTH(CURDATE()) AND 
                             YEAR(DateReview) = YEAR(CURDATE()) AND 
                             isReview = 1
                       GROUP BY memberID
                       ORDER BY review_count DESC
                       LIMIT 1";

$stmtBoxes = $conn->prepare($reviewerQueryBoxes);
$stmtBoxes->execute();
$resultBoxes = $stmtBoxes->get_result();
if ($topReviewerBoxes = $resultBoxes->fetch_assoc()) {
    $isTopReviewerBoxes = ($topReviewerBoxes['memberID'] === $current_user_id);
}

// Determine if the current user is the top borrower of books for the current month
$isTopBorrowerBooks = false;
$borrowerQueryBooks = "SELECT memberID, COUNT(*) as borrow_count
                       FROM issuebook
                       WHERE MONTH(IssueDate) = MONTH(CURDATE()) AND 
                             YEAR(IssueDate) = YEAR(CURDATE())
                       GROUP BY memberID
                       ORDER BY borrow_count DESC
                       LIMIT 1";

$stmtBorrowBooks = $conn->prepare($borrowerQueryBooks);
$stmtBorrowBooks->execute();
$resultBorrowBooks = $stmtBorrowBooks->get_result();
if ($topBorrowerBooks = $resultBorrowBooks->fetch_assoc()) {
    $isTopBorrowerBooks = ($topBorrowerBooks['memberID'] === $current_user_id);
}

// Determine if the current user has completed reviews for boxes
$boxReviewStatusQuery = "SELECT 
    ib.issueBoxID, 
    ib.BoxSerialNum, 
    b.category, 
    ib.IssueDate, 
    ib.DueDate, 
    ib.ReturnDate, 
    b.BoxPicture,
    CASE
        WHEN EXISTS (SELECT 1 FROM reviewbox WHERE reviewbox.issueBoxID = ib.issueBoxID AND reviewbox.memberID = ib.memberID AND isReview = 1) THEN 'Completed'
        ELSE 'Incomplete'
    END as ReviewStatus
FROM issuebox ib
INNER JOIN boxs b ON ib.BoxSerialNum = b.BoxSerialNum
INNER JOIN register r ON ib.memberID = r.memberID
WHERE ib.memberID = ?";

$boxReviewStatusStmt = $conn->prepare($boxReviewStatusQuery);
$boxReviewStatusStmt->bind_param("s", $current_user_id);
$boxReviewStatusStmt->execute();
$boxReviewStatusResult = $boxReviewStatusStmt->get_result();
$boxReviews = $boxReviewStatusResult->fetch_all(MYSQLI_ASSOC);

// Fetch the number of books returned by the user
$queryBooksReturned = "SELECT COUNT(*) as Returned FROM issuebook WHERE memberID = ? AND ReturnDate IS NOT NULL";
$stmt = $conn->prepare($queryBooksReturned);
$stmt->bind_param('s', $memberID);
$stmt->execute();
$result = $stmt->get_result();
$booksReturned = $result->fetch_assoc()['Returned'];

// Fetch the 'requested' status for the user
$queryRequestedStatus = "SELECT requested FROM loyalty_cards WHERE memberID = ?";
$stmt = $conn->prepare($queryRequestedStatus);
$stmt->bind_param('s', $memberID);
$stmt->execute();
$result = $stmt->get_result();
$requested = $result->fetch_assoc()['requested'] ?? 1;
?>

<DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="Cssfile/navStu.css">
    <style>
       .congratulations-popup {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: white;
    border: 2px solid #ccc;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    padding: 20px;
    text-align: center;
    width: 600px;
    border-radius: 10px;
}

.congratulations-popup .close-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 20px;
    cursor: pointer;
}

.congratulations-popup .congratulations-image {
    width: 240px; /* Increased width */
    height: 150px; /* Increased height */
    margin-bottom: 20px;
}

.congratulations-popup h2 {
    font-size: 34px;
    color: black;
    margin-bottom: 10px;
}

.congratulations-popup p {
    color: grey;
    font-size: 14px;
}

    </style>
</head>
<body>

<section class="home">

<video class="video-slide active" src="uploads/lib1.mp4" autoplay muted loop></video>
<video class="video-slide" src="uploads/lib2.mp4" autoplay muted loop></video>

    <div class="content active">
        <h1>Welcome <br><span>Back</span></h1>
        <p> "Welcome back to our magical online library! Explore new worlds, manage your borrowed books, 
        check fines, and share your thoughts with our interactive review feature. Dive into adventures, make
        new literary friends, and let your imagination soar with our digital treasure trove of books. 
         Come on in, the virtual pages are waiting for you!"</p>
    </div>

    <div class="content">
        <h1>Library <br><span>SKK</span></h1>
        <p> "Step into a realm where the boundaries between reality and imagination blur, where each book becomes a 
            portal to infinite possibilities, inviting you to traverse landscapes of knowledge, emotion, and adventure,
             unlocking the boundless treasures of the written word and igniting the flames of curiosity within your soul."</p>
    </div>

<div class="slider-navigation">
    <div class="nav-btn active"></div>
    <div class="nav-btn"></div>
</div>

</section>

<div class="container-padding">
<div class="congratulations-container">
    <?php if ($isTopReviewerBooks): ?>
        <div class="top-status">
            <img src="uploads/topbook.png" alt="Champion">
            <p><strong>Congratulations!</strong> You are the top reviewer of books for this month! Keep up the great work!</p>
        </div>
    <?php else: ?>
        <div class="non-top-status">
            <img src="uploads/book.png" alt="Non-Champion">
            <p><strong>Try Again!</strong> You are not the top reviewer for books this month. Keep reviewing!</p>
        </div>
    <?php endif; ?>

    <?php if ($isTopReviewerBoxes): ?>
        <div class="top-status">
            <img src="uploads/topbox.png" alt="Champion">
            <p><strong>Congratulations!</strong> You are the top reviewer of boxes for this month! Keep up the great work!</p>
        </div>
    <?php else: ?>
        <div class="non-top-status">
            <img src="uploads/box.png" alt="Non-Champion">
            <p><strong>Try Again!</strong> You are not the top reviewer for boxes this month. Keep reviewing!</p>
        </div>
    <?php endif; ?>

    <?php if ($isTopBorrowerBooks): ?>
        <div class="top-status">
            <img src="uploads/topissue.png" alt="Champion">
            <p><strong>Congratulations!</strong> You are the top borrower of books for this month! Keep up the great work!</p>
        </div>
    <?php else: ?>
        <div class="non-top-status">
            <img src="uploads/issue.png" alt="Non-Champion">
            <p><strong>Try Again!</strong> You are not the top borrower of books this month. Keep borrowing!</p>
        </div>
    <?php endif; ?>
</div>
<div class="top-rankings">
    <h2>Top Ranked Books</h2>
    <div style="position: relative;">
        <div class="rankings-books">
            <?php 
            $bookQuery = "SELECT b.book_acquisition, b.Title, b.picture, COUNT(ib.bookID) AS Count 
                          FROM issuebook ib
                          JOIN books b ON ib.bookID = b.book_acquisition
                          GROUP BY ib.bookID
                          ORDER BY Count DESC
                          LIMIT 10";
            $bookResult = $conn->query($bookQuery);
            if ($bookResult && $bookResult->num_rows > 0):
                $rank = 1;
                $uploadDir = ''; 
                while($row = $bookResult->fetch_assoc()): ?>
                    <div class='ranking-item'>
                        <a href="SList1.php?bookAcquisition=<?php echo $row['book_acquisition']; ?>" class="ranking-link">
                            <span class="ranking-number"><?php echo $rank; ?></span>
                            <img src="<?php echo $uploadDir . $row['picture']; ?>" alt='Book Cover' class='ranking-cover'>
                            <div class='info-overlay'>
                                <p class='ranking-title'><strong><?php echo htmlspecialchars($row['Title']); ?></strong></p>
                                <p class='ranking-count'>Borrowed: <?php echo $row['Count']; ?> times</p>
                            </div>
                        </a>
                    </div>
                <?php $rank++; endwhile; 
            else: ?>
                <p>No top-ranked books available.</p>
            <?php endif; ?>
        </div>
        <button class="nav-arrow nav-left" onclick="scrollToLeft('.rankings-books')"><</button>
        <button class="nav-arrow nav-right" onclick="scrollRight('.rankings-books')">></button>
    </div>
</div>

<div class="top-rankings">
    <div style="position: relative;">
        <h2>Top Ranked Boxes</h2>
        <div class="rankings-boxes">
            <?php 
            $boxQuery = "SELECT b.BoxSerialNum, b.BoxPicture, COUNT(ib.BoxSerialNum) AS Count 
                         FROM issuebox ib
                         JOIN boxs b ON ib.BoxSerialNum = b.BoxSerialNum
                         GROUP BY ib.BoxSerialNum
                         ORDER BY Count DESC
                         LIMIT 10";
            $boxResult = $conn->query($boxQuery);
            if ($boxResult && $boxResult->num_rows > 0):
                $rank = 1;
                $uploadDir = ''; 
                while($row = $boxResult->fetch_assoc()): ?>
                    <div class='ranking-item'>
                        <a href="SListBox1.php?BoxSerialNum=<?php echo $row['BoxSerialNum']; ?>" class="ranking-link">
                            <span class="ranking-number"><?php echo $rank; ?></span>
                            <img src="<?php echo $uploadDir . $row['BoxPicture']; ?>" alt='Box Picture' class='ranking-cover'>
                            <div class='info-overlay'>
                                <p class='ranking-title'><strong><?php echo htmlspecialchars($row['BoxSerialNum']); ?></strong></p>
                                <p class='ranking-count'>Issued: <?php echo $row['Count']; ?> times</p>
                            </div>
                        </a>
                    </div>
                <?php $rank++; endwhile; 
            else: ?>
                <p>No top-ranked boxes available.</p>
            <?php endif; ?>
        </div>
        <button class="nav-arrow nav-left" onclick="scrollToLeft('.rankings-boxes')"><</button>
        <button class="nav-arrow nav-right" onclick="scrollRight('.rankings-boxes')">></button>
    </div>
</div>
<div class="recently-added">
    <h2>Recently Added Books</h2>
    <div style="position: relative;">
        <div class="recently-books">
            <?php 
            $recentBooksQuery = "SELECT book_acquisition, Title, picture, DateReceived 
                                 FROM books 
                                 ORDER BY DateReceived DESC 
                                 LIMIT 10";
            $recentBooksResult = $conn->query($recentBooksQuery);
            if ($recentBooksResult && $recentBooksResult->num_rows > 0):
                $uploadDir = ''; 
                while($row = $recentBooksResult->fetch_assoc()): ?>
                    <div class='ranking-item'>
                        <a href="SList1.php?bookAcquisition=<?php echo $row['book_acquisition']; ?>" class="ranking-link">
                            <img src="<?php echo $uploadDir . $row['picture']; ?>" alt='Book Cover' class='ranking-cover'>
                            <div class='info-overlay'>
                                <p class='ranking-title'><strong><?php echo htmlspecialchars($row['Title']); ?></strong></p>
                                <p class='ranking-date'>Added: <?php echo $row['DateReceived']; ?></p>
                            </div>
                        </a>
                    </div>
                <?php endwhile; 
            else: ?>
                <p>No recently added books available.</p>
            <?php endif; ?>
        </div>
        <button class="nav-arrow nav-left" onclick="scrollToLeft('.recently-books')"><</button>
        <button class="nav-arrow nav-right" onclick="scrollRight('.recently-books')">></button>
    </div>
</div>

<div class="recently-added">
    <h2>Recently Added Boxes</h2>
    <div style="position: relative;">
        <div class="recently-boxes">
            <?php 
            $recentBoxesQuery = "SELECT BoxSerialNum, BoxPicture, DateCreate 
                                 FROM boxs 
                                 ORDER BY DateCreate DESC 
                                 LIMIT 10";
            $recentBoxesResult = $conn->query($recentBoxesQuery);
            if ($recentBoxesResult && $recentBoxesResult->num_rows > 0):
                $uploadDir = ''; 
                while($row = $recentBoxesResult->fetch_assoc()): ?>
                    <div class='ranking-item'>
                        <a href="SListBox1.php?BoxSerialNum=<?php echo $row['BoxSerialNum']; ?>" class="ranking-link">
                            <img src="<?php echo $uploadDir . $row['BoxPicture']; ?>" alt='Box Picture' class='ranking-cover'>
                            <div class='info-overlay'>
                                <p class='ranking-title'><strong><?php echo htmlspecialchars($row['BoxSerialNum']); ?></strong></p>
                                <p class='ranking-date'>Added: <?php echo $row['DateCreate']; ?></p>
                            </div>
                        </a>
                    </div>
                <?php endwhile; 
            else: ?>
                <p>No recently added boxes available.</p>
            <?php endif; ?>
        </div>
        <button class="nav-arrow nav-left" onclick="scrollToLeft('.recently-boxes')"><</button>
        <button class="nav-arrow nav-right" onclick="scrollRight('.recently-boxes')">></button>
    </div>
</div>

<div class="recommendations">
    <h2 class="recommendations-heading">Recommended Books for You</h2>
    <div class="rankings-recommend">
        <?php if (!empty($recommendations) && is_array($recommendations)): ?>
            <?php foreach ($recommendations as $book): ?>
                <?php $details = fetchBookDetails($book); ?>
                <?php if ($details): ?>
                    <div class='ranking-recommendation'>
                        <a href="SList1.php?bookAcquisition=<?= htmlspecialchars($details['book_acquisition']) ?>" class="recommendation-link">
                            <img src="<?= htmlspecialchars($details['picture'] ?? 'path/to/default/image.jpg') ?>" alt='Cover' class='recommendation-cover'>
                            <div class='info-overlay'>
                                <p class='recommendation-title'><?= htmlspecialchars($details['title'] ?? $book) ?></p>
                                <p class='recommendation-count'><?= htmlspecialchars($details['genre'] ?? 'Unknown genre') ?></p>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No recommendations available.</p>
        <?php endif; ?>
    </div>
</div>

</div>

<?php if ($booksReturned >= 10 && !isset($_SESSION['congratulation_shown']) && $requested == 0): ?>
    <div id="congratulationsPopup" class="congratulations-popup">
        <span class="close-btn" onclick="document.getElementById('congratulationsPopup').style.display='none'">&times;</span>
        <img src="pic/congratulation_5511415.png" alt="Congratulations" class="congratulations-image">
        <h2>CONGRATULATIONS!</h2>
        <p>You are eligible for a lucky draw. Please give your name to the library teacher or library prefects.</p>
    </div>
<script>
    document.getElementById('congratulationsPopup').style.display = 'block';
</script>
<?php $_SESSION['congratulation_shown'] = true; endif; ?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>

function scrollToLeft(selector) {
    const container = document.querySelector(selector);
    container.scrollBy({
        top: 0,
        left: -300, // Negative value for left scrolling
        behavior: 'smooth' // Optional: for smooth scrolling
    });
}


function scrollRight(selector) {
    const container = document.querySelector(selector);
    container.scrollBy({
        top: 0,
        left: 300, // Positive value for right scrolling
        behavior: 'smooth' // Optional: for smooth scrolling
    });
}


document.querySelectorAll('.ranking-item').forEach(item => {
    item.addEventListener('mouseover', function() {
        this.classList.add('hover-effect');
    });
    item.addEventListener('mouseout', function() {
        this.classList.remove('hover-effect');
    });
});
document.querySelectorAll('.ranking-recommendation').forEach(item => {
    item.addEventListener('mouseover', function() {
        this.classList.add('hover-effect');
    });
    item.addEventListener('mouseout', function() {
        this.classList.remove('hover-effect');
    });
});


$(document).ready(function() {
    function fetchBookDetails(bookTitle) {
        $.ajax({
            url: 'fetchRecommend.php',
            type: 'POST',
            data: { title: bookTitle },
            success: function(response) {
                var details = JSON.parse(response);
                if (details && !details.error) {
                    var html = '<div class="ranking-item">' +
                        '<img src="' + (details.picture || 'path/to/default/image.jpg') + '" alt="Cover" class="ranking-cover">' +
                        '<p class="ranking-title">' + details.title + '</p>' +
                        '<p class="ranking-count">' + (details.genre || 'Unknown genre') + '</p>' +
                        '</div>';
                    $('.recommendations .rankings-recommend').append(html);
                }
            },
            error: function() {
                console.log('Error fetching book details');
            }
        });
    }

    // Fetch recommendations when the page is fully loaded
    if (!<?= json_encode($recommendations) ?>.length) {
        $.ajax({
            url: 'recommend.php',
            type: 'GET',
            data: { member_id: '<?= $memberID ?>' }, // Changed from user_id to member_id
            success: function(data) {
                var recommendations = JSON.parse(data);
                if (recommendations.length > 0) {
                    $('.recommendations .rankings-recommend').empty(); // Clear existing items
                    recommendations.forEach(function(book) {
                        fetchBookDetails(book); // Function to get book details and display them
                    });
                } else {
                    $('.recommendations .rankings-recommend').html('<p>No recommendations available.</p>');
                }
            },
            error: function() {
                $('.recommendations .rankings-recommend').html('<p>Error loading recommendations.</p>');
            }
        });
    }
});
</script>
<script type="text/javascript">
    //javascript slider
    const btns = document.querySelectorAll(".nav-btn");
    const slides = document.querySelectorAll(".video-slide");
    const contents = document.querySelectorAll(".content")

    var sliderNav = function (manual) {  
        btns.forEach((btn) => {
            btn.classList.remove("active")
        });

        slides.forEach((slide) => {
            slide.classList.remove("active")
     });

     contents.forEach((content) => {
        content.classList.remove("active")
     });

        btns[manual].classList.add("active");
        slides[manual].classList.add("active");
        contents[manual].classList.add("active");
    }

    btns.forEach((btn, i) => {
        btn.addEventListener("click", () => {
            sliderNav(i);
        });
    });
</script> 
</body>
</html>
