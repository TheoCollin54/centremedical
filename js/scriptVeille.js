let inactivityTime = function () {
    let timer;

    function resetTimer() {
        clearTimeout(timer);
        timer = setTimeout(() => {
            window.location.href = "index.html";
        }, 3000); // 5 minutes = 300000 ms
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