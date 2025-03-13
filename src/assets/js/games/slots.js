var conn = new WebSocket(document.getElementById("wsURLStash").value);

var balance = Number(document.getElementById("userBalanceStash").value);
var bidAmount = 100;

document.getElementById("betInput").addEventListener("input", (e) => {
    bidAmount = Number(e.target.value);
    document.getElementById("totalBet").innerText = formatCurrency(bidAmount*8);
});

var spinning = false;

conn.onopen = function(e) {
    conn.send(JSON.stringify({"type": "check-in", "apiKey": document.getElementById("apiTokenStash").value, "gameID": 4}));
};

function animate(id, numOfElems, row) {
    let timings = {1: 6000, 2: 8000, 3: 10000}
    let roller = document.getElementById("sR"+id);
    let height = roller.offsetHeight-30

    let totalHeight = (numOfElems-1)*height;

    let inner = document.getElementById("sRI"+id);

    inner.animate([
        {
          transform: "translateY(0)"
        },
        {
          transform: `translateY(-${totalHeight}px)`
        }
      ], {
        duration: timings[row]+(Math.floor(Math.random() * 1001) - 500),
        fill: "forwards",
        easing: 'cubic-bezier(0.1, 0.9, 0.2, 1)'
      });
}

function spin() {
    if (!spinning) {
        conn.send(JSON.stringify({"event": "slotsRoll", "betAmount": bidAmount}));
    }
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
            case 14:
                return notify("Bitte gib erst einen Wetteinsatz ein");
            case 15:
                return notify("Wetteinsatz zu hoch");
            default:
                console.error("Unknown error occurred: "+data);
                return;
        }
    } else if (data["type"] == "success") {
        console.log(data)
        if (data["event"] == "check-in") {
            console.info("WS connection established");
        } else if (data["event"] == "slotsRoll") {
            spinning = true;
            let index = 1;
            data["results"].forEach(element => {
                let amount = Math.ceil(index/3)*30;
                let field = document.getElementById("sRI"+index);
                const fragment = document.createDocumentFragment();
                for (let i=0;i<amount;i++) {
                    const img = document.createElement('img');
                    img.src = `../../assets/img/slots/${(i%10==0)?10:i%10}.png`;
                    fragment.appendChild(img);
                }
                for (let i=1;i<element+1;i++) {
                    const img = document.createElement('img');
                    img.src = `../../assets/img/slots/${(i%10==0)?10:i%10}.png`;
                    fragment.appendChild(img);
                }
                field.appendChild(fragment);
                let elems = 1+amount+element;
                animate(index, elems, Math.ceil(index/3));
                index++;
            });

            setTimeout(() => {
                document.getElementById("winIndic").innerText = formatCurrency(data["winLoss"])+"€";
                document.getElementById("freeSpinIndic").innerText = data["freeSpins"];

                if (data["freeSpins"] > 0) {
                    document.getElementById("betInputBlocker").classList.remove("hidden");
                    document.getElementById("betInputWrapper").classList.add("disabled");
                    document.getElementById("spinIndicWrapper").classList.add("attention");
                } else {
                    document.getElementById("betInputBlocker").classList.add("hidden");
                    document.getElementById("betInputWrapper").classList.remove("disabled");
                    document.getElementById("spinIndicWrapper").classList.remove("attention");
                }

                let newBalance = data["newBalance"];
                balance = newBalance;
                let totalBalanceEl = document.getElementById("balanceDisplay");
                let startAnimBalance = parseInt(totalBalanceEl.innerHTML.replace(".", ""), 10);
                (newBalance > startAnimBalance)?animateCountUp(totalBalanceEl, 2000, startAnimBalance, newBalance):animateCountDown(totalBalanceEl, 2000, startAnimBalance, newBalance);
                spinning = false;

                let index = 1;
                data["results"].forEach(element => {
                    let field = document.getElementById("sR"+index);
                    field.innerHTML = `<div id="sRI${index}" class="sRI">
                                <img src="../../assets/img/slots/${element}.png">
                            </div>`;
                    index++;
                });
            }, 10000);
        }
    } else {
        console.log(data);
    }
};
