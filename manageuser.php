<?php
include 'config.php';

$recordsPerPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $recordsPerPage;
$sort = $_GET['sort'] ?? 'active'; // Default to 'active' to show active users
$search = $_GET['search'] ?? ''; // Fetch the search query

// Determine the sorting and search criteria
$query = "SELECT * FROM register";
$conditions = [];

if ($sort === 'inactive') {
    $conditions[] = "status = 'inactive'";
} elseif ($sort === 'active' || $sort === 'all') {
    $conditions[] = "status = 'active'";
}

if ($sort !== 'all' && $sort !== 'active' && $sort !== 'inactive') {
    $conditions[] = "role = '" . mysqli_real_escape_string($conn, $sort) . "'";
}

if (!empty($search)) {
    $conditions[] = "(fullname LIKE '%" . mysqli_real_escape_string($conn, $search) . "%' OR memberID LIKE '%" . mysqli_real_escape_string($conn, $search) . "%' OR role LIKE '%" . mysqli_real_escape_string($conn, $search) . "%')";
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(' AND ', $conditions);
}

$query .= " ORDER BY fullname LIMIT $recordsPerPage OFFSET $offset";
$result = mysqli_query($conn, $query);

$countQuery = "SELECT COUNT(*) AS totalRecords FROM register";
if (!empty($conditions)) {
    $countQuery .= " WHERE " . implode(' AND ', $conditions);
}

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
    <title>Manage Users</title>
    <link rel="stylesheet" href="Cssfile/style3.css">
    <link rel="stylesheet" href="Cssfile/style4.css">
</head>
<body>
<?php include 'navigation.php'; ?>

<div class="details">
    <div class="BookList">
        <div class="bookHeader">
            <h2>List of Users</h2>
            <div class="search3">
                <label>
                    <input type="text" placeholder="Click here" id="searchInput" value="<?= htmlspecialchars($search) ?>">
                </label>
                <div class="sort">
                    <button class="sortbtn">
                        <ion-icon name="filter-outline"></ion-icon> Filter
                    </button>
                    <div class="sort-content">
                        <a href="?sort=all&search=<?= htmlspecialchars($search) ?>">All</a>
                        <a href="?sort=Student&search=<?= htmlspecialchars($search) ?>">Student</a>
                        <a href="?sort=LibPre&search=<?= htmlspecialchars($search) ?>">LibPre</a>
                        <a href="?sort=admin&search=<?= htmlspecialchars($search) ?>">Admin</a>
                        <a href="?sort=active&search=<?= htmlspecialchars($search) ?>">Active</a>
                        <a href="?sort=inactive&search=<?= htmlspecialchars($search) ?>">Inactive</a>
                    </div>
                </div>
            </div>
        </div>
        <table id="BookListTable">
            <thead>
                <tr>
                    <th>Picture</th>
                    <th>Name</th>
                    <th>Member ID</th>
                    <th>Identification Number</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $userImage = $row['picture'] ? "<img src='" . htmlspecialchars($row['picture']) . "' alt='User Image' class='box-image'>" : "";
                        echo "<tr>";
                        echo "<td>" . $userImage . "</td>";
                        echo "<td>" . htmlspecialchars($row['fullname']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['memberID']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['IC']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['role']) . "</td>"; 
                        echo "<td>" . htmlspecialchars($row['status']) . "</td>"; 
                        echo "<td>
                                  <div class='action-icons'>
                                     <button class='eye-btn'><ion-icon name='eye-outline'></ion-icon></button>
                                     <button class='edit-btn'><ion-icon name='create-outline'></ion-icon></button>
                                     <!-- <button class='delete-btn' onclick='togglePopup(\"" . htmlspecialchars($row['memberID']) . "\")'><ion-icon name='trash-outline'></ion-icon></button> -->
                                 </div>
                             </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No users found</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i; ?>&sort=<?= htmlspecialchars($sort) ?>&search=<?= htmlspecialchars($search) ?>" class="<?= ($page === $i) ? 'active' : ''; ?>">
                    <?= $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>
</div>

<!-- delete confirmation message -->

<div class="popup1" id="popup-1">
    <div class="overlay"></div>
    <div class="content">
        <div class="alert-icon">
            <ion-icon name="alert-outline"></ion-icon>
        </div>
        <h1>Delete Confirmation</h1>
        <p>Are you sure you want to delete this user?<br>This process cannot be undone</p>
        <div class="deletebutton">
            <button type="button" class="cancelbtn" onclick="closePopup()">Cancel</button>
            <button type="button" class="deletebtn" onclick="confirmDelete()" id="delete-confirm-btn">Delete</button>
        </div>
        <div class="close-btn" onclick="closePopup()">&times;</div>
    </div>
</div>

<!-- delete message -->
<div class="popup3" id="popup3">
    <div class="overlay"></div>
    <div class="content">
        <div class="alert-icon">
            <ion-icon name="checkmark-outline" style="color: red; font-size: 50px"></ion-icon>
        </div>
        <h1>Deletion Successful</h1>
        <p>The user has been deleted successfully.</p>
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
        <h1>Edit Successful</h1>
        <p>Your user has been edited successfully.</p>
        <div class="close-btn" onclick="closeSuccessPopup()">&times;</div>
    </div>
</div>

<!-- Book Details Modal -->

<div id="userDetailsModal" class="popup5">
    <div class="overlay">
        <div class="content">
            <div id="userDetails" class>Loading...</div>
            <div class="close-btn" onclick="closeUserDetailsModal()">&times;</div>
        </div>
    </div>
</div>

<!-- delete function -->
<script>
function togglePopup(memberID) {
    var popup = document.getElementById('popup-1');
    if (popup) {
        document.getElementById('delete-confirm-btn').setAttribute('data-memberID', memberID);  // Set member ID on delete button
        popup.classList.toggle('active');  // Toggle popup visibility
    }
}

function closePopup() {
    var popup = document.getElementById('popup-1');
    popup.classList.remove('active');
}

function confirmDelete() {
    var memberID = document.getElementById('delete-confirm-btn').getAttribute('data-memberID');

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "deleteuser.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (this.readyState === XMLHttpRequest.DONE) {
            if (this.status === 200 && this.responseText.trim() === "User deleted successfully") {
                toggleDeleteSuccessPopup();
                setTimeout(function() {
                    closeDeleteSuccessPopup();
                }, 3000);
            } else {
                console.error("Error: " + this.responseText); // This will log the actual error message from the server
            }
        }
    };
    xhr.send("memberID=" + memberID);
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

<!-- edit function -->
<script>
 document.querySelectorAll('.edit-btn').forEach(function(btn) {
    btn.addEventListener('click', function(event) {
        event.preventDefault(); 
        var memberID = btn.closest('tr').querySelector('td:nth-child(3)').textContent; 
        window.location.href = 'edituser.php?memberID=' + memberID;
    });
});
</script>

<!-- search function -->
<script>
    document.getElementById('searchInput').addEventListener('keyup', function(event) {
        if (event.key === 'Enter') {
            var searchQuery = event.target.value;
            window.location.href = '?search=' + searchQuery + '&sort=<?= htmlspecialchars($sort) ?>';
        }
    });

    document.getElementById('searchInput').addEventListener('input', function() {
        if (this.value === '') {
            window.location.href = '?sort=<?= htmlspecialchars($sort) ?>';
        }
    });
</script>

<!-- view function -->
<script>
function openUserDetailsModal() {
    document.getElementById('userDetailsModal').classList.add('active');
}

function closeUserDetailsModal() {
    document.getElementById('userDetailsModal').classList.remove('active');
}

function loadUserDetails(memberID) {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "viewuser.php?memberID=" + memberID, true);
    xhr.onreadystatechange = function() {
        if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
            document.getElementById('userDetails').innerHTML = this.responseText;
        }
    };
    xhr.send();
}

// Event listener for eye-icon click
document.querySelectorAll('.eye-btn').forEach(function(btn) {
    btn.addEventListener('click', function(event) {
        event.preventDefault();
        var memberID = btn.closest('tr').querySelector('td:nth-child(3)').textContent;
        loadUserDetails(memberID);
        openUserDetailsModal();
    });
});

document.addEventListener('DOMContentLoaded', function() {
        var searchInput = document.getElementById('searchInput');

        searchInput.addEventListener('keyup', function() {
            var filter = searchInput.value.toUpperCase();
            var table = document.getElementById('BookListTable');
            var tr = table.getElementsByTagName('tr');

            for (var i = 1; i < tr.length; i++) {  // Start from index 1, not 0
                var tds = tr[i].getElementsByTagName("td");
                var found = false;
                for (var j = 0; j < tds.length; j++) {
                    if (tds[j]) {
                        if (tds[j].textContent.toUpperCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }
                if (found) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        });
    });

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

<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>
