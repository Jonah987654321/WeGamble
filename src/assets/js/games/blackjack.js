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
ws.setAfterReconnect(() => {
    document.getElementById("userCards").innerHTML = "";
    document.getElementById("dealerCards").innerHTML = "";
});
ws.setGameStateRestoreHandler((data) => {
    // Hide the bet amount input & show the main playtable, set the bet amount from the restored gs
    document.getElementById("startGameContainer").classList.add("hidden");
    document.getElementById("playtable").classList.remove("hidden");
    document.getElementById("betAmountDisplay").innerHTML = formatCurrency(data["restoredData"]["betAmount"])+"€";

    // Restore player & dealer cards
    data["restoredData"]["userCards"].forEach(e => {
        const cardElement = `
        <div class="card flipped">
          <div class="card-inner">
            <div class="card-front">
              <img src="${document.getElementById("serverURLStash").value}/assets/img/cards/blue_back.png" alt="Rückseite">
            </div>
            <div class="card-back">
              <img src="${document.getElementById("serverURLStash").value}/assets/img/cards/${e}.png" alt="Vorderseite">
            </div>
          </div>
        </div>
        `;
        document.getElementById("userCards").innerHTML += cardElement;
    });
    data["restoredData"]["dealerCards"].forEach(e => {
       const cardElement = `
        <div class="card flipped">
          <div class="card-inner">
            <div class="card-front">
              <img src="${document.getElementById("serverURLStash").value}/assets/img/cards/blue_back.png" alt="Rückseite">
            </div>
            <div class="card-back">
              <img src="${document.getElementById("serverURLStash").value}/assets/img/cards/${e}.png" alt="Vorderseite">
            </div>
          </div>
        </div>
        `;
        document.getElementById("dealerCards").innerHTML += cardElement;
    });

    // The last dealer card is still hidden, so display the back of the card
    if (!data["restoredData"]["dealerCardShown"]) {
        const cardElement = `
        <div class="card" id="dealerHiddenCardWrapper">
          <div class="card-inner">
            <div class="card-front">
              <img src="${document.getElementById("serverURLStash").value}/assets/img/cards/blue_back.png" alt="Rückseite">
            </div>
            <div class="card-back">
              <img src="${document.getElementById("serverURLStash").value}/assets/img/cards/blue_back.png" id="dealerHoleCard" alt="Vorderseite">
            </div>
          </div>
        </div>
        `;
        document.getElementById("dealerCards").innerHTML += cardElement;
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
    const animation = new CardAnimation();
    animation.addToQueue(data["gameUpdates"]["newCards"]);
    if (data["gameData"]["gameRunning"]) {
        // The last dealer card is still hidden, so display the back of the card
        animation.addToQueue([{"type": 2, "card": "blue_back"}]);

        animation.setAfter(() => {
            // Only enable the buttons that are possible to be pushed next
            setNextButtons(data["gameUpdates"]["possibleNext"]);
        });
    } else {
        animation.setAfter(() => {
            endGame(data["gameUpdates"]["displayText"], data["gameUpdates"]["userBalance"]);
        });
    }
    animation.run();
});
ws.registerSuccessEvent("surrender", (data) => {endGame(data["gameUpdates"]["displayText"], data["gameUpdates"]["userBalance"]);});
ws.registerSuccessEvent("hit", (data) => {
    // Display new cards
    const animation = new CardAnimation();
    animation.addToQueue(data["gameUpdates"]["newCards"]);

    if (data["gameData"]["gameRunning"]) {
        animation.setAfter(() => {
            // Only enable the buttons that are possible to be pushed next
            setNextButtons(data["gameUpdates"]["possibleNext"]);
        });
    } else {
        animation.setAfter(() => {
            endGame(data["gameUpdates"]["displayText"], data["gameUpdates"]["userBalance"]);
        });
    }
    animation.run();
});
ws.registerSuccessEvent("stand", (data) => {
    const animation = new CardAnimation();
    animation.addToQueue(data["gameUpdates"]["newCards"]);

    animation.setAfter(() => {
        endGame(data["gameUpdates"]["displayText"], data["gameUpdates"]["userBalance"]);
    });

    animation.run();
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
class CardAnimation {
    #cards;
    #afterAnimation;

    constructor() {
        this.#cards = [];
        this.#afterAnimation = null;
    }

    addToQueue(newCards) {
        this.#cards.push(...newCards);
    }

    setAfter(callback) {
        this.#afterAnimation = callback;
    }

    run() {
        if (this.#cards.length == 0) {
            if (this.#afterAnimation != null) {
                this.#afterAnimation();
            }
            return;
        }

        const c = this.#cards.shift();
        
        console.log(document.getElementById("dealerHiddenCardWrapper"));
        if (c.type == 2 && document.getElementById("dealerHiddenCardWrapper") != null) {
            document.getElementById("dealerHoleCard").src = `${document.getElementById("serverURLStash").value}/assets/img/cards/${c.card}.png`;
            document.getElementById("dealerHiddenCardWrapper").classList.add("flipped");
            document.getElementById("dealerHiddenCardWrapper").id = guidGenerator();
            setTimeout(() => {this.run()}, 500);
            return;
        }

        const cardId = (c.card =="blue_back")?'dealerHiddenCardWrapper':guidGenerator();
        const cardElement = `
        <div class="card" id="${cardId}">
          <div class="card-inner">
            <div class="card-front">
              <img src="${document.getElementById("serverURLStash").value}/assets/img/cards/blue_back.png" alt="Rückseite">
            </div>
            <div class="card-back">
              <img src="${document.getElementById("serverURLStash").value}/assets/img/cards/${c.card}.png" ${(c.card == "blue_back")?'id="dealerHoleCard"':''} alt="Vorderseite">
            </div>
          </div>
        </div>
        `;
        const wrapper = (c.type==1)?document.getElementById("userCards"):document.getElementById("dealerCards");
        wrapper.innerHTML += cardElement;

        setTimeout(() => {
            if (c.card != "blue_back") {
                document.getElementById(cardId).classList.add("flipped");
            }
            setTimeout(() => {this.run()}, 500);
        }, 400);
    }
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
