function formatCurrency(balance) {
    balance = String(balance); // Konvertiere balance in einen String
    if (balance.length > 4) { // Überprüfe, ob die Länge größer als 4 ist
        balance = balance.split("").reverse().join(""); // Drehe den String um
        balance = balance.match(/.{1,3}/g); // Teile den umgedrehten String in 3er-Gruppen auf
        return balance.join(".").split("").reverse().join(""); // Verbinde mit Punkt und drehe zurück
    } else {
        return balance;
    }
}

function notify(message) {
    // Erstelle ein neues div-Element für die Benachrichtigung
    const notification = document.createElement("div");

    // Füge die Nachricht als Textinhalt hinzu
    notification.textContent = message;

    // Setze einige Stil-Eigenschaften für die Benachrichtigung
    notification.style.position = "fixed";
    notification.style.bottom = "20px";
    notification.style.right = "20px";
    notification.style.backgroundColor = "#ffffff";
    notification.style.color = "black";
    notification.style.padding = "10px 20px";
    notification.style.borderRadius = "5px";
    notification.style.boxShadow = "0px 0px 10px rgba(0,0,0,0.5)";
    notification.style.zIndex = "1000";
    notification.style.borderBottom = "3px solid #FFB800";

    // Füge die Benachrichtigung zum Dokument hinzu
    document.body.appendChild(notification);

    // Entferne die Benachrichtigung nach 3 Sekunden
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

