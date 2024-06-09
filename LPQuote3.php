<?php
include 'navigaLib.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'LibPre') {
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
    <title>My Quotes</title>
    <link rel="stylesheet" href="Cssfile/quote.css">
    <style>
        h1 {
    font-size: 3.5em;
}
    </style>
</head>
<body>

<div class="wishlist-container">
    <i class="fab fa-twitter icon"></i>
    <h1>My Quotes</h1>
    <p class="description">Edit your quotes to made it more memorable</p>

    <div class="quote-nav">
        <a href="LPQuote.php">Create a Quote</a>
        <a href="LPQuote3.php" class="active">Your Quotes</a>
        <a href="LPQuote2.php">View All Quotes</a>
    </div>
    <table class="wishlist-table">
        <thead>
            <tr>
                <th>Quote</th>
                <th>Tags</th>
                <th>Likes</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $query = "SELECT quoteID, quote, tags, likes FROM quotes WHERE memberID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $memberID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td>{$row['quote']}</td>
                    <td>{$row['tags']}</td>
                    <td>{$row['likes']}</td>
                    <td>
                        <button class='edit-button' data-id='{$row['quoteID']}' data-quote='{$row['quote']}' data-tags='{$row['tags']}'>
                            <i class='fas fa-edit'></i>
                        </button>
                        <button class='remove-button' data-id='{$row['quoteID']}'>
                            <i class='fas fa-trash-alt'></i>
                        </button>
                    </td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='4' class='empty-message'>You don't have any quotes yet.</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <i class="fas fa-edit icon"></i>
        <h2>Edit Quote</h2>
        <form id="editForm">
            <label for="editTags">Tags:</label>
            <input type="text" id="editTags" required>
            <label for="editQuote">Quote:</label>
            <textarea id="editQuote" rows="4" required></textarea>
            <div class="modal-footer">
                <button type="button" class="cancel">Cancel</button>
                <button type="submit" class="confirm" style="background-color: #2e2185;">Save Changes</button>
            </div>
        </form>
    </div>
</div>


<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <i class="fas fa-times-circle icon"></i>
        <h2>Are you sure?</h2>
        <p>Do you really want to delete this quote? This process cannot be undone.</p>
        <div class="modal-footer">
            <button class="cancel">Cancel</button>
            <button class="confirm">Delete</button>
        </div>
    </div>
</div>

<!-- Success Message Modal -->
<div id="successMessageOverlay" class="success-message-overlay"></div>
<div id="successMessage" class="success-message">
    <i class="fas fa-check-circle icon"></i>
    <h2>Success!</h2>
    <p>Your action has been completed successfully.</p>
</div>

<script>
    document.querySelectorAll('.remove-button').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            showModal('deleteModal', id);
        });
    });

    document.querySelectorAll('.edit-button').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const quote = this.getAttribute('data-quote');
            const tags = this.getAttribute('data-tags');
            showEditModal(id, quote, tags);
        });
    });

    function showModal(modalId, id) {
        const modal = document.getElementById(modalId);
        modal.style.display = 'block';

        const confirmButton = modal.querySelector('.confirm');
        confirmButton.onclick = function() {
            deleteQuote(id);
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

    function showEditModal(id, quote, tags) {
        const modal = document.getElementById('editModal');
        modal.style.display = 'block';

        document.getElementById('editQuote').value = quote;
        document.getElementById('editTags').value = tags;

        const form = document.getElementById('editForm');
        form.onsubmit = function(event) {
            event.preventDefault();
            const updatedQuote = document.getElementById('editQuote').value;
            const updatedTags = document.getElementById('editTags').value;
            editQuote(id, updatedQuote, updatedTags);
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

    function deleteQuote(id) {
        fetch('SQuoteDelete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessMessage();
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error: Could not delete quote. Please try again.');
        });
    }

    function editQuote(id, quote, tags) {
        fetch('SQuoteEdit.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id, quote: quote, tags: tags })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessMessage();
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error: Could not update quote. Please try again.');
        });
    }

    function showSuccessMessage() {
        const successMessageOverlay = document.getElementById('successMessageOverlay');
        const successMessage = document.getElementById('successMessage');
        successMessageOverlay.style.display = 'block';
        successMessage.style.display = 'block';

        setTimeout(() => {
            successMessageOverlay.style.display = 'none';
            successMessage.style.display = 'none';
            location.reload();
        }, 1000);
    }
</script>

</body>
</html>

