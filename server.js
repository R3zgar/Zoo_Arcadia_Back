// Charger les variables d'environnement depuis .env.local
require('dotenv').config();
console.log('MONGODB_URI:', process.env.MONGODB_URI);

// Importation des bibliothèques nécessaires
const express = require('express');
const connectDB = require('./db'); // Importation du fichier de connexion à MongoDB

const app = express(); // Création de l'application Express

// Utiliser les middlewares (par exemple, pour analyser le JSON)
app.use(express.json());

// Connexion à MongoDB
connectDB();

// Route de base pour vérifier le fonctionnement du serveur
app.get('/', (req, res) => {
    res.send('Bienvenue à Zoo Arcadia!');
});

// Démarrer le serveur sur le port 3000
app.listen(3000, () => {
    console.log('Le serveur fonctionne sur le port 3000');
});
