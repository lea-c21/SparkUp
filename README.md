# SparkUp!

An e-commerce web application for selling fireworks, built with PHP, 
MySQL and integrated with Stripe for payment processing.

## Features

- **User authentication** — registration, login, session management
- **CSRF protection** — security tokens on all forms
- **Product catalog** — browse and purchase fireworks
- **Shopping cart** — add, remove, manage orders
- **Stripe payment integration** — secure checkout (test environment)
- **Order tracking** — order history per user
- **Real-time chat** — messaging between users via AJAX
- **Hate speech filter** — automatic detection and blocking of 
  offensive words using Word2Vec and a trained classifier
- **User moderation** — ban system (`malevole.html`)
- **Like/comment system** — social interaction on products

## Tech stack

- **Backend:** PHP
- **Database:** MySQL (via WAMP)
- **Frontend:** HTML, CSS, JavaScript, jQuery, AJAX
- **Machine Learning:** Python — Word2Vec, hate speech classifier
- **Payments:** Stripe API (test mode)
- **Security:** CSRF tokens, prepared statements (PDO)

## Security features

- CSRF attack detection and logging
- Password hashing
- PDO prepared statements to prevent SQL injection
- Input validation (client and server side)

## Project structure
```
├── api/                  # API endpoints
├── styles/               # CSS and JS files
├── images/               # Product images
├── bd.php                # Database connection
├── index.php             # Home page
├── acheter.php           # Purchase page
├── panier.php            # Shopping cart
├── paiement.php          # Stripe payment
├── chat.php              # Chat interface
├── chat_send.php         # Send messages
├── chat_load.php         # Load messages
├── hate_classifier.pkl   # Trained hate speech model
├── generate_word2vec.py  # Word2Vec model generation
├── generate_scoremap.py  # Score mapping
└── csrf.php              # CSRF protection
```

## Author

Individual project — Licence MIASHS, Université Paul-Valéry Montpellier (2025)
