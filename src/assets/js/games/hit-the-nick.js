var conn = new WebSocket(document.getElementById("wsURLStash").value);

var balance = Number(document.getElementById("userBalanceStash").value);
var bidAmount = 0

conn.onopen = function(e) {
    conn.send(JSON.stringify({"type": "check-in", "apiKey": document.getElementById("apiTokenStash").value, "gameID": 3}));
};

function resetGame() {
    document.getElementById("nick").remove();
    document.getElementById("imgDLeft").classList.add("notActive")
    document.getElementById("blocker").classList.add("hidden")
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

function redoGame() {
    if (balance < bidAmount) {
        return notify("Nicht mehr genug Geld");
    }

    document.getElementById("nick").remove();
    document.getElementById("imgDLeft").classList.add("notActive")
    document.getElementById("blocker").classList.add("hidden")
    document.querySelectorAll(".deskWrapper").forEach(e => {
        e.classList.remove("active");
    });
    document.getElementById("msgTop").innerHTML = `
                            <div speech-bubble pbottom aright>
                                <p id="betAmount">Du hast ${formatCurrency(bidAmount)}€ gesetzt!</p>
                                <p>Wähle nun den Tisch aus, wo du denkst, dass Nick sitzt!</p>
                            </div>`;
}

function startGame() {
    bidAmount = Number(document.getElementById("betAmountInp").value);
    
    if (bidAmount == 0) {
        return notify("Bitte gib erst einen Wetteinsatz ein");
    }

    if (bidAmount > balance) {
        return notify("Wetteinsatz zu hoch");
    }

    document.getElementById("startGameContainer").classList.add("hidden");
    document.getElementById("playtable").classList.remove("hidden");

    document.getElementById("msgTop").innerHTML = `
                            <div speech-bubble pbottom aright>
                                <p id="betAmount">Du hast ${formatCurrency(bidAmount)}€ gesetzt!</p>
                                <p>Wähle nun den Tisch aus, wo du denkst, dass Nick sitzt!</p>
                            </div>`;
}

function guess(sel) {
    conn.send(JSON.stringify({"event": "hitTheNick-runGame", "betAmount": bidAmount, "selection": sel}));

    document.getElementById("d"+sel).classList.toggle("active")
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
            case 11:
                return notify("Bitte gib erst einen Wetteinsatz ein");
            case 12:
                return notify("Wetteinsatz zu hoch");
            case 13:
                return notify("Auswahl fehlt");
            default:
                console.error("Unknown error occurred: "+data);
                return;
        }
    } else if (data["type"] == "success") {
        console.log(data)
        if (data["event"] == "check-in") {
            console.info("WS connection established");
        } else if (data["event"] == "hitTheNick-runGame") {
            let newBalance = data["newBalance"];
            balance = newBalance;
            let totalBalanceEl = document.getElementById("balanceDisplay");
            let startAnimBalance = parseInt(totalBalanceEl.innerHTML.replace(".", ""), 10);
            (newBalance > startAnimBalance)?animateCountUp(totalBalanceEl, 2000, startAnimBalance, newBalance):animateCountDown(totalBalanceEl, 2000, startAnimBalance, newBalance);

            let newMsg = (data["result"]=="win")?"Du hast "+formatCurrency(bidAmount*8)+"€ gewonnen":"Du hast "+formatCurrency(bidAmount)+"€ verloren";
            document.getElementById("msgTop").innerHTML = `<div speech-bubble pbottom aright>
                                                    <p>${newMsg}</p>
                                                </div>`;
            document.getElementById("d"+data["out"]).innerHTML += `<img src="../../assets/img/student.png" id="nick">`;
            document.getElementById("imgDLeft").classList.remove("notActive")
            document.getElementById("blocker").classList.remove("hidden")
        }
    } else {
        console.log(data);
    }
};
