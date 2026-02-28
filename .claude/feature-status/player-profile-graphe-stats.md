# Feature : player-profile-graphe-stats

## Objectif
Ajouter 2 graphiques Chart.js sur la page profil joueur (overview) :
1. **Médailles dans le temps** (line chart) - Évolution des médailles platinum/gold/silver/bronze au fil du temps
2. **Distribution des positions** (bar chart) - Nombre de records par position (1er, 2ème, ... 30+)

Les données proviennent du contexte DWH (table `dwh_player`). Les anciens controllers API (`GetPositions`, `GetMedalsByTime`) existent déjà dans `Dwh/Presentation/Api/Controller/Player/` mais n'ont pas de routes. Il faut créer de nouveaux controllers Web dans le contexte DWH avec des routes JSON, puis les appeler via des Stimulus controllers avec Chart.js.

## Migration en cours
Aucune pour l'instant (pas besoin de migration, les tables DWH existent déjà)

## Fichiers modifiés
- (à venir)

## État
- [x] Exploration du codebase terminée
- [ ] Installer Chart.js (npm)
- [ ] Créer les 2 controllers API dans DWH avec routes
- [ ] Créer les 2 Stimulus controllers (medals_chart + positions_chart)
- [ ] Modifier le template player profile overview pour ajouter les graphiques
- [ ] Ajouter les traductions
