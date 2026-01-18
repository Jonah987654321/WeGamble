class Chat {
    #wsClient;
    #messagesContainer;

    constructor(wsClient) {
        this.#wsClient = wsClient;

        this.#wsClient.registerBroadcastEvent("chatMessageReceived", (data) => {
            this.#handleNewMessage(data["data"])
        });
    }

    start() {
        this.#createUI();
    }

    #createUI() {
        const chatWrapper = document.getElementById("lobbyChatWrapper");
        if (!chatWrapper) {
            console.error("lobbyChatWrapper element not found");
            return;
        }

        // Clear the wrapper
        chatWrapper.innerHTML = "";

        // Create header
        const header = document.createElement("div");
        header.classList.add("chatHeader");

        const title = document.createElement("h2");
        title.classList.add("chatTitle");
        title.innerHTML = '<i class="fa-solid fa-comments"></i> Chat';
        header.appendChild(title);

        chatWrapper.appendChild(header);

        // Create messages container
        this.#messagesContainer = document.createElement("div");
        this.#messagesContainer.classList.add("chatMessagesContainer");

        chatWrapper.appendChild(this.#messagesContainer);

        // Create input area
        const inputArea = document.createElement("div");
        inputArea.classList.add("chatInputArea");

        const input = document.createElement("input");
        input.id = "chatMessageInput";
        input.type = "text";
        input.classList.add("chatInput");
        input.placeholder = "Chatte hier...";
        input.maxLength = 200;
        input.onkeydown = (e) => {
            if (e.key === "Enter") {
                this.#sendMessage();
            }
        };

        const sendButton = document.createElement("button");
        sendButton.onclick = () => { this.#sendMessage() };
        sendButton.classList.add("chatSendButton");
        sendButton.innerHTML = '<i class="fa-solid fa-paper-plane"></i>';

        inputArea.appendChild(input);
        inputArea.appendChild(sendButton);

        chatWrapper.appendChild(inputArea);

        // Auto-scroll to bottom
        this.#scrollToBottom();
    }

    #createMessageElement(username, text, time) {
        const messageDiv = document.createElement("div");
        messageDiv.classList.add("chatMessage");

        const headerDiv = document.createElement("div");
        headerDiv.classList.add("chatMessageHeader");

        const usernameSpan = document.createElement("span");
        usernameSpan.classList.add("chatMessageUsername");
        usernameSpan.textContent = username;

        const timeSpan = document.createElement("span");
        timeSpan.classList.add("chatMessageTime");
        timeSpan.textContent = time;

        headerDiv.appendChild(usernameSpan);
        headerDiv.appendChild(timeSpan);

        const textDiv = document.createElement("div");
        textDiv.classList.add("chatMessageText");
        textDiv.textContent = text;

        messageDiv.appendChild(headerDiv);
        messageDiv.appendChild(textDiv);

        return messageDiv;
    }

    #scrollToBottom() {
        if (this.#messagesContainer) {
            this.#messagesContainer.scrollTop = this.#messagesContainer.scrollHeight;
        }
    }

    #sendMessage() {
        const textbox = document.getElementById("chatMessageInput");
        const content = textbox.value;
        if (content.trim() == "") return;
        this.#wsClient.sendAsJson({
            "type": "chatMessageSend",
            "messageContent": content,
        });
        textbox.value = "";
    }

    #handleNewMessage(data) {
        const timestamp = new Date(data["timestamp"] * 1000).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false })
        const messageElement = this.#createMessageElement(data["from"], data["messageContent"], timestamp);
        this.#messagesContainer.appendChild(messageElement);
        this.#scrollToBottom();
    }

}