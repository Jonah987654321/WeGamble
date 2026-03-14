class LobbyWaitingRoom {
    #wsClient
    #lobbyData;
    #userIsOwner;

    #playerHolder;

    #gameplayHandler;

    constructor(wsClient) {
        this.#wsClient = wsClient;
        this.#wsClient.registerBroadcastEvent("playerJoin", (data) => {
            this.#lobbyData = data["data"]["fullLobbyData"];
            this.#updatePlayerList();
        });
        this.#wsClient.registerBroadcastEvent("gameHasStarted", (data) => {
            document.getElementById("lobbyWaitingWrapper").classList.add("hidden");
            document.getElementById("lobbyGameTableWrapper").classList.remove("hidden");
            if (this.#gameplayHandler != null) {
                this.#gameplayHandler(data["data"]);
            }
        });
        this.#wsClient.registerErrorCode(6108, (data) => {
            notify("Dir fehlt dazu die Berechtigung!");
        });
        this.#wsClient.registerErrorCode(6109, (data) => {
            notify("Mindestens " + this.#lobbyData["lobbyMinPlayers"] + " Spieler benötigt");
        })
    }

    setGameplayHandler(callback) {
        if (typeof callback !== "function") {
            throw new TypeError("GS Restore Handler must be a function");
        }
        this.#gameplayHandler = callback;
    }

    setLobbyData(lobbyData, userIsOwner) {
        this.#lobbyData = lobbyData;
        this.#userIsOwner = userIsOwner;
    }

    start() {
        this.#createUI();
        this.#updatePlayerList()
    }

    #updatePlayerList() {
        this.#playerHolder.innerHTML = '';
        this.#lobbyData["players"].forEach((p) => {
            const playerCard = document.createElement("div");
            playerCard.classList.add("wr_playerList_playerCard");
            playerCard.textContent = p["userName"];
            this.#playerHolder.appendChild(playerCard);
        });
    }

    #createUI() {
        const wrapper = document.getElementById("lobbyWaitingWrapper");

        const lobbyHeader = document.createElement("div");
        lobbyHeader.classList.add("wr_lobbyHeader");
        const lobbyTitle = document.createElement("h1");
        lobbyTitle.textContent = "Lobby wartet auf Start..."
        lobbyHeader.appendChild(lobbyTitle);
        const lobbyDetails = document.createElement("div");
        lobbyDetails.classList.add("wr_lobbyDetails");
        const lobbyName = document.createElement("div");
        lobbyName.classList.add("wr_lobbyDetailContent");
        lobbyName.innerHTML = '<i class="fa-solid fa-tag fa-rotate-90"></i> Lobby-Name: ' + this.#lobbyData["lobbyName"];
        lobbyDetails.appendChild(lobbyName);
        const lobbyPassword = document.createElement("div");
        lobbyPassword.classList.add("wr_lobbyDetailContent");
        lobbyPassword.innerHTML = '<i class="fa-solid fa-lock"></i> Lobby-Passwort: ' + this.#lobbyData["lobbyPassword"];
        lobbyDetails.appendChild(lobbyPassword);
        lobbyHeader.appendChild(lobbyDetails);
        wrapper.appendChild(lobbyHeader);

        const lobbyBody = document.createElement("div");
        lobbyBody.classList.add("wr_lobbyBody");
        wrapper.appendChild(lobbyBody);

        const playerList = document.createElement("div");
        lobbyBody.appendChild(playerList);
        playerList.classList.add("wr_bodyContent");
        const playerListTitle = document.createElement("h2");
        playerListTitle.innerHTML = '<i class="fa-solid fa-users"></i> Verbundene Spieler:';
        playerList.appendChild(playerListTitle);
        this.#playerHolder = document.createElement("div");
        playerList.appendChild(this.#playerHolder);

        const settings = document.createElement("div");
        lobbyBody.appendChild(settings);
        settings.classList.add("wr_bodyContent");
        const settingsTitle = document.createElement("h2");
        settingsTitle.innerHTML = '<i class="fa-solid fa-cogs"></i> Einstellungen:';
        settings.appendChild(settingsTitle);
        const startButton = document.createElement("button");
        startButton.innerHTML = '<i class="fa-solid fa-play"></i> Starten';
        startButton.classList.add("createLobbyButton");
        startButton.onclick = () => {this.#startGame()};
        settings.appendChild(startButton);
    }

    #startGame() {
        this.#wsClient.sendAsJson({
            "type": "lobbyStart",
        });
    }
}