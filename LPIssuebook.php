<?php include 'navigaLib.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IssueBook</title>
    <link rel="stylesheet" href="Cssfile/issue.css">
    <link rel="stylesheet" href="Cssfile/list.css"> 

    <style>
        .table1 {
            width: 100%; 
            table-layout: fixed; 
        }
        .table1 th, .table1 td {
            min-width: 150px; 
            padding: 10px; 
            text-align: center;
        }
    </style>
       <script>
        function calculateDueDate() {
            var borrowDate = document.getElementById('borrowD').value;
            if (borrowDate) {
                var borrowDateObj = new Date(borrowDate);
                borrowDateObj.setDate(borrowDateObj.getDate() + 7); 

                var dueDate = borrowDateObj.toISOString().split('T')[0]; 
                document.getElementById('dueD').value = dueDate;
            }
        }


//search 
let lastInputTime = Date.now();
let inputTimeout = null; 
let isSearching = false; 

function handleInput() {
    const inputField = document.getElementById('memberID');
    const currentTime = Date.now();
    const timeDifference = currentTime - lastInputTime;
    lastInputTime = currentTime; // Update last input time.


    if (inputTimeout !== null) {
        clearTimeout(inputTimeout);
    }

    if (inputField.value.length >= 6 && timeDifference < 50) {
      
        searchMember(true);
    } else {
        
        inputTimeout = setTimeout(() => {
           
        }, 500); 
    }
}

function manualSearch() {
    clearTimeout(inputTimeout); 
    searchMember(false);
}

function searchMember(isScan) {
    const memberID = document.getElementById('memberID').value.trim();
    if (!memberID) {
        alert('Please enter a Member ID.');
        return;
    }
    if (isSearching && !isScan) return; 

    isSearching = true; 
    fetch(`searchMember.php?memberID=${encodeURIComponent(memberID)}`)
        .then(response => response.json())
        .then(data => {
            isSearching = false;
            if (data.success) {
                document.getElementById('fullname').value = data.fullname;
                document.getElementById('IC').value = data.IC;
                document.getElementById('class').value = data.class;

               
                fetchIssueAvailability(memberID);
            } else if (!isScan) { 
                alert('Member not found or inactive.');
            }
        })
        .catch(error => {
            isSearching = false; 
            console.error('Error:', error);
            if (!isScan) {
                alert('Error: ' + error.message);
            }
        });
}

document.getElementById('memberID').addEventListener('input', handleInput);
document.getElementById('searchButton').addEventListener('click', manualSearch);

//func issue
async function issueBook(event) {
    event.preventDefault();  

    var bookAvailabilityMsg = document.getElementById('book_availability_msg').textContent.trim();
    if (bookAvailabilityMsg === 'Book not available') {
        alert('Sorry, this book is fully borrowed and currently unavailable.');
        return; 
    }

    var memberID = document.querySelector('input[name="memberID"]').value.trim();
    var isbn = document.getElementById('ISBN').value.trim();
    var borrowDate = document.getElementById('borrowD').value.trim();
    var dueDate = document.getElementById('dueD').value.trim();

    if (!memberID || !isbn || !borrowDate || !dueDate) {
        alert('Please fill in all required fields: Member ID, ISBN, Borrow Date, and Due Date.');
        return;
    }

    const canBorrow = await checkBorrowLimit(memberID);
    if (!canBorrow) return; 

    var formData = new FormData();
    formData.append('memberID', memberID);
    formData.append('ISBN', isbn);
    formData.append('borrowDate', borrowDate);
    formData.append('dueDate', dueDate);

    fetch('issueBook1.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(text => {
        console.log("Raw response:", text);
        return JSON.parse(text); 
    })
    .then(data => {
        if (data.success) {
            togglePopup('issueSuccessPopup', true); 
            document.getElementById('issueForm').reset(); 
        } else {
            alert('Failed to issue book. Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}


//check Borrow Limit
function checkBorrowLimit(memberID) {
    return fetch(`checkBorrowLimit.php?memberID=${encodeURIComponent(memberID)}`)
        .then(response => response.json())
        .then(data => {
            if (data.currentlyBorrowed >= 4) {
                alert("You have reached your borrowing limit. Please return some books to borrow more.");
                return false; // Indicate that the limit has been reached
            }
            return true; // Indicate that the user can borrow more books
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to check borrowing limit.');
            return false;
        });
}

function displayIssues(memberID = '') {
    let url = 'issuelist.php';
    if (memberID) {
        url += '?memberID=' + encodeURIComponent(memberID);
    }

    fetch(url)
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text(); 
    })
    .then(text => {
        console.log("Response text:", text); 
        return JSON.parse(text); 
    })
    .then(data => {
        console.log("Parsed JSON data:", data); 
        var tableBody = document.querySelector('.table1 tbody');
        tableBody.innerHTML = ''; 

        if (Array.isArray(data) && data.length > 0) {
            data.forEach(issue => {
                var row = tableBody.insertRow();
                var cellName = row.insertCell(0);
                var cellTitle = row.insertCell(1);
                var cellBorrowDate = row.insertCell(2);
                var cellDueDate = row.insertCell(3);

                cellName.textContent = issue.fullname || 'N/A';
                cellTitle.textContent = issue.title || 'N/A';
                cellBorrowDate.textContent = issue.borrowDate || 'N/A';
                cellDueDate.textContent = issue.dueDate || 'N/A';
            });
        } else {
            tableBody.innerHTML = '<tr><td colspan="4">No issues found for this member.</td></tr>';
        }
    })
    .catch(error => {
        console.error('Error fetching issues:', error);
    });
}


// Call displayIssues with the memberID when you want to filter by member
function searchAndDisplayIssues() {
    var memberIDInput = document.querySelector('input[name="memberID"]');
    var memberID = memberIDInput.value.trim();
    if (memberID) {
        console.log("Fetching issues for memberID:", memberID); // Debug log
        displayIssues(memberID);
    } else {
        alert('Please enter a Member ID to search.');
    }
}

function fetchIssueAvailability(memberID) {
    fetch('issueAvail.php?memberID=' + memberID)
        .then(response => response.json())
        .then(data => {
            updateIssueAvailabilityDisplay(data);
        })
        .catch(error => {
            console.error('Error fetching issue availability:', error);
        });
}

function updateIssueAvailabilityDisplay(data) {
    if (data.error) {
        console.error('Error from server:', data.error);
    } else {
    
        var issueAmountAllowed = data.issueAmountAllowed;
        var issueBookAllowed = data.issueBookAllowed;
       
        var infoBox = document.querySelector('.info-box');
        var issueAmountAllowedSpanTotal = infoBox.querySelector('.issue-amount-allowed .info-total');
        var issueAmountAllowedSpanAvailable = infoBox.querySelector('.issue-amount-allowed .info-available');
        var issueBookAllowedSpanTotal = infoBox.querySelector('.issue-book-allowed .info-total');
        var issueBookAllowedSpanAvailable = infoBox.querySelector('.issue-book-allowed .info-available');

        issueAmountAllowedSpanTotal.textContent = `Total: ${issueAmountAllowed.total}`;
        issueAmountAllowedSpanAvailable.textContent = `Available: ${issueAmountAllowed.available}`;
        issueBookAllowedSpanTotal.textContent = `Total: ${issueBookAllowed.total}`;
        issueBookAllowedSpanAvailable.textContent = `Available: ${issueBookAllowed.available}`;
    }
}

let lastISBNInputTime = Date.now();
let isbnInputTimeout = null; 

function handleISBNInput() {
    const isbnField = document.getElementById('ISBN');
    const currentTime = Date.now();
    const timeDifference = currentTime - lastISBNInputTime;
    lastISBNInputTime = currentTime; 

    if (isbnInputTimeout !== null) {
        clearTimeout(isbnInputTimeout); 
    }

    const isbnValue = isbnField.value.trim(); 

    if (isbnValue.length === 13 && timeDifference < 50) { 
        validateBookAvailability(true); 
    } else {
        isbnInputTimeout = setTimeout(() => {
            validateBookAvailability(false); 
        }, 500); 
    }
}

function validateBookAvailability(isScan) {
    const isbn = document.getElementById('ISBN').value.trim();
    const bookAvailabilityMsg = document.getElementById('book_availability_msg');
    const titleInput = document.getElementById('Title');
    bookAvailabilityMsg.textContent = ''; 
    titleInput.value = ''; 

    if (!isbn) {
        bookAvailabilityMsg.textContent = 'Please enter an ISBN';
        return;
    }

    fetch(`validateBook.php?ISBN=${encodeURIComponent(isbn)}`)
        .then(response => response.json())
        .then(data => {
            if (data.isAvailable) {
                bookAvailabilityMsg.textContent = 'Book available';
                titleInput.value = data.details.Title; 
            } else {
                bookAvailabilityMsg.textContent = isScan ? '' : 'Book not available'; 
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            bookAvailabilityMsg.textContent = 'Error fetching book details';
        });
}

document.getElementById('ISBN').addEventListener('input', handleISBNInput);



function togglePopup(popupId, show) {
    const activePopups = document.querySelectorAll('.popup.active');
    activePopups.forEach(popup => popup.classList.remove('active'));

    const popup = document.getElementById(popupId);
    if (show) {
        popup.classList.add("active");
    }
}

function closePopup(popupId) {
    document.getElementById(popupId).classList.remove("active");
}

    </script>
</head>
<body>
    
    <div class = "container-padding">
    <h1 style="color: black;">Borrow Operation</h1>
    

        <h1 style="color: black;">Borrow Operation</h1>


        <div class="header">
            <div class="topmain">
                <label for="stuId">Student ID:</label>
                <div class="search">
                <input type="text" name="memberID" placeholder="Search..." id="memberID" oninput="handleInput()">
                <button type="button" class="issueSearchButton" id ="searchButton" onclick="searchMember()">Search</button>

                </div>
            </div>
        </div>

        <!-- Issue Success Popup -->
<div class="popup" id="issueSuccessPopup">
    <div class="overlay"></div>
    <div class="content">
    <div class="alert-icon">
            <ion-icon name="checkmark-outline" style="color: red; font-size: 50px"></ion-icon>
        </div>
        <div class="close-btn" onclick="togglePopup('issueSuccessPopup', false)">&times;</div>
        <h1 style="color:black">Issue Successful</h1>
        <p>The book has been borrow successfully!</p>
    </div>
</div>


    <div class="details">
        <div class="issuedetails">
 
            <form id="issueForm" method="post">
                <div class="form-group">
                    <label for="fullname">Name:</label>
                    <input type="text" name="fullname" id="fullname" value="<?php echo isset($userData['fullname']) ? htmlspecialchars($userData['fullname']) : ''; ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="IC">Identification Number:</label>
                    <input type="text" name="IC" id="IC" value="<?php echo isset($userData['IC']) ? htmlspecialchars($userData['IC']) : ''; ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="class">Class:</label>
                    <input type="text" name="class" id="class" value="<?php echo isset($userData['class']) ? htmlspecialchars($userData['class']) : ''; ?>" readonly>
                </div>

                <div class="form-group">
    <label for="isbn">ISBN:</label>
    <input type="text" name="ISBN" id="ISBN" required oninput="validateBookAvailability()">
    <span id="book_availability_msg" class="validation-message"></span>
</div>
<div class="form-group">
    <label for="title">Title:</label>
    <input type="text" name="Title" id="Title" readonly>
</div>



                <div class="form-group">
                       <label for="borrowD">Borrow Date:</label>
                       <input type="date" name="borrowDate" id="borrowD" required onchange="calculateDueDate()"> 
               </div>

               <div class="form-group">
                     <label for="dueD">Due Date:</label>
                     <input type="date" name="dueDate" id="dueD" required readonly> 
               </div> 

              

            </form>
            <div class="issuebtn">
            <button type="button" onclick="issueBook(event)">Issue</button>
            <button type="button" onclick="searchAndDisplayIssues()">Display</button>
            </div>

            <table class ="table1">
                  
                      <thead>
                        <tr>
                            <th>Name: </th>
                            <th>Title: </th>
                            <th>Borrow Date: </th>
                            <th>Due Date: </th>
                        </tr>
                      </thead>
                      <tbody>
                               <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                               </tr>
                      </tbody>
            </table>
        </div>

        <div class="availableDetails">
    <div class="info-box">

        <div class="info-item issue-amount-allowed">
            <span class="info-title">Issue Total Allowed</span>
            <div class="totals-container">
                <span class="info-total">Total:</span>
                <span class="info-available red">Available:</span>
            </div>
        </div>
        <div class="info-item issue-book-allowed">
            <span class="info-title">Issue Book Allowed</span>
            <div class="totals-container">
                <span class="info-total">Total:</span>
                <span class="info-available red">Available:</span>
            </div>
        </div>
        </div>
   
</div>

       
    </div>
</body>
</html>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>