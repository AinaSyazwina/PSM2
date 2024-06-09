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
function retrieveStudentData() {
    var memberId = document.getElementById('memberID').value;
    if (memberId) {
        fetch('getStudentData.php?memberId=' + memberId)
            .then(response => response.json())
            .then(data => {
                document.getElementById('name').value = data.fullName;
            })
            .catch(error => {
                console.error('Error fetching student data:', error);
            });
    }
}

function searchAndDisplayIssues() {
    var memberId = document.getElementById('memberID').value.trim();

    if (memberId) {
        fetch('getMemberData.php', {
            method: 'POST',
            body: JSON.stringify({ memberID: memberId }),
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateTable(data.data, memberId);
            } else {
                alert(data.message);
                console.error('Error message: ', data.message);
            }
        })
        .catch(error => {
            console.error('Error fetching issues data:', error);
            alert('An error occurred while fetching data.');
        });
    } else {
        alert('Member ID is required.');
    }
}

function populateTable(issues, memberId) {
    var tbody = document.querySelector('.fineTable tbody');
    tbody.innerHTML = '';

    issues.forEach(issue => {
        var tr = document.createElement('tr');

        var memberIdTd = document.createElement('td');
        memberIdTd.textContent = issue.memberID;

        var ISBNTd = document.createElement('td');
        ISBNTd.textContent = issue.ISBN ? issue.ISBN : 'N/A'; // Changed to ISBN

        var fineTypeTd = document.createElement('td');
        fineTypeTd.textContent = issue.FineType.replace('_', ' '); // Changed to FineType

        var amountTd = document.createElement('td');
        amountTd.textContent = issue.amount;

       
        var dateOfPaymentTd = document.createElement('td');
        dateOfPaymentTd.textContent = issue.datePaid ? issue.datePaid : 'Not Paid';

        tr.appendChild(memberIdTd);
        tr.appendChild(ISBNTd);
        tr.appendChild(fineTypeTd);
        tr.appendChild(amountTd);
       
        tr.appendChild(dateOfPaymentTd);

        tbody.appendChild(tr);
    });
}

function payBook() {
    var form = document.getElementById('paymentForm');
    var formData = new FormData(form);

    var fineMapping = {
        "return_late": "1",
        "missing": "3",
        "damage": "4"
    };

    var paymentFor = form.querySelector('[name="paymentFor"]').value;
    var fineID = fineMapping[paymentFor.toLowerCase().replace(' ', '_')];
    formData.append('fineID', fineID);

    // Validate the total amount
    var total = parseFloat(form.querySelector('[name="total"]').value);
    var requiredAmounts = {
        "return_late": 2.00,
        "missing": 5.00,
        "damage": 4.00
    };

    if (total !== requiredAmounts[paymentFor]) {
        alert('Insufficient amount. Please enter the correct amount for ' + paymentFor.replace('_', ' ') + ': RM' + requiredAmounts[paymentFor].toFixed(2));
        return;
    }

    fetch('processFinePayment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Payment successful!');
        } else {
            alert('Payment failed: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error processing payment:', error);
        alert('An error occurred while processing payment.');
    });
}

function showPopup() {
    var popup = document.getElementById('confirmationPopup');
    popup.classList.add('active');
}

function hidePopup() {
    var popup = document.getElementById('confirmationPopup');
    popup.classList.remove('active');
}

function togglePopup() {
    var popup = document.getElementById('confirmationPopup');
    if (popup.classList.contains('active')) {
        hidePopup();
    } else {
        showPopup();
    }
}
</script>

<div class="details">
    <div class="BookList">
        <div class="fineHeader">
            <h2>BOOK PAYMENT</h2>
        </div>
        
        <form class="fineForm" id="paymentForm" method="POST">
            <div class="form-group">
                <label for="memberID">Member ID:</label>
                <input type="text" id="memberID" name="memberID" required oninput="retrieveStudentData()">
            </div>
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" readonly>
            </div>
            <div class="form-group">
                <label for="ISBN">ISBN:</label>
                <input type="text" id="ISBN" name="ISBN" required>
            </div>
            <div class="form-group">
                <label for="paymentFor">Payment For:</label>
                <select id="paymentFor" name="paymentFor" required>
                    <option value="">Select An Option</option>
                    <option value="return_late">Return Late 2.00</option>
                    <option value="missing">Missing 5.00</option>
                    <option value="damage">Damage 4.00</option>
                </select>
            </div>
            <div class="form-group">
                <label for="total">Total:</label>
                <input type="text" id="total" name="total" required>
            </div>
            <div class="issuebtn">
                <button type="button" onclick="payBook()">Paid</button>
                <button type="button" onclick="searchAndDisplayIssues()">Display</button>
            </div>
        </form>
        
        <table class="fineTable">
            <thead>
                <tr>
                    <th>MemberID</th>
                    <th>ISBN</th>
                    <th>Category</th>
                    <th>Total</th>
                    <th>Date of Payment</th>
                    
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        
        <div class="view">
            <a href="#" class="btn">View All</a>
        </div>
    </div>
</div>

 <!-- Paid Success Popup -->
<div class="popup1" id="confirmationPopup" style="display: none;">
    <div class="overlay"></div>
    <div class="content">
        <div class="alert-icon">
            <ion-icon name="checkmark-outline"></ion-icon>
        </div>
        <h1>Payment Successful!</h1>
        <p>Your payment has been processed successfully.</p>
        <div class="deletebutton">
            <button type="button" class="cancelbtn" onclick="hidePopup()">Close</button>
        </div>
        <div class="close-btn" onclick="hidePopup()">&times;</div>
    </div>
</div>

</body>
</html>
