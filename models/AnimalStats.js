const mongoose = require('mongoose');

// Schéma pour le modèle AnimalStats, qui stocke le nom de l'animal et son nombre de vues
const AnimalStatsSchema = new mongoose.Schema({
    animal_name: { type: String, required: true, unique: true }, // Nom unique pour chaque animal
    view_count: { type: Number, default: 0 } // Compteur de vues par défaut à 0
});

// Exportation du modèle pour l'utiliser dans d'autres parties de l'application
module.exports = mongoose.model('AnimalStats', AnimalStatsSchema, 'animal_stats');
