<?php
include 'navigaLib.php';
include 'config.php';

session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'LibPre') {
    header('Location: index.php');
    exit();
}

if (empty($_SESSION['memberID'])) {
    die('Member ID is not set in the session.');
}

$memberID = $_SESSION['memberID'];

$stmt = $conn->prepare("
SELECT 
    ib.issueBoxID, 
    ib.BoxSerialNum, 
    b.category, 
    ib.IssueDate, 
    ib.DueDate, 
    ib.ReturnDate, 
    b.BoxPicture,
    CASE
        WHEN EXISTS (SELECT 1 FROM reviewbox WHERE reviewbox.issueBoxID = ib.issueBoxID AND reviewbox.memberID = ib.memberID AND isReview = 1) THEN 'Completed'
        ELSE 'Incomplete'
    END as ReviewStatus
FROM issuebox ib
INNER JOIN boxs b ON ib.BoxSerialNum = b.BoxSerialNum
INNER JOIN register r ON ib.memberID = r.memberID
    WHERE ib.memberID = ?
");

$stmt->bind_param('s', $memberID);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="Cssfile/Sreview.css">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body>

<div class="content">
    <div class="header-space"></div> <!-- Space under the header -->
    <h1 class="review-header">Review Box</h1>

    <div class="container-padding">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php $uploadDir = ''; ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class='book-review'>
                    <img src="<?php echo htmlspecialchars($uploadDir . $row['BoxPicture']); ?>" alt='Book Cover' class='book-cover'>

                    <div class='book-details'>
                        <p class='book-title'><strong><?php echo htmlspecialchars($row['BoxSerialNum']); ?></strong></p>
                        <p class='author-name'>Category: <?php echo htmlspecialchars($row['category']); ?></p>
                        <p class='status <?php echo strtolower($row['ReviewStatus']); ?>'>Review Status: <?php echo $row['ReviewStatus']; ?></p>
                    </div>
                    <div class='action-icons'>
                        <button class='eye-btn' data-issueid='<?php echo htmlspecialchars($row['issueBoxID']); ?>' data-rating='' data-review='' onclick="openModal(this.getAttribute('data-issueid'), this.getAttribute('data-rating'), this.getAttribute('data-review'))"><ion-icon name="add-outline"></ion-icon></button>
                        <button class='edit-btn' onclick="editReview(<?php echo htmlspecialchars($row['issueBoxID']); ?>)"><ion-icon name='create-outline'></ion-icon></button>
                        <button class='view-btn' onclick="viewReview(<?php echo htmlspecialchars($row['issueBoxID']); ?>)"><ion-icon name="eye-outline"></ion-icon></button>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No borrowing history found.</p>
        <?php endif; ?>
    </div>
</div>

<div id="addReviewModal" class="review-modal"> 
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Rate Product</h2>
        <form id="addReviewForm" method="post" action="SaddreviewBox.php">
            <input type="hidden" name="issueBoxID" id="issueBoxID" value="">

            <div class="rating-container">
                <span>Box Quality:</span>
                <div id="star-rating">
                    <input type="radio" id="star5" name="rating" value="5" /><label for="star5"></label>
                    <input type="radio" id="star4" name="rating" value="4" /><label for="star4"></label>
                    <input type="radio" id="star3" name="rating" value="3" /><label for="star3"></label>
                    <input type="radio" id="star2" name="rating" value="2" /><label for="star2"></label>
                    <input type="radio" id="star1" name="rating" value="1" /><label for="star1"></label>
                </div>
            </div>
            <textarea name="review" placeholder="Your comments..." oninput="limitWords(this)"></textarea>
            <div class="form-buttons">
                <button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
                <button type="button" id="submitReview" class="submit-btn">Submit</button>
            </div>
        </form>
    </div>
</div>

<div id="successModal" class="modal" style="display:none;">
    <div class="success-modal-content">
        <div class="success-checkmark">
            <ion-icon name="checkmark-circle-outline"></ion-icon>
        </div>
        <p class="success-message"><strong>Thanks for your review</strong></p>
    </div>
</div>

<div id="editReviewModal" class="review-modal" style="display:none;">
    <div class="modal-content">
        <span class="close" onclick="closeEditModal()">&times;</span>
        <h2>Edit Review</h2>
        <form id="editReviewForm" method="post">
            <input type="hidden" name="issueBoxID" id="editIssueID">
            <div class="rating-container">
                <span>Box Quality:</span>
                <div id="edit-star-rating">
                    <input type="radio" id="edit-star5" name="rating" value="5" /><label for="edit-star5"></label>
                    <input type="radio" id="edit-star4" name="rating" value="4" /><label for="edit-star4"></label>
                    <input type="radio" id="edit-star3" name="rating" value="3" /><label for="edit-star3"></label>
                    <input type="radio" id="edit-star2" name="rating" value="2" /><label for="edit-star2"></label>
                    <input type="radio" id="edit-star1" name="rating" value="1" /><label for="edit-star1"></label>
                </div>
            </div>
            <textarea name="review" id="editReview" placeholder="Your comments..."></textarea>
            <div class="form-buttons">
                <button type="button" class="cancel-btn" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="submit-btn">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<div id="viewReviewModal" class="review-modal" style="display:none;">
    <div class="modal-content">
        <span class="close" onclick="closeViewModal()">&times;</span>
        <h2>View Review</h2>
        <div class="rating-container">
            <span>Box Quality:</span>
            <div id="view-star-rating" class="star-rating">
                <!-- Stars will be generated by JavaScript -->
            </div>
        </div>
        <div id="viewReviewText" class="review-text"></div>
    </div>
</div>

<script>
function closeModal() {
    var modal = document.getElementById('addReviewModal');
    if (modal) {
        modal.style.display = 'none';
        document.getElementById('addReviewForm').reset();
    }
}

function showSuccessModal() {
    var successModal = document.getElementById('successModal');
    successModal.style.display = 'block'; 

    setTimeout(function() {
        successModal.style.display = 'none'; 
    }, 1000);
}

document.getElementById('submitReview').addEventListener('click', function(event) {
    event.preventDefault(); // Prevent the default form submission

    var ratingChecked = document.querySelector('input[name="rating"]:checked');
    var reviewText = document.querySelector('textarea[name="review"]').value.trim();

    if (!ratingChecked) {
        alert('Please add a rating for the box.');
        return;
    }

    if (!reviewText) {
        alert('Please add a review text.');
        return;
    }

    var xhr = new XMLHttpRequest();
    var formData = new FormData(document.getElementById('addReviewForm'));

    xhr.open('POST', 'SaddreviewBox.php', true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                if (xhr.responseText.trim() === "success") {
                    closeModal(); // Close the review modal
                    showSuccessModal(); // Show the success message modal
                } else {
                    alert('Error submitting review: ' + xhr.responseText);
                }
            } else {
                alert('Request failed. Returned status of ' + xhr.status);
            }
        }
    };
    xhr.send(formData);
});

document.addEventListener('DOMContentLoaded', (event) => {
    var eyeButtons = document.querySelectorAll('.eye-btn');
    eyeButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var issueBoxID = this.getAttribute('data-issueid');
            openModal(issueBoxID);
        });
    });

    var closeModalButtons = document.querySelectorAll('.close');
    closeModalButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            closeModal();
            var successModal = document.getElementById('successModal');
            if (successModal) {
                successModal.style.display = 'none';
            }
        });
    });

    var editForm = document.getElementById('editReviewForm');
    if (editForm) {
        editForm.addEventListener('submit', submitEditedReview);
    }
});

function limitWords(textarea) {
    var wordLimit = 200;
    var words = textarea.value.split(/\s+/);
    if (words.length > wordLimit) {
        var trimmedWords = words.slice(0, wordLimit);
        textarea.value = trimmedWords.join(" ") + " ";
    }
}

function openModal(issueBoxID) {
    var modal = document.getElementById('addReviewModal');
    var reviewForm = document.getElementById('addReviewForm');
    reviewForm['issueBoxID'].value = issueBoxID; 
    modal.style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editReviewModal').style.display = 'none';
}

function editReview(issueBoxID) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'Sfetchreviewbox.php?issueBoxID=' + issueBoxID, true);
    xhr.onload = function() {
        if (xhr.status == 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.success) {
                var reviewData = response.data;
                var editModal = document.getElementById('editReviewModal');
                var editForm = document.getElementById('editReviewForm');

                document.getElementById('editIssueID').value = issueBoxID;

                document.querySelectorAll('#edit-star-rating input').forEach(function(star) {
                    star.checked = (star.value === reviewData.rating.toString());
                });

                document.getElementById('editReview').value = reviewData.review;

                editModal.style.display = 'block';
            } else {
                alert('No review data found.');
            }
        } else {
            alert('Error fetching review data.');
        }
    };
    xhr.send();
}

document.getElementById('editReviewForm').addEventListener('submit', function(event) {
    event.preventDefault();
    submitEditedReview(event.target);
});

function submitEditedReview(form) {
    var xhr = new XMLHttpRequest();
    var formData = new FormData(form);
    xhr.open('POST', 'Seditreviewbox.php', true);
    xhr.onload = function() {
        if (xhr.status == 200) {
            try {
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    closeEditModal();
                    showSuccessModal();
                } else {
                    alert(response.message);
                }
            } catch (e) {
                alert('Failed to parse the response.');
            }
        } else {
            alert('Error submitting review.');
        }
    };
    xhr.onerror = function() {
        alert('Request failed.');
    };
    xhr.send(formData);
}

function viewReview(issueBoxID) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'SfetchreviewBox.php?issueBoxID=' + issueBoxID, true);
    xhr.onload = function() {
        if (xhr.status == 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.success) {
                var reviewData = response.data;
                var viewModal = document.getElementById('viewReviewModal');
                var reviewText = document.getElementById('viewReviewText');

                var rating = parseInt(reviewData.rating, 10);

                var starRatingContainer = document.getElementById('view-star-rating');
                starRatingContainer.innerHTML = '';

                for (var i = 1; i <= 5; i++) {
                    var star = document.createElement('span');
                    star.className = i <= rating ? 'star filled' : 'star';
                    star.textContent = '★'; 
                    starRatingContainer.appendChild(star);
                }

                reviewText.textContent = reviewData.review;

                viewModal.style.display = 'block';
            } else {
                alert('No review data found.');
            }
        } else {
            alert('Error fetching review data.');
        }
    };
    xhr.send();
}

function closeViewModal() {
    document.getElementById('viewReviewModal').style.display = 'none';
}
</script>

</body>
</html>
