

    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Operation</title>
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
</head>
<body>
<?php include 'navigaLib.php'; ?>
  <div class = "container-padding">
    <h1 style="color: black;">Return Operation</h1>
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
        <p>The box has been returned successfully!</p>
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
                <label for="BoxSerialNum">Box ID:</label>
                <input type="text" name="BoxSerialNum" id="BoxSerialNum">
            </div>

            <div class="form-group">
    <label for="category">Category:</label>
    <input type="text" id="category" name="category" readonly>
</div>


            <div class="form-group">
                <label for="borrowDate">Borrow Date:</label>
                <input type="date" name="borrowDate" id="borrowDate" readonly> 
            </div>
            <div class="form-group">
                <label for="returnDate">Return Date:</label>
                <input type="date" name="dueDate" id="returnDate"> 
            </div> 
            <div class="issuebtn">
            <button type="button" onclick="returnBox(event)">Return</button>

            <button type="button" onclick="searchAndDisplayBoxReturns()">Display</button>

            </div>
        </form>


            <table class ="table1">
                  
                      <thead>
                        <tr>
                        <th>Box Serial Num: </th>
                            <th>Category: </th>
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
        </div>
   
</div>



    <script>
 
 function fetchBoxDetails() {
    var BoxSerialNum = document.getElementById('BoxSerialNum').value.trim();
    var memberID = document.getElementById('memberID').value.trim();
    if (BoxSerialNum && memberID) {
      fetch(`fetchBox1Details.php?BoxSerialNum=${encodeURIComponent(BoxSerialNum)}&memberID=${encodeURIComponent(memberID)}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            document.getElementById('category').value = data.category;
            document.getElementById('borrowDate').value = data.borrowDate;
            // Update the sidebar based on the current member
            fetchAndDisplayBoxLimit(memberID);
          } else {
            document.getElementById('category').value = '';
            document.getElementById('borrowDate').value = '';
          }
        })
        .catch(error => {
          console.error('Fetch error:', error);
        });
    } else {
      document.getElementById('category').value = '';
      document.getElementById('borrowDate').value = '';
    }
  }
  function fetchBorrowDate(boxSerialNum, memberID) {
    if (!boxSerialNum) {
        alert('Please enter a Box Serial Number.');
        return;
    }
    if (!memberID) {
        alert('Please enter a Member ID.');
        return;
    }

    console.log("Box Serial Number: ", boxSerialNum);  
    console.log("Member ID: ", memberID);  

    const url = `fetchBorrowDate.php?BoxSerialNum=${boxSerialNum}&memberID=${memberID}`;
    console.log("Constructed URL for fetch:", url);

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.issueDate) {
                document.getElementById('borrowDate').value = data.issueDate;
                console.log('Borrow date fetched successfully:', data.issueDate);
            } else {
                throw new Error(data.error || 'No information available for this box');
            }
        })
        .catch(error => {
            console.error('Error fetching borrow date:', error);
            alert('Error fetching borrow date: ' + error.message);
        });
}

document.getElementById('BoxSerialNum').addEventListener('change', function() {
    var boxSerialNum = this.value.trim();  
    var memberID = document.getElementById('memberID').value.trim();
    if (boxSerialNum && memberID) {
        fetchBorrowDate(boxSerialNum, memberID);
    }
});

document.getElementById('memberID').addEventListener('change', function() {
    var memberID = this.value.trim();
    if (memberID) {
        fetchMemberInfo();
        var boxSerialNum = document.getElementById('BoxSerialNum').value.trim();
        if (boxSerialNum) {
            fetchBorrowDate(boxSerialNum, memberID);
        }
    }
});

// Global variables

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

          // Fetch and update the sidebar with the new limits
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



    // Validate and return the box if all required fields are filled in
    function returnBox(event) {
        event.preventDefault();

        var memberID = document.getElementById('memberID').value.trim();
        var BoxSerialNum = document.getElementById('BoxSerialNum').value.trim();
        var returnDate = document.getElementById('returnDate').value.trim();

        if (!memberID || !BoxSerialNum || !returnDate) {
            alert('Please enter Member ID, Box Serial Number, and Return Date.');
            return;
        }

        var formData = new FormData();
        formData.append('memberID', memberID);
        formData.append('BoxSerialNum', BoxSerialNum);
        formData.append('returnDate', returnDate);

        fetch('returnbox1.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                togglePopup('returnSuccessPopup', true);
                document.getElementById('issueForm').reset();
            } else {
                alert('Return failed. Error: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error returning the box:', error);
            alert('Error returning the box. Please try again.');
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

function fetchIssueAvailability(memberID) {
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
                updateIssueAvailabilityDisplay(data);
            }
        })
        .catch(error => {
            console.error('Error fetching issue availability:', error);
        });
}

function searchAndDisplayBoxReturns() {
    var memberID = document.getElementById('memberID').value; 
    if (!memberID) {
        alert('Please enter a Member ID.');
        return;
    }

    fetch('returnBoxList.php?memberID=' + encodeURIComponent(memberID))
    .then(response => response.json())
    .then(data => {
        var tableBody = document.querySelector('.table1 tbody');
        tableBody.innerHTML = ''; 

        if (Array.isArray(data) && data.length > 0) {
            data.forEach(returnInfo => {
                var row = tableBody.insertRow();
                row.insertCell(0).textContent = returnInfo.BoxSerialNum || 'N/A'; 
                row.insertCell(1).textContent = returnInfo.category || 'N/A'; 
                row.insertCell(2).textContent = returnInfo.borrowDate || 'N/A'; 
                row.insertCell(3).textContent = returnInfo.DueDate || 'N/A'; 
                row.insertCell(4).textContent = returnInfo.ReturnDate || 'N/A'; 
                
            });
        } else {
            tableBody.innerHTML = '<tr><td colspan="6">No returns found for this member.</td></tr>';
        }
    })
    .catch(error => {
        console.error('Error fetching return records:', error);
        alert('Error fetching return records: ' + error.message);
    });
}

function fetchAndDisplayBoxLimit(memberID) {
    fetch(`fetchBoxLimit.php?memberID=${encodeURIComponent(memberID)}`)
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        return response.json();
      })
      .then(data => {
        if (data.error) {
          console.error('Error from server:', data.error);
          resetIssueAvailabilityDisplay();
        } else {
          updateIssueAvailabilityDisplay(data);
        }
      })
      .catch(error => {
        console.error('Error fetching box limit:', error);
        resetIssueAvailabilityDisplay();
      });
  }

  function updateIssueAvailabilityDisplay(data) {
    document.getElementById('issueAmountTotal').textContent = `Total: ${data.issueAmountAllowed.total}`;
    document.getElementById('issueAmountAvailable').textContent = `Available: ${data.issueAmountAllowed.available}`;
    document.getElementById('issueBoxTotal').textContent = `Total: ${data.issueBoxAllowed.total}`;
    document.getElementById('issueBoxAvailable').textContent = `Available: ${data.issueBoxAllowed.available}`;
  }

  function resetIssueAvailabilityDisplay() {
    document.getElementById('issueAmountTotal').textContent = "Total: --";
    document.getElementById('issueAmountAvailable').textContent = "Available: --";
    document.getElementById('issueBoxTotal').textContent = "Total: --";
    document.getElementById('issueBoxAvailable').textContent = "Available: --";
  }

  document.getElementById('memberID').addEventListener('input', handleInput);
  document.getElementById('searchButton').addEventListener('click', manualSearch);
  document.getElementById('BoxSerialNum').addEventListener('change', fetchBoxDetails);

    </script>
</body>
</html>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>