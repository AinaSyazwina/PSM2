<?php
include 'navigaStu.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    header('Location: index.php');
    exit;
}

include 'config.php';

$username = $_SESSION['username'];
$query = $conn->prepare("SELECT memberID FROM register WHERE username = ?");
$query->bind_param("s", $username);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();
$memberID = $user['memberID'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Cssfile/wishlist.css">

    <title>My Box Wishlist</title>
    
</head>
<body>

<div class="wishlist-container">
    <div class="wishlist-header">
        <i class="fas fa-heart icon"></i>
        <h1>My Box Wishlist</h1>
        <nav>
            <a href="Swishlist.php">Book Wishlist</a>
            <a href="Swishbox.php" class="active">Box Wishlist</a>
        </nav>
    </div>
    <table class="wishlist-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Box Serial Number</th>
                <th>Category</th>
                <th>Color</th>
                <th>Added On</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $query = "SELECT wb.id, b.BoxSerialNum, b.category, b.color, wb.added_at, b.Boxpicture 
                  FROM wishlistbox wb 
                  JOIN boxs b ON wb.BoxSerialNum = b.BoxSerialNum 
                  WHERE wb.memberID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $memberID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td><img src='{$row['Boxpicture']}' alt='Box Picture'></td>
                    <td>{$row['BoxSerialNum']}</td>
                    <td>{$row['category']}</td>
                    <td>{$row['color']}</td>
                    <td>{$row['added_at']}</td>
                    <td><button class='remove-button' data-id='{$row['id']}'>
                        <i class='fas fa-trash-alt'></i>
                    </button></td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='6' class='empty-message' style='text-align: center;'>You don't have any wishlist boxes yet.</td></tr>";

        }
        ?>
        </tbody>
    </table>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <i class="fas fa-times-circle icon"></i>
        <h2>Are you sure?</h2>
        <p>Do you really want to delete this wishlist? This process cannot be undone.</p>
        <div class="modal-footer">
            <button class="cancel">Cancel</button>
            <button class="confirm">Delete</button>
        </div>
    </div>
</div>

<!-- Success Message Modal -->
<div id="successModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div class="modal-header">
            <i class="fas fa-check-circle icon"></i>
            <h2>Wishlist Removed</h2>
        </div>
        <p>Wishlist is successfully deleted.</p>
    </div>
</div>

<script>
    document.querySelectorAll('.remove-button').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            showModal('deleteModal', id);
        });
    });

    function showModal(modalId, id) {
        const modal = document.getElementById(modalId);
        modal.style.display = 'block';

        const confirmButton = modal.querySelector('.confirm');
        confirmButton.onclick = function() {
            deleteWishlistItem(id);
            modal.style.display = 'none';
        };

        const cancelButton = modal.querySelector('.cancel');
        cancelButton.onclick = function() {
            modal.style.display = 'none';
        };

        const closeButton = modal.querySelector('.close');
        closeButton.onclick = function() {
            modal.style.display = 'none';
        };

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        };
    }

    function deleteWishlistItem(id) {
        fetch('Sremovewishbox.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessModal();
            } else {
                alert('Failed to remove from wishlist');
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function showSuccessModal() {
        const successModal = document.getElementById('successModal');
        successModal.style.display = 'block';

        setTimeout(() => {
            successModal.style.display = 'none';
            location.reload();
        }, 3000);
    }

    document.querySelectorAll('.wishlist-header nav a').forEach(link => {
        link.addEventListener('click', function() {
            document.querySelectorAll('.wishlist-header nav a').forEach(link => {
                link.classList.remove('active');
            });
            this.classList.add('active');
        });
    });
</script>

</body>
</html>