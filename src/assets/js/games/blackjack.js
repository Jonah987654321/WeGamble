// Getting user balance
let balance = Number(document.getElementById("userBalanceStash").value);

// ===== Defining relevant variables for the websocket =====
const wsURL = document.getElementById("wsURLStash").value;
const apiKey = document.getElementById("apiTokenStash").value;
const gameID = 2;

const ws = new WsClient(wsURL, gameID, apiKey);


// ===== Registering error codes =====
ws.registerErrorCode(7, (data) => {notify("Bitte gib erst einen Wetteinsatz ein");});
ws.registerErrorCode(8, (data) => {notify("Wetteinsatz zu hoch");});


// ===== Registering game state restoring =====
ws.setGameStateRestoreHandler((data) => {
    // Hide the bet amount input & show the main playtable, set the bet amount from the restored gs
    document.getElementById("startGameContainer").classList.add("hidden");
    document.getElementById("playtable").classList.remove("hidden");
    document.getElementById("betAmountDisplay").innerHTML = formatCurrency(data["restoredData"]["betAmount"])+"€";

    // Restore player & dealer cards
    data["restoredData"]["userCards"].forEach(e => {
        document.getElementById("userCards").innerHTML += `<img src="${document.getElementById("serverURLStash").value}/assets/img/cards/${e}.png">`;
    });
    data["restoredData"]["dealerCards"].forEach(e => {
        document.getElementById("dealerCards").innerHTML += `<img src="${document.getElementById("serverURLStash").value}/assets/img/cards/${e}.png">`;
    });

    // The last dealer card is still hidden, so display the back of the card
    if (!data["restoredData"]["dealerCardShown"]) {
        document.getElementById("dealerCards").innerHTML += `<img src="${document.getElementById("serverURLStash").value}/assets/img/cards/blue_back.png" id="dealerHoleCard">`;
    }

    // Only enable the buttons that are possible to be pushed next
    setNextButtons(data["restoredData"]["possibleNext"]);

    // Update the balance, because we need to add the bet amount again (because we remove it on disconnect)
    document.getElementById("balanceDisplay").innerHTML = formatCurrency(data["restoredData"]["balance"])+"€";
});

// ===== Registering game events =====
ws.registerSuccessEvent("initGame", (data ) => {
    // Hide the bet amount input & show the main playtable
    document.getElementById("startGameContainer").classList.add("hidden");
    document.getElementById("playtable").classList.remove("hidden");

    // Show the new cards
    animateCards(data["gameUpdates"]["newCards"]);
    if (data["gameData"]["gameRunning"]) {
        // The last dealer card is still hidden, so display the back of the card
        animateCards([{"type": 2, "card": "blue_back"}]);

        // Only enable the buttons that are possible to be pushed next
        setNextButtons(data["gameUpdates"]["possibleNext"]);
    } else {
        endGame(data["gameUpdates"]["displayText"], data["gameUpdates"]["userBalance"]);
    }
});
ws.registerSuccessEvent("surrender", (data) => {endGame(data["gameUpdates"]["displayText"], data["gameUpdates"]["userBalance"]);});
ws.registerSuccessEvent("hit", (data) => {
    // Display new cards
    animateCards(data["gameUpdates"]["newCards"]);

    if (data["gameData"]["gameRunning"]) {
        // Only enable the buttons that are possible to be pushed next
        setNextButtons(data["gameUpdates"]["possibleNext"]);
    } else {
        endGame(data["gameUpdates"]["displayText"], data["gameUpdates"]["userBalance"]);
    }
});
ws.registerSuccessEvent("stand", (data) => {
    document.getElementById("dealerHoleCard").remove();
    animateCards(data["gameUpdates"]["newCards"]);
    endGame(data["gameUpdates"]["displayText"], data["gameUpdates"]["userBalance"]);
});


// Starting ws connection
ws.startConnection();


// ===== Button onClick Functions =====
function startGame() {
    let bidAmount = Number(document.getElementById("betAmountInp").value);
    
    if (bidAmount == 0) {
        return notify("Bitte gib erst einen Wetteinsatz ein");
    }

    if (bidAmount > balance) {
        return notify("Wetteinsatz zu hoch");
    }

    document.getElementById("betAmountDisplay").innerHTML = formatCurrency(bidAmount)+"€";
    ws.sendAsJson({"type": "initGame", "betAmount": bidAmount});
}

function surrender() {
    ws.sendAsJson({"type": "surrender"});
}

function stand() {
    ws.sendAsJson({"type": "stand"});
}

function hit() {
    ws.sendAsJson({"type": "hit"});
}

function doubleDown() {
    ws.sendAsJson({"type": "doubleDown"});
}


// ===== Helper Functions for manipulating HTML =====
function animateCards(cards) {
    cards.forEach(c => {
        const cardElement = `<img src="${document.getElementById("serverURLStash").value}/assets/img/cards/${c["card"]}.png" ${(c["card"]=="blue_back")?'id="dealerHoleCard"':''}>`;
        const wrapper = (c["type"]==1)?document.getElementById("userCards"):document.getElementById("dealerCards");
        wrapper.innerHTML += cardElement;
    });
}

function showEnd(message) {
    document.getElementById("endMessage").innerHTML = message;
    document.getElementById("endMessage").classList.remove("hidden");
}

function endGame(message, newBalance) {
    showEnd(message);

    let totalBalanceEl = document.getElementById("balanceDisplay");
    let startAnimBalance = parseInt(totalBalanceEl.innerHTML.replace(".", ""), 10);
    (newBalance > startAnimBalance)?animateCountUp(totalBalanceEl, 2000, startAnimBalance, newBalance):animateCountDown(totalBalanceEl, 2000, startAnimBalance, newBalance);
    balance = newBalance;

    document.getElementById("inGameBtns").classList.add("hidden");
    document.getElementById("postGameBtns").classList.remove("hidden");
}

function setNextButtons(possibleNext) {
    document.querySelectorAll(".uaBtn").forEach(element => {
        element.disabled = true;
    });
    possibleNext.forEach(element => {
        document.getElementById("uaBtn-"+element).disabled = false;
    });
}

function resetBoard() {
    document.getElementById("playtable").classList.add("hidden");
    document.getElementById("betAmountInp").value = 0;
    document.getElementById("startGameContainer").classList.remove("hidden");

    document.getElementById("dealerCards").innerHTML = "";
    document.getElementById("userCards").innerHTML = "";
    document.getElementById("betAmountDisplay").innerHTML = "";
    document.getElementById("endMessage").innerHTML = "";
    document.getElementById("endMessage").classList.add("hidden");
    
    document.getElementById("inGameBtns").classList.remove("hidden");
    document.getElementById("postGameBtns").classList.add("hidden");
}
