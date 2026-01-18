class LobbyManager {
    #wsClient;
    #selector;
    #chat;

    constructor(wsClient) {
        this.#wsClient = wsClient;
        this.#wsClient.setGameStateNoRestoreHandler((data) => {
            // user didn't disconnect from a lobby last time -> start lobby selection
            this.#selector.start();
        });
        this.#wsClient.setGameStateRestoreHandler((data) => {
            this.#showMainWrapper(false)
        });

        this.#selector = new LobbySelector(wsClient);
        this.#selector.setLobbyJoinHandler((lobbyData, selfCreated) => {
            this.#handleLobbySelectorFinished(lobbyData, selfCreated);
        });

        this.#chat = new Chat(wsClient);

        this.#createUIContainers();
    }

    #showMainWrapper(lobbyStarted) {
        // Show main lobby components
        document.getElementById("lobbyMainWrapper").classList.remove("hidden");
        if (lobbyStarted) {
            document.getElementById("lobbyGameTableWrapper").classList.remove("hidden");
        } else {
            document.getElementById("lobbyWaitingWrapper").classList.remove("hidden");
        }
        this.#chat.start();
    }

    #handleLobbySelectorFinished(lobbyData, selfCreated) {
        this.#showMainWrapper(false);
    }

    #createUIContainers() {
        const lobbyContainer = document.getElementById("lobbyContainer");

        const lobbySelectorWrapper = document.createElement("div");
        lobbySelectorWrapper.id = "lobbySelectorWrapper";
        lobbyContainer.appendChild(lobbySelectorWrapper);

        const lobbyMainWrapper = document.createElement("div");
        lobbyMainWrapper.id = "lobbyMainWrapper";
        lobbyMainWrapper.classList.add("hidden");

        const lobbyWaitingWrapper = document.createElement("div");
        lobbyWaitingWrapper.id = "lobbyWaitingWrapper";
        lobbyWaitingWrapper.classList.add("lobbyMainSpace", "hidden");
        lobbyMainWrapper.appendChild(lobbyWaitingWrapper);
        const lobbyGameTableWrapper = document.createElement("div");
        lobbyGameTableWrapper.id = "lobbyGameTableWrapper";
        lobbyGameTableWrapper.classList.add("lobbyMainSpace", "hidden");
        lobbyMainWrapper.appendChild(lobbyGameTableWrapper);
        const lobbyChatWrapper = document.createElement("div");
        lobbyChatWrapper.id = "lobbyChatWrapper";
        lobbyMainWrapper.appendChild(lobbyChatWrapper);

        lobbyContainer.appendChild(lobbyMainWrapper);
    }
}