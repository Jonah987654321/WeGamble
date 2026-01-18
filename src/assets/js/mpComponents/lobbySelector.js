class LobbySelector {
    #wsClient;
    #lobbyJoinHandler;

    constructor(wsClient) {
        // Set ws client instance for latter use
        this.#wsClient = wsClient;

        // Register websocket handlers
        this.#wsClient.registerSuccessEvent("requestLobbies", (data) => {
            this.#updateLobbyList(data["lobbies"]);
        });
        this.#wsClient.registerSuccessEvent("joinLobby", (data) => {
            this.#handleInLobby(data["lobbyData"], false);
        });
        this.#wsClient.registerSuccessEvent("createLobby", (data) => {
            this.#handleInLobby(data["lobbyData"], true);
        })
        this.#wsClient.registerErrorCode(6107, () => {
            notify("Der Beitritt zum Spiel ist fehlgeschlagen.");
            this.#requestLobbyList();
        });
    }

    setLobbyJoinHandler(callback) {
        if (typeof callback !== "function") {
            throw new TypeError("GS Restore Handler must be a function");
        }

        this.#lobbyJoinHandler = callback;
    }

    start() {
        if (this.#wsClient.getStatus() != STATUS_CHECKED_IN) {
            throw new Error("Lobby Selector only allowed to start on checked-in wsClient")
        }
        this.#createUI();
        this.#requestLobbyList();
    }

    #createUI() {
        const wrapper = document.getElementById("lobbySelectorWrapper");
        if (!wrapper) {
            console.error("lobbySelectorWrapper element not found");
            return;
        }

        // Clear the wrapper
        wrapper.innerHTML = "";

        // Create the main container
        const container = document.createElement("div");
        container.classList.add("lobbySelectorContainer");

        // Header section
        const header = document.createElement("div");
        header.classList.add("lobbyHeader");

        const title = document.createElement("h1");
        title.textContent = "Einer Lobby beitreten";
        title.classList.add("lobbyTitle");
        header.appendChild(title);

        container.appendChild(header);

        // Tabs container
        const tabsContainer = document.createElement("div");
        tabsContainer.classList.add("lobbyTabs");

        const joinTab = document.createElement("button");
        joinTab.classList.add("lobbyTab", "active");
        joinTab.dataset.tab = "join";
        joinTab.innerHTML = '<i class="fa-solid fa-door-open"></i> Spiel beitreten';
        joinTab.onclick = () => switchTab("join");

        const createTab = document.createElement("button");
        createTab.classList.add("lobbyTab");
        createTab.dataset.tab = "create";
        createTab.innerHTML = '<i class="fa-solid fa-plus"></i> Neues Spiel';
        createTab.onclick = () => switchTab("create");

        tabsContainer.appendChild(joinTab);
        tabsContainer.appendChild(createTab);
        container.appendChild(tabsContainer);

        // Content area
        const contentArea = document.createElement("div");
        contentArea.classList.add("lobbyContent");

        // Join lobbies tab
        const joinContent = document.createElement("div");
        joinContent.id = "joinTab";
        joinContent.classList.add("lobbyContentTab", "active");

        const lobbyList = document.createElement("div");
        lobbyList.id = "lobbyList";
        lobbyList.classList.add("lobbyList");

        joinContent.appendChild(lobbyList);
        contentArea.appendChild(joinContent);

        // Create lobby tab
        const createContent = document.createElement("div");
        createContent.id = "createTab";
        createContent.classList.add("lobbyContentTab");

        const createForm = document.createElement("form");
        createForm.classList.add("lobbyCreateForm");

        // Lobby name input
        const nameGroup = document.createElement("div");
        nameGroup.classList.add("formGroup");

        const nameLabel = document.createElement("label");
        nameLabel.htmlFor = "lobbyNameInput";
        nameLabel.textContent = "Spielname";
        nameGroup.appendChild(nameLabel);

        const nameInput = document.createElement("input");
        nameInput.type = "text";
        nameInput.id = "lobbyNameInput";
        nameInput.placeholder = "Wähle einen Namen für die Lobby";
        nameInput.maxLength = 50;
        nameGroup.appendChild(nameInput);
        createForm.appendChild(nameGroup);

        // Password input
        const pwdGroup = document.createElement("div");
        pwdGroup.classList.add("formGroup");

        const pwdLabel = document.createElement("label");
        pwdLabel.htmlFor = "lobbyPasswordInput";
        pwdLabel.textContent = "Passwort (optional)";
        pwdGroup.appendChild(pwdLabel);

        const pwdInput = document.createElement("input");
        pwdInput.type = "password";
        pwdInput.id = "lobbyPasswordInput";
        pwdInput.placeholder = "Leer lassen für öffentliches Spiel";
        pwdInput.maxLength = 50;
        pwdGroup.appendChild(pwdInput);
        createForm.appendChild(pwdGroup);

        // Create button
        const createButton = document.createElement("button");
        createButton.type = "button";
        createButton.classList.add("createLobbyButton");
        createButton.innerHTML = '<i class="fa-solid fa-check"></i> Spiel erstellen';
        createButton.onclick = () => this.#handleCreateLobby(nameInput, pwdInput);
        createForm.appendChild(createButton);

        createContent.appendChild(createForm);
        contentArea.appendChild(createContent);

        container.appendChild(contentArea);
        wrapper.appendChild(container);
    }

    // Requests the list of available lobbies from the server
    #requestLobbyList() {
        // Set UI to loading state
        const lobbyList = document.getElementById("lobbyList");
        lobbyList.innerHTML = "";
        const loadingMessage = document.createElement("div");
        loadingMessage.classList.add("loadingMessage");
        loadingMessage.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Laden...';
        lobbyList.appendChild(loadingMessage);

        try {
            this.#wsClient.sendAsJson({ "type": "requestLobbies" });
        } catch (error) {
            console.error("Error requesting lobbies:", error);
        }
    }

    // Updates the lobby list display with available lobbies
    #updateLobbyList(lobbies) {
        const lobbyList = document.getElementById("lobbyList");
        if (!lobbyList) return;

        lobbyList.innerHTML = "";

        const retryButton = document.createElement("button");
        retryButton.classList.add("createLobbyButton");
        retryButton.innerHTML = '<i class="fa-solid fa-arrows-rotate"></i> Aktualisieren';
        retryButton.onclick = () => { this.#requestLobbyList() };

        if (lobbies.length === 0) {
            const emptyMessage = document.createElement("div");
            emptyMessage.classList.add("emptyMessage");
            emptyMessage.innerHTML = '<i class="fa-solid fa-inbox"></i><p>Keine verfügbaren Spiele</p><small>Erstelle ein neues Spiel um zu beginnen</small>';
            lobbyList.appendChild(emptyMessage);
            lobbyList.appendChild(retryButton);
            return;
        }

        lobbies.forEach((lobby) => {
            const lobbyItem = document.createElement("div");
            lobbyItem.classList.add("lobbyItem");

            // Lobby info
            const lobbyInfo = document.createElement("div");
            lobbyInfo.classList.add("lobbyInfo");

            const lobbyName = document.createElement("div");
            lobbyName.classList.add("lobbyName");
            lobbyName.textContent = lobby["lobbyName"];
            lobbyInfo.appendChild(lobbyName);

            const lobbyStats = document.createElement("div");
            lobbyStats.classList.add("lobbyStats");

            const playerCount = document.createElement("span");
            playerCount.classList.add("playerCount");
            playerCount.innerHTML = `<i class="fa-solid fa-users"></i> ${lobby["lobbyCurrentPlayerCount"]}/${lobby["lobbyMaxPlayers"]} Spieler`;
            lobbyStats.appendChild(playerCount);

            if (lobby["lobbyIsPrivate"]) {
                const privateIcon = document.createElement("span");
                privateIcon.classList.add("privateIcon");
                privateIcon.innerHTML = '<i class="fa-solid fa-lock"></i> Privat';
                lobbyStats.appendChild(privateIcon);
            }

            lobbyInfo.appendChild(lobbyStats);
            lobbyItem.appendChild(lobbyInfo);

            // Join button or password prompt
            const joinButton = document.createElement("button");
            joinButton.classList.add("joinButton");
            joinButton.innerHTML = '<i class="fa-solid fa-arrow-right"></i>';
            joinButton.title = "Beitreten";

            joinButton.onclick = () => {
                if (lobby["lobbyIsPrivate"]) {
                    this.#promptForPassword(lobby["lobbyID"]);
                } else {
                    this.#joinLobby(lobby["lobbyID"]);
                }
            };

            lobbyItem.appendChild(joinButton);
            lobbyList.appendChild(lobbyItem);
        });
        lobbyList.appendChild(retryButton);
    }

    // Handles joining a lobby
    #joinLobby(lobbyID, password = null) {
        const data = { "type": "joinLobby", "lobbyID": lobbyID };
        if (password !== null) {
            data["lobbyPassword"] = password;
        }
        this.#wsClient.sendAsJson(data);
    }

    // Prompts user for password when joining a private lobby
    #promptForPassword(lobbyID) {
        const overlayContent = document.createElement("div");
        overlayContent.classList.add("passwordPrompt");

        const title = document.createElement("h2");
        title.textContent = "Passwort erforderlich";
        title.style.color = 'black';
        overlayContent.appendChild(title);

        const description = document.createElement("p");
        description.textContent = "Dieses Spiel ist privat. Bitte gib das Passwort ein:";
        description.style.color = 'black';
        overlayContent.appendChild(description);

        const input = document.createElement("input");
        input.type = "password";
        input.placeholder = "Passwort";
        input.classList.add("passwordInput");
        overlayContent.appendChild(input);

        const buttonGroup = document.createElement("div");
        buttonGroup.classList.add("overlaySuccessWrapper");

        const cancelButton = document.createElement("button");
        cancelButton.textContent = "Abbrechen";
        cancelButton.style.marginRight = "10px";
        cancelButton.style.backgroundColor = "#999";
        cancelButton.onclick = () => destructOverlay();
        buttonGroup.appendChild(cancelButton);

        const submitButton = document.createElement("button");
        submitButton.textContent = "Beitreten";
        submitButton.onclick = () => {
            const password = input.value.trim();
            if (password === "") {
                return;
            }
            destructOverlay();
            this.#joinLobby(lobbyID, password);
        };
        buttonGroup.appendChild(submitButton);

        overlayContent.appendChild(buttonGroup);
        createNewOverlay(overlayContent);
    }

    // Handles creating a new lobby
    #handleCreateLobby(nameInput, pwdInput) {
        const name = nameInput.value.trim();

        if (name === "") {
            notify("Bitte gib einen Spielnamen ein");
            return;
        }

        const data = {
            "type": "createLobby",
            "lobbyName": name
        };

        const password = pwdInput.value.trim();
        if (password !== "") {
            data["lobbyPassword"] = password;
        }

        this.#wsClient.sendAsJson(data);
    }

    // Handle the event of a successful lobby join/create -> lobbySelector is finished
    #handleInLobby(lobbyData, selfCreated) {
        // Clear our UI
        const wrapper = document.getElementById("lobbySelectorWrapper");
        if (wrapper) {
            wrapper.innerHTML = "";
        }

        // Notify the game logic
        this.#lobbyJoinHandler(lobbyData, selfCreated);
    }
}

// Switches between tabs
function switchTab(tabName) {
    // Hide all tabs
    const tabs = document.querySelectorAll(".lobbyContentTab");
    tabs.forEach(tab => tab.classList.remove("active"));

    // Remove active class from all tab buttons
    const tabButtons = document.querySelectorAll(".lobbyTab");
    tabButtons.forEach(btn => btn.classList.remove("active"));

    // Show selected tab
    const selectedTab = document.getElementById(tabName + "Tab");
    if (selectedTab) {
        selectedTab.classList.add("active");
    }

    // Mark selected tab button as active
    const selectedButton = document.querySelector(`[data-tab="${tabName}"]`);
    if (selectedButton) {
        selectedButton.classList.add("active");
    }
}
