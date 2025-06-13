document.addEventListener("DOMContentLoaded", function () {
  // Récupérer le message passé via l'attribut data-message du body
  const message = document.body.dataset.message;

  if (message) {
    alert(message);
  }
});