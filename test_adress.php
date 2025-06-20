<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Test Autocomplétion Adresse</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input[type="text"] {
            width: 300px;
            padding: 8px;
            font-size: 16px;
        }

        ul#suggestions {
            width: 300px;
            border: 1px solid #ccc;
            list-style: none;
            padding: 0;
            margin-top: 5px;
            position: absolute;
            background-color: white;
            z-index: 10;
        }

        ul#suggestions li {
            padding: 8px;
            cursor: pointer;
        }

        ul#suggestions li:hover {
            background-color: #eee;
        }
    </style>
</head>
<body>

    <h1>Test Autocomplétion d'Adresse (BAN)</h1>

    <label for="adresse">Adresse :</label>
    <input type="text" id="adresse" placeholder="Entrez une adresse...">
    <ul id="suggestions"></ul>

    <script>
        const input = document.getElementById('adresse');
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

        // Cacher les suggestions si on clique ailleurs
        document.addEventListener('click', (e) => {
            if (!suggestions.contains(e.target) && e.target !== input) {
                suggestions.innerHTML = "";
            }
        });
    </script>

</body>
</html>
