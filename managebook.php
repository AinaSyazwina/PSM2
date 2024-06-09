<?php
include 'config.php';

$recordsPerPage = 10;  
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $recordsPerPage;
$sort = $_GET['sort'] ?? 'All'; // Fetch the sort criterion from the query parameter

// Determine the sorting criteria
$query = "SELECT * FROM books";
if ($sort !== 'All') {
    switch ($sort) {
        case 'Latest':
            $query .= " ORDER BY DateReceived DESC";
            break;
        case 'Oldest':
            $query .= " ORDER BY DateReceived ASC";
            break;
        case 'Fiction':
        case 'Non-Fiction':
        case 'Mystery':
        case 'FairyTale':
        case 'Action':
        case 'Fantasy':
        case 'Historical':
            $query .= " WHERE genre = '" . mysqli_real_escape_string($conn, $sort) . "' ORDER BY Title";
            break;
        default:
            $query .= " ORDER BY Title"; // Default sort
            break;
    }
} else {
    $query .= " ORDER BY Title";
}
$query .= " LIMIT $recordsPerPage OFFSET $offset";

$result = mysqli_query($conn, $query);

$countQuery = "SELECT COUNT(*) AS totalRecords FROM books";
if ($sort !== 'All' && $sort !== 'Latest' && $sort !== 'Oldest') {
    $countQuery .= " WHERE genre = '" . mysqli_real_escape_string($conn, $sort) . "'";
}
$countResult = mysqli_query($conn, $countQuery);
$countRow = mysqli_fetch_assoc($countResult);
$totalRecords = $countRow['totalRecords'];
$totalPages = ceil($totalRecords / $recordsPerPage);
$uploadDir = '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books</title>
    <link rel="stylesheet" href="Cssfile/style3.css">
    <link rel="stylesheet" href="Cssfile/style4.css">
</head>
<body>
<?php include 'navigation.php'; ?>

<div class="actionBtn">
    <a href="addbook.php" class="add-btn">Add Book</a>
</div>

<div class="details">
    <div class="BookList">
        <div class="bookHeader">
            <h2>List of Books</h2>
            <div class="search4">
                <label>
                    <input type="text" placeholder="Click here" id="searchInput">
                </label>

                <div class="sort">
    <button class="sortbtn">
        <ion-icon name="filter-outline"></ion-icon> Filter
    </button>
    <div class="sort-content">
        <a href="?sort=All">All</a>
        <a href="?sort=Fiction">Fiction</a>
        <a href="?sort=Non-Fiction">Non-Fiction</a>
        <a href="?sort=Mystery">Mystery</a>
        <a href="?sort=FairyTale">FairyTale</a>
        <a href="?sort=Action">Action</a>
        <a href="?sort=Fantasy">Fantasy</a>
        <a href="?sort=Historical">Historical</a>
        <a href="?sort=Latest">Latest</a>
        <a href="?sort=Oldest">Oldest</a>
    </div>
</div>

            </div>
        </div>

        <table id="BookListTable">
            <thead>
                <tr>
                <th>Picture</th>
                    <th>Name</th>
                    <th>ISBN</th>
                    <th>Author</th>
                    <th>Genre</th>
                    <th>Date Received</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                      
                        $pictureSrc = !empty($row['picture']) ? $uploadDir . $row['picture'] : '';
                        $pictureHTML = $pictureSrc ? "<img src='" . htmlspecialchars($pictureSrc) . "' alt='Book Image' class='book-image'>" : "";
                        echo "<td>" . $pictureHTML . "</td>";
                        echo "<td>" . htmlspecialchars($row['Title']) . "</td>";
                        echo "<td>" . $row['ISBN'] . "</td>";
                        echo "<td>" . $row['author1'] . "</td>";
                        echo "<td>"  . $row['genre'] ." </td>";
                        echo "<td>"  . $row['DateReceived'] ." </td>"; 
                        echo "<td>
                                  <div class='action-icons'>
                                     <button class='eye-btn'><ion-icon name='eye-outline'></ion-icon></button>
                                     <button class='edit-btn'><ion-icon name='create-outline'></ion-icon></button>
                                     <button class='delete-btn' onclick='togglePopup(\"" . $row['book_acquisition'] . "\")'><ion-icon name='trash-outline'></ion-icon></button>
                                 </div>
                             </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No books found</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <div class="pagination">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i; ?>" class="<?= ($page === $i) ? 'active' : ''; ?>"><?= $i; ?></a>
    <?php endfor; ?>
</div>

    </div>
</div>
<!-- Book Details Modal -->

<div id="bookDetailsModal" class="popup4">
    <div class="overlay">
        <div class="content">
            <div id="bookDetails"  class>Loading...</div>
            <div class="close-btn" onclick="closeBookDetailsModal()">&times;</div>
        </div>
    </div>
</div>


<div class="popup1" id="popup-1">
    <div class="overlay"></div>
    <div class="content">
        <div class="alert-icon">
            <ion-icon name="alert-outline"></ion-icon>
        </div>
        <h1>Delete Confirmation</h1>
        <p>Are you sure you want to delete this item?<br>This process cannot be undone</p>
        <div class="deletebutton">
            <button type="button" class="cancelbtn" onclick="closePopup()">Cancel</button>
            <button type="button" class="deletebtn" onclick="confirmDelete()" id="delete-confirm-btn">Delete</button>
        </div>
        <div class="close-btn" onclick="closePopup()">&times;</div>
    </div>
</div>

<div class="popup2" id="popup2">
    <div class="overlay"></div>
    <div class="content">
        <div class="alert-icon">
            <ion-icon name="checkmark-outline" style="color: red; font-size: 50px"></ion-icon>
        </div>
        <h1 style="color:black">Edit Successful</h1>
        <p>Your book has been edited successfully.</p>
        <div class="close-btn" onclick="closeSuccessPopup()">&times;</div>
    </div>
</div>

<div class="popup3" id="popup3">
    <div class="overlay"></div>
    <div class="content">
        <div class="alert-icon">
            <ion-icon name="checkmark-outline" style="color: red; font-size: 50px"></ion-icon>
        </div>
        <h1 style="color:black">Deletion Successful</h1>
        <p>The book has been deleted successfully.</p>
        <div class="close-btn" onclick="closeDeleteSuccessPopup()">&times;</div>
    </div>
</div>

<!-- delete function -->

<script>
    function togglePopup(book_acquisition) {
        var popup = document.getElementById('popup-1');
        popup.classList.toggle('active');
        document.getElementById('delete-confirm-btn').setAttribute('data-bookacq', book_acquisition);
    }

    function closePopup() {
        var popup = document.getElementById('popup-1');
        popup.classList.remove('active');
    }

    function confirmDelete() {
    var book_acquisition = document.getElementById('delete-confirm-btn').getAttribute('data-bookacq');

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "deletebook.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
            closePopup(); 

            if (this.responseText.trim() === "Book deleted successfully") {
                toggleDeleteSuccessPopup(); 
                setTimeout(function() {
                    closeDeleteSuccessPopup(); 
                    location.reload(); 
                }, 3000); 
            } else {
               
                console.error("Error: could not delete book");
            }
        }
    };
    xhr.send("bookacq=" + book_acquisition);


}
</script>

<!-- edit function -->
<script>
   document.querySelectorAll('.edit-btn').forEach(function(btn) {
    btn.addEventListener('click', function(event) {
        event.preventDefault(); 

        var ISBN = btn.closest('tr').querySelector('td:nth-child(3)').textContent;

        window.location.href = 'editbook.php?ISBN=' + ISBN;
    });
});

</script>

<script>
  
  document.addEventListener('DOMContentLoaded', function() {
    checkSuccessParameter();
});

function checkSuccessParameter() {
    var urlParams = new URLSearchParams(window.location.search);
    var success = urlParams.get('success');
    if (success === 'true') {
        toggleSuccessPopup();
    }
}

function toggleSuccessPopup() {
    var successPopup = document.getElementById('popup2');
    successPopup.classList.toggle('active');
}

function closeSuccessPopup() {
    var successPopup = document.getElementById('popup2');
    successPopup.classList.remove('active');
    
    var url = new URL(window.location.href);
    url.searchParams.delete('success');
    window.history.replaceState({}, '', url);
}

function toggleDeleteSuccessPopup() {
    var successPopup = document.getElementById('popup3');
    successPopup.classList.toggle('active');
}

function closeDeleteSuccessPopup() {
    var successPopup = document.getElementById('popup3');
    successPopup.classList.remove('active');
    location.reload(); 
}
</script>

<!-- search function -->
<script>
    document.getElementById('searchInput').addEventListener('keyup', function(event) {
        var searchQuery = event.target.value.toLowerCase();
        var tableRows = document.querySelectorAll("#BookListTable tbody tr");


        tableRows.forEach(function(row) {
            var titleCell = row.querySelector("td:first-child").textContent.toLowerCase();
            var isbnCell = row.querySelector("td:nth-child(2)").textContent.toLowerCase();
            var authorCell = row.querySelector("td:nth-child(3)").textContent.toLowerCase();

            if (titleCell.includes(searchQuery) || isbnCell.includes(searchQuery) || authorCell.includes(searchQuery)) {
                row.style.display = ""; // Show row
            } else {
                row.style.display = "none"; // Hide row
            }
        });
    });
</script>

<script>
       // Function to open the book details modal
       function openBookDetailsModal() {
    document.getElementById('bookDetailsModal').classList.add('active');
}

function closeBookDetailsModal() {
    document.getElementById('bookDetailsModal').classList.remove('active');
}

// Function to load book details
function loadBookDetails(ISBN) {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "viewbook.php?ISBN=" + ISBN, true);
    xhr.onreadystatechange = function() {
        if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
            document.getElementById('bookDetails').innerHTML = this.responseText;
        }
    };
    xhr.send();
}

// Event listener for eye-icon click
document.querySelectorAll('.eye-btn').forEach(function(btn) {
    btn.addEventListener('click', function(event) {
        event.preventDefault();
        // The ISBN is now in the third cell due to the addition of the picture cell at the beginning.
        var ISBN = btn.closest('tr').querySelector('td:nth-child(3)').textContent;
        loadBookDetails(ISBN);
        openBookDetailsModal();
    });
});


</script>

<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>