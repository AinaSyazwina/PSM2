<?php
ob_start(); 
session_start(); 

include 'config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

function getUserDetails($conn, $memberID) {
    $stmt = $conn->prepare("SELECT fullname, picture FROM register WHERE memberID = ?");
    $stmt->bind_param("s", $memberID);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Set upload directory for user images
$uploadDir = ""; // Define your actual upload directory here
if (isset($_SESSION['memberID'])) {
    $userDetails = getUserDetails($conn, $_SESSION['memberID']);
    $userImage = !empty($userDetails['picture']) ? $uploadDir . $userDetails['picture'] : 'pic/default-avatar.png';
} else {
    $userImage = 'pic/default-avatar.png';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <!-- ===Navigation====== -->
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

    <link rel="stylesheet" href="Cssfile/style3.css">
<div class ="container">
      <div class = "navigation">
         <ul class="nav-links">
           <li>
             <a href="#" >
                
                <img src="pic/logoedit.png" alt="BookPanda & GrabBook" class="icon">
            </span>
                <span class="title">BookPanda & GrabBook</span>
             </a>
           </li>

           <li>
             <a href="dashboard.php" >
                <span class="icon"><ion-icon name="home-outline"></ion-icon>
            </span>
                <span class="title">DashBoard</span>
             </a>
           </li>

           <li>
             <a href="register.php" >
                <span class="icon"><ion-icon name="person-add-outline"></ion-icon>
            </span>
                <span class="title">Registration</span>
             </a>
           </li>

           <li>
             <a href="manageuser.php" >
                <span class="icon"><ion-icon name="person-outline"></ion-icon>
            </span>
                <span class="title">Manage User</span>
             </a>
           </li>

           <li>
              <a href="managebook.php" >
                <span class="icon"><ion-icon name="book-outline"></ion-icon></span>
                <span class="title">Manage Book</span>
                <!-- <i class='bx bxs-chevron-down'></i>-->
             </a>
           <!-- <div class="dropdown-content">
               <div class="dropdown-items">
                  <a href="managebook.php">Book Details</a>
                  <a href="addbook.php">Add Book</a>
              </div>
           </div> -->
           </li>
          
        <li>
           <a href="managebox.php">
               <span class="icon"><ion-icon name="cube-outline"></ion-icon></span>
               <span class="title">Manage Box</span>
          </a>
      
</li>          

<li class="dropdown">
    <a href="#">
        <span class="icon"><ion-icon name="bag-add-outline"></ion-icon></span>
        <span class="title">Book Operation</span>
        <i class='bx bxs-chevron-down'></i>
    </a>
    <div class="dropdown-content">
        <div class="dropdown-items">
            <a href="issuebook.php">Borrow Book</a>   
            <a href="returnBook.php">Return Book</a>
            <a href="bookAvail.php">Status Availability</a>
        </div>
    </div>
</li>
          
         <li class="dropdown">
             <a href="#" >
                <span class="icon"><ion-icon name="archive-outline"></ion-icon></span>
                <span class="title">Box Operation</span>
                <i class='bx bxs-chevron-down'></i>
             </a>
             <div class="dropdown-content">
                 <div class="dropdown-items">
                      <a href="issuebox.php">Borrow Box</a>   
                      <a href="returnbox.php">Return Box</a>
                      <a href="boxAvail.php">Status Availability</a>
                 </div>
            </div>
      </li>
          
           <li class="dropdown">
             <a href="#" >
                <span class="icon"><ion-icon name="cash-outline"></ion-icon>
            </span>
                <span class="title"> Fine</span>
                <i class='bx bxs-chevron-down'></i>
             </a>
             <div class="dropdown-content">
                 <div class="dropdown-items">
                      <a href="fineDetail.php">Fine Book Details</a>   
                      <a href="fineDetailBox.php">Fine Box Details</a>
                      <a href="finePayment.php">Book Payment</a>
                      <a href="fineBoxPayment.php">Box Payment</a>
                      <a href="fineissue.php">Issue Book Fine</a>
                      <a href="fineboxissue.php">Issue Box Fine</a>
                 </div>
            </div>
           </li>
          
           
           <li class="dropdown">
             <a href="#" >
             <span class="icon"><ion-icon name="chatbubbles-outline"></ion-icon>
            </span>
                <span class="title"> Review</span>
                <i class='bx bxs-chevron-down'></i>
             </a>
             <div class="dropdown-content">
                 <div class="dropdown-items">
                      <a href="review.php">Review Book</a>   
                      <a href="reviewbox.php">Review Box</a>
                 </div>
            </div>
           </li>

           <li>
             <a href="report.php" >
                <span class="icon"><ion-icon name="document-text-outline"></ion-icon>
            </span>
                <span class="title">Report</span>
             </a>
           </li>    

           <li>
             <a href="quote.php" >
                <span class="icon"><ion-icon name="bookmark-outline"></ion-icon>
            </span>
                <span class="title">Quote</span>
             </a>
           </li>  


          </ul>
       </div>
       <?php
include 'config.php';

function fetchNotifications($conn) {
    $today = date('Y-m-d');
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $oneMonthAgo = date('Y-m-d', strtotime('-1 month'));

    $notifications = [];

  // Fetch book notifications
$bookQuery = "SELECT 
'Book' as type, 
m.fullname, 
i.issueID as itemID, 
i.DueDate, 
b.ISBN
FROM issuebook i
JOIN register m ON i.memberID = m.memberID
JOIN books b ON i.bookID = b.book_acquisition
WHERE (i.DueDate = '$tomorrow' OR i.DueDate < '$today')
    AND i.ReturnDate IS NULL
    AND i.DueDate >= '$oneMonthAgo'";

$bookResult = mysqli_query($conn, $bookQuery);
while ($row = mysqli_fetch_assoc($bookResult)) {
$notifications[] = "{$row['fullname']}'s {$row['type']} (ISBN: {$row['ISBN']}) is " . 
       ($row['DueDate'] == $tomorrow ? 'due tomorrow.' : 'overdue.');
}

    // Fetch box notifications
    $boxQuery = "SELECT 'Box' as type, m.fullname, i.issueBoxID as itemID, i.DueDate
                 FROM issuebox i
                 JOIN register m ON i.memberID = m.memberID
                 WHERE (i.DueDate = '$tomorrow' OR i.DueDate < '$today')
                       AND i.ReturnDate IS NULL
                 AND i.DueDate >= '$oneMonthAgo'";  
    $boxResult = mysqli_query($conn, $boxQuery);
    while ($row = mysqli_fetch_assoc($boxResult)) {
        $notifications[] = "{$row['fullname']}'s {$row['type']} (ID: {$row['itemID']}) is " . ($row['DueDate'] == $tomorrow ? 'due tomorrow.' : 'overdue.');
    }

    return $notifications;
}

$notifications = fetchNotifications($conn);
?>

       <!--- Main -->
       <div class="main">
    <div class="topbar">
        <div class="toggle">
            <ion-icon name="menu-outline"></ion-icon>
        </div>

       <div class="search1">
    <label>
        <input type="text" placeholder="Search here">
        <button type="submit">
            <ion-icon name="search-outline"></ion-icon>
        </button>
    </label>
</div>


        <div class="user-section">
            
        <div class="notifications" onclick="toggleNotifications()">
    <ion-icon name="notifications-outline" style="font-size: 24px;"></ion-icon>
    <span id="notificationCount" class="notification-count"><?= count($notifications) ?></span>
    <div id="notificationDropdown" class="notification-content" style="display: none;">
        <?php foreach ($notifications as $notification): ?>
            <div class="notification-item">
                <ion-icon name="alert-circle-outline"></ion-icon>
                <strong><?= $notification ?></strong>
                <ion-icon name="close-outline" onclick="removeNotification(this)"></ion-icon>
            </div>
        <?php endforeach; ?>
        <?php if (count($notifications) === 0): ?>
            <p>No new notifications</p>
        <?php endif; ?>
    </div>
</div>

<div class="user" onclick="toggleMenu()">
<?php  $uploadDir = ''; ?>
<img src="<?= $userImage ?>" alt="User Image" class="userlogo">
    <div class="sub-menu-wrap" id="subMenu">
        <div class="sub-menu">
        <div class="userinfo">
                    <img src="<?= htmlspecialchars($userImage) ?>" alt="User Image" class="userlogo">
                    <div> <h3><?= htmlspecialchars($userDetails['fullname']) ?></h3></div>
                </div>
  


                 <hr>
                 <a href="myprofile.php" class=sub-menu-link>
                        <img src="pic/view.png" width="65px">
                        <p>My Profile</p>
                        <span></span>
                 </a>

                 <a href="regulation.php" class=sub-menu-link>
                        <img src="pic/law-icon-2041x2048-ek40ipuw.png" width="65px">
                        <p>Regulations</p>
                        <span></span>
                 </a>

                 <a href="aboutus.php" class=sub-menu-link>
                        <img src="pic/exclamation-mark-png-exclamation-mark-icon-11563006763v9utxg8tnp.png" width="65px">
                        <p>About Us</p>
                        <span></span>
                 </a>


                 <a href="faq.php" class=sub-menu-link>
                        <img src="pic/faq-chat-bubble-ask-dialog-web-icon-vector-23828481-removebg-preview.png" width="65px">
                        <p>FAQ</p>
                        <span></span>
                 </a>

                <!-- <a href="#" class=sub-menu-link>
                        <img src="pic/edit.png" width="40px">
                        <p>Edit Profile</p>
                        <span></span>       
                 </a> -->
                 <a href="logout.php" class=sub-menu-link>
                        <img src="pic/signoutt.png" width="30px">
                        <p>Log Out</p>
                        <span></span>
                 </a>

                 
            </div>
        </div> 
        </div>
        </div>
         <!--- jgn buang ni top bar main smbg home -->
    </div>

    <script src="jsfile/main.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
   <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
   
   <script>
         function toggleDropdown() {
        var dropdowns = document.querySelectorAll('.dropdown');
        dropdowns.forEach((dropdown) => {
            dropdown.addEventListener('click', function(event) {
                event.preventDefault(); // Prevent default action to avoid page jump
                
                this.classList.toggle('active');
                var dropdownContent = this.querySelector('.dropdown-content');
                if (dropdownContent.style.display === 'block') {
                    dropdownContent.style.display = 'none';
                } else {
                    dropdownContent.style.display = 'block';
                }
            });
        });

        // Handling click on the dropdown items
        var dropdownItems = document.querySelectorAll('.dropdown-content .dropdown-items a');
        dropdownItems.forEach((item) => {
            item.addEventListener('click', function(event) {
                var hrefValue = item.getAttribute('href');
                
                // Perform navigation based on the href value
                //if (hrefValue === 'managebook.php') {
               //     window.location.href = hrefValue; // Navigate to managebook.php
               // } else if (hrefValue === 'addbook.php') {
                    window.location.href = hrefValue; // Navigate to addbook.php
               // }
            });
        });
    }

    window.onload = function() {
        toggleDropdown();
    };
   </script>

<script>

function toggleMenu() {
    var subMenu = document.getElementById("subMenu");
    subMenu.classList.toggle("open-menu");
}

</script>



<script>
 function toggleNotifications() {
    var notificationDropdown = document.getElementById("notificationDropdown");
    notificationDropdown.style.display = notificationDropdown.style.display === 'none' ? 'block' : 'none';
}

function removeNotification(icon) {
    var item = icon.parentNode;
    item.parentNode.removeChild(item); // Removes the notification item from the dropdown
    // Update the notification count
    var count = document.getElementById('notificationCount');
    var currentCount = parseInt(count.textContent, 10) - 1;
    count.textContent = currentCount;

    if (currentCount <= 0) {
        document.getElementById("notificationDropdown").innerHTML = '<p>No new notifications</p>';
    }
}
function updateNotificationCount() {
    var notifications = document.querySelectorAll('.notification-item');
    var notificationCount = document.getElementById("notificationCount");
    notificationCount.textContent = notifications.length;
}
function removeNotification(icon) {
    var item = icon.parentNode;
    item.parentNode.removeChild(item); // Removes the notification item from the dropdown
    // Update the notification count
    var count = document.getElementById('notificationCount');
    var currentCount = parseInt(count.textContent, 10) - 1;
    count.textContent = currentCount;

    if (currentCount <= 0) {
        document.getElementById("notificationDropdown").innerHTML = '<p>No new notifications</p>';
    }
}


</script>
</body>
</html>