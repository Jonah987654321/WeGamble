var bids = {};
var lastBids = {};
var balance = Number(document.getElementById("userBalanceStash").value);
var biddingOpen = false;
var currentBidID = null;
var spinRunning = false;

function renderBids() {
    for (const [key, value] of Object.entries(bids)) {
        document.getElementById("roBD-"+key).innerHTML = formatCurrency(value).toString()+"€"
    };
}

function finishBid(id) {
    let amount = Number(document.getElementById("bettingMoneySelector").value);
    if (id in bids) {
        let difference = bids[id]-amount
        bids[id] = amount;
        balance += difference;
    } else {
        bids[id] = amount;
        balance -= amount;
    }

    renderBids();
    destructOverlay();
    currentBidID = null;
    biddingOpen = false;
}

function removeBid(id) {
    balance += bids[id];
    delete bids[id];

    document.getElementById("roBD-"+id).innerHTML = "";
    destructOverlay();
}

document.addEventListener("keydown", (e) => {
    if (e.key == "Enter") {
        biddingOpen?finishBid(currentBidID):spin();
    }
})

function animateToNumber(number) {
    let animationSteps = [0, 3, 6, 9, 12, 15, 18, 21, 24, 27, 30, 33, 36, 2, 5, 8, 11, 14, 17, 20, 23, 26, 29, 32, 35, 1, 4, 7, 10, 13, 16, 19, 22, 25, 28, 31, 34]
    let animationNumber = getRandomInt(0, 36);
    let totalFrames = 37*(getRandomInt(2, 3))+((animationSteps.indexOf(animationNumber) > animationSteps.indexOf(number))?(animationSteps.length-animationSteps.indexOf(animationNumber))+animationSteps.indexOf(number):animationSteps.indexOf(number)-animationSteps.indexOf(animationNumber));
    let animationDuration = 3000;
    
    let frameDurations = [];
    let initialFrameDuration = animationDuration / (totalFrames * 1.5); // kürzerer Startwert
    let slowDownFactor = 10; // Faktor, um die Dauer zu verlangsamen
    let lastSlowFrames = getRandomInt(5, 12);

    for (let i = 0; i < (totalFrames-(lastSlowFrames+5)); i++) {
        // Erhöhe die Dauer schrittweise bis zum Ende
        let progress = i / totalFrames;
        frameDurations.push(initialFrameDuration * (1 + slowDownFactor * progress));
    }

    for (let i=0; i < lastSlowFrames; i++) {
        frameDurations.push(750);
    }

    frameDurations.push(1300);
    frameDurations.push(1300);
    frameDurations.push(1300);
    frameDurations.push(2000);
    frameDurations.push(2000);

    // Normiere die Frame-Dauern so, dass die Summe der Frame-Dauern `animationDuration` ergibt
    let totalDuration = frameDurations.reduce((sum, duration) => sum + duration, 0);
    frameDurations = frameDurations.map(duration => duration * (animationDuration / totalDuration));

    document.getElementById("roF-"+animationNumber.toString()).classList.toggle("success");
    let frame = 0;

    function lastFrames(iterations) {
        iterations--;

        document.getElementById("roF-"+number.toString()).classList.toggle("success");

        if (iterations === 0) {
            return
        }
        setTimeout(function() {lastFrames(iterations)}, 200);
    }
    
    function animateFrame() {
		frame++;
        document.getElementById("roF-"+animationNumber.toString()).classList.toggle("success");
        animationNumber = (animationNumber==animationSteps[animationSteps.length-1])?animationSteps[0]:animationSteps[animationSteps.indexOf(animationNumber)+1];
        document.getElementById("roF-"+animationNumber.toString()).classList.toggle("success");
        if (frame === totalFrames) {
            setTimeout(function() {lastFrames(6)}, 200);
            return;
        }

        setTimeout(animateFrame, frameDurations[frame]);
    }

    // Start der Animation
    setTimeout(animateFrame, frameDurations[0]);
}

function placeBid(e) {
    if (spinRunning) {
        return;
    }
    const id = e.target.id.split("-")[1];
    biddingOpen = true;
    currentBidID = id;
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
        <button onclick="document.getElementById('bettingMoneySelector').value=${qBalance}; document.getElementById('bidDisplayInput').value=${qBalance};">1/4</button>
        <button onclick="document.getElementById('bettingMoneySelector').value=${hBalance}; document.getElementById('bidDisplayInput').value=${hBalance};">1/2</button>
        <button onclick="document.getElementById('bettingMoneySelector').value=${tqBalance}; document.getElementById('bidDisplayInput').value=${tqBalance};">3/4</button>
        <button onclick="document.getElementById('bettingMoneySelector').value=${maxBalance}; document.getElementById('bidDisplayInput').value=${maxBalance};">All in</button>
    </div>
    <div class="finishBid">
        ${secondaryButton}
        <button onclick="finishBid('${id}');">Bestätigen</button>
    </div>
    `;
    createNewOverlay(bidPlacing);

    if (!isMobile()) {
        document.getElementById("bidDisplayInput").focus();
    }

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
    spinRunning = true;
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
            notify("Bitte platziere erst eine Wette");
            btn.disabled = false;
            return;
        }
        if (res.status == 406) {
            //action for invalid bids
        }
        return res.json();
    }).then(data => {
        animateToNumber(data["number"]);
        setTimeout(() => {
            document.getElementById("userBalanceStash").value = data["newBalance"];
            let totalBalanceEl = document.getElementById("balanceDisplay");
            let newBalance = Number(data["newBalance"]);
            let startAnimBalance = parseInt(totalBalanceEl.innerHTML.replace(".", ""), 10);
            (newBalance > balance)?animateCountUp(totalBalanceEl, 2000, startAnimBalance, newBalance):animateCountDown(totalBalanceEl, 2000, startAnimBalance, newBalance);
            balance = newBalance;
            data["winningBids"].forEach(bid => {
                let winEl = document.getElementById("roBD-"+bid["ID"].toString());
                winEl.classList.toggle("winning");
                animateCountUp(winEl, 500, bids[bid["ID"]], bids[bid["ID"]]+bid["winAmount"]);
            });
            data["losingBids"].forEach(bid => {
                let looseEl = document.getElementById("roBD-"+bid.toString());
                looseEl.classList.toggle("losing")
                animateCountDown(looseEl, 500, bids[bid.toString()], 0);
            });
            setTimeout(() => {
                resetBoard();
                document.getElementById("spinBtn").disabled = false;
            }, 1500);
        }, 6500);
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

    lastBids = structuredClone(bids);
    bids = {};
    document.getElementById("lastBidsRedo").style.visibility = "visible";
    spinRunning = false;
}

function redoLastBids(multiplier) {
    if (spinRunning) {
        return;
    }
    bids = structuredClone(lastBids);
    for (const [key, value] of Object.entries(bids)) {
        bids[key] = value*multiplier;
    }
    renderBids();
}
