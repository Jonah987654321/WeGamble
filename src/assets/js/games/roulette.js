var bids = {};
var balance = Number(document.getElementById("userBalanceStash").value);

function finishBid(id) {
    let amount = Number(document.getElementById("bettingMoneySelector").value);
    if (id in bids) {
        let difference = bids[id]-amount
        bids[id] = amount;
        balance += difference
    } else {
        bids[id] = amount;
        balance -= amount;
    }

    document.getElementById("roBD-"+id).innerHTML = amount.toString()+"€"
    destructOverlay();
}

function removeBid(id) {
    balance += bids[id];
    delete bids[id];

    document.getElementById("roBD-"+id).innerHTML = "";
    destructOverlay();
}

function placeBid(e) {
    const id = e.target.id.split("-")[1];
    const maxBalance = (id in bids)?balance+bids[id]:balance;
    const tqBalance = Math.round(.75*maxBalance);
    const hBalance = Math.round(.5*maxBalance);
    const qBalance = Math.round(.25*maxBalance);

    const secondaryButton = (!(id in bids))?`<button id="cancel" onclick="destructOverlay();">Abbrechen</button>`:`<button id="cancel" onclick="removeBid('${id}');">Entfernen</button>`;
    const bidPlacing = document.createElement("div");
    bidPlacing.innerHTML = `
    <button class="overlayClose" onclick="destructOverlay();" type="button"><i class="fa-solid fa-xmark"></i></button>
    <h2 style="margin-bottom: 20px;">Wette auf ${e.target.innerHTML}</h2>
    <div class="bidSelector">
        <label for="bettingMoneySelector">Betrag auswählen:</label><br>
        <input type="range" min="1" max="${maxBalance}" value="${(id in bids)?bids[id]:hBalance}" class="slider" id="bettingMoneySelector">
    </div>
    <div id="bidDisplay">
        <input type="number" id="bidDisplayInput">
    </div>
    <div class="bidButtons">
        <button onclick="document.getElementById('bettingMoneySelector').value=${qBalance}; document.getElementById('bidDisplay').value=${qBalance};">1/4</button>
        <button onclick="document.getElementById('bettingMoneySelector').value=${hBalance}; document.getElementById('bidDisplay').value=${hBalance};">1/2</button>
        <button onclick="document.getElementById('bettingMoneySelector').value=${tqBalance}; document.getElementById('bidDisplay').value=${tqBalance};">3/4</button>
        <button onclick="document.getElementById('bettingMoneySelector').value=${maxBalance}; document.getElementById('bidDisplay').value=${maxBalance};">All in</button>
    </div>
    <div class="finishBid">
        ${secondaryButton}
        <button onclick="finishBid('${id}');">Bestätigen</button>
    </div>
    `;
    createNewOverlay(bidPlacing);

    var slider = document.getElementById("bettingMoneySelector");
    var output = document.getElementById("bidDisplayInput");
    output.value = slider.value;

    output.oninput = function() {
        slider.value = output.value;
    }

    slider.oninput = function() {
        output.value = slider.value;
    }
}

document.querySelectorAll(".roF").forEach((field) => {
    field.addEventListener("click", placeBid);
});

function spin() {
    let btn = document.getElementById("spinBtn");
    btn.disabled = true;
    fetch(document.getElementById("serverURLStash").value+"/backend/roulette",
        {
            headers: {
                "Content-Type": "application/json"
            },
            method: "POST",
            body: JSON.stringify({
                "auth": document.getElementById("apiTokenStash").value,
                "bids": bids
            })
        }
    ).then(res => {
        if (res.status == 401) {
            window.location.href="/logout";
        }
        if (res.status == 204) {
            //action for no bids placed
        }
        if (res.status == 406) {
            //action for invalid bids
        }
        return res.json();
    }).then(data => {
        document.getElementById("userBalanceStash").value = data["newBalance"];
        document.getElementById("balanceDisplay").innerHTML = data["newBalance"]+"€";
        balance = Number(data["newBalance"]);
        document.getElementById("roF-"+data["number"].toString()).classList.toggle("success");
        data["winningBids"].forEach(bid => {
            document.getElementById("roBD-"+bid.toString()).classList.toggle("winning")
        });
        data["losingBids"].forEach(bid => {
            document.getElementById("roBD-"+bid.toString()).classList.toggle("losing")
        });
        btn.hidden = true;
        btn.disabled = false;
        document.getElementById("newGameBtn").hidden = false;
    });
}

function resetBoard() {
    document.querySelectorAll(".roF").forEach(e => {
        e.classList.remove("success");
    });

    document.querySelectorAll(".betDisplay").forEach(e => {
        e.innerHTML = '';
        e.classList.remove("losing");
        e.classList.remove("winning");
    });

    bids = {};
    document.getElementById("newGameBtn").hidden = true;
    document.getElementById("spinBtn").hidden = false;
}
