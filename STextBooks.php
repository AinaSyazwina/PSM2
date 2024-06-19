<?php
include 'navigaStu.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Text Books</title>
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .header {
            text-align: center;
            margin-top: 100px; /* Add space from the top */
        }

        .search-bar {
            text-align: right;
            margin: 20px 10%;
        }

        .container {
            width: 90%;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
            border-top: none; /* Remove top border from table headers */
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        a {
            color: #007BFF;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        /* Modern CSS */
        h1 {
            font-size: 2.5em;
            color: #333;
        }

        .filter-bar select {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Text Books</h1>
    </div>
   
    <div class="container">
        <table id="resourcesTable" class="display">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Year/Grade</th>
                    <th>Book Title</th>
                    <th>Type</th>
                    <th>Format</th>
                    <th>Download Link</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Sample file data
                $files = [
                    "TextBook/Copy of BAHASA MELAYU T1 SK.pdf",
                    "TextBook/Copy of BAHASA MELAYU T2 SK - JLD2.pdf",
                    "TextBook/Copy of BAHASA MELAYU T3 SK - JLD1 - 1 DRP 2 (2).pdf",
                    "TextBook/Copy of BAHASA MELAYU T4 SK.pdf",
                    "TextBook/Copy of BAHASA MELAYU T5 SK - 1 DRP 2.pdf",
                    "TextBook/Copy of BAHASA MELAYU T6 SK - 1 DRP 2.pdf",
                    "TextBook/Copy of MATEMATIK T1 SK - JLD2.pdf",
                    "TextBook/Copy of MATHEMATICS Y1 - PART 2.pdf",
                    "TextBook/Copy of MATHEMATICS Y2 - PART 1.pdf",
                    "TextBook/Copy of MATHEMATICS Y4 - 1 DRP 2.pdf",
                    "TextBook/Copy of MATHEMATICS Y5 - 1 OF 2.pdf",
                    "TextBook/Copy of PENDIDIKAN ISLAM T2 (1).pdf",
                    "TextBook/Copy of PENDIDIKAN ISLAM T2.pdf",
                    "TextBook/Copy of PENDIDIKAN ISLAM T4.pdf",
                    "TextBook/Copy of PENDIDIKAN ISLAM T5.pdf",
                    "TextBook/Copy of SAINS SK T2.pdf",
                    "TextBook/Copy of SAINS T1 SK.pdf",
                    "TextBook/Copy of SAINS T1 SK.pdf",
                    "TextBook/Copy of SCIENCE Y1.pdf",
                    "TextBook/Copy of SCIENCE Y3.pdf",
                    "TextBook/Copy of SCIENCE Y4.pdf",
                    "TextBook/Copy of SCIENCE Y5 - 1 OF 4.pdf",
                    "TextBook/Copy of SCIENCE Y6.pdf"
                ];

                // Function to extract year/grade from the title
                function extractYearGrade($title) {
                    if (preg_match('/T(\d+)/i', $title, $matches) || preg_match('/Y(\d+)/i', $title, $matches)) {
                        return 'Year ' . $matches[1];
                    }
                    return 'Unknown';
                }

                // Function to extract the first two words from the title and remove unwanted parts
                function extractFirstTwoWords($title) {
                    $title = preg_replace('/\b(T\d+|Y\d+|SK|pdf|PART|OF|[-.])\b/', '', $title); // Remove unwanted parts
                    $title = preg_replace('/[-.]/', '', $title); // Remove remaining hyphens and dots
                    $words = preg_split('/\s+/', trim($title));
                    return implode(' ', array_slice($words, 0, 2));
                }

                // Function to sort books by year/grade and title
                function sortBooks($a, $b) {
                    $yearGradeA = extractYearGrade($a);
                    $yearGradeB = extractYearGrade($b);

                    // Extract year number for comparison
                    preg_match('/\d+/', $yearGradeA, $matchesA);
                    preg_match('/\d+/', $yearGradeB, $matchesB);

                    $yearA = isset($matchesA[0]) ? (int)$matchesA[0] : 0;
                    $yearB = isset($matchesB[0]) ? (int)$matchesB[0] : 0;

                    if ($yearA === $yearB) {
                        return strcmp($a, $b);
                    }
                    return $yearA - $yearB;
                }

                // Sort the files array
                usort($files, 'sortBooks');

                // Generate table rows dynamically
                $no = 1;
                foreach ($files as $file) {
                    $parts = explode('/', $file);
                    $filename = end($parts);
                    $title = str_replace('Copy of ', '', $filename);
                    $shortTitle = extractFirstTwoWords($title);
                    $yearGrade = extractYearGrade($title);
                    $type = "SK"; // Static type as example
                    $format = pathinfo($filename, PATHINFO_EXTENSION);
                    echo "<tr data-title='$shortTitle'>
                            <td>$no</td>
                            <td>$yearGrade</td>
                            <td>$shortTitle</td>
                            <td>$type</td>
                            <td>$format</td>
                            <td><a href='$file'>Download</a></td>
                          </tr>";
                    $no++;
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <!-- DataTables JS -->
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTables
            var table = $('#resourcesTable').DataTable();
            
            // Append filter to the search box container
            $('#resourcesTable_filter').append('<select id="filterSelect" class="filter-bar"><option value="all">All</option><option value="mathematics">Mathematics</option><option value="matematik">Matematik</option><option value="sains">SAINS</option></select>');

            // Filter function
            $('#filterSelect').on('change', function() {
                var filter = this.value.toUpperCase();
                var table = $('#resourcesTable').DataTable();
                table.search(filter).draw();
            });
        });
    </script>
</body>
</html>
