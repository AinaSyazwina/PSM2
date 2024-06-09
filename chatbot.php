<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="Cssfile/chatbot.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <script src="chatbot.js" defer></script>
</head>
<body > 

<button class="chatbot-toggler">
<span class="material-symbols-outlined">mode_comment</span>
<span class="material-symbols-outlined">close</span>
</button>
    <div class="chatbot">

    <header>
    <h2 style="text-align: center;">Chatbot</h2>

        <span class="close-btn material-symbols-outlined">close</span>
    </header>

    <ul class="chatbox">
        <li class="chat incoming">
            <span class="material-symbols-outlined">smart_toy</span>
            <p> Hi there👋  <br> How can i help you today </p>
        </li>
    </ul>

    <div class= "chat-input">

    <textarea placeholder="Enter a message...." required></textarea>
    <span id="send-btn" class="material-symbols-outlined">send </span>

    </div>

    </div>
</body>
</html>