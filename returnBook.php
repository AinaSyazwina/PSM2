
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Operation</title>
    <link rel="stylesheet" href="Cssfile/issue.css">
    <link rel="stylesheet" href="Cssfile/list.css"> 
</head>
<body>
  <?php include 'navigation.php'; ?>
    <h1 style="color: black;">Return Operation</h1>
    

    <div class="header" id="returnOperation">
        <div class="topmain">
            <label for="stuId">Student ID:</label>
            <div class="search">
                <!-- Input for Member ID -->
                <input type="text" name="memberID" placeholder="Search..." id="memberID" oninput="handleInput()">
                <button id="searchButton" onclick="manualSearch()">Search</button>

            </div>
        </div>
    </div>
     <!-- Success Popup -->
     <div class="popup" id="returnSuccessPopup">
    <div class="overlay"></div>
    <div class="content">
    <div class="alert-icon">
            <ion-icon name="checkmark-outline" style="color: red; font-size: 50px"></ion-icon>
        </div>
        <div class="close-btn" onclick="togglePopup('returnSuccessPopup', false)">&times;</div>
        <h1 style="color: black;">Return Successful</h1>
        <p>The book has been returned successfully!</p>
    </div>
</div>


    <div class="details">
        <div class="issuedetails">
 
        <form id="issueForm" method="post">
                <div class="form-group">
                    <label for="fullname">Name:</label>
                    <input type="text" name="fullname" id="fullname" readonly>
                </div>
                <div class="form-group">
                    <label for="IC">Identification Number:</label>
                    <input type="text" name="IC" id="IC" readonly>
                </div>
                <div class="form-group">
                    <label for="class">Class:</label>
                    <input type="text" name="class" id="class" readonly>
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
                <input type="date" id="borrowD" name="borrowDate" readonly>
            </div>
                <div class="form-group">
                <label for="returnD">Return Date:</label>
                     <input type="date" name="returnDate" id="returnD" required> 
                </div>
                <div class="issuebtn">
                <button type="button" onclick="returnBook(event)">Return</button>

                    <button type="button" onclick="displayReturns()">Display</button>
                </div>
            </form>


            <table class ="table1">
                  
                      <thead>
                        <tr>
                            <th>Name: </th>
                            <th>ISBN: </th>
                            <th>Borrow Date: </th>
                            <th>Due Date: </th>
                            <th>Return Date: </th>
                            
                        </tr>
                      </thead>
                      <tbody>
                               <tr>
                                    <td></td>
                                    <td></td>
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


<script>

    //SEARCH

let lastInputTime = Date.now();
let inputTimeout = null;
let isSearching = false;

function handleInput() {
    const inputField = document.getElementById('memberID');
    const currentTime = Date.now();
    const timeDifference = currentTime - lastInputTime;
    lastInputTime = currentTime; 

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




 function fetchBookDetails(bookID, memberID) {
    if (!bookID) {
        alert('Please enter a Book ID.');
        return;
    }
    if (!memberID) {
        alert('Please enter a Member ID.');
        return;
    }

    console.log("Book ID: ", bookID);  
    console.log("Member ID: ", memberID);  

    fetch(`fetchBookDetails.php?bookID=${bookID}&memberID=${memberID}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                document.getElementById('borrowD').value = data.issueDate;
                document.getElementById('book_availability_msg').textContent = data.isReturned ? 'This book has already been returned.' : '';
            } else {
                throw new Error(data.error || 'No information available for this book');
            }
        })
        .catch(error => {
            console.error('Error fetching book details:', error);
            alert('Error fetching book details: ' + error.message);
        });
}


function validateBookAvailability() {
    var bookID = document.getElementById('book_acquisition').value;
    var memberID = document.getElementById('memberID').value;  
    fetchBookDetails(bookID, memberID);
}
   
function validateBookAvailability() {
    var isbn = document.getElementById('ISBN').value;
    var memberID = document.getElementById('memberID').value;
    var bookAvailabilityMsg = document.getElementById('book_availability_msg');
    var titleInput = document.getElementById('Title');
    var borrowDateInput = document.getElementById('borrowD');

    if (!isbn || !memberID) {
        bookAvailabilityMsg.textContent = 'Please enter both an ISBN and a Member ID';
        titleInput.value = '';
        borrowDateInput.value = '';
        return;
    }

    fetch(`validateBook1.php?ISBN=${encodeURIComponent(isbn)}&memberID=${encodeURIComponent(memberID)}`)
    .then(response => response.json())
    .then(data => {
        if (data.isAvailable) {
            bookAvailabilityMsg.textContent = 'Book available';
            titleInput.value = data.details.Title;
            borrowDateInput.value = data.details.IssueDate;
        } else {
            bookAvailabilityMsg.textContent = data.error || 'Book not available';
            titleInput.value = '';
            borrowDateInput.value = '';
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        bookAvailabilityMsg.textContent = 'Error fetching book details';
        titleInput.value = '';
        borrowDateInput.value = '';
    });
}



function displayReturns() {
    var memberID = document.querySelector('input[name="memberID"]').value;
    fetch('displayReturns.php?memberID=' + memberID)
    .then(response => response.json())
    .then(data => {
        var tableBody = document.querySelector('.table1 tbody');
        tableBody.innerHTML = '';
        data.forEach(returnRecord => {
            var row = tableBody.insertRow();
           
        });
    })
    .catch(error => {
        console.error('Error fetching return records:', error);
    });
}

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
function returnBook(event) {
    event.preventDefault();  

    var memberID = document.getElementById('memberID').value;
    var isbn = document.getElementById('ISBN').value;
    var returnDate = document.getElementById('returnD').value;

    var formData = new FormData();
    formData.append('memberID', memberID);
    formData.append('ISBN', isbn);
    formData.append('returnDate', returnDate);

    fetch('returnBook1.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            togglePopup('returnSuccessPopup', true);  
            document.getElementById('issueForm').reset();  
        } else {
            alert('Failed to return book. Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error returning book:', error);
        alert('Error returning book: ' + error.message);
    });
}


function displayReturns() {
    var memberID = document.getElementById('memberID').value; 
    fetch('returnBookList.php?memberID=' + memberID)
    .then(response => response.json())
    .then(data => {
        var tableBody = document.querySelector('.table1 tbody');
        tableBody.innerHTML = ''; 

        data.forEach(issue => {
            var row = tableBody.insertRow();
            row.insertCell(0).textContent = issue.fullname || 'N/A';
            row.insertCell(1).textContent = issue.ISBN || 'N/A';
            row.insertCell(2).textContent = issue.borrowDate || 'N/A';
            row.insertCell(3).textContent = issue.dueDate || 'N/A';
            row.insertCell(4).textContent = issue.ReturnDate || 'N/A'; 
          
        });
    })
    .catch(error => {
        console.error('Error fetching return records:', error);
        alert('Error fetching return records: ' + error.message);
    });
}


function fetchIssueAvailability(memberID) {
        console.log("Fetching issue availability for Member ID:", memberID);
        fetch(`issueAvail.php?memberID=${memberID}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok for fetching availability');
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    console.error('Error from server:', data.error);
                } else {
                    console.log('Received data:', data);
                    updateIssueAvailabilityDisplay(data);
                }
            })
            .catch(error => {
                console.error('Error fetching issue availability:', error);
            });
    }

    function updateIssueAvailabilityDisplay(data) {
        var infoBox = document.querySelector('.info-box');
        if (!infoBox) return; 

        var issueAmountAllowedSpanTotal = infoBox.querySelector('.issue-amount-allowed .info-total');
        var issueAmountAllowedSpanAvailable = infoBox.querySelector('.issue-amount-allowed .info-available');
        var issueBookAllowedSpanTotal = infoBox.querySelector('.issue-book-allowed .info-total');
        var issueBookAllowedSpanAvailable = infoBox.querySelector('.issue-book-allowed .info-available');

        issueAmountAllowedSpanTotal.textContent = `Total: ${data.issueAmountAllowed.total}`;
        issueAmountAllowedSpanAvailable.textContent = `Available: ${data.issueAmountAllowed.available}`;
        issueBookAllowedSpanTotal.textContent = `Total: ${data.issueBookAllowed.total}`;
        issueBookAllowedSpanAvailable.textContent = `Available: ${data.issueBookAllowed.available}`;
    }

    document.getElementById('memberID').addEventListener('input', handleInput);
    document.getElementById('searchButton').addEventListener('click', manualSearch);




    </script>
</body>
</html>


