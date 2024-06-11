<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fine Payment</title>
    <?php include 'navigation.php'; ?>
    <link rel="stylesheet" href="Cssfile/fine.css">
    <style>
        .add-remove-buttons {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .add-remove-buttons button {
            width: 30px;
            height: 30px;
            background-color: #4c44b6;
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 20px;
            line-height: 30px;
            cursor: pointer;
        }

        .add-remove-buttons button:hover {
            background-color: #3b3399;
        }

        .form-group input {
            padding: 8px;
            border: 1px solid #ccc; /* Adjust the border as needed */
            border-radius: 4px;
        }

        .form-group {
            margin-bottom: 10px;
        }
    </style>
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
    var BoxSerialNum = document.getElementById('BoxSerialNum').value;
    var paymentForSelect = document.getElementById('paymentFor');
    var paymentFor = paymentForSelect.options[paymentForSelect.selectedIndex].value;

    fetch('getFineBoxAmount.php', {
        method: 'POST',
        body: JSON.stringify({ fineType: paymentFor }),
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.amount) {
            issueFine(memberId, BoxSerialNum, paymentFor, data.amount);
        } else {
            alert(data.message || 'Failed to fetch fine amount.');
        }
    })
    .catch(error => {
        console.error('Error fetching fine amount:', error);
    });
}

function handleFineTypeChange() {
    var paymentForSelect = document.getElementById('paymentFor');
    var paymentFor = paymentForSelect.options[paymentForSelect.selectedIndex].value;

    if (paymentFor === 'missing_books') {
        document.getElementById('isbnFields').style.display = 'block';
        document.getElementById('isbnFields').innerHTML = ''; // Clear existing fields
        addBookField(); // Add initial fields
    } else {
        document.getElementById('isbnFields').style.display = 'none';
        document.getElementById('isbnFields').innerHTML = ''; // Clear fields
    }
}

function addBookField() {
    const isbnField = document.createElement('div');
    isbnField.classList.add('form-group');
    isbnField.innerHTML = `
        <label for="isbn">ISBN:</label>
        <input type="text" class="isbn" name="isbn">
    `;

    const copyCountField = document.createElement('div');
    copyCountField.classList.add('form-group');
    copyCountField.innerHTML = `
        <label for="copyCount">Copy Count:</label>
        <input type="number" class="copyCount" name="copyCount">
    `;

    const addRemoveButton = document.createElement('div');
    addRemoveButton.classList.add('add-remove-buttons');
    addRemoveButton.innerHTML = `
        <button type="button" onclick="addBookField()">+</button>
        <button type="button" onclick="removeBookField(this)">-</button>
    `;

    const fieldWrapper = document.createElement('div');
    fieldWrapper.appendChild(isbnField);
    fieldWrapper.appendChild(copyCountField);
    fieldWrapper.appendChild(addRemoveButton);

    document.getElementById('isbnFields').appendChild(fieldWrapper);
}

function removeBookField(button) {
    button.parentElement.parentElement.remove();
}

function issueFine(memberId, BoxSerialNum, paymentFor, total) {
    const isbnFields = document.querySelectorAll('.isbn');
    const copyCountFields = document.querySelectorAll('.copyCount');

    const isbnArray = [];
    const copyCountArray = [];

    isbnFields.forEach((field, index) => {
        isbnArray.push(field.value);
        copyCountArray.push(copyCountFields[index].value);
    });

    const formData = new FormData();
    formData.append('memberID', memberId);
    formData.append('BoxSerialNum', BoxSerialNum);
    formData.append('paymentFor', paymentFor.toLowerCase());
    formData.append('total', parseFloat(total).toFixed(2));
    formData.append('isbn', JSON.stringify(isbnArray));
    formData.append('copyCount', JSON.stringify(copyCountArray));

    fetch('processFineBoxIssue.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => { throw new Error(text) });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('Fine issued successfully.');
            resetForm(); // Reset the form after successful fine issuance
        } else {
            alert('Failed to issue fine: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error issuing fine:', error);
        alert('An error occurred while issuing the fine: ' + error.message);
    });
}

function resetForm() {
    document.querySelector('.fineForm').reset();
    document.getElementById('isbnFields').style.display = 'none';
    document.getElementById('isbnFields').innerHTML = '';
}
</script>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fine Payment</title>
   
    <link rel="stylesheet" href="Cssfile/fine.css">
    <style>
        .add-remove-buttons {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .add-remove-buttons button {
            width: 30px;
            height: 30px;
            background-color: #4c44b6;
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 20px;
            line-height: 30px;
            cursor: pointer;
        }

        .add-remove-buttons button:hover {
            background-color: #3b3399;
        }

        .form-group input {
            padding: 8px;
            border: 1px solid #ccc; /* Adjust the border as needed */
            border-radius: 4px;
        }

        .form-group {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="details">
    <div class="BookList">
        <div class="fineHeader">
            <h2>ISSUE BOX FINE</h2>
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
                <label for="BoxSerialNum">Box Serial Number:</label>
                <input type="text" id="BoxSerialNum" name="BoxSerialNum">
            </div>
            <div class="form-group">
                <label for="paymentFor">Payment For</label>
                <select id="paymentFor" onchange="handleFineTypeChange()">
                    <option value="select">Select An Option</option>
                    <option value="missing">Missing Box 15.00</option>
                    <option value="damage">Damage 10.00</option>
                    <option value="missing_books">Missing Books</option>
                </select>
            </div>
            <div id="isbnFields" style="display: none;">
                <div class="form-group">
                    <label for="isbn">ISBN:</label>
                    <input type="text" class="isbn" name="isbn">
                </div>
                <div class="form-group">
                    <label for="copyCount">Copy Count:</label>
                    <input type="number" class="copyCount" name="copyCount">
                </div>
                <div class="add-remove-buttons">
                    <button type="button" onclick="addBookField()">+</button>
                    <button type="button" onclick="removeBookField(this)">-</button>
                </div>
            </div>
            <div class="issuebtn">
                <button type="button" onclick="fetchFineAmountAndIssueFine()">Issue</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
