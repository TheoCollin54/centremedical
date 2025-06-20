const input = document.getElementById('adress');
const suggestions = document.getElementById('suggestions');

input.addEventListener('input', async () => {
    const query = input.value;
    if (query.length < 3) {
        suggestions.innerHTML = "";
        return;
    }

    try {
        const res = await fetch(`https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(query)}&limit=5`);
        const data = await res.json();

        suggestions.innerHTML = "";
        data.features.forEach((feature) => {
            const li = document.createElement('li');
            li.textContent = feature.properties.label;
            li.addEventListener('click', () => {
                input.value = feature.properties.label;
                suggestions.innerHTML = "";
            });
            suggestions.appendChild(li);
        });
    } catch (e) {
        console.error("Erreur API :", e);
        suggestions.innerHTML = "";
    }
});

document.addEventListener('click', (e) => {
    if (!suggestions.contains(e.target) && e.target !== input) {
        suggestions.innerHTML = "";
    }
});
