var conn = new WebSocket(document.getElementById("wsURLStash").value);

var balance = Number(document.getElementById("userBalanceStash").value);

conn.onopen = function(e) {
    conn.send(JSON.stringify({"type": "check-in", "apiKey": document.getElementById("apiTokenStash").value, "gameID": 2}));
};

function startGame() {
    let bidAmount = Number(document.getElementById("betAmountInp").value);
    
    if (bidAmount == 0) {
        return notify("Bitte gib erst einen Wetteinsatz ein");
    }

    if (bidAmount > balance) {
        return notify("Wetteinsatz zu hoch");
    }

    document.getElementById("betAmountDisplay").innerHTML = formatCurrency(bidAmount)+"€";
    conn.send(JSON.stringify({"type": "initGame", "betAmount": bidAmount}));
}

function animateCards(cards) {
    cards.forEach(c => {
        cardElement = `<img src="${document.getElementById("serverURLStash").value}/assets/img/cards/${c["card"]}.png" ${(c["card"]=="blue_back")?'id="dealerHoleCard"':''}>`;
        wrapper = (c["type"]==1)?document.getElementById("userCards"):document.getElementById("dealerCards");
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

    document.getElementById("inGameBtns").classList.add("hidden");
    document.getElementById("postGameBtns").classList.remove("hidden");
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

function surrender() {
    conn.send(JSON.stringify({"type": "surrender"}));
}

function stand() {
    conn.send(JSON.stringify({"type": "stand"}));
}

function hit() {
    conn.send(JSON.stringify({"type": "hit"}));
}

function doubleDown() {
    conn.send(JSON.stringify({"type": "doubleDown"}));
}

conn.onmessage = function(e) {
    data = JSON.parse(e.data);
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
                window.location.href="/logout";
                return;
            case 7:
                return notify("Bitte gib erst einen Wetteinsatz ein");
            case 8:
                return notify("Wetteinsatz zu hoch");
            default:
                console.error("Unknown error occurred: "+data);
                return;
        }
    } else if (data["type"] == "success") {
        console.log(data)
        if (data["event"] == "check-in") {
            console.info("WS connection established");

            if (data["restored"]) {
                document.getElementById("startGameContainer").classList.add("hidden");
                document.getElementById("playtable").classList.remove("hidden");
                document.getElementById("betAmountDisplay").innerHTML = formatCurrency(data["restoredData"]["betAmount"])+"€";

                data["restoredData"]["userCards"].forEach(e => {
                    document.getElementById("userCards").innerHTML += `<img src="${document.getElementById("serverURLStash").value}/assets/img/cards/${e}.png">`
                });

                data["restoredData"]["dealerCards"].forEach(e => {
                    document.getElementById("dealerCards").innerHTML += `<img src="${document.getElementById("serverURLStash").value}/assets/img/cards/${e}.png">`
                });

                if (!data["restoredData"]["dealerCardShown"]) {
                    document.getElementById("dealerCards").innerHTML += `<img src="${document.getElementById("serverURLStash").value}/assets/img/cards/blue_back.png" id="dealerHoleCard">`;
                }

                document.querySelectorAll(".uaBtn").forEach(element => {
                    element.disabled = true;
                });
                data["restoredData"]["possibleNext"].forEach(element => {
                    document.getElementById("uaBtn-"+element).disabled = false;
                });

                document.getElementById("balanceDisplay").innerHTML = formatCurrency(data["restoredData"]["balance"])+"€"
            }
        }

        if (data["event"] == "initGame") {
            document.getElementById("startGameContainer").classList.add("hidden");
            document.getElementById("playtable").classList.remove("hidden");
            animateCards(data["gameUpdates"]["newCards"]);
            if (data["gameData"]["gameRunning"]) {
                animateCards([{"type": 2, "card": "blue_back"}]);

                document.querySelectorAll(".uaBtn").forEach(element => {
                    element.disabled = true;
                });
                data["gameUpdates"]["possibleNext"].forEach(element => {
                    document.getElementById("uaBtn-"+element).disabled = false;
                });
            } else {
                endGame(data["gameUpdates"]["displayText"], data["gameUpdates"]["userBalance"]);
            }
        }

        if (data["event"] == "surrender") {
            endGame(data["gameUpdates"]["displayText"], data["gameUpdates"]["userBalance"]);
        }

        if (data["event"] == "hit") {
            animateCards(data["gameUpdates"]["newCards"]);
            if (data["gameData"]["gameRunning"]) {
                document.querySelectorAll(".uaBtn").forEach(element => {
                    element.disabled = true;
                });
                data["gameUpdates"]["possibleNext"].forEach(element => {
                    document.getElementById("uaBtn-"+element).disabled = false;
                });
            } else {
                endGame(data["gameUpdates"]["displayText"], data["gameUpdates"]["userBalance"]);
            }
        }

        if (data["event"] == "stand") {
            document.getElementById("dealerHoleCard").remove();
            animateCards(data["gameUpdates"]["newCards"]);
            endGame(data["gameUpdates"]["displayText"], data["gameUpdates"]["userBalance"]);
        }
    } else {
        console.log(data);
    }
};