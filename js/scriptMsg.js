document.addEventListener("DOMContentLoaded", function () {
  const message = document.body.dataset.message;
  if (message && message.trim() !== "") {
    alert(message);
  }
});