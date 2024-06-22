<?php
include 'navigaStu.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Text Books</title>

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
            margin-top: 100px; 
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
            border-top: none;
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

        h1 {
            font-size: 2.5em;
            color: #333;
        }

        .dataTables_wrapper .dataTables_filter {
            text-align: right;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px; 
        }

        .filter-bar {
            display: inline-block;
            position: relative;
            width: 120px;
            height: 35px;
            background-color: #f8f9fa;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            color: #6c757d;
            margin-left: 10px;
        }

        .filter-bar select {
            border: none;
            background: none;
            font-size: 16px;
            width: 100%;
            height: 100%;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            padding-right: 20px; 
        }

        .filter-bar::after {
            content: '▼';
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: #6c757d;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: #333 !important;
            background-color: white !important;
            border: 1px solid #ccc !important;
            border-radius: 4px !important;
            padding: 5px 10px !important;
            margin: 2px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover,
        .dataTables_wrapper .dataTables_paginate .paginate_button:focus {
            background-color: #e9ecef !important;
            border: 1px solid #e9ecef!important;
            color: #ccc !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background-color: #e9ecef !important;
            border: 1px solid #e9ecef !important;
            color: white !important;
        }

        .dataTables_wrapper .dataTables_paginate {
            margin-top: 20px;
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
                    $title = preg_replace('/\b(T\d+|Y\d+|SK|pdf|PART|OF|[-.])\b/', '', $title); 
                    $title = preg_replace('/[-.]/', '', $title); 
                    $words = preg_split('/\s+/', trim($title));
                    return implode(' ', array_slice($words, 0, 2));
                }

                // Function to sort books by year/grade and title
                function sortBooks($a, $b) {
                    $yearGradeA = extractYearGrade($a);
                    $yearGradeB = extractYearGrade($b);

                   
                    preg_match('/\d+/', $yearGradeA, $matchesA);
                    preg_match('/\d+/', $yearGradeB, $matchesB);

                    $yearA = isset($matchesA[0]) ? (int)$matchesA[0] : 0;
                    $yearB = isset($matchesB[0]) ? (int)$matchesB[0] : 0;

                    if ($yearA === $yearB) {
                        return strcmp($a, $b);
                    }
                    return $yearA - $yearB;
                }

              
                usort($files, 'sortBooks');

                $no = 1;
                foreach ($files as $file) {
                    $parts = explode('/', $file);
                    $filename = end($parts);
                    $title = str_replace('Copy of ', '', $filename);
                    $shortTitle = extractFirstTwoWords($title);
                    $yearGrade = extractYearGrade($title);
                    $type = "SK"; 
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

    
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
   
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>

    <script>
        $(document).ready(function() {
         
            var table = $('#resourcesTable').DataTable();
            
            
            $('#resourcesTable_filter').append('<div class="filter-bar"><select id="filterSelect"><option value="all">All</option><option value="Science">Science</option><option value="mathematics">Mathematics</option><option value="matematik">Matematik</option><option value="sains">Sains</option><option value="bahasa melayu">Bahasa Melayu</option><option value="pendidikan islam">Pendidikan Islam</option></select></div>');

            
            $('#filterSelect').on('change', function() {
                var filter = this.value.toUpperCase();
                if (filter === 'ALL') {
                    table.search('').draw();
                } else {
                    table.search(filter).draw();
                }
            });
        });
    </script>
</body>
</html>
