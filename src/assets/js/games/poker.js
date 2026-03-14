// Getting user balance
let balance = Number(document.getElementById("userBalanceStash").value);

// ===== Defining relevant variables for the websocket =====
const wsURL = document.getElementById("wsURLStash").value;
const apiKey = document.getElementById("apiTokenStash").value;
const userID = document.getElementById("userIDStash").value;
const gameID = 5;

const cardImgBaseURL = document.getElementById("serverURLStash").value + "/assets/img/cards/";

const ws = new WsClient(wsURL, gameID, apiKey);
const lobbyManager = new LobbyManager(ws);

lobbyManager.setGamestartHandler((data) => {
    const mainWrapper = document.getElementById("lobbyGameTableWrapper");

    const pokerTableContainer = document.createElement("div");
    mainWrapper.appendChild(pokerTableContainer);
    pokerTableContainer.classList.add("poker-table-container");

    const pokerTable = document.createElement("div");
    pokerTableContainer.appendChild(pokerTable);
    pokerTable.classList.add("poker-table");
    pokerTable.innerHTML = `<div class="table-center">
                            <div class="pot-area">
                                <h3>Pot</h3>
                                <div class="pot-amount">0€</div>
                            </div>
                            <div class="community-cards" id="communityCards">
                                <div class="card-placeholder"></div>
                                <div class="card-placeholder"></div>
                                <div class="card-placeholder"></div>
                                <div class="card-placeholder"></div>
                                <div class="card-placeholder"></div>
                            </div>
                        </div>
                        <div id="playerSeats"></div>`;
    
    const lobbyData = data["fullLobbyData"];
    const players = lobbyData["players"];

    const degIncrement = 360/players.length;
    let nextDegree = 0;

    let ownIndex = -1;
    for (let i = 0; i < players.length; i++) {
        const curr = players[i];
        if (ownIndex == -1) {
            // Still looking for ourselves to begin with
            if (curr["userID"] == userID) {
                ownIndex = i;

                createPlayerSeat(curr, nextDegree, lobbyData["gameSpecificData"]["userBalances"][curr["userID"]]);
                nextDegree += degIncrement;
            }
        } else {
            // Already found ourselves as entry
            createPlayerSeat(curr, nextDegree, lobbyData["gameSpecificData"]["userBalances"][curr["userID"]]);
            nextDegree += degIncrement;
        }
    }
    
    // Create the players who we skipped before
    for (let i = 0; i < ownIndex; i++) {
        const curr = players[i];
        createPlayerSeat(curr, nextDegree, lobbyData["gameSpecificData"]["userBalances"][curr["userID"]]);
        nextDegree += degIncrement;
    }
});

ws.registerBroadcastEvent("poker_newRound", (data) => {
    data = data["data"];
    Object.keys(data["activePlayers"]).forEach(e => {
        if (e != userID) {
            setCard(e, 1, "blue_back");
            setCard(e, 2, "blue_back");
        }
    });

    setCard(userID, 1, data["playerCards"][0]);
    setCard(userID, 2, data["playerCards"][1]);
});

// Starting ws connection
ws.startConnection();

// Helper functions:
function createPlayerSeat(userObject, degree, balance) {
    const userID = userObject["userID"];
    document.getElementById("playerSeats").innerHTML += `
        <div class="playerSeat deg${degree}" id="playerSeat-player${userID}">
            <div class="playerSeat-name">${userObject["userName"]}</div>
            <div class="playerSeat-balance"><span id="playerSeat-player${userID}-balance">${formatCurrency(balance)}</span>€</div>
            <div class="playerSeat-cardArea">
                <div class="cardholder empty" id="cardholder-${userID}-1">
                    <img id="cardholder-${userID}-1-img">
                </div>
                <div class="cardholder" id="cardholder-${userID}-2">
                    <img id="cardholder-${userID}-2-img">
                </div>
            </div>
        </div>
    `;
}

function setCard(userID, cardNumber, cardName) {
    document.getElementById("cardholder-"+userID+"-"+cardNumber).classList.remove("empty");
    document.getElementById("cardholder-"+userID+"-"+cardNumber+"-img").src = cardImgBaseURL + cardName + ".png";
}
