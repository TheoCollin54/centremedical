document.addEventListener("DOMContentLoaded", function () {
    // Sélectionne tous les champs avec class "date-input"
    const classInputs = document.querySelectorAll(".date-input");

    // Applique Flatpickr à tous les éléments avec la classe "date-input"
    classInputs.forEach(function (input) {
        flatpickr(input, {
            enableTime: true,
            dateFormat: "Y-m-d H:i:S",
            time_24hr: true,
            minuteIncrement: 15,
            locale: "fr"
        });
    });

    // Sélectionne tous les éléments avec l'ID "date-input" (même si c'est incorrect d’en avoir plusieurs)
    const idInputs = document.querySelectorAll("#date-input");

    // Applique Flatpickr à tous les éléments avec cet ID
    idInputs.forEach(function (input) {
        flatpickr(input, {
            enableTime: true,
            dateFormat: "Y-m-d H:i:S",
            time_24hr: true,
            minuteIncrement: 15,
            locale: "fr"
        });
    });
});