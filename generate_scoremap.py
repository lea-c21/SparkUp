import os
import csv
import json
import math
import re
from collections import defaultdict, Counter

# configuration
DATASET_FOLDER = "sampled_train"
CSV_FILE = "sampled_train/annotations_metadata.csv"
OUTPUT_JSON = "scoremap.json"


STOPWORDS = {
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
}

def tokenize(text):
    """Estrae parole dal testo"""
    words = re.findall(r'\b[a-z]+\b', text.lower())
    return [w for w in words if w not in STOPWORDS and len(w) > 2]

# verifica existence file
if not os.path.exists(DATASET_FOLDER) or not os.path.exists(CSV_FILE):
    print("Errore: Dataset non trovato!")
    exit(1)

texts = []
labels = []

# chargement données

with open(CSV_FILE, "r", encoding="utf-8") as csvfile:
    reader = csv.DictReader(csvfile)
    
    for row in reader:
        label_str = row["label"].strip()
        if label_str not in {"hate", "noHate"}:
            continue
        
        label = 1 if label_str == "hate" else 0
        file_id = row["file_id"]
        num_contexts = int(row["num_contexts"])
        
        # file csv principale
        filepath = os.path.join(DATASET_FOLDER, f"{file_id}.txt")
        if os.path.exists(filepath):
            with open(filepath, "r", encoding="utf-8", errors="ignore") as f:
                texts.append(tokenize(f.read()))
                labels.append(label)
        
        # altri file
        for ctx in range(2, num_contexts + 1):
            filepath = os.path.join(DATASET_FOLDER, f"{file_id}_{ctx}.txt")
            if os.path.exists(filepath):
                with open(filepath, "r", encoding="utf-8", errors="ignore") as f:
                    texts.append(tokenize(f.read()))
                    labels.append(label)

N = len(texts)  

if N == 0:
    print("erruer: 0 fichers chargés!")
    exit(1)

# calcul IDF
idf_count = defaultdict(int)

for words in texts:
    for w in set(words):
        idf_count[w] += 1

idf = {w: math.log(N / (1 + count)) for w, count in idf_count.items()}
print(f" {len(idf)} mots uniques")

# calcul ScoreMap
ScoreMap = defaultdict(float)

for words, label in zip(texts, labels):
    tf = Counter(words)
    
    for word, freq in tf.items():
        tfidf = freq * idf[word]
        
        if label == 1:  # hate
            ScoreMap[word] -= tfidf
        else:  # noHate
            ScoreMap[word] += tfidf

manual_hate_words = {
    'nigger': -100.0, 'nigga': -100.0, 'fuck': -60.0, 'fucking': -60.0,
    'bitch': -50.0, 'shit': -40.0, 'cunt': -80.0, 'faggot': -80.0,
    'retard': -40.0, 'whore': -60.0, 'slut': -60.0, 'asshole': -50.0,
    'dick': -40.0, 'cock': -40.0, 'pussy': -40.0, 'bastard': -30.0,
    'damn': -20.0, 'piss': -20.0
}

for word, score in manual_hate_words.items():
    if word not in ScoreMap:
        ScoreMap[word] = score

# sauvegarde
with open(OUTPUT_JSON, "w", encoding="utf-8") as f:
    json.dump(ScoreMap, f, indent=2, ensure_ascii=False)


#test
sorted_words = sorted(ScoreMap.items(), key=lambda x: x[1], reverse=True)

print("\n" + "="*60)
print("Top 5 mots POSITIVE:")
for word, score in sorted_words[:5]:
    print(f"  {word}: {score:.2f}")

print("\nTop 5 mots NEGATIVE:")
for word, score in sorted_words[-5:]:
    print(f"  {word}: {score:.2f}")
print("="*60)


def test_message(msg, threshold=-15):
    words = tokenize(msg)
    scores = [ScoreMap[w] for w in words if w in ScoreMap]
    
    if not scores:
        return 0, True
    
    avg = sum(scores) / len(scores)
    return avg, avg > threshold

print("\message test:")
test_msgs = [
    "Hello, how are you?",
    "I hate you all",
    "fuck you",
    "nigger",
    "This is nice"
]

for msg in test_msgs:
    score, valid = test_message(msg)
    status = " OK" if valid else "BLOQUE"
    print(f"{status:12} | Score: {score:7.2f} | '{msg}'")

