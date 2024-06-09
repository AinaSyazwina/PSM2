<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <?php include 'navigation.php'; ?>
    <link rel="stylesheet" href="Cssfile/report.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    var headers = {
        'Borrow Return, Book': ['No', 'Name', 'Book ID', 'Title', 'Borrow Date', 'Due Date', 'Return Date', 'Status'],
        'Borrow Return, Box': ['No', 'Name', 'Box Serial Number', 'Category', 'Borrow Date', 'Due Date', 'Return Date', 'Status'],
        'Fine, Book': ['No', 'Name', 'Book ID', 'Title', 'Due Date', 'Return Date', 'Fine Category', 'Total', 'Status'],
        'Fine, Box': ['No', 'Name', 'Box Serial Number', 'Category','Due Date', 'Return Date', 'Fine Category', 'Total', 'Status']
    };

    function updateTableHeaders() {
        var category = $('#category').val();
        var reportType = $('#reportType').val();
        var key = reportType + ', ' + category;

        var tableHeaders = headers[key] || [];
        var thead = $('.BookList table thead');
        thead.empty(); 

        var row = $('<tr></tr>');
        tableHeaders.forEach(function(header) {
            row.append($('<th></th>').text(header));
        });
        thead.append(row);
    }

    $('#submit-btn').click(function(event) {
        event.preventDefault();
        updateTableHeaders();

        var session = $('#session').val();
        var month = $('#month').val();
        var reportType = $('#reportType').val();
        var category = $('#category').val();

        var data = {
            session: session,
            month: month,
            reportType: reportType,
            category: category
        };

        $.ajax({
    type: 'POST',
    url: 'report1.php',
    data: data,
    dataType: 'json',
    success: function(response) {
        console.log("Processed response:", response);
        var tbody = $('.BookList table tbody');
        tbody.empty(); 

        if (response.error) {
            console.error("Error from server:", response.error);
            tbody.append('<tr><td colspan="9">' + response.error + '</td></tr>');
        } else if (response.data && response.data.length === 0) {
            console.log("No data returned from the server.");
            tbody.append('<tr><td colspan="9">No data available for the selected criteria.</td></tr>');
        } else if (response.data) {
           
response.data.forEach(function(item, index) {
    var row = $('<tr></tr>');
    row.append('<td>' + (index + 1) + '</td>');
    row.append('<td>' + item.fullname + '</td>'); 

    if (category === 'Box') {
        row.append('<td>' + item.BoxSerialNum + '</td>'); 
        row.append('<td>' + item.category + '</td>');
    } else if (category === 'Book') {
        row.append('<td>' + item.ISBN + '</td>');
        row.append('<td>' + item.Title + '</td>');
    }

    if (reportType === 'Fine') {
        row.append('<td>' + item.DueDate + '</td>');
        row.append('<td>' + item.ReturnDate + '</td>');
        row.append('<td>' + item.FineType + '</td>'); 
        row.append('<td>' + item.FineAmount + '</td>');
        row.append('<td>' + item.FineStatus + '</td>'); 
    } else {
        row.append('<td>' + item.IssueDate + '</td>');
        row.append('<td>' + item.DueDate + '</td>');
        row.append('<td>' + item.ReturnDate + '</td>');
        row.append('<td>' + item.Status + '</td>'); 
    }
    tbody.append(row);
});

        }
    },
    error: function(xhr, status, error) {
        console.error("Server responded with status: " + status + " Error: " + error);
        var tbody = $('.BookList table tbody');
        tbody.empty(); // Clear existing rows in case of error
        console.error("Raw response:", xhr.responseText);
        console.error("Status: " + status + " Error: " + error);
        tbody.append('<tr><td colspan="9">Error loading data.</td></tr>');
    }
});


    });
});
</script>

<script>
// Assuming the 'formSubmitted' flag is defined at a higher scope if not in this script
var formSubmitted = false; // Initialize the flag to false

$(document).ready(function() {
    $('#submit-btn').click(function(event) {
        event.preventDefault();
        // Assuming code to update and submit the form data is here
        formSubmitted = true; // Set the flag to true when form is successfully submitted
    });

    $('.btn').click(function(event) {
        event.preventDefault();
        if (!formSubmitted) {
            alert("Please submit the category before printing.");
            return; 
        }

        var dataToPrint = {
            session: $('#session').val(),
            month: $('#month').val(),
            reportType: $('#reportType').val(),
            category: $('#category').val()
        };

        $.ajax({
            type: 'POST',
            url: 'reportPrint.php',
            data: dataToPrint,
            xhrFields: {
                responseType: 'blob'  // to ensure you handle the PDF as binary data
            },
            success: function(response) {
                var blob = new Blob([response], { type: 'application/pdf' });
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = "Report.pdf";
                document.body.appendChild(link); // for Firefox
                link.click();
                document.body.removeChild(link); // remove the link when done
            },
            error: function(xhr, status, error) {
                console.error("Error: " + error);
            }
        });
    });
});
</script>

</head>
<body>

<h1 style="color: black;text-align:center;">REPORT</h1>

<div class="form-container">
        <div class="form-group">
        
        <div class="field-container">

        <label for="year" >Select Session:</label>
            <select id="session" >
                <option value="2025">2025</option>
                <option value="2024">2024</option>
                <option value="2023">2023</option>
                <option value="2022">2022</option>
            </select>
        </div>
            

        <div class="field-container">

        <label for="month" >Select Month:</label>
            <select id="month" >
                <option value="all">All</option>
                <option value="January">January</option>
                <option value="February">February</option>
                <option value="March">March</option>
                <option value="April">April</option>
                <option value="May">May</option>
                <option value="June">June</option>
                <option value="July">July</option>
                <option value="August">August</option>
                <option value="September">September</option>
                <option value="October">October</option>
                <option value="November">November</option>
                <option value="December">December</option>
               
            </select>
        </div>
        
        <div class="field-container">

          <label for="reportType" >Select Report:</label>
            <select id="reportType" >
                <option value="Borrow Return">Borrow Return </option>
                <option value="Fine">Fine</option>
            </select>
        </div>

        <div class="field-container"> 
        <label for="category" >Select Category:</label>
            <select id="category" >
                <option value="Book">Book</option>
                <option value="Box">Box</option>
                
            </select>
        </div>
        <a href="#" id="submit-btn" class="submitbtn">Submit</a>

        </div>
    </div>

    <div class="details">
    <div class="BookList">
        <table>
            <thead></thead> 
            <tbody></tbody>
        </table>
        <div class="view">
            <a href="#" class="btn">Print PDF</a>
        </div>
    </div>
</div>

</body>
</html>
