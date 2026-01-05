import os
import csv
import json
import re
import numpy as np
from collections import defaultdict
from gensim.models import Word2Vec
from sklearn.linear_model import LogisticRegression
import pickle

# configuration
DATASET_FOLDER = "sampled_train"
CSV_FILE = "sampled_train/annotations_metadata.csv"
OUTPUT_MODEL = "word2vec_model.bin"
OUTPUT_CLASSIFIER = "hate_classifier.pkl"
OUTPUT_CONFIG = "w2v_config.json"

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
#tokenize text and remove stopwords
def tokenize(text):
    words = re.findall(r'\b[a-z]+\b', text.lower())
    return [w for w in words if w not in STOPWORDS and len(w) > 2]

print(" Chargement du dataset...")
texts_raw = []
texts_tokenized = []
labels = []

with open(CSV_FILE, "r", encoding="utf-8") as csvfile:
    reader = csv.DictReader(csvfile)
    
    for row in reader:
        label_str = row["label"].strip()
        if label_str not in {"hate", "noHate"}:
            continue
        
        label = 1 if label_str == "hate" else 0
        file_id = row["file_id"]
        num_contexts = int(row["num_contexts"])
        
        # charge fichier principal
        filepath = os.path.join(DATASET_FOLDER, f"{file_id}.txt")
        if os.path.exists(filepath):
            with open(filepath, "r", encoding="utf-8", errors="ignore") as f:
                text = f.read()
                texts_raw.append(text)
                texts_tokenized.append(tokenize(text))
                labels.append(label)
        
        # charge fichiers contextuels
        for ctx in range(2, num_contexts + 1):
            filepath = os.path.join(DATASET_FOLDER, f"{file_id}_{ctx}.txt")
            if os.path.exists(filepath):
                with open(filepath, "r", encoding="utf-8", errors="ignore") as f:
                    text = f.read()
                    texts_raw.append(text)
                    texts_tokenized.append(tokenize(text))
                    labels.append(label)

print(f"{len(texts_tokenized)} documents chargés")
print(f"   - Hate: {sum(labels)}")
print(f"   - NoHate: {len(labels) - sum(labels)}")

# Entraînement Word2Vec
print("\n Entraînement du modèle Word2Vec...")
w2v_model = Word2Vec(
    sentences=texts_tokenized,
    vector_size=100,        # Dimension des vecteurs
    window=5,               # Contexte de 5 mots
    min_count=2,            # Ignore les mots rares
    workers=4,
    sg=1,                   # Skip-gram (meilleur pour petits datasets)
    epochs=20
)

w2v_model.save(OUTPUT_MODEL)
print(f"Modèle Word2Vec sauvegardé: {OUTPUT_MODEL}")

# création des vecteurs de documents (moyenne des word embeddings)
def text_to_vector(tokens, model):
    """Convertit un texte en vecteur (moyenne des embeddings)"""
    vectors = []
    for word in tokens:
        if word in model.wv:
            vectors.append(model.wv[word])
    
    if len(vectors) == 0:
        return np.zeros(model.vector_size)
    
    return np.mean(vectors, axis=0)

print("\n Génération des vecteurs de documents...")
X_train = np.array([text_to_vector(tokens, w2v_model) for tokens in texts_tokenized])
y_train = np.array(labels)

# entraînement d'un classificateur
print("\n Entraînement du classificateur...")
classifier = LogisticRegression(
    max_iter=1000,
    class_weight='balanced',  # Important pour datasets déséquilibrés
    random_state=42
)
classifier.fit(X_train, y_train)

# Évaluation
train_score = classifier.score(X_train, y_train)
print(f" Précision sur le training set: {train_score:.2%}")

# Sauvegarde du classificateur
with open(OUTPUT_CLASSIFIER, 'wb') as f:
    pickle.dump(classifier, f)
print(f" Classificateur sauvegardé: {OUTPUT_CLASSIFIER}")

# Configuration pour PHP
config = {
    "vector_size": w2v_model.vector_size,
    "threshold": 0.5,  # Seuil de probabilité pour classifier comme "hate"
    "model_file": OUTPUT_MODEL,
    "classifier_file": OUTPUT_CLASSIFIER
}

with open(OUTPUT_CONFIG, 'w') as f:
    json.dump(config, f, indent=2)
print(f" Configuration sauvegardée: {OUTPUT_CONFIG}")

# Tests
print(" Tests du modèle Word2Vec:")

test_messages = [
    "Hello, how are you?",
    "I hate you all",
    "fuck you",
    "This is nice",
    "You are stupid and ugly",
    "Have a great day!"
]

for msg in test_messages:
    tokens = tokenize(msg)
    if len(tokens) == 0:
        print(f"  NEUTRAL  | '{msg}' (pas de mots significatifs)")
        continue
    
    vec = text_to_vector(tokens, w2v_model).reshape(1, -1)
    proba = classifier.predict_proba(vec)[0]
    pred = classifier.predict(vec)[0]
    
    hate_prob = proba[1] * 100
    status = " HATE" if pred == 1 else " OK"
    
    print(f"{status:12} | Hate: {hate_prob:5.1f}% | '{msg}'")


# mots similaires (pour vérifier la qualité du modèle)
print("\n Mots similaires aux mots-clés:")
test_words = ["hate", "love", "stupid", "nice"]
for word in test_words:
    if word in w2v_model.wv:
        similar = w2v_model.wv.most_similar(word, topn=5)
        print(f"\n'{word}' est proche de:")
        for sim_word, score in similar:
            print(f"  - {sim_word} ({score:.3f})")

print("\n Script terminé!")
print(f"\n Fichiers générés:")
print(f"   - {OUTPUT_MODEL} (modèle Word2Vec)")
print(f"   - {OUTPUT_CLASSIFIER} (classificateur)")
print(f"   - {OUTPUT_CONFIG} (configuration)")