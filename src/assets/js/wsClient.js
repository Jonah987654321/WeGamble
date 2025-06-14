class WsClient {
    #connection;

    #gameID;
    #apiKey;
    #wsURL;

    #registeredErrorCodes;
    #registeredSuccessEvents;
    #gsRestoreHandler;

    constructor(wsURL, gameID, apiKey) {
        // Verifying & setting ws url
        if (typeof wsURL !== "string") {
            throw new TypeError("WS URL must be a string");
        }
        this.#wsURL = wsURL;

        // Verifying & setting gameID
        if (typeof gameID !== "number") {
            throw new TypeError("GameID must be a number");
        }
        this.#gameID = gameID;

        // Verifying & setting api key
        if (typeof apiKey !== "string") {
            throw new TypeError("API Key must be a string");
        }
        this.#apiKey = apiKey;

        this.#registeredErrorCodes = {};
        this.#registeredSuccessEvents = {};
    }

    registerErrorCode(code, callback) {
        if (typeof code !== "number") {
            throw new TypeError("Error code must be a number");
        }

        if (typeof callback !== "function") {
            throw new TypeError("Error callback must be a function");
        }

        // All arguments correct, we can register the function linked to the error code
        this.#registeredErrorCodes[code] = callback;
    }

    registerSuccessEvent(eventType, callback) {
        if (typeof eventType !== "string") {
            throw new TypeError("Event type must be a string");
        }

        if (typeof callback !== "function") {
            throw new TypeError("Event callback must be a function");
        }

        // All arguments correct, we can register the function linked to the error code
        this.#registeredSuccessEvents[eventType] = callback;
    }

    setGameStateRestoreHandler(callback) {
        if (typeof callback !== "function") {
            throw new TypeError("GS Restore Handler must be a function");
        }

        this.#gsRestoreHandler = callback;
    }

    startConnection() {
        this.#connection = new WebSocket(this.#wsURL);
        this.#connection.onopen = this.#runOnOpen.bind(this);
        this.#connection.onmessage = this.#runOnMessage.bind(this);
    }

    sendAsJson(data) {
        if (!this.#connection || this.#connection.readyState !== WebSocket.OPEN) {
            throw new DOMException("WebSocket ist nicht verbunden.", "InvalidStateError");
        }

        this.#connection.send(JSON.stringify(data));
    }

    #runOnOpen() {
        const checkInData = {"type": "check-in", "apiKey": this.#apiKey, "gameID": this.#gameID};
        this.sendAsJson(checkInData);
    }

    #runOnMessage(event) {
        const data = JSON.parse(event.data);

        if (data["type"] == "error") {
            switch (data["code"]) {
                case 1:
                    console.error("Invalid JSON given to ws");
                    return;
                case 2:
                    console.error("Missed check-in");
                    return;
                case 3:
                    console.error("Invalid data provided for check-in")
                    return;
                case 6:
                    // API key invalid - lets log out the user
                    window.location.href="/logout";
                    return;
                default:
                    if (data["code"] in this.#registeredErrorCodes) {
                        // WS Client instance has registered behavior for this code
                        this.#registeredErrorCodes[data["code"]](data);
                    } else {
                        console.error("Unknown error occurred: ", data);
                    }
                    return;
            }
        } else if (data["type"] == "success") {
            if (data["event"] == "check-in") {
                console.info("WS check-in successful");

                if (data["restored"]) {
                    this.#gsRestoreHandler(data);
                }
            } else if (data["event"] in this.#registeredSuccessEvents) {
                this.#registeredSuccessEvents[data["event"]](data);
            } else {
                console.warn("Unaccounted event: ", data)
            }
        } else {
            console.debug(data);
        }
    }
}