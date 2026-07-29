document.addEventListener("DOMContentLoaded", function () {
    const chatLog = document.getElementById('chatLog');
    const chatInput = document.getElementById('chatInput');
    const sendButton = document.getElementById('sendMessageBtn');

    sendButton.addEventListener('click', () => {
        const message = chatInput.value.trim();
        if (!message) return;

        appendMessage(message, 'user');
        chatInput.value = '';

        fetch('/api/cha-gemini.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ message })
})
        .then(res => res.json())
        .then(data => {
            appendMessage(data.reply, 'bot');
        })
        .catch(() => {
            appendMessage('Error al conectar con Gemini.', 'bot');
        });
    });

    const toggleBtn = document.getElementById('chatToggle');
    const chatWidget = document.getElementById('chatWidget');

    toggleBtn.addEventListener('click', () => {
        chatWidget.style.display = chatWidget.style.display === 'none' ? 'flex' : 'none';
    });

    // Asegúrate de declarar esta función dentro del DOMContentLoaded o fuera, pero una sola vez
    function appendMessage(text, sender) {
        const msg = document.createElement('div');
        msg.className = `chat-msg ${sender}-msg`;

        if (sender === 'bot') {
            msg.innerHTML = marked.parse(text); // convierte Markdown a HTML
        } else {
            msg.textContent = text; // texto plano para el usuario
        }

        chatLog.appendChild(msg);
        chatLog.scrollTop = chatLog.scrollHeight;
    }
});