<?php
include 'config.php';

$recordsPerPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $recordsPerPage;
$sort = $_GET['sort'] ?? 'All'; // Fetch the sort criterion from the query parameter
$search = $_GET['search'] ?? ''; // Fetch the search query

// Determine the sorting and search criteria
$query = "SELECT b.*, IFNULL(SUM(bd.CopyCount), 0) AS TotalBooks FROM boxs b LEFT JOIN book_distribution bd ON b.BoxSerialNum = bd.BoxSerialNum";
$conditions = [];

if ($sort !== 'All') {
    switch ($sort) {
        case 'Latest':
            $orderBy = "b.DateCreate DESC";
            break;
        case 'Oldest':
            $orderBy = "b.DateCreate ASC";
            break;
        case 'BookPanda':
        case 'GrabBook':
            $conditions[] = "b.category = '" . mysqli_real_escape_string($conn, $sort) . "'";
            $orderBy = "b.DateCreate DESC";
            break;
        default:
            $orderBy = "b.DateCreate DESC"; // Default sort
            break;
    }
} else {
    $orderBy = "b.DateCreate DESC";
}

if (!empty($search)) {
    $conditions[] = "(b.BoxSerialNum LIKE '%" . mysqli_real_escape_string($conn, $search) . "%' OR b.category LIKE '%" . mysqli_real_escape_string($conn, $search) . "%')";
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(' AND ', $conditions);
}

$query .= " GROUP BY b.BoxSerialNum ORDER BY " . $orderBy . " LIMIT $recordsPerPage OFFSET $offset";

$result = mysqli_query($conn, $query);

$countQuery = "SELECT COUNT(*) AS totalRecords FROM (SELECT COUNT(*) FROM boxs b LEFT JOIN book_distribution bd ON b.BoxSerialNum = bd.BoxSerialNum";

if (!empty($conditions)) {
    $countQuery .= " WHERE " . implode(' AND ', $conditions);
}

$countQuery .= " GROUP BY b.BoxSerialNum) as temp";

$countResult = mysqli_query($conn, $countQuery);
$countRow = mysqli_fetch_assoc($countResult);
$totalRecords = $countRow['totalRecords'];
$totalPages = ceil($totalRecords / $recordsPerPage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Boxes</title>
    <link rel="stylesheet" href="Cssfile/style4.css">
    <link rel="stylesheet" href="Cssfile/box.css">
</head>
<body>
<?php include 'navigation.php'; ?>

<div class="actionBtn">
    <a href="addbox.php" class="add-btn">Add Box</a>
</div>

<div class="details">
    <div class="BookList">
        <div class="bookHeader">
            <h2>List of Box</h2>
            <div class="search4">
                <label>
                    <input type="text" placeholder="Click here" id="searchInput" value="<?= htmlspecialchars($search) ?>">
                </label>
                <div class="sort">
                    <button class="sortbtn">
                        <ion-icon name="filter-outline"></ion-icon> Filter
                    </button>
                    <div class="sort-content">
                        <a href="?sort=All&search=<?= htmlspecialchars($search) ?>">All</a>
                        <a href="?sort=BookPanda&search=<?= htmlspecialchars($search) ?>">BookPanda</a>
                        <a href="?sort=GrabBook&search=<?= htmlspecialchars($search) ?>">GrabBook</a>
                        <a href="?sort=Latest&search=<?= htmlspecialchars($search) ?>">Latest</a>
                        <a href="?sort=Oldest&search=<?= htmlspecialchars($search) ?>">Oldest</a>
                    </div>
                </div>
            </div>
        </div>
        <table id="BoxListTable">
            <thead>
                <tr>
                    <th>Picture</th>
                    <th>Box ID</th>
                    <th>Category</th>
                    <th>Date Created</th>
                    <th>Box Color</th>
                    <th>Book Capacity</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><img src="<?= htmlspecialchars($row['Boxpicture']) ?>" alt="Box Image" class="box-image"></td>
                            <td><?= $row['BoxSerialNum'] ?></td>
                            <td><?= $row['category'] ?></td>
                            <td><?= $row['DateCreate'] ?></td>
                            <td><?= $row['color'] ?></td>
                            <td><?= $row['TotalBooks'] . ' / ' . $row['BookQuantity'] ?></td>
                            <td>
                                <div class='actions-icons'>
                                    <button class='eye-btn'><ion-icon name='eye-outline'></ion-icon></button>
                                    <button class='edit-btn'><ion-icon name='create-outline'></ion-icon></button>
                                    <button class='delete-btn' onclick='togglePopup("<?= $row['BoxSerialNum'] ?>")'><ion-icon name='trash-outline'></ion-icon></button>
                                    <button class='list-btn' style='background-color:<?= ($row['TotalBooks'] > 0 ? 'rgb(222, 148, 84)' : 'grey') ?>;'><ion-icon name='menu-outline'></ion-icon></button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7">No boxes found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i; ?>&sort=<?= htmlspecialchars($sort) ?>&search=<?= htmlspecialchars($search) ?>" class="<?= ($page === $i) ? 'active' : ''; ?>"><?= $i; ?></a>
            <?php endfor; ?>
        </div>
    </div>
</div>

<!-- delete message -->

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


<div class="popup3" id="popup3">
    <div class="overlay"></div>
    <div class="content">
        <div class="alert-icon">
            <ion-icon name="checkmark-outline" style="color: red; font-size: 50px"></ion-icon>
        </div>
        <h1 style="color:black">Deletion Successful</h1>
        <p>The box has been deleted successfully.</p>
        <div class="close-btn" onclick="closeDeleteSuccessPopup()">&times;</div>
    </div>
</div>

<!-- edit message -->
<div class="popup2" id="popup2">
    <div class="overlay"></div>
    <div class="content">
        <div class="alert-icon">
            <ion-icon name="checkmark-outline" style="color: red; font-size: 50px"></ion-icon>
        </div>
        <h1 style="color:black">Edit Successful</h1>
        <p>Your box has been edited successfully.</p>
        <div class="close-btn" onclick="closeSuccessPopup()">&times;</div>
    </div>
</div>
<!--view message -->

<div id="boxDetailsModal" class="popup5">
    <div class="overlay">
        <div class="content">
            <div id="boxDetails"  class>Loading...</div>
            <div class="close-btn" onclick="closeBoxDetailsModal()">&times;</div>
        </div>
    </div>
</div>

<!-- list book -->
<div id="boxListModal" class="popup6">
    <div class="overlay">
        <div class="content">
            <div id="boxListDetails" >Loading...</div> 
            <div class="close-btn" onclick="closeBoxListModal()">&times;</div>
        </div>
    </div>
</div>

<!-- delete function -->
<script>
    function togglePopup(BoxSerialNum) {
        var popup = document.getElementById('popup-1');
        popup.classList.add('active');
        document.getElementById('delete-confirm-btn').setAttribute('data-BoxSerialNum', BoxSerialNum);
    }

    function closePopup() {
        var popup = document.getElementById('popup-1');
        popup.classList.remove('active');
    }

    function toggleDeleteSuccessPopup() {
        var successPopup = document.getElementById('popup3');
        successPopup.classList.toggle('active');
    }

    function closeDeleteSuccessPopup() {
        var successPopup = document.getElementById('popup3');
        successPopup.classList.remove('active');
        location.reload(); // Reloading the page after success popup is closed
    }

    function confirmDelete() {
        var BoxSerialNum = document.getElementById('delete-confirm-btn').getAttribute('data-BoxSerialNum');

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "deletebox.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
                closePopup();

                if (this.responseText.trim() === "Box deleted successfully") {
                    toggleDeleteSuccessPopup(); // Display success popup
                    setTimeout(function() {
                        closeDeleteSuccessPopup(); // Close the success popup after a delay
                    }, 3000);
                } else {
                    console.error("Error: could not delete box");
                }
            }
        };
        xhr.send("BoxSerialNum=" + BoxSerialNum);
    }
</script>

<!-- edit function -->
<script>
 document.querySelectorAll('.edit-btn').forEach(function(btn) {
    btn.addEventListener('click', function(event) {
        event.preventDefault(); // Prevent the default behavior of the anchor tag
        var BoxSerialNum = btn.closest('tr').querySelector('td:nth-child(2)').textContent; 
        window.location.href = 'editbox.php?BoxSerialNum=' + BoxSerialNum;
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
</script>

<!-- search function -->
<script>
    document.getElementById('searchInput').addEventListener('keyup', function(event) {
        var searchQuery = event.target.value.toLowerCase();
        var rows = document.querySelectorAll("#BoxListTable tbody tr");

        rows.forEach(function(row) {
            var match = row.innerText.toLowerCase().includes(searchQuery);
            row.style.display = match ? '' : 'none';
        });

        if (event.key === 'Enter') {
            var currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('search', searchQuery);
            window.location.href = currentUrl.toString();
        }
    });

    document.getElementById('searchInput').addEventListener('input', function() {
        if (this.value === '') {
            var currentUrl = new URL(window.location.href);
            currentUrl.searchParams.delete('search');
            window.location.href = currentUrl.toString();
        }
    });
</script>

<!-- view function -->
<script>
    // Function to open the box details modal
    function openBoxDetailsModal() {
        document.getElementById('boxDetailsModal').classList.add('active');
    }

    function closeBoxDetailsModal() {
        document.getElementById('boxDetailsModal').classList.remove('active');
    }

    // Function to load box details
    function loadBoxDetails(BoxSerialNum) {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "viewbox.php?BoxSerialNum=" + BoxSerialNum, true);
        xhr.onreadystatechange = function() {
            if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
                document.getElementById('boxDetails').innerHTML = this.responseText;
            }
        };
        xhr.send();
    }

    // Event listener for eye-icon click
    document.querySelectorAll('.eye-btn').forEach(function(btn) {
        btn.addEventListener('click', function(event) {
            event.preventDefault();
            var BoxSerialNum = btn.closest('tr').querySelector('td:nth-child(2)').textContent;
            loadBoxDetails(BoxSerialNum);
            openBoxDetailsModal();
        });
    });
</script>

<!--list function -->
<script>
    document.querySelectorAll('.list-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var BoxSerialNum = btn.closest('tr').querySelector('td:nth-child(2)').textContent;
            loadBookListDetails(BoxSerialNum);
        });
    });

    function loadBookListDetails(BoxSerialNum) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'listbook.php?BoxSerialNum=' + BoxSerialNum, true);
        xhr.onreadystatechange = function() {
            if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
                document.getElementById('boxListDetails').innerHTML = this.responseText;
                openBoxListModal();
            }
        };
        xhr.send();
    }

    function openBoxListModal() {
        document.getElementById('boxListModal').classList.add('active');
    }

    function closeBoxListModal() {
        document.getElementById('boxListModal').classList.remove('active');
    }
</script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>
