const chatInput = document.querySelector(".chat-input textarea");
const sendChatBtn = document.querySelector(".chat-input span");
const chatbox = document.querySelector(".chatbox");
const chatbotToggler = document.querySelector(".chatbot-toggler");
const chatbotCloseBtn = document.querySelector(".close-btn");

let userMessage;

const createChatLi = (message, className) => {
    const chatLi = document.createElement("li");
    chatLi.classList.add("chat", className);
    let chatContent = className === "outgoing" ? `<p>${message}</p>` :
        `<span class="material-symbols-outlined">smart_toy</span><p>${message}</p>`;
    chatLi.innerHTML = chatContent;
    return chatLi;
}

const generateResponse = (incomingChatLi) => {
    const API_URL = `https://api.wit.ai/message?v=20210513&q=${encodeURIComponent(userMessage)}`;

    const requestOptions = {
        method: "GET",
        headers: {
            "Authorization": "Bearer 5PLSX7KCZBSVUPVOT6DISFCORBRXUHSZ",
            "Content-Type": "application/json"
        }
    };

    fetch(API_URL, requestOptions)
        .then(response => response.json()) 
        .then(data => {
            console.log("Full API Response:", JSON.stringify(data));  
            if (data.intents && data.intents.length > 0) {
                handleIntent(data, incomingChatLi);  
            } else {
                incomingChatLi.querySelector("p").textContent = "I didn't understand that.";
            }
        })
        .catch(error => {
            console.error("Error processing response:", error);
            incomingChatLi.querySelector("p").textContent = `Error: ${error.message}`;
        });
};

function handleIntent(response, incomingChatLi) {
    const intent = response.intents && response.intents.length > 0 ? 
    response.intents[0] : null;
    const entities = response.entities;

    if (!intent) {
        incomingChatLi.querySelector("p").textContent = "No intent found.";
        return;
    }

    switch (intent.name) {
        case 'greeting':
        case 'list_book_genres':
        case 'schoolHistory':
        case 'libraryHistory':
        case 'fineBookType':
        case 'fineBoxType':
        case 'fineBookPrice':
        case 'fineBoxPrice':
        case 'how_are_you':
        case 'welcome':
        case 'libraryTime':
            handleSimpleResponses(intent, incomingChatLi);
            break;
        case 'books_by_genre':
            handleGenreSpecificQuery(intent, entities, incomingChatLi);
            break;
        case 'books_by_author':
            handleAuthorSpecificQuery(intent, entities, incomingChatLi);
            break;
        case 'boxs_by_category':
            if (entities && entities['category:category']) {
                const categoryValue = entities['category:category'][0].value;
                handleQuery(intent, categoryValue, incomingChatLi);
            } else {
                incomingChatLi.querySelector("p").textContent = "Category not specified.";
            }
            break;
        case 'recently_added_books':
        case 'popular_books':
        case 'top_authors':
        case 'most_popular_box':
        case 'recently_added_box':
        case 'highest_boxquantity':
        case 'lowest_boxquantity':
            handleGeneralQuery(intent, incomingChatLi);
            break;
        default:
            incomingChatLi.querySelector("p").textContent = "I didn't understand that.";
            break;
    }
}




function handleBoxQuery(intent, incomingChatLi) {
    let fetchUrl = `chatbotBox.php?queryType=${intent.name}`;
    console.log("Fetching URL for boxes:", fetchUrl);

    // Fetch and handle the server response
    fetch(fetchUrl)
        .then(response => response.json())
        .then(data => handleBoxServerResponse(data, incomingChatLi))
        .catch(error => {
            console.error("Fetch error for box query:", error);
            incomingChatLi.querySelector("p").textContent = `Fetch Error: ${error.message}`;
        });
}

function handleBoxServerResponse(data, incomingChatLi) {
    if (data.error) {
        incomingChatLi.querySelector("p").textContent = data.error;  // Handle errors
        return;
    }

    if (data.message) {
        incomingChatLi.querySelector("p").textContent = data.message;  // Display response message
        return;
    }

    // Assuming 'boxes' is the data key for box results, similar to 'books' for book results
    if (data.boxes && data.boxes.length > 0) {
        const boxDetails = data.boxes.map(box => `Box ${box.BoxSerialNum} with ${box.Count} issues.`).join(", ");
        incomingChatLi.querySelector("p").textContent = "Popular Boxes: " + boxDetails;  // Display box details
    } else {
        incomingChatLi.querySelector("p").textContent = "No boxes found for this query.";  // No boxes found
    }
}


function handleSimpleResponses(intent, incomingChatLi) {
    let responseText = "";
    switch (intent.name) {
        case 'greeting':
    responseText = "Hello there!👋 How can I help you?";
    break;

        case 'list_book_genres':
            responseText = "The available book genres are: romance, fiction, non-fiction, mystery, fairytale, action, fantasy, and historical.";
            break;
        case 'schoolHistory':
            responseText = "SK Kamunting is a primary school located at Jalan Sekolah Rendah 1, Kamunting, Perak. It was established on 3 January 1987, with Encik Ahmad Ahtar bin Abdul Latiff serving as its first principal. The school has 669 students and employs 54 teachers.";
            break;
        case 'libraryHistory':
            responseText = "The library at SK Kamunting was built in October 1988 to provide students with access to a wide range of books and resources for their academic and personal development.";
            break;
        case 'fineBookType':
            responseText = "There are four types of fines for books: late return, missing, damage, and kept.";
            break;
        case 'fineBoxType':
            responseText = "There are three types of fines for boxes: late return, missing, and damage.";
            break;
        case 'fineBookPrice':
            responseText = "The fines for books are: return late RM2.00, missing RM5.00, damage RM4.00, and kept RM5.00.";
            break;
        case 'fineBoxPrice':
            responseText = "The fines for boxes are: return late RM5.00, missing RM15.00, and damage RM10.00.";
            break;
        case 'how_are_you':
            responseText = "Hello! 😊 I'm fine, thank you. How can I assist you today?";
            break;
        
        case 'welcome':
            responseText = "Glad I could help you! 🤗 See you again. Goodbye!";
            break;
            
        case 'libraryTime':
            responseText = "The library opens from 8:30 AM until 12:30 PM.";
            break;


        default:
            responseText = "I didn't understand that.";
            break;
    }
    incomingChatLi.querySelector("p").textContent = responseText;
}


function handleAuthorSpecificQuery(intent, entities, incomingChatLi) {
    if (entities && 'author:author' in entities && entities['author:author'][0]) {
        const authorName = entities['author:author'][0].value;
        handleQuery(intent, authorName, incomingChatLi);
    } else {
        incomingChatLi.querySelector("p").textContent = "Author not specified.";
    }
}

function handleGenreSpecificQuery(intent, entities, incomingChatLi) {
    if (entities && 'genre:genre' in entities && entities['genre:genre'][0]) {
        const genreValue = entities['genre:genre'][0].value;
        handleQuery(intent, genreValue, incomingChatLi);
    } else {
        incomingChatLi.querySelector("p").textContent = "Genre not specified.";
    }
}

function handleGeneralQuery(intent, incomingChatLi) {
    // No additional data needs to be passed for these queries
    handleQuery(intent, null, incomingChatLi);
}


function handleQuery(intent, entityValue, incomingChatLi) {
    let fetchUrl = "";

    if (intent.name.includes('box')) {
        fetchUrl = `chatbotBox.php?queryType=${intent.name}`;
        if (intent.name === 'boxs_by_category' && entityValue) {
            fetchUrl += `&category=${encodeURIComponent(entityValue)}`;
        }
    } else {
        fetchUrl = `chatbotBook.php?queryType=${intent.name}`;
        if (entityValue) {
            let queryParam = (intent.name === 'books_by_genre') ? 'genre' : 'author';
            fetchUrl += `&${queryParam}=${encodeURIComponent(entityValue)}`;
        }
    }
    console.log("Fetching URL:", fetchUrl);  // Check the URL being fetched

    fetch(fetchUrl)
        .then(response => response.json())
        .then(data => handleServerResponse(data, incomingChatLi))
        .catch(error => {
            console.error("Fetch error:", error);
            incomingChatLi.querySelector("p").textContent = `Fetch Error: ${error.message}`;
        });
}



function handleServerResponse(data, incomingChatLi) {
    if (data.error) {
        incomingChatLi.querySelector("p").textContent = data.error;
        return;
    }

    if (data.message) {
        incomingChatLi.querySelector("p").textContent = data.message;
        return;
    }

    if (data.boxes && data.boxes.length > 0) {
        const boxes = data.boxes.map(box => `Box ${box.BoxSerialNum}`).join(", ");
        incomingChatLi.querySelector("p").textContent = "Boxes: " + boxes;
    } else {
        incomingChatLi.querySelector("p").textContent = "No boxes found for this query.";
    }
}


// Ensure other intents are handled without requiring a genre
function getQueryTypeFromIntent(intent) {
    switch (intent.name) {
        case 'books_by_author':
            return 'books_by_author';
        case 'books_by_genre':
            return 'books_by_genre';
        case 'recently_added_books':
            return 'recently_added_books';
        case 'popular_books':
            return 'popular_books';
        case 'top_authors':
            return 'top_authors';
        // Handle box-related intents
        case 'most_popular_box':
            return 'most_popular_box';
        case 'recently_added_box':
            return 'recently_added_box';
        case 'highest_boxquantity':
            return 'highest_boxquantity';
        case 'lowest_boxquantity':
            return 'lowest_boxquantity';
        default:
            return null;  
    }
}


const handleChat = () => {
    userMessage = chatInput.value.trim();
    if (!userMessage) return;
    chatInput.value = "";
    chatbox.appendChild(createChatLi(userMessage, "outgoing"));
    chatbox.scrollTo(0, chatbox.scrollHeight);

    setTimeout(() => {
        const incomingChatLi = createChatLi("Thinking...", "incoming");
        chatbox.appendChild(incomingChatLi);
        generateResponse(incomingChatLi);
    }, 200);
}

document.addEventListener('DOMContentLoaded', () => {
    const chatbotToggler = document.querySelector('.chatbot-toggler');
    const chatbot = document.querySelector('.chatbot');
    let isChatbotOpen = false; // Track the state of the chatbot

    chatbotToggler.addEventListener('click', () => {
        if (isChatbotOpen) {
            // Close chatbot
            chatbot.style.transform = 'scale(0.5)';
            chatbot.style.opacity = '0';
            chatbot.style.pointerEvents = 'none';
            isChatbotOpen = false;
        } else {
            // Open chatbot
            chatbot.style.transform = 'scale(1)';
            chatbot.style.opacity = '1';
            chatbot.style.pointerEvents = 'auto';
            isChatbotOpen = true;
        }
    });
});

chatbotToggler.addEventListener('click', () => {
    const isOpen = chatbot.style.opacity === '1';
    if (isOpen) {
        // Close the chatbot
        chatbot.style.transform = 'scale(0.5)';
        chatbot.style.opacity = '0';
        chatbot.style.pointerEvents = 'none';
        chatbotToggler.innerHTML = '<span class="material-symbols-outlined">mode_comment</span>';
    } else {
        // Open the chatbot
        chatbot.style.transform = 'scale(1)';
        chatbot.style.opacity = '1';
        chatbot.style.pointerEvents = 'auto';
        chatbotToggler.innerHTML = '<span class="material-symbols-outlined">close</span>';
    }
});


sendChatBtn.addEventListener("click", handleChat);
chatbotCloseBtn.addEventListener("click", () => document.body.classList.remove("show-chatbot"));
chatbotToggler.addEventListener("click", () => document.body.classList.toggle("show-chatbot"));