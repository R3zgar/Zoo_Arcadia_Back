const express = require('express');
const AnimalStats = require('../models/AnimalStats'); // Modèle MongoDB pour les statistiques d'animaux
const router = express.Router();

// Endpoint pour incrémenter le nombre de vues d'un animal
router.post('/increment-view', async (req, res) => {
    const { animal_name } = req.body; // Récupérer le nom de l'animal depuis la requête
    try {
        const animal = await AnimalStats.findOneAndUpdate(
            { animal_name }, // Chercher l'animal par son nom
            { $inc: { view_count: 1 } }, // Incrémenter le compteur de vues de 1
            { new: true } // Créer l'entrée si elle n'existe pas
        );
        res.status(200).json({
            message: 'Nombre de vues mis à jour avec succès !',
            animal
        });
    } catch (error) {
        res.status(500).json({ message: 'Erreur lors de la mise à jour', error });
    }
});

// Exporter le routeur pour l'utiliser dans server.js
module.exports = router;
