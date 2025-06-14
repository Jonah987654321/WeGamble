// Defining relevant variables for the websocket
const wsURL = document.getElementById("wsURLStash").value;
const apiKey = document.getElementById("apiTokenStash").value;
const gameID = 3;

const ws = new WsClient(wsURL, gameID, apiKey);

// Registering error codes
ws.registerErrorCode(11, (data) => {notify("Bitte gib erst einen Wetteinsatz ein");});
ws.registerErrorCode(12, (data) => {notify("Wetteinsatz zu hoch");});
ws.registerErrorCode(13, (data) => {notify("Auswahl fehlt");});

// Registering game events
ws.registerSuccessEvent("hitTheNick-runGame", (data) => {
    let newBalance = data["newBalance"];
    balance = newBalance;
    let totalBalanceEl = document.getElementById("balanceDisplay");
    let startAnimBalance = parseInt(totalBalanceEl.innerHTML.replace(".", ""), 10);
    (newBalance > startAnimBalance)?animateCountUp(totalBalanceEl, 2000, startAnimBalance, newBalance):animateCountDown(totalBalanceEl, 2000, startAnimBalance, newBalance);

    let newMsg = (data["result"]=="win")?"Du hast "+formatCurrency(bidAmount*8)+"€ gewonnen":"Du hast "+formatCurrency(bidAmount)+"€ verloren";
    document.getElementById("msgTop").innerHTML = `<div speech-bubble pbottom aright>
                                            <p>${newMsg}</p>
                                        </div>`;
    document.getElementById("d"+data["out"]).innerHTML += `<img src="../../assets/img/hitTheNick/student.png" id="nick">`;
    document.getElementById("imgDLeft").classList.remove("notActive");
    document.getElementById("deskBlocker").classList.remove("hidden");
});

// Starting ws connection
ws.startConnection();

// Game relevant variables
let balance = Number(document.getElementById("userBalanceStash").value);
let bidAmount = 0;

// Function to reset the page for a new game
function resetGame() {
    document.getElementById("nick").remove();
    document.getElementById("imgDLeft").classList.add("notActive");
    document.getElementById("deskBlocker").classList.add("hidden");
    document.querySelectorAll(".deskWrapper").forEach(e => {
        e.classList.remove("active");
    });
    document.getElementById("msgTop").innerHTML = `
    <div speech-bubble pbottom aright>
        <p>Gib links einen Wetteinsatz ein, um das Spiel zu starten</p>
    </div>
    `;
    document.getElementById("startGameContainer").classList.remove("hidden");
    document.getElementById("playtable").classList.add("hidden");
}

// Function to initiate a new game with the same balance as the last
function redoGame() {
    if (balance < bidAmount) {
        return notify("Nicht mehr genug Geld");
    }

    document.getElementById("nick").remove();
    document.getElementById("imgDLeft").classList.add("notActive");
    document.getElementById("deskBlocker").classList.add("hidden");
    document.querySelectorAll(".deskWrapper").forEach(e => {
        e.classList.remove("active");
    });
    document.getElementById("msgTop").innerHTML = `
                            <div speech-bubble pbottom aright>
                                <p id="betAmount">Du hast ${formatCurrency(bidAmount)}€ gesetzt!</p>
                                <p>Wähle nun den Tisch aus, wo du denkst, dass Nick sitzt!</p>
                            </div>`;
}

// Start a new game with the provided bet amount
function startGame() {
    bidAmount = Number(document.getElementById("betAmountInp").value);
    
    if (bidAmount == 0) {
        return notify("Bitte gib erst einen Wetteinsatz ein");
    }

    if (bidAmount > balance) {
        return notify("Wetteinsatz zu hoch");
    }

    document.getElementById("deskBlocker").classList.add("hidden");
    document.getElementById("startGameContainer").classList.add("hidden");
    document.getElementById("playtable").classList.remove("hidden");

    document.getElementById("msgTop").innerHTML = `
                            <div speech-bubble pbottom aright>
                                <p id="betAmount">Du hast ${formatCurrency(bidAmount)}€ gesetzt!</p>
                                <p>Wähle nun den Tisch aus, wo du denkst, dass Nick sitzt!</p>
                            </div>`;
}

// Function to send a guess to the websocket
function guess(sel) {
    ws.sendAsJson({"event": "hitTheNick-runGame", "betAmount": bidAmount, "selection": sel});

    document.getElementById("d"+sel).classList.toggle("active");
}
