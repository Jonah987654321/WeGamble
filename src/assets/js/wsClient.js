const ICON_CONNECTION = '<i class="fa-solid fa-wifi fa-beat-fade fa-3x" style="margin-bottom: 30px"></i>';
const ICON_ERROR = '<i class="fa-solid fa-triangle-exclamation fa-3x" style="margin-bottom: 30px"></i>';

const STATUS_INIT = 0;
const STATUS_TRYING = 1
const STATUS_OPENED = 2;
const STATUS_CHECKED_IN = 3;
const STATUS_CLOSED = 4;
const STATUS_ERROR = 5;

class WsClient {
    #connection;
    #connectionStatus;

    #gameID;
    #apiKey;
    #wsURL;

    #registeredErrorCodes;
    #registeredSuccessEvents;
    #gsRestoreHandler;
    #afterReconnect;
    #afterCheckin;

    #reconnectAttempts;
    #reconnectUiInterval;
    #connReconnected;

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

        this.#connectionStatus = STATUS_INIT;
        this.#reconnectAttempts = 0;
        this.#reconnectUiInterval = null;
        this.#connReconnected = false;

        this.#gsRestoreHandler = null;
        this.#afterReconnect = null;
    }

    getStatus() {
        return this.#connectionStatus;
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

    setAfterCheckIn(callback) {
        if (typeof callback !== "function") {
            throw new TypeError("GS Restore Handler must be a function");
        }

        this.#afterCheckin = callback;
    }

    setAfterReconnect(callback) {
        if (typeof callback !== "function") {
            throw new TypeError("AfterReconnect Handler must be a function");
        }

        this.#afterReconnect = callback;
    }

    startConnection() {
        if (this.#connection && this.#connection.readyState === WebSocket.CONNECTING) {
            return; // Already trying to connect
        }

        // Clear reconnect countdown if existing
        if (this.#reconnectUiInterval !== null) {
            clearInterval(this.#reconnectUiInterval);
            this.#reconnectUiInterval = null;
        }

        // If there is an old connection, close it
        if (this.#connection) {
            this.#connection.close();
        }

        this.#renderUIBlock(ICON_CONNECTION, "Verbindung zum Server wird hergestellt...");
        this.#connection = new WebSocket(this.#wsURL);
        this.#connection.onopen = this.#runOnOpen.bind(this);
        this.#connection.onmessage = this.#runOnMessage.bind(this);
        this.#connection.onerror = this.#runOnError.bind(this);
        this.#connection.onclose = this.#runOnClose.bind(this);
        this.#connectionStatus = STATUS_TRYING;
    }

    sendAsJson(data) {
        if (!this.#connection || this.#connection.readyState !== WebSocket.OPEN) {
            throw new DOMException("WebSocket ist nicht verbunden.", "InvalidStateError");
        }

        this.#connection.send(JSON.stringify(data));
    }

    #runOnOpen() {
        if (this.#reconnectAttempts > 0) {
            this.#connReconnected = true;
            this.#reconnectAttempts = 0;
        }
        const checkInData = {"type": "check-in", "apiKey": this.#apiKey, "gameID": this.#gameID};
        this.sendAsJson(checkInData);
        this.#connectionStatus = STATUS_OPENED;
    }

    #runOnMessage(event) {
        const data = JSON.parse(event.data);

        if (data["type"] == "error") {
            switch (data["code"]) {
                case 6001:
                    console.error("Invalid JSON given to ws");
                    this.#displayErrorCode("WS6001");
                    return;
                case 6002:
                    console.error("Missed check-in");
                    this.#displayErrorCode("WS6002");
                    return;
                case 6003:
                    console.error("Invalid data provided for check-in");
                    this.#displayErrorCode("WS6003");
                    return;
                case 6004:
                    // API key invalid - lets log out the user
                    window.location.href="/logout";
                    return;
                case 6500:
                    console.error("Internal server error");
                    this.#displayErrorCode("WS6500");
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
                this.#connectionStatus = STATUS_CHECKED_IN;
                console.info("WS check-in successful");

                if (this.#connReconnected) {
                    if (this.#afterReconnect != null) {
                        this.#afterReconnect();
                    }
                    this.#connReconnected = false;
                }

                if (data["restored"]) {
                    this.#gsRestoreHandler(data);
                }

                this.#hideUIBlocker();
                if (this.#afterCheckin != null) {
                    this.#afterCheckin();
                } 
            } else if (data["event"] in this.#registeredSuccessEvents) {
                this.#registeredSuccessEvents[data["event"]](data);
            } else {
                console.warn("Unaccounted success event: ", data)
            }
        } else {
            console.warn("Unaccounted websocket type: ", data);
        }
    }

    #runOnError(error) {
        if (this.#reconnectAttempts == 0) {
            if (this.#connectionStatus == STATUS_TRYING) {
                this.#renderUIBlock(ICON_ERROR, "Die Verbindung zum Server konnte nicht hergestellt werden!", "Bitte versuche es später nochmal")
            } else {
                this.#renderUIBlock(ICON_ERROR, "Ein unbekannter Fehler ist aufgetreten!", "Bitte lade die Seite neu");
            }
            this.#connectionStatus = STATUS_ERROR;
        }
    }

    #runOnClose(event) {
        if (this.#connectionStatus != STATUS_ERROR) {
            // Reconnect logic
            this.#reconnectAttempts++;
            const delay = Math.min(1000 * Math.pow(2, this.#reconnectAttempts), 30000); // max 30s
            let secondsRemaining = delay / 1000;

            this.#renderUIBlock(ICON_ERROR, "Verbindung zum Server wurde unterbrochen!", `Neuer Verbindungsversuch in ${secondsRemaining}sek`);

            if (this.#reconnectUiInterval !== null) {
                clearInterval(this.#reconnectUiInterval);
            }
            this.#reconnectUiInterval = setInterval(() => {
                secondsRemaining--;
                if (secondsRemaining <= 0) {
                    clearInterval(this.#reconnectUiInterval);
                    this.#reconnectUiInterval = null;
                } else {
                    this.#renderUIBlock(ICON_ERROR, "Verbindung zum Server wurde unterbrochen!", `Neuer Verbindungsversuch in ${secondsRemaining}sek`);
                }
            }, 1000);

            setTimeout(this.startConnection.bind(this), delay);
        }
        this.#connectionStatus = STATUS_CLOSED;
    }

    #displayErrorCode(errorCode) {
        this.#renderUIBlock(ICON_ERROR, "Ein Fehler ist aufgetreten!", `Fehlercode: ${errorCode}`);
        this.#connectionStatus = STATUS_ERROR;
    }

    #renderUIBlock(icon, message, details = "") {
        if (document.getElementById("uiBlockWs") != null) {
            document.getElementById("uiBlockWs").innerHTML = `
                <div style="text-align: center">
                    ${icon}
                    <h2>${message}</h2>
                    <p style="margin-top: 30px; font-size: 120%;">${details}</p>
                </div>
            `;
        } else {
            const uiBlock = document.createElement("div");
            uiBlock.innerHTML = `
            <div class="uiBlock" id="uiBlockWs">
                <div style="text-align: center">
                    ${icon}
                    <h2>${message}</h2>
                    <p style="margin-top: 30px; font-size: 120%;">${details}</p>
                </div>
            </div>
            `;
            document.querySelector(".mainWrapper").appendChild(uiBlock);
        }
    }

    #hideUIBlocker() {
        document.getElementById("uiBlockWs").remove();
    }
    
}