var injectCSS = `
.overlayBG {
    position: fixed;
    z-index: 10;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.219);
    display: flex;
    justify-content: center;
    align-items: center;
}

.overlay {
    background-color: white;
    display: block;
    overflow: auto;
    border-radius: 10px;
    color: black;
    padding: 20px;
    position: relative;
}

@media only screen and (max-width: 481px) {
    .overlay {
        width: 100vw;
    }
}

.overlayTitle {
    margin-bottom: 30px;
    margin-right: 60px;
}

.overlayClose {
    position: absolute;
    top: 25;
    right: 20;
    color: red;
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
}

.overlaySuccessWrapper {
    text-align: right;
    padding-top: 20px;
}

.overlaySuccessWrapper button {
    border: none;
    background-color: #489c48;
    font-size: 16px;
    padding: 7px;
    color: white;
    cursor: pointer;
}

`;

function createNewOverlay(contentNode) {
    const wrapper = document.createElement("div");
    wrapper.id = "overlayWrapper";

    const overlayCSS = document.createElement("style");
    overlayCSS.type = "text/css";
    overlayCSS.innerText = injectCSS;
    wrapper.appendChild(overlayCSS);

    const background = document.createElement("div");
    background.classList.add("overlayBG");
    const overlay = document.createElement("div");
    overlay.classList.add("overlay");
    overlay.appendChild(contentNode);
    background.appendChild(overlay);
    wrapper.appendChild(background);

    document.body.appendChild(wrapper);
}

function destructOverlay() {
    document.getElementById("overlayWrapper").remove();
}
