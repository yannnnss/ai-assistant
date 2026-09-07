<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Workspace & Assistant</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
   
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
</head>
<body>
    <div class="app-container">
        <header>
            <h2>AI-ASSISTANT</h2>
            <div class="mode-selector">
                <label for="mode">Mode:</label>
                <select id="mode">
                    <option value="general">💬 chatbot general</option>
                    <option value="work">💼 babu tugas</option>
                </select>
            </div>
        </header>

        <div class="chat-box" id="chatbox">
            <div class="message-wrapper ai">
            </div>
        </div>

        <form id="chatForm" class="input-area">
            <input type="text" id="userInput" placeholder="apa saja tanyakan..." autocomplete="off" required>
            <button type="submit" id="sendBtn">kirim</button>
        </form>
    </div>

    <script src="script.js"></script>
</body>
</html>