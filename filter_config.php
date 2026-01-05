<?php
//POUR CHANGER DE MÉTHODE: Modifiez simplement FILTER_METHOD

define('FILTER_METHOD', 'tfidf');  // 'tfidf' ou 'word2vec'

// seuil bloquer message
define('FILTER_THRESHOLD', -15);

// path des fichiers
define('SCOREMAP_FILE', __DIR__ . '/scoremap.json');
define('PYTHON_SCRIPT', __DIR__ . '/predict_message.py');
define('PYTHON_EXECUTABLE', 'python'); 

// blacklist (forbidden words)
$BLACKLIST = [
    'nigger', 'nigga', 'fuck', 'fucking', 'bitch', 'shit', 'cunt',
    'faggot', 'fag', 'retard', 'whore', 'slut', 'dick', 'cock',
    'pussy', 'asshole', 'bastard', 'damn', 'piss','hate'
];

// stopwords (tf-idf)
$STOPWORDS = [
    'the', 'a', 'an', 'and', 'or', 'but', 'is', 'are', 'was', 'were', 'be', 'been',
    'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'should',
    'could', 'may', 'might', 'must', 'can', 'of', 'at', 'by', 'for', 'with', 'about',
    'against', 'between', 'into', 'through', 'during', 'before', 'after', 'above',
    'below', 'to', 'from', 'up', 'down', 'in', 'out', 'on', 'off', 'over', 'under',
    'again', 'further', 'then', 'once', 'here', 'there', 'when', 'where', 'why',
    'how', 'all', 'both', 'each', 'few', 'more', 'most', 'other', 'some', 'such',
    'no', 'nor', 'not', 'only', 'own', 'same', 'so', 'than', 'too', 'very', 's',
    't', 'just', 'don', 'now', 'i', 'me', 'my', 'myself', 'we', 'our', 'ours',
    'ourselves', 'you', 'your', 'yours', 'yourself', 'yourselves', 'he', 'him',
    'his', 'himself', 'she', 'her', 'hers', 'herself', 'it', 'its', 'itself',
    'they', 'them', 'their', 'theirs', 'themselves', 'this', 'that', 'these',
    'those', 'am', 'as', 'if', 'because', 'until', 'while', 'who', 'which', 'what',
    'day', 'time', 'today', 'tomorrow', 'yesterday', 'night', 'morning', 'evening',
    'week', 'month', 'year', 'thing', 'things', 'way'
];

$SCOREMAP_CACHE = null;

/*
 * fonction filtrage message
 * param string $message message a verifier
 * return array ['is_offensive' => bool, 'score' => float, 'method' => string, 'reason' => string]
 */

function filterMessage($message) {
    global $BLACKLIST;
    
    // chekc blacklist (tous les cas)
    if (containsBlacklistedWords($message, $BLACKLIST)) {
        return [
            'is_offensive' => true,
            'score' => -100,
            'method' => 'blacklist',
            'reason' => 'mot dans blacklist'
        ];
    }
    
    // selon methode 
    if (FILTER_METHOD === 'tfidf') {
        return filterWithTFIDF($message);
    } elseif (FILTER_METHOD === 'word2vec') {
        return filterWithWord2Vec($message);
    } else {
        error_log("Metodo di filtro non valido: " . FILTER_METHOD);
        return [
            'is_offensive' => false,
            'score' => 0,
            'method' => 'none',
            'reason' => 'Metodo non configurato'
        ];
    }
}

//mot dans blacklist?
function containsBlacklistedWords($message, $blacklist) {
    $message_lower = strtolower($message);
    preg_match_all('/\b[a-z]+\b/', $message_lower, $matches);
    $words = $matches[0];
    
    foreach ($words as $word) {
        if (in_array($word, $blacklist)) {
            error_log("Parola blacklist trovata: $word");
            return true;
        }
    }
    
    return false;
}

// filtrage  TF-IDF (ScoreMap)
 
function filterWithTFIDF($message) {
    global $SCOREMAP_CACHE, $STOPWORDS;
    
    // telecharge scoremap 
    if ($SCOREMAP_CACHE === null) {
        if (!file_exists(SCOREMAP_FILE)) {
            error_log("ERRORE: File scoremap.json non trouvé!");
            return [
                'is_offensive' => false,
                'score' => 0,
                'method' => 'tfidf',
                'reason' => 'File scoremap non trouvé'
            ];
        }
        
        $json = file_get_contents(SCOREMAP_FILE);
        $SCOREMAP_CACHE = json_decode($json, true);
        
        if (!is_array($SCOREMAP_CACHE)) {
            error_log("ERRORE: Scoremap invalide!");
            return [
                'is_offensive' => false,
                'score' => 0,
                'method' => 'tfidf',
                'reason' => 'Scoremap invalide'
            ];
        }
    }
    
    // calcul score
    $score = calculateTFIDFScore($message, $SCOREMAP_CACHE, $STOPWORDS);
    
    $is_offensive = $score < FILTER_THRESHOLD;
    
    error_log("TF-IDF score: $score | Threshold: " . FILTER_THRESHOLD . " | Offensive: " . ($is_offensive ? "SI" : "NO"));
    
    return [
        'is_offensive' => $is_offensive,
        'score' => $score,
        'method' => 'tfidf',
        'reason' => $is_offensive ? "Score TF-IDF trop bas ($score)" : "OK"
    ];
}

//Calcola score TF-IDF

function calculateTFIDFScore($message, $scoremap, $stopwords) {
    $message = strtolower($message);
    
    preg_match_all('/\b[a-z]+\b/', $message, $matches);
    $words = $matches[0];
    
    $filtered_words = array_filter($words, function($word) use ($stopwords) {
        return strlen($word) > 2 && !in_array($word, $stopwords);
    });
    
    if (empty($filtered_words)) {
        return 0;
    }
    
    $scores = [];
    foreach ($filtered_words as $word) {
        if (isset($scoremap[$word])) {
            $scores[] = $scoremap[$word];
        }
    }
    
    if (empty($scores)) {
        return 0;
    }
    
    return array_sum($scores) / count($scores);
}

// filtrage word2Vec

function filterWithWord2Vec($message) {
    // escape del messaggio per passarlo a python
    $message_escaped = escapeshellarg($message);
    
    // commande python
    $command = PYTHON_EXECUTABLE . " " . escapeshellarg(PYTHON_SCRIPT) . " $message_escaped 2>&1";
    
    error_log("execution commande python: $command");
    
    // esecution python
    $output = shell_exec($command);
    
    if ($output === null) {
        error_log("ERROR: impossible execute python!");
        return [
            'is_offensive' => false,
            'score' => 0,
            'method' => 'word2vec',
            'reason' => 'erreur execution python'
        ];
    }
    
    // Parse JSON response
    $result = json_decode($output, true);
    
    if (!$result || !isset($result['success'])) {
        error_log("ERRORE Python output: $output");
        return [
            'is_offensive' => false,
            'score' => 0,
            'method' => 'word2vec',
            'reason' => 'Output Python invalide'
        ];
    }
    
    if (!$result['success']) {
        error_log("ERRORE Python: " . ($result['error'] ?? 'Unknown'));
        return [
            'is_offensive' => false,
            'score' => 0,
            'method' => 'word2vec',
            'reason' => $result['error'] ?? 'Erreur Python'
        ];
    }
    
    $is_hate = $result['is_hate'];
    $confidence = $result['confidence'];
    
    // conversion confidence in score negativo se hate
    $score = $is_hate ? -($confidence * 100) : ($confidence * 100);
    
    error_log("Word2Vec result: is_hate=$is_hate, confidence=$confidence, score=$score");
    
    return [
        'is_offensive' => $is_hate,
        'score' => $score,
        'method' => 'word2vec',
        'reason' => $is_hate ? "classifié comme hate (confidence: $confidence)" : "OK"
    ];
}
?>