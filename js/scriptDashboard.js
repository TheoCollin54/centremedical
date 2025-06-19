document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    const modal = document.getElementById('rdvModal');
    const modalClose = document.getElementById('rdvModalClose');
    const modalNom = document.getElementById('modalNom');
    const modalPrenom = document.getElementById('modalPrenom');
    const modalTel = document.getElementById('modalTel');
    const modalSecu = document.getElementById('modalSecu');
    const modalDate = document.getElementById('modalDate');
    const modalRdvIdEdit = document.getElementById('modalRdvIdEdit');
    const modalRdvIdDel = document.getElementById('modalRdvIdDel');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'fr',
        height: 'auto',
        firstDay: 1,
        events: rdvData,
        buttonText: {
            today: "Aujourd'hui"
        },
        dayCellDidMount: function (info) {
            const today = new Date();
            const cellDate = info.date;

            // Supprime les heures pour une comparaison uniquement sur la date
            today.setHours(0, 0, 0, 0);
            cellDate.setHours(0, 0, 0, 0);

            if (cellDate < today) {
                // Appliquer un style aux jours passés
                info.el.style.backgroundColor = "#f0f0f0"; // gris clair
                info.el.style.opacity = "0.5"; // ou simplement réduire l'opacité
                info.el.style.pointerEvents = "none"; // empêche l’interaction si souhaité
            }
        },
        eventDidMount: function (info) {
            info.el.setAttribute('title', `Nom : ${info.event.extendedProps.nom}\nN° Sécu: ${info.event.extendedProps.num_secu}`);
        },
        eventClick: function (info) {
            // Remplissage de la modale avec les infos de l'événement
            modalNom.textContent = info.event.extendedProps.nom || info.event.title;
            modalPrenom.textContent = info.event.extendedProps.prenom || '';
            modalTel.textContent = info.event.extendedProps.tel || '';
            modalSecu.textContent = info.event.extendedProps.num_secu || '';
            // Affichage de la date sous la forme 06/08/2025 à 09:00
            if (info.event.start) {
                const date = info.event.start;
                const datePart = date.toLocaleDateString('fr-FR'); // "19/06/2025"
                const options = { hour: '2-digit', minute: '2-digit' };
                const timePart = date.toLocaleTimeString('fr-FR', options); // "09:00"

                modalDate.textContent = `${datePart} à ${timePart}`;
            } else {
                modalDate.textContent = '';
            }
            modalRdvIdEdit.value = info.event.id;
            modalRdvIdDel.value = info.event.id;

            // Affiche la modale
            modal.style.display = "block";
        }
    });

    calendar.render();

    // Fermeture de la modale au clic sur la croix
    modalClose.onclick = function () {
        modal.style.display = "none";
    };

    // Fermeture de la modale au clic en dehors du contenu
    window.onclick = function (event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    };
});