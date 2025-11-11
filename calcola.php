<?php

// Imposta l'intestazione per far capire al browser che stiamo inviando JSON
header('Content-Type: application/json');

// --- FUNZIONI DI SUPPORTO (Le stesse di prima) ---

/**
 * Pulisce e normalizza una stringa per il calcolo
 */
function pulisciStringa($str) {
    $str = strtoupper(trim($str));
    // Sostituisce lettere accentate
    $accentate = ['À', 'È', 'É', 'Ì', 'Ò', 'Ù'];
    $normali   = ['A', 'E', 'E', 'I', 'O', 'U'];
    $str = str_replace($accentate, $normali, $str);
    // Rimuove apostrofi, spazi e caratteri non alfabetici
    $str = preg_replace("/[^A-Z]/", "", $str);
    return $str;
}

/**
 * Estrae le consonanti da una stringa
 */
function getConsonanti($str) {
    return preg_replace("/[AEIOU]/", "", $str);
}

/**
 * Estrae le vocali da una stringa
 */
function getVocali($str) {
    return preg_replace("/[^AEIOU]/", "", $str);
}


// --- FUNZIONI DI CALCOLO CODICE FISCALE (Le stesse di prima) ---

/**
 * 1. Calcolo Cognome (3 LLL)
 */
function calcolaCognome($cognome) {
    $cognome = pulisciStringa($cognome);
    $consonanti = getConsonanti($cognome);
    $vocali = getVocali($cognome);
    $codice = $consonanti . $vocali;

    if (strlen($codice) >= 3) {
        return substr($codice, 0, 3);
    } else {
        return str_pad($codice, 3, 'X');
    }
}

/**
 * 2. Calcolo Nome (3 LLL)
 */
function calcolaNome($nome) {
    $nome = pulisciStringa($nome);
    $consonanti = getConsonanti($nome);
    $vocali = getVocali($nome);

    if (strlen($consonanti) >= 4) {
        return $consonanti[0] . $consonanti[2] . $consonanti[3];
    } elseif (strlen($consonanti) == 3) {
        return $consonanti[0] . $consonanti[1] . $consonanti[2];
    } else {
        $codice = $consonanti . $vocali;
        if (strlen($codice) >= 3) {
            return substr($codice, 0, 3);
        } else {
            return str_pad($codice, 3, 'X');
        }
    }
}

/**
 * 3. Calcolo Data di Nascita e Sesso (AAMGG)
 */
function calcolaDataSesso($data_nascita, $sesso) {
    // $data_nascita arriva come "YYYY-MM-DD"
    $anno = substr($data_nascita, 2, 2); 
    $mese_num = substr($data_nascita, 5, 2);
    $giorno_num = substr($data_nascita, 8, 2);

    $map_mesi = [
        '01' => 'A', '02' => 'B', '03' => 'C', '04' => 'D', '05' => 'E',
        '06' => 'H', '07' => 'L', '08' => 'M', '09' => 'P', '10' => 'R',
        '11' => 'S', '12' => 'T'
    ];
    $mese_char = $map_mesi[$mese_num];

    if ($sesso == 'F') {
        $giorno_cf = (int)$giorno_num + 40;
    } else {
        $giorno_cf = (int)$giorno_num;
    }
    $giorno_str = str_pad($giorno_cf, 2, '0', STR_PAD_LEFT);

    return $anno . $mese_char . $giorno_str;
}

/**
 * 4. Calcolo Carattere di Controllo (L)
 */
function calcolaCarattereControllo($codice_parziale) {
    $somma = 0;
    $map_dispari = [
        '0'=>1, '1'=>0, '2'=>5, '3'=>7, '4'=>9, '5'=>13, '6'=>15, '7'=>17, '8'=>19, '9'=>21,
        'A'=>1, 'B'=>0, 'C'=>5, 'D'=>7, 'E'=>9, 'F'=>13, 'G'=>15, 'H'=>17, 'I'=>19, 'J'=>21,
        'K'=>2, 'L'=>4, 'M'=>18, 'N'=>20, 'O'=>11, 'P'=>3, 'Q'=>6, 'R'=>8, 'S'=>12, 'T'=>14,
        'U'=>16, 'V'=>10, 'W'=>22, 'X'=>25, 'Y'=>24, 'Z'=>23
    ];
    $map_pari = [
        '0'=>0, '1'=>1, '2'=>2, '3'=>3, '4'=>4, '5'=>5, '6'=>6, '7'=>7, '8'=>8, '9'=>9,
        'A'=>0, 'B'=>1, 'C'=>2, 'D'=>3, 'E'=>4, 'F'=>5, 'G'=>6, 'H'=>7, 'I'=>8, 'J'=>9,
        'K'=>10, 'L'=>11, 'M'=>12, 'N'=>13, 'O'=>14, 'P'=>15, 'Q'=>16, 'R'=>17, 'S'=>18,
        'T'=>19, 'U'=>20, 'V'=>21, 'W'=>22, 'X'=>23, 'Y'=>24, 'Z'=>25
    ];

    for ($i = 0; $i < 15; $i++) {
        $char = $codice_parziale[$i];
        if (($i + 1) % 2 == 0) {
            $somma += $map_pari[$char];
        } else {
            $somma += $map_dispari[$char];
        }
    }
    $resto = $somma % 26;
    $map_controllo = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    return $map_controllo[$resto];
}


// --- BLOCCO PRINCIPALE DI ESECUZIONE (API) ---

$response = []; // Array che conterrà la nostra risposta JSON

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    try {
        // 1. Recupero Dati dal Form (inviati da fetch)
        // Controllo che i campi non siano vuoti
        if (empty($_POST['cognome']) || empty($_POST['nome']) || empty($_POST['data_nascita']) || empty($_POST['sesso']) || empty($_POST['comune'])) {
             throw new Exception("Tutti i campi sono obbligatori.");
        }

        $cognome = $_POST['cognome'];
        $nome = $_POST['nome'];
        $data_nascita = $_POST['data_nascita'];
        $sesso = $_POST['sesso'];
        $codice_comune = $_POST['comune'];

        // 2. Calcolo
        $cf_cognome = calcolaCognome($cognome);
        $cf_nome = calcolaNome($nome);
        $cf_data_sesso = calcolaDataSesso($data_nascita, $sesso);
        
        $codice_parziale = $cf_cognome . $cf_nome . $cf_data_sesso . $codice_comune;
        $carattere_controllo = calcolaCarattereControllo($codice_parziale);
        $codice_fiscale_finale = $codice_parziale . $carattere_controllo;

        // 3. Preparo la risposta di successo
        $response['success'] = true;
        $response['codice_fiscale'] = $codice_fiscale_finale;

    } catch (Exception $e) {
        // 4. Preparo la risposta di errore
        $response['success'] = false;
        $response['error'] = $e->getMessage();
    }

} else {
    // Se non è una richiesta POST
    $response['success'] = false;
    $response['error'] = 'Metodo di richiesta non valido.';
}

// 5. Invio la risposta JSON
echo json_encode($response);
exit; // Termina lo script
?>