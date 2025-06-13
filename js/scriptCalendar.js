document.addEventListener("DOMContentLoaded", function () {
    const dateInput = document.querySelector("#date");
    if (dateInput) {
        flatpickr(dateInput, {
            enableTime: true,
            dateFormat: "Y-m-d H:i:S",
            time_24hr: true,
            minuteIncrement: 15,
            locale: "fr"
        });
    }
});
