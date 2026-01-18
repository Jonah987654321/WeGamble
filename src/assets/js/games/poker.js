// Getting user balance
let balance = Number(document.getElementById("userBalanceStash").value);

// ===== Defining relevant variables for the websocket =====
const wsURL = document.getElementById("wsURLStash").value;
const apiKey = document.getElementById("apiTokenStash").value;
const gameID = 5;

const ws = new WsClient(wsURL, gameID, apiKey);
const lobbyManager = new LobbyManager(ws);

// Starting ws connection
ws.startConnection();