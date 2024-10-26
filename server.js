// Charger les variables d'environnement depuis .env.local
require('dotenv').config();
console.log('MONGODB_URI:', process.env.MONGODB_URI);

// Importation des bibliothèques nécessaires
const express = require('express');
const connectDB = require('./db'); // Importation du fichier de connexion à MongoDB

const animalRoutes = require('./routes/animal'); // Importer les routes pour les statistiques d'animaux

const app = express(); // Création de l'application Express

// Utiliser les middlewares (par exemple, pour analyser le JSON)
app.use(express.json());

// Connexion à MongoDB
connectDB();

// Utiliser les routes sous le chemin /api/animals
app.use('/api/animals', animalRoutes);

// Route de base pour vérifier le fonctionnement du serveur
app.get('/', (req, res) => {
    res.send('Bienvenue à Zoo Arcadia!');
});

// Démarrer le serveur sur le port défini dans .env ou 3001 par défaut
const PORT = process.env.PORT || 3001;
app.listen(PORT, () => {
    console.log(`Le serveur fonctionne sur le port ${PORT}`);
});
