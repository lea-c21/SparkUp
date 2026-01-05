#!/usr/bin/env python3
"""
predict_message.py - Predice se un messaggio è offensivo usando Word2Vec

Usage: python3 predict_message.py "messaggio da analizzare"
"""

import sys
import json
import re
import os
import numpy as np

# Stopwords (stesse di PHP)
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
    """Tokenizza il testo rimuovendo stopwords"""
    words = re.findall(r'\b[a-z]+\b', text.lower())
    return [w for w in words if w not in STOPWORDS and len(w) > 2]

def text_to_vector(tokens, model):
    """Converte un testo tokenizzato in vettore (media degli embeddings)"""
    vectors = []
    for word in tokens:
        if word in model.wv:
            vectors.append(model.wv[word])
    
    if len(vectors) == 0:
        return np.zeros(model.vector_size)
    
    return np.mean(vectors, axis=0)

def predict_hate(message):
    """
    Predice se un messaggio è hate speech
    
    Returns:
        dict: {
            'success': bool,
            'is_hate': bool,
            'confidence': float (0-1),
            'tokens': list,
            'error': str (se success=False)
        }
    """
    try:
        # Import pesanti solo quando necessario
        from gensim.models import Word2Vec
        import pickle
        
        # Percorsi file (relativi allo script)
        script_dir = os.path.dirname(os.path.abspath(__file__))
        model_path = os.path.join(script_dir, 'word2vec_model.bin')
        classifier_path = os.path.join(script_dir, 'hate_classifier.pkl')
        
        # Verifica esistenza file
        if not os.path.exists(model_path):
            return {
                'success': False,
                'error': f'File non trovato: {model_path}'
            }
        
        if not os.path.exists(classifier_path):
            return {
                'success': False,
                'error': f'File non trovato: {classifier_path}'
            }
        
        # Carica modelli (questo può richiedere tempo)
        model = Word2Vec.load(model_path)
        
        with open(classifier_path, 'rb') as f:
            classifier = pickle.load(f)
        
        # Tokenizza messaggio
        tokens = tokenize(message)
        
        # Se non ci sono parole significative, non è hate
        if len(tokens) == 0:
            return {
                'success': True,
                'is_hate': False,
                'confidence': 0.0,
                'tokens': [],
                'reason': 'No significant words'
            }
        
        # Converti in vettore
        vector = text_to_vector(tokens, model).reshape(1, -1)
        
        # Predizione
        prediction = classifier.predict(vector)[0]
        probabilities = classifier.predict_proba(vector)[0]
        
        # probability[1] = probabilità che sia hate
        confidence = float(probabilities[1])
        is_hate = bool(prediction == 1)
        
        return {
            'success': True,
            'is_hate': is_hate,
            'confidence': round(confidence, 4),
            'tokens': tokens[:10]  # Prime 10 parole per debug
        }
        
    except ImportError as e:
        return {
            'success': False,
            'error': f'Libreria mancante: {str(e)}'
        }
    except Exception as e:
        return {
            'success': False,
            'error': f'Errore: {str(e)}'
        }

def main():
    """Entry point dello script"""
    
    # Verifica argomenti
    if len(sys.argv) < 2:
        result = {
            'success': False,
            'error': 'Usage: python3 predict_message.py "message"'
        }
        print(json.dumps(result))
        sys.exit(1)
    
    # Leggi messaggio dagli argomenti
    message = sys.argv[1]
    
    # Predici
    result = predict_hate(message)
    
    # Output JSON
    print(json.dumps(result))
    
    # Exit code
    sys.exit(0 if result['success'] else 1)

if __name__ == '__main__':
    main()