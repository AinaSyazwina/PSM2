<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fine Payment</title>
    <?php include 'navigation.php'; ?>
    
    <link rel="stylesheet" href="Cssfile/fine.css">
</head>
<body>

<script>
function fetchStudentName(event) {
    if (event.key === 'Enter') {
        var memberId = document.getElementById('memberID').value;
        fetch('getStudentName.php', {
            method: 'POST',
            body: JSON.stringify({ memberID: memberId }), 
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.fullName) {
                document.getElementById('name').value = data.fullName;
            } else {
                alert(data.message || 'Student name not found.');
            }
        })
        .catch(error => {
            console.error('Error fetching student name:', error);
        });
    }
}

function fetchFineAmountAndIssueFine() {
    var memberId = document.getElementById('memberID').value;
    var isbn = document.getElementById('isbn').value;  // Changed field from bookID to isbn
    var paymentForSelect = document.getElementById('paymentFor');
    var paymentFor = paymentForSelect.options[paymentForSelect.selectedIndex].value;

    if (paymentFor === 'select') {
        alert('Please select a valid fine type.');
        return;
    }

    fetch('getFineAmount.php', {
        method: 'POST',
        body: JSON.stringify({ fineType: paymentFor }),
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.amount) {
            // Now call issueFine with the correct amount
            issueFine(memberId, isbn, paymentFor, data.amount);
        } else {
            alert(data.message || 'Failed to fetch fine amount.');
        }
    })
    .catch(error => {
        console.error('Error fetching fine amount:', error);
    });
}

function issueFine(memberId, isbn, paymentFor, total) {
    var formData = new FormData();
    formData.append('memberID', memberId);
    formData.append('isbn', isbn);  // Changed to isbn
    formData.append('paymentFor', paymentFor.toLowerCase());
    formData.append('total', total);

    fetch('processFineIssue.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Fine issued successfully.');
        } else {
            alert('Failed to issue fine: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error issuing fine:', error);
        alert('An error occurred while issuing the fine.');
    });
}
</script>

<div class="details">
    <div class="BookList">
        <div class="fineHeader">
            <h2>ISSUE BOOK FINE</h2>
        </div>
        
        <form class="fineForm">
            <div class="form-group">
                <label for="memberID">Member ID</label>
                <input type="text" id="memberID" name="memberID" onkeyup="fetchStudentName(event)">
            </div>
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" readonly>
            </div>
            <div class="form-group">
                <label for="isbn">ISBN</label>
                <input type="text" id="isbn" name="isbn">
            </div>
            <div class="form-group">
                <label for="paymentFor">Payment For</label>
                <select id="paymentFor">
                    <option value="select">Select An Option</option>
                    <option value="missing">Missing 5.00</option>
                    <option value="damage">Damage 4.00</option>
                </select>
            </div>
            <div class="issuebtn">
                <button type="button" onclick="fetchFineAmountAndIssueFine()">Issue</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
