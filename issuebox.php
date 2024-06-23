<?php include 'navigation.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IssueBox</title>
    <link rel="stylesheet" href="Cssfile/issue.css">
    <link rel="stylesheet" href="Cssfile/list.css"> 
       <script>
     function calculateDueDate() {
    var borrowDate = document.getElementById('borrowDate').value;
    if (borrowDate) {
        var borrowDateObj = new Date(borrowDate);
        borrowDateObj.setDate(borrowDateObj.getDate() + 7);
        var dueDate = borrowDateObj.toISOString().split('T')[0];
        document.getElementById('dueDate').value = dueDate;
    }
}

//list function
function searchAndDisplayBoxIssues() {
    var memberIDInput = document.querySelector('input[name="memberID"]');
    var memberID = memberIDInput.value.trim();
    if (memberID) {
        console.log("Fetching issues for memberID:", memberID); 
        displayBoxIssues(memberID);
    } else {
        alert('Please enter a Member ID to search.');
    }
}

function displayBoxIssues(memberID) {
    let url = 'issueboxlist.php';
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
            var cellBoxSerialNum = row.insertCell(0);
            var cellCategory = row.insertCell(1);
            var cellBorrowDate = row.insertCell(2);
            var cellDueDate = row.insertCell(3);

            cellBoxSerialNum.textContent = issue.BoxSerialNum || 'N/A';
            cellCategory.textContent = issue.category || 'N/A';
            cellBorrowDate.textContent = issue.borrowDate || 'N/A';
            cellDueDate.textContent = issue.dueDate || 'N/A';
        });
    } else {
        tableBody.innerHTML = '<tr><td colspan="4">No box issues found for this member.</td></tr>';
    }
})
.catch(error => {
    console.error('Error fetching box issues:', error);
});
}

//search func

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


function handlePaste(event) {
    const pastedData = (event.clipboardData || window.clipboardData).getData('text');
    if (pastedData.length >= 6) {
        
        searchMember(true);
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

                fetchAndDisplayBoxLimit(memberID);
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

function fetchBoxDetails() {
    var BoxSerialNum = document.getElementById('BoxSerialNum').value;
    var memberID = document.getElementById('memberID').value;
    if (BoxSerialNum && memberID) {
        fetch('fetchBoxDetails.php?BoxSerialNum=' + encodeURIComponent(BoxSerialNum))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('category').value = data.box.category || 'No category found';

                fetchAndDisplayBoxLimit(memberID);
            } else {
                console.error('Box not found or error fetching details');
                document.getElementById('category').value = ''; 
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            document.getElementById('category').value = ''; 
        });
    } else {
        document.getElementById('category').value = ''; 
    }
}

// Event listeners
document.getElementById('memberID').addEventListener('input', handleInput);
document.getElementById('memberID').addEventListener('paste', handlePaste);
document.getElementById('searchButton').addEventListener('click', manualSearch);
document.getElementById('BoxSerialNum').addEventListener('change', fetchBoxDetails);


//issue func
async function issueBox(event) {
    event.preventDefault(); 

    var memberID = document.getElementById('memberID').value.trim();
    var BoxSerialNum = document.getElementById('BoxSerialNum').value.trim();
    var borrowDate = document.getElementById('borrowDate').value.trim();
    var dueDate = document.getElementById('dueDate').value.trim();

    const canBorrow = await checkBoxBorrowLimit(memberID);
    if (!canBorrow) {
        return; 
    }

    var missingFields = [];
    if (!memberID) {
        missingFields.push('Member ID');
    }
    if (!BoxSerialNum) {
        missingFields.push('Box Serial Number');
    }
    if (!borrowDate) {
        missingFields.push('Borrow Date');
    }

    if (missingFields.length > 0) {
        alert('Please fill in the required information: ' + missingFields.join(', ') + '.');
        return; 
    }

    var formData = new FormData();
    formData.append('memberID', memberID);
    formData.append('BoxSerialNum', BoxSerialNum);
    formData.append('borrowDate', borrowDate);
    formData.append('dueDate', dueDate);

    fetch('issueBox1.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            togglePopup('issueSuccessPopup', true); 
            document.getElementById('issueForm').reset(); 
        } else {
            alert('Failed to issue box. Error: ' + data.error); 
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while trying to issue the box.');
    });
}



// Function to fetch and display the issue limits
function fetchAndDisplayBoxLimit(memberID) {
    fetch('fetchBoxLimit.php?memberID=' + encodeURIComponent(memberID))
    .then(response => response.json())
    .then(data => {
        console.log('Data received from fetchBoxLimit:', data); 
        updateSidebar(data); 
    })
    .catch(error => {
        console.error('Error fetching box limit:', error);
    });
}

function updateSidebar(data) {
    if (data && data.issueAmountAllowed && data.issueBoxAllowed) {
        const issueAmountTotal = document.getElementById('issueAmountTotal');
        const issueAmountAvailable = document.getElementById('issueAmountAvailable');
        const issueBoxTotal = document.getElementById('issueBoxTotal');
        const issueBoxAvailable = document.getElementById('issueBoxAvailable');

        if (issueAmountTotal && issueAmountAvailable && issueBoxTotal && issueBoxAvailable) {
            issueAmountTotal.textContent = `Total: ${data.issueAmountAllowed.total}`;
            issueAmountAvailable.textContent = `Available: ${data.issueAmountAllowed.available}`;
            issueBoxTotal.textContent = `Total: ${data.issueBoxAllowed.total}`;
            issueBoxAvailable.textContent = `Available: ${data.issueBoxAllowed.available}`;
        } else {
            console.error('One or more elements do not exist in the DOM.');
        }
    } else {
        console.error('Invalid data structure:', data);
    }
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

                fetchAndDisplayBoxLimit(memberID);
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


function fetchBoxDetails() {
    var BoxSerialNum = document.getElementById('BoxSerialNum').value;
    var memberID = document.getElementById('memberID').value;
    if (BoxSerialNum && memberID) {
        fetch('fetchBoxDetails.php?BoxSerialNum=' + encodeURIComponent(BoxSerialNum))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('category').value = data.box.category || 'No category found';

                fetchAndDisplayBoxLimit(memberID);
            } else {
                console.error('Box not found or error fetching details');
                document.getElementById('category').value = ''; 
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            document.getElementById('category').value = ''; 
        });
    } else {
        document.getElementById('category').value = ''; 
    }
}

// Event listeners
document.getElementById('memberID').addEventListener('input', handleInput);
document.getElementById('memberID').addEventListener('paste', handlePaste);
document.getElementById('searchButton').addEventListener('click', manualSearch);
document.getElementById('BoxSerialNum').addEventListener('change', fetchBoxDetails);


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


document.addEventListener('DOMContentLoaded', function() {
    
});

//checkLimit
function checkBoxBorrowLimit(memberID) {
    return fetch(`checkBoxBorrowLimit.php?memberID=${encodeURIComponent(memberID)}`)
        .then(response => response.json())
        .then(data => {
            if (data.currentlyBorrowed >= 2) {
                alert("You have reached your borrowing limit for boxes. Please return some boxes to borrow more.");
                return false;
            }
            return true;
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to check borrowing limit for boxes.');
            return false;
        });
}

    </script>
</head>
<body>
    <h1 style="color: black;">Borrow Operation</h1>
    

        <div class="header">
            <div class="topmain">
                <label for="stuId">Student ID:</label>
                <div class="search">
                <input type="text" name="memberID" placeholder="Search..." id="memberID" oninput="handleInput()">
                <button type="button" class="issueSearchButton" id="searchButton" onclick="manualSearch()">Search</button>

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
        <h1 style= "color: black;">Issue Successful</h1>
        <p>The box has been borrow successfully!</p>
    </div>
</div>


    <div class="details">
        <div class="issuedetails">
 
        <form id="issueForm" method="post">
        <input type="hidden" name="memberID" id="memberID" value="TheMemberIDValue" />
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
    <label for="BoxSerialNum">Box ID:</label>
    <input type="text" name="BoxSerialNum" id="BoxSerialNum" onchange="fetchBoxDetails()">
</div>

<div class="form-group">
    <label for="category">Category:</label>
    <input type="text" name="category" id="category" readonly>
</div>



               
                <div class="form-group">
                       <label for="borrowDate">Borrow Date:</label>
                       <input type="date" name="borrowDate" id="borrowDate" required onchange="calculateDueDate()"> 
               </div>

               <div class="form-group">
                     <label for="dueDate">Due Date:</label>
                     <input type="date" name="dueDate" id="dueDate" required readonly> 
               </div> 

              

            </form>
            <div class="issuebtn">
            <button type="button" onclick="issueBox(event)">Issue</button>

            <button type="button" onclick="searchAndDisplayBoxIssues()">Display</button>

            </div>

            <table class ="table1">
                  
                      <thead>
                        <tr>
                            <th>Box Serial Num: </th>
                            <th>Category: </th>
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
                  <span class="info-total" id="issueAmountTotal">Total:</span>
                 <span class="info-available red" id="issueAmountAvailable">Available:</span>
             </div>
    </div>
    
<div class="info-item issue-book-allowed">
    <span class="info-title">Issue Box Allowed</span>
    <div class="totals-container">
        <span class="info-total" id="issueBoxTotal">Total:</span>
        <span class="info-available red" id="issueBoxAvailable">Available:</span>
    </div>
</div>

</body>
</html>
