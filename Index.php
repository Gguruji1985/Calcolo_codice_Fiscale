<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calcolo codice fiscale</title>
    <link rel="stylesheet" href="styles.css">
    </head>
<body>
    <header>
        <h1>Calcolo codice fiscale</h1>
    </header>
    <main>
        <form id="codiceFiscaleForm">
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" required>

            <label for="cognome">Cognome:</label>
            <input type="text" id="cognome" name="cognome" required>

            <label for="dataNascita">Data di nascita:</label>
            <input type="date" id="dataNascita" name="dataNascita" required>

            <label for="sesso">Sesso:</label>
            <select id="sesso" name="sesso" required>
                <option value="M">Maschio</option>
                <option value="F">Femmina</option>
            </select>

            <label for="luogoNascita">Luogo di nascita:</label>
            <input type="text" id="luogoNascita" name="luogoNascita" required>

            <button type="submit">Calcola Codice Fiscale</button>
        </form>
        <div id="result"></div>
    </main>
    <script src="script.js"></script>
</body>
</html>