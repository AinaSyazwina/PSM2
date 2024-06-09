<?php include 'navigaStu.php'?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reading Tips and Guides</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px; /* Reduced the space right and left */
            margin: 80px auto; /* Add more space from the top header */
            background: white;
            padding: 40px; /* Increase padding for more space inside the container */
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            font-size: 2.5em;
            text-align: center;
            margin-bottom: 10px;
        }
        .intro-text {
            text-align: center;
            font-size: 1.2em;
            color: #333;
            margin-bottom: 40px;
        }
        .tip {
            margin-bottom: 40px;
        }
        .tip h2 {
            color: #2e2185;
            font-size: 2em;
            margin-bottom: 10px;
        }
        .tip p {
            font-size: 1.2em;
            line-height: 1.8;
            color: #000; /* Ensure all font is black */
        }
        .tip ul {
            list-style-type: disc;
            padding-left: 20px;
            font-size: 1.1em;
            line-height: 1.6;
            color: #000; /* Ensure all font is black */
        }
        .tip .separator {
            text-align: center;
            margin: 30px 0;
        }
        .tip .separator span {
            font-size: 1.5em;
            color: #ffa500; /* Star color */
        }
        .separator img {
            width: 100%;
            max-width: 50px; /* Adjust the size of the separator star */
            height: auto;
        }
        .video-container {
            margin-top: 15px;
            text-align: center;
        }
        .video-container video,
        .video-container img {
            width: 100%;
            max-width: 700px;
            height: auto;
            border-radius: 8px;
        }
        .small-video {
            max-width: 350px; /* Reduce the size for smaller videos */
        }
        .small-image {
            max-width: 30px; /* Reduce the size for smaller images */
        }
        .thank-you {
            text-align: center;
            margin: 40px 0;
        }
        .thank-you h3 {
            font-size: 1.8em;
            color: #2e2185;
        }
        .thank-you p {
            font-size: 1.2em;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Reading Tips and Guides</h1>
        <p class="intro-text">Below are comprehensive reading tips and guides that students can diligently follow to not only enhance their interest in reading but also to cultivate a deep and lasting appreciation for the written word.</p>
        <?php
        $tips = [
            [
                "title" => "Finding the Right Book",
                "description" => [
                    "Finding the right book is crucial for enjoying your reading experience. Start by choosing books that match your interests and reading level.",
                    "Look for books with subjects you enjoy, such as animals, adventure, or mystery. This makes the reading process more engaging and enjoyable.",
                    "If you're unsure about which books to pick, don't hesitate to ask your librarian or teacher for recommendations. They can guide you to books that are popular and well-suited for your age and interests."
                ],
                "video" => "video/Find The Right Book.mp4"
            ],
            [
                "title" => "Setting a Reading Routine",
                "description" => [
                    "Establishing a reading routine can significantly improve your reading habits. Set aside a specific time each day for reading.",
                    "Try to read for at least 15-30 minutes daily, whether it’s before bed, after school, or during a quiet time at home. This consistency helps in developing a habit.",
                    "Having a regular reading time makes it easier to incorporate reading into your daily schedule and ensures you make steady progress through your books."
                ],
                "image" => "video/Cute Step by Step Reading More Books Facebook Post.png"
            ],
            [
                "title" => "Creating a Comfortable Reading Space",
                "description" => [
                    "Your reading environment plays a crucial role in how much you enjoy your reading sessions. Find a quiet and comfortable place to read.",
                    "Choose a spot with good lighting and minimal distractions. This could be a cozy corner of your room or a quiet spot in the library.",
                    "A comfortable reading space helps you concentrate better, reducing eye strain and making your reading experience more enjoyable."
                ],
                "video" => "video/Reading Spaces.mp4"
            ],
            [
                "title" => "Making Reading Fun",
                "description" => [
                    "Reading should be a fun and enjoyable activity. Add some fun to your reading sessions by using different voices for characters or imagining what happens next.",
                    "Engage with the story creatively, such as drawing pictures of scenes from the book or acting out parts of the story with friends.",
                    "Making reading fun helps you stay motivated and makes the experience more memorable."
                ]
            ],
            [
                "title" => "Keeping a Reading Journal",
                "description" => [
                    "Keeping a reading journal is an excellent way to reflect on what you read. Write about the books you read, noting down the title, author, and a summary of the story.",
                    "Include your thoughts on what you liked or didn’t like about the book. This practice helps improve your comprehension and retention of the material.",
                    "Journaling about your reading experiences allows you to track your progress and revisit your thoughts on different books over time."
                ],
                "video" => "video/Easy Bullet Journal Hacks _ LIFE HACKS FOR KIDS.mp4"
            ],
            [
                "title" => "Discussing Books with Friends",
                "description" => [
                    "Talking about books with your friends can enhance your reading experience. Share your favorite parts of the book and discuss the characters and plot.",
                    "Recommend books to each other and exchange views on different stories. This interaction helps you see different perspectives and deepen your understanding of the book.",
                    "Book discussions can also introduce you to new genres and authors, broadening your reading horizons."
                ]
            ],
            [
                "title" => "Using a Dictionary",
                "description" => [
                    "Expanding your vocabulary is an important part of becoming a better reader. Keep a dictionary handy to look up unfamiliar words you come across while reading.",
                    "Write down the meanings of these words in your reading journal. This helps you remember them better and improves your comprehension.",
                    "Learning new words enhances your ability to understand complex texts and express yourself more clearly."
                ]
            ],
            [
                "title" => "Breaking Down Difficult Books",
                "description" => [
                    "Don't get discouraged by challenging books. Break the book into smaller sections and read a little at a time to make it more manageable.",
                    "Discuss difficult parts with a teacher or parent to get a better understanding. This support can help you overcome obstacles and improve your reading skills.",
                    "Taking it slow and seeking help when needed can make reading tough books an achievable goal."
                ]
            ],
            [
                "title" => "Asking Questions While Reading",
                "description" => [
                    "Be curious about the story and engage actively with the text. Ask yourself questions like 'What will happen next?', 'Why did the character do that?', and 'How would I feel in this situation?'",
                    "Questioning helps you stay engaged and think more deeply about the text. It enhances your critical thinking skills and comprehension.",
                    "This practice makes reading an interactive and thought-provoking activity."
                ],
                "image" => "video/Reading Comprehension Questions.png",
                "image_class" => "small-image" /* Add class for small image */
            ],
            [
                "title" => "Using Bookmarks",
                "description" => [
                    "Use bookmarks to keep your place in the book. Make or choose a fun bookmark to save your spot so you can easily continue reading later.",
                    "Bookmarks are a simple yet effective tool to track your reading progress. They can also add a personal touch to your reading experience.",
                    "Having a bookmark prevents you from losing your place and makes it easy to pick up where you left off."
                ],
                "video" => "video/How to Make Bunny Corner Bookmarks - origami for kids.mp4"
            ],
            [
                "title" => "Reading Tips from Bill Gates",
                "description" => [
                    "Bill Gates reads about 50 books a year, which breaks down to about one a week. Gates shared his four habits and hacks that help him get the most out of his reading.",
                    "Knowing the habits of successful people can help you improve your own reading habits. Gates’ tips on how to read effectively are no exception.",
                    "Gates emphasizes the importance of taking notes, actively engaging with the material, and choosing a variety of books to broaden your knowledge."
                ],
                "video" => "video/How Bill Gates reads books.mp4"
            ]
        ];

        foreach ($tips as $tip) {
            echo "<div class='tip'>";
            echo "<h2>" . htmlspecialchars($tip['title']) . "</h2>";
            echo "<p>";
            if (is_array($tip['description'])) {
                foreach ($tip['description'] as $desc) {
                    echo "<ul><li>" . htmlspecialchars($desc) . "</li></ul>";
                }
            } else {
                echo htmlspecialchars($tip['description']);
            }
            echo "</p>";
            if (isset($tip['video'])) {
                echo "<div class='video-container'>";
                echo "<video controls>";
                echo "<source src='{$tip['video']}' type='video/mp4'>";
                echo "Your browser does not support the video tag.";
                echo "</video>";
                echo "</div>";
            }
            if (isset($tip['image'])) {
                echo "<div class='video-container'>";
                echo "<img src='{$tip['image']}' alt='Image for {$tip['title']}' class='" . (isset($tip['image_class']) ? $tip['image_class'] : "") . "'>";
                echo "</div>";
            }
            echo "<div class='separator'><span>✦✦✦✦✦</span></div>";
            echo "</div>";
        }
        ?>
        <div class="thank-you">
            <h3>Thank you! Hope this will be helpful.</h3>
            <p>Keep reading and exploring new worlds through books!</p>
        </div>
    </div>
</body>
</html>
