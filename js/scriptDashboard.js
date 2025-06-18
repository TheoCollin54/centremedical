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
        eventDidMount: function (info) {
            info.el.setAttribute('title', `Téléphone: ${info.event.extendedProps.tel}\nN° Sécu: ${info.event.extendedProps.num_secu}`);
        },
        eventClick: function (info) {
            // Remplissage de la modale avec les infos de l'événement
            modalNom.textContent = info.event.extendedProps.nom || info.event.title;
            modalPrenom.textContent = info.event.extendedProps.prenom || '';
            modalTel.textContent = info.event.extendedProps.tel || '';
            modalSecu.textContent = info.event.extendedProps.num_secu || '';
            modalDate.textContent = info.event.start ? info.event.start.toLocaleString() : '';
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