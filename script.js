document.getElementById('chatForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const inputField = document.getElementById('userInput');
    const sendBtn = document.getElementById('sendBtn');
    const chatBox = document.getElementById('chatBox');
    const modeSelect = document.getElementById('mode');

    const message = inputField.value.trim();
    const mode = modeSelect ? modeSelect.value : 'general';

    if (!message) return;

    appendMessage(message, 'user');
    inputField.value = '';
    inputField.disabled = true;
    sendBtn.disabled = true;

    const aiMessageElement = appendMessage('', 'ai');
    
    aiMessageElement.innerHTML = `
        <div class="typing-indicator">
            <span></span>
            <span></span>
            <span></span>
        </div>
    `;

    let rawAiText = '';
    let isFirstChunk = true;

    try {
        const response = await fetch('process.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: message, mode: mode })
        });

        if (!response.ok) throw new Error('Network response was not ok');

        const reader = response.body.getReader();
        const decoder = new TextDecoder('utf-8');
        let buffer = '';

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;

            buffer += decoder.decode(value, { stream: true });
            
            // SOLUSI 1: Split berdasarkan satu baris (\n), bukan dua (\n\n) agar streaming lancar
            const lines = buffer.split("\n");
            buffer = lines.pop(); // Simpan sisa baris yang belum utuh

            for (const line of lines) {
                const trimmedLine = line.trim();
                if (!trimmedLine || !trimmedLine.startsWith('data: ')) continue;

                const dataStr = trimmedLine.replace('data: ', '').trim();
                if (dataStr === '[DONE]') break;

                try {
                    const parsed = JSON.parse(dataStr);
                    if (parsed.chunk) {
                        if (isFirstChunk) {
                            aiMessageElement.innerHTML = '';
                            isFirstChunk = false;
                        }

                        rawAiText += parsed.chunk;

                        // Render streaming lebih mulus
                        if (window.marked) {
                            aiMessageElement.innerHTML = marked.parse(rawAiText);
                        } else {
                            aiMessageElement.textContent = rawAiText;
                        }

                        chatBox.scrollTop = chatBox.scrollHeight;
                    } else if (parsed.error) {
                        aiMessageElement.textContent = 'Error: ' + parsed.error;
                    }
                } catch (err) {
                    // Ignore JSON parse error untuk chunk parsial
                }
            }
        }
    } catch (error) {
        aiMessageElement.textContent = 'Terjadi kesalahan sistem/koneksi.';
    } finally {
        inputField.disabled = false;
        sendBtn.disabled = false;
        inputField.focus();
        chatBox.scrollTop = chatBox.scrollHeight;
    }
});

function appendMessage(text, sender) {
    const chatBox = document.getElementById('chatBox');
    
    const wrapper = document.createElement('div');
    wrapper.classList.add('message-wrapper', sender);

    const avatar = document.createElement('div');
    avatar.classList.add('avatar');
    avatar.textContent = sender === 'user' ? '👤' : '👾';

    const msgDiv = document.createElement('div');
    msgDiv.classList.add('message');
    
    if (text) {
        if (window.marked && sender === 'ai') {
            msgDiv.innerHTML = marked.parse(text);
        } else {
            msgDiv.textContent = text;
        }
    }

    wrapper.appendChild(avatar);
    wrapper.appendChild(msgDiv);
    chatBox.appendChild(wrapper);

    chatBox.scrollTop = chatBox.scrollHeight;
    return msgDiv;
}