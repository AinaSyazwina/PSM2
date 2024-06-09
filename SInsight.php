<?php
include 'navigaStu.php';  // Include navigation-related functionality
include 'config.php';     // Database configuration settings

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$memberID = $_SESSION['memberID'] ?? null;  // Get member ID from session

// Initialize arrays to hold issue and fines data
$issuesData = [];
$finesData = ['return late' => ['Paid' => 0, 'Unpaid' => 0], 'damage' => ['Paid' => 0, 'Unpaid' => 0], 'missing' => ['Paid' => 0, 'Unpaid' => 0]];
$boxFinesData = ['return late' => ['Paid' => 0, 'Unpaid' => 0], 'damage' => ['Paid' => 0, 'Unpaid' => 0], 'missing' => ['Paid' => 0, 'Unpaid' => 0]];
$totalUnpaidBooks = ['return late' => 0.00, 'damage' => 0.00, 'missing' => 0.00];
$totalUnpaidBoxes = ['return late' => 0.00, 'damage' => 0.00, 'missing' => 0.00];
$issueCategoryData = ['Return Late' => 0, 'On Time' => 0, 'Exceed' => 0, 'In Process' => 0];

// Query to fetch issue data by month
$queryIssues = "SELECT 'Book' as Type, MONTH(IssueDate) AS Month, COUNT(*) AS Count 
                FROM issuebook 
                WHERE memberID = ? AND IssueDate > DATE_SUB(NOW(), INTERVAL 12 MONTH) 
                GROUP BY MONTH(IssueDate)
                UNION ALL
                SELECT 'Box' as Type, MONTH(IssueDate) AS Month, COUNT(*) AS Count 
                FROM issuebox 
                WHERE memberID = ? AND IssueDate > DATE_SUB(NOW(), INTERVAL 12 MONTH) 
                GROUP BY MONTH(IssueDate)";
$stmt = $conn->prepare($queryIssues);
$stmt->bind_param('ss', $memberID, $memberID);  // Bind memberID twice for both parts of the UNION query
$stmt->execute();
$result = $stmt->get_result();
$combinedIssuesData = ['Book' => [], 'Box' => []];
while ($row = $result->fetch_assoc()) {
    $combinedIssuesData[$row['Type']][(int)$row['Month']] = $row['Count'];
}
$stmt->close();

// Query to fetch fines data by type and payment status for books
$queryFines = "SELECT fb.type, f.isPaid, COUNT(*) AS Count, SUM(f.amount) AS TotalAmount 
               FROM fines f 
               JOIN finebook fb ON f.fineID = fb.fineID 
               WHERE f.memberID = ? 
               GROUP BY fb.type, f.isPaid";
$stmt = $conn->prepare($queryFines);
$stmt->bind_param('s', $memberID);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $type = strtolower($row['type']);  // Convert type to lower case to match keys in $finesData
    $status = $row['isPaid'] ? 'Paid' : 'Unpaid';
    $finesData[$type][$status] = $row['Count'];
    if ($status == 'Unpaid') {
        $totalUnpaidBooks[$type] += $row['TotalAmount'];
    }
}
$stmt->close();

// Query to fetch fines data by type and payment status for boxes
$queryBoxFines = "SELECT fb.type, f.isPaid, COUNT(*) AS Count, SUM(f.amount) AS TotalAmount 
                  FROM boxfines f 
                  JOIN finebox fb ON f.fineID = fb.fineID 
                  WHERE f.memberID = ? 
                  GROUP BY fb.type, f.isPaid";
$stmt = $conn->prepare($queryBoxFines);
$stmt->bind_param('s', $memberID);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $type = strtolower($row['type']);  // Convert type to lower case to match keys in $boxFinesData
    $status = $row['isPaid'] ? 'Paid' : 'Unpaid';
    $boxFinesData[$type][$status] = $row['Count'];
    if ($status == 'Unpaid') {
        $totalUnpaidBoxes[$type] += $row['TotalAmount'];
    }
}
$stmt->close();

// Query to fetch issue book categories data
$queryIssueCategories = "SELECT 
    CASE
        WHEN ib.DueDate < CURDATE() AND ib.ReturnDate IS NULL THEN 'Exceed'
        WHEN ib.DueDate < ib.ReturnDate THEN 'Return Late'
        WHEN ib.DueDate >= ib.ReturnDate THEN 'On Time'
        WHEN ib.ReturnDate IS NULL THEN 'In Process'
        ELSE 'Unknown'
    END as Status, COUNT(*) as Count
    FROM issuebook ib
    WHERE ib.memberID = ?
    GROUP BY Status";
$stmt = $conn->prepare($queryIssueCategories);
$stmt->bind_param('s', $memberID);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $issueCategoryData[$row['Status']] = $row['Count'];
}
$stmt->close();

// Prepare data for JavaScript
echo "<script>var finesData = " . json_encode($finesData) . ";</script>";
echo "<script>var boxFinesData = " . json_encode($boxFinesData) . ";</script>";
echo "<script>var totalUnpaidBooks = " . json_encode($totalUnpaidBooks) . ";</script>";
echo "<script>var totalUnpaidBoxes = " . json_encode($totalUnpaidBoxes) . ";</script>";
echo "<script>var issueCategoryData = " . json_encode($issueCategoryData) . ";</script>";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insight Dashboard</title>
    <link rel="stylesheet" href="path/to/your/cssfile.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            width: 80%;
            margin: 50px auto;
            margin-top: 100px;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            position: relative;
        }
        canvas {
            width: 100% !important;
            height: 400px !important;
        }
        .custom-legend {
            position: absolute;
            top: 10px;
            right: 20px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 10px;
            font-size: 14px;
            color: #666;
        }
        .custom-legend div {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        .custom-legend div span {
            display: inline-block;
            width: 20px;
            height: 20px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
<div class="chart-container">
    <h2>Issue Book and Box Analytic</h2>
    <canvas id="issuesChart"></canvas>
</div>
<div class="chart-container">
    <h2>Book Fines Data Analytic</h2>
    <div id="custom-legend" class="custom-legend"></div>
    <canvas id="finesBarChart"></canvas>
</div>
<div class="chart-container">
    <h2>Box Fines Data Analytic</h2>
    <div id="custom-box-legend" class="custom-legend"></div>
    <canvas id="boxFinesBarChart"></canvas>
</div>
<div class="chart-container">
    <h2>Issue Book Categories</h2>
    <canvas id="issueCategoryChart"></canvas>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var combinedIssuesData = <?php echo json_encode($combinedIssuesData); ?>;
    var finesData = <?php echo json_encode($finesData); ?>;
    var boxFinesData = <?php echo json_encode($boxFinesData); ?>;
    var totalUnpaidBooks = <?php echo json_encode($totalUnpaidBooks); ?>;
    var totalUnpaidBoxes = <?php echo json_encode($totalUnpaidBoxes); ?>;
    var issueCategoryData = <?php echo json_encode($issueCategoryData); ?>;

    const ctx = document.getElementById('issuesChart').getContext('2d');
    const finesCtx = document.getElementById('finesBarChart').getContext('2d');
    const boxFinesCtx = document.getElementById('boxFinesBarChart').getContext('2d');
    const issueCategoryCtx = document.getElementById('issueCategoryChart').getContext('2d');

    function getValue(data, label, status) {
        return data[label] ? data[label][status] : 0;
    }

    // Issue Data Chart
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: Object.keys(combinedIssuesData['Book']).map(m => ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"][m-1]),
            datasets: [{
                label: 'Book Issues',
                data: Object.values(combinedIssuesData['Book']),
                backgroundColor: '#FF6384',
                borderRadius: 20,
                barPercentage: 0.6,
                categoryPercentage: 0.5
            }, {
                label: 'Box Issues',
                data: Object.values(combinedIssuesData['Box']),
                backgroundColor: '#36A2EB',
                borderRadius: 20,
                barPercentage: 0.6,
                categoryPercentage: 0.5
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    max: 10,
                    ticks: {
                        stepSize: 2,
                        color: "#aaa",
                        font: {
                            size: 14,
                        },
                    },
                    grid: {
                        color: "rgba(200, 200, 200, 0.3)",
                        borderDash: [5, 5],
                        zeroLineColor: "rgba(200, 200, 200, 0.8)",
                        zeroLineWidth: 1
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: "#aaa",
                        font: {
                            size: 14,
                        },
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        color: '#333',
                        font: {
                            size: 16
                        }
                    }
                },
                tooltip: {
                    enabled: true,
                    backgroundColor: 'rgba(0,0,0,0.7)',
                    bodyColor: '#fff',
                    titleColor: '#fff',
                    borderColor: '#333',
                    borderWidth: 1
                }
            },
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Fines Data Chart
    new Chart(finesCtx, {
        type: 'bar',
        data: {
            labels: ['return late', 'damage', 'missing'],
            datasets: [{
                label: 'Paid',
                data: ['return late', 'damage', 'missing'].map(label => getValue(finesData, label, 'Paid')),
                backgroundColor: 'rgba(75, 192, 192, 0.5)',
                borderRadius: 20,
                barPercentage: 0.6,
                categoryPercentage: 0.5
            }, {
                label: 'Unpaid',
                data: ['return late', 'damage', 'missing'].map(label => getValue(finesData, label, 'Unpaid')),
                backgroundColor: 'rgba(255, 99, 132, 0.5)',
                borderRadius: 20,
                barPercentage: 0.6,
                categoryPercentage: 0.5
            }]
        },
        options: {
            indexAxis: 'y', // Specify that this is a horizontal bar chart
            scales: {
                x: {
                    beginAtZero: true,
                    max: 10,
                    ticks: {
                        stepSize: 2,
                        color: "#aaa",
                        font: {
                            size: 14,
                        },
                    },
                    grid: {
                        color: "rgba(200, 200, 200, 0.3)",
                        borderDash: [5, 5],
                        zeroLineColor: "rgba(200, 200, 200, 0.8)",
                        zeroLineWidth: 1
                    }
                },
                y: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: "#aaa",
                        font: {
                            size: 14,
                        },
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        color: '#333',
                        font: {
                            size: 16
                        }
                    }
                }
            },
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Box Fines Data Chart
    new Chart(boxFinesCtx, {
        type: 'bar',
        data: {
            labels: ['return late', 'damage', 'missing'],
            datasets: [{
                label: 'Paid',
                data: ['return late', 'damage', 'missing'].map(label => getValue(boxFinesData, label, 'Paid')),
                backgroundColor: 'rgba(75, 192, 192, 0.5)',
                borderRadius: 20,
                barPercentage: 0.6,
                categoryPercentage: 0.5
            }, {
                label: 'Unpaid',
                data: ['return late', 'damage', 'missing'].map(label => getValue(boxFinesData, label, 'Unpaid')),
                backgroundColor: 'rgba(255, 99, 132, 0.5)',
                borderRadius: 20,
                barPercentage: 0.6,
                categoryPercentage: 0.5
            }]
        },
        options: {
            indexAxis: 'y', // Specify that this is a horizontal bar chart
            scales: {
                x: {
                    beginAtZero: true,
                    max: 10,
                    ticks: {
                        stepSize: 2,
                        color: "#aaa",
                        font: {
                            size: 14,
                        },
                    },
                    grid: {
                        color: "rgba(200, 200, 200, 0.3)",
                        borderDash: [5, 5],
                        zeroLineColor: "rgba(200, 200, 200, 0.8)",
                        zeroLineWidth: 1
                    }
                },
                y: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: "#aaa",
                        font: {
                            size: 14,
                        },
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        color: '#333',
                        font: {
                            size: 16
                        }
                    }
                }
            },
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Issue Category Data Chart
    new Chart(issueCategoryCtx, {
        type: 'pie',
        data: {
            labels: Object.keys(issueCategoryData),
            datasets: [{
                data: Object.values(issueCategoryData),
                backgroundColor: [
                    'rgba(255, 99, 132, 0.5)',
                    'rgba(54, 162, 235, 0.5)',
                    'rgba(255, 206, 86, 0.5)',
                    'rgba(75, 192, 192, 0.5)'
                ]
            }]
        },
        options: {
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        color: '#333',
                        font: {
                            size: 16
                        }
                    }
                }
            },
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Update custom legend
    const customLegend = document.getElementById('custom-legend');
    customLegend.innerHTML = `
        <div><span style="background-color: rgba(255, 99, 132, 0.5);"></span>Total unpaid return late: RM${totalUnpaidBooks['return late'].toFixed(2)}</div>
        <div><span style="background-color: rgba(255, 99, 132, 0.5);"></span>Total unpaid damage: RM${totalUnpaidBooks['damage'].toFixed(2)}</div>
        <div><span style="background-color: rgba(255, 99, 132, 0.5);"></span>Total unpaid missing: RM${totalUnpaidBooks['missing'].toFixed(2)}</div>
    `;

    // Update custom box legend
    const customBoxLegend = document.getElementById('custom-box-legend');
    customBoxLegend.innerHTML = `
        <div><span style="background-color: rgba(255, 99, 132, 0.5);"></span>Total unpaid return late: RM${totalUnpaidBoxes['return late'].toFixed(2)}</div>
        <div><span style="background-color: rgba(255, 99, 132, 0.5);"></span>Total unpaid damage: RM${totalUnpaidBoxes['damage'].toFixed(2)}</div>
        <div><span style="background-color: rgba(255, 99, 132, 0.5);"></span>Total unpaid missing: RM${totalUnpaidBoxes['missing'].toFixed(2)}</div>
    `;
});
</script>

</body>
</html>
