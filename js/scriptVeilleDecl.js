let inactivityTime = function () {
    let timer;

    function resetTimer() {
        clearTimeout(timer);
        timer = setTimeout(() => {
            window.location.href = "index.html";
        }, 30000); // 30 secondes = 30000 ms
    }

    // Événements détectés comme activité
    window.onload = resetTimer;
    document.onmousemove = resetTimer;
    document.onkeypress = resetTimer;
    document.onclick = resetTimer;
    document.onscroll = resetTimer;
    document.onmousedown = resetTimer;
    document.ontouchstart = resetTimer;

};

inactivityTime();