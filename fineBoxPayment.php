<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fine Payment</title>
    <?php include 'navigation.php';?>
    
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
        fetch('getMemberBox1.php', {
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

    var combinedFines = {};

    issues.forEach(issue => {
        if (issue.fineType === "missing_books") {
            if (!combinedFines[issue.BoxSerialNum]) {
                combinedFines[issue.BoxSerialNum] = { totalAmount: 0, bookISBN: [], copyCount: 0, datePaid: issue.datePaid };
            }
            combinedFines[issue.BoxSerialNum].totalAmount += parseFloat(issue.amount);
            combinedFines[issue.BoxSerialNum].bookISBN.push(issue.bookISBN + ' (' + issue.copyCount + ')');
            combinedFines[issue.BoxSerialNum].copyCount += issue.copyCount;
            combinedFines[issue.BoxSerialNum].datePaid = issue.datePaid || 'Not Paid';
        } else {
            var tr = document.createElement('tr');

            var memberIdTd = document.createElement('td');
            memberIdTd.textContent = issue.memberID;

            var BoxSerialNumTd = document.createElement('td');
            BoxSerialNumTd.textContent = issue.BoxSerialNum;

            var fineTypeTd = document.createElement('td');
            fineTypeTd.textContent = issue.fineType;

            var amountTd = document.createElement('td');
            amountTd.textContent = parseFloat(issue.amount).toFixed(2);

            var ISBNTd = document.createElement('td');
            ISBNTd.textContent = issue.bookISBN ? issue.bookISBN + ' (' + issue.copyCount + ')' : 'N/A';

            var dateOfPaymentTd = document.createElement('td');
            dateOfPaymentTd.textContent = issue.datePaid ? issue.datePaid : 'Not Paid';

            tr.appendChild(memberIdTd);
            tr.appendChild(BoxSerialNumTd);
            tr.appendChild(fineTypeTd);
            tr.appendChild(amountTd);
            tr.appendChild(ISBNTd);
            tr.appendChild(dateOfPaymentTd);

            tbody.appendChild(tr);
        }
    });

    Object.keys(combinedFines).forEach(BoxSerialNum => {
        var tr = document.createElement('tr');

        var memberIdTd = document.createElement('td');
        memberIdTd.textContent = memberId;

        var BoxSerialNumTd = document.createElement('td');
        BoxSerialNumTd.textContent = BoxSerialNum;

        var fineTypeTd = document.createElement('td');
        fineTypeTd.textContent = "Missing Books";

        var amountTd = document.createElement('td');
        amountTd.textContent = combinedFines[BoxSerialNum].totalAmount.toFixed(2);

        var ISBNTd = document.createElement('td');
        ISBNTd.textContent = combinedFines[BoxSerialNum].bookISBN.join(', ');

        var dateOfPaymentTd = document.createElement('td');
        dateOfPaymentTd.textContent = combinedFines[BoxSerialNum].datePaid;

        tr.appendChild(memberIdTd);
        tr.appendChild(BoxSerialNumTd);
        tr.appendChild(fineTypeTd);
        tr.appendChild(amountTd);
        tr.appendChild(ISBNTd);
        tr.appendChild(dateOfPaymentTd);

        tbody.appendChild(tr);
    });
}

function payBox() {
    var memberId = document.getElementById('memberID').value;
    var BoxSerialNum = document.getElementById('BoxSerialNum').value;
    var paymentFor = document.getElementById('paymentFor').value;
    var total = parseFloat(document.getElementById('total').value);

    var fineMapping = {
        "return_late": "1",
        "missing": "3",
        "damage": "4",
        "missing_books": "5"
    };

    var fineID = fineMapping[paymentFor];

    if (!memberId || !BoxSerialNum || !fineID || isNaN(total)) {
        alert('All fields must be filled out.');
        return;
    }

    var formData = new FormData();
    formData.append('memberID', memberId);
    formData.append('BoxSerialNum', BoxSerialNum);
    formData.append('fineID', fineID);
    formData.append('total', total);

    fetch('processBoxFinePayment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Payment successful!');
            searchAndDisplayIssues();
        } else {
            alert('Payment failed: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error processing payment:', error);
        alert('An error occurred while processing payment.');
    });
}

</script>

<div class="details">
    <div class="BookList">
        <div class="fineHeader">
            <h2>BOX PAYMENT</h2>
        </div>
        
        <form class="fineForm" method="POST">
            <div class="form-group">
                <label for="memberID">Member ID</label>
                <input type="text" id="memberID" oninput="retrieveStudentData()">
            </div>

            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" readonly>
            </div>

            <div class="form-group">
                <label for="BoxSerialNum">Box Serial Number:</label>
                <input type="text" id="BoxSerialNum">
            </div>
        
            <div class="form-group">
                <label for="paymentFor">Payment For</label>
                <select id="paymentFor">
                    <option value="select">Select An Option</option>
                    <option value="return_late">Return Late 5.00</option>
                    <option value="missing">Missing 15.00</option>
                    <option value="damage">Damage 10.00</option>
                    <option value="missing_books">Missing Books</option>
                </select>
            </div>

            <div class="form-group">
                <label for="total">Total</label>
                <input type="text" id="total">
            </div>
        
            <div class="issuebtn">
                <button type="button" onclick="payBox()">Paid</button>
                <button type="button" onclick="searchAndDisplayIssues()">Display</button>
            </div>
        </form>
        
        <table class="fineTable">
            <thead>
                <tr>
                    <th>MemberID</th>
                    <th>Box Serial Num</th>
                    <th>Fine Category</th>
                    <th>Amount</th>
                    <th>ISBN (Copy)</th>
                    <th>Date Paid</th>
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

</body>
</html>
