# Contexte VideoGamesRecords.Core

## Vue d'ensemble

Le contexte **VideoGamesRecords.Core** est le coeur métier de l'application. Il gère la structure complète des jeux vidéo, des classements de joueurs, et des systèmes de scores/preuves. C'est le domaine principal du système avec des relations complexes et une logique métier riche.

## Fonctionnalités

### Gestion des Jeux
- **Game** : Jeux vidéo avec métadonnées complètes
- **Group** : Groupes de classements (ex: "Arcade Mode", "Story Mode")
- **Chart** : Classements spécifiques avec variantes
- **Serie** : Séries de jeux (ex: "Mario", "Zelda")
- **Platform** : Plateformes de jeu (consoles, PC, mobile)

### Système de Scores
- **PlayerChart** : Scores des joueurs sur les classements
- **ChartLib/PlayerChartLib** : Variantes de scores
- **Proof** : Système de preuves (lié au contexte Proof)

### Systèmes de Ranking
- Ranking global par points
- Ranking médailles (1ers)
- Ranking coupes (1ers par jeu)
- Ranking badges
- Ranking preuves
- Ranking pays

### Statistiques Joueurs
- **PlayerGame** : Stats agrégées par jeu
- **PlayerGroup** : Stats agrégées par groupe
- **PlayerPlatform** : Stats agrégées par plateforme
- **PlayerSerie** : Stats agrégées par série
- **PlayerTopRanking** : Historique des classements

## Structure du Contexte

```
src/BoundedContext/VideoGamesRecords/Core/
├── Domain/
│   ├── Entity/                     # 23 entités métier
│   │   ├── Player.php             # Joueur
│   │   ├── Game.php               # Jeu vidéo
│   │   ├── Group.php              # Groupe de classements
│   │   ├── Chart.php              # Classement
│   │   ├── PlayerChart.php        # Score joueur
│   │   ├── Platform.php           # Plateforme
│   │   ├── Serie.php              # Série de jeux
│   │   ├── Rule.php               # Règles
│   │   ├── Country.php            # Pays
│   │   ├── Discord.php            # Serveurs Discord
│   │   └── ...                    # Entités de relations et stats
│   ├── Event/                      # Events métier
│   │   ├── LostPositionEvent.php
│   │   └── Admin/AdminPlayerChartUpdated.php
│   └── ValueObject/                # Enums et Value Objects
│       ├── PlayerStatusEnum.php
│       ├── PlayerChartStatusEnum.php
│       ├── GameStatus.php
│       ├── SerieStatus.php
│       └── GroupOrderBy.php
├── Application/
│   ├── Command/                    # CLI commands
│   │   ├── GenerateRankingsCommand.php
│   │   ├── UpdatePlayerChartRankCommand.php
│   │   └── IgdbGameMatchingCommand.php
│   ├── Service/                    # Services applicatifs
│   │   ├── PlayerRankingService.php
│   │   └── GameRankingService.php
│   ├── Manager/                    # Business managers
│   │   ├── GameManager.php
│   │   ├── GameOfDayManager.php
│   │   ├── LostPositionManager.php
│   │   └── ScoreManager.php
│   ├── DataProvider/               # Data providers pour rankings
│   │   └── Ranking/
│   │       ├── PlayerRankingProvider.php
│   │       ├── PlayerGameRankingProvider.php
│   │       ├── GameRankingProvider.php
│   │       └── ...
│   ├── Message/                    # Messages CQRS
│   │   └── Player/
│   │       ├── UpdatePlayerRank.php
│   │       ├── UpdatePlayerGameRank.php
│   │       └── ...
│   └── MessageHandler/             # Handlers
│       └── Player/
│           ├── UpdatePlayerRankHandler.php
│           └── ...
├── Infrastructure/
│   ├── Doctrine/
│   │   ├── Repository/             # 27 repositories
│   │   └── EventListener/          # 7 Doctrine listeners
│   ├── Security/
│   │   └── UserProvider.php
│   ├── EventListener/
│   │   └── AuthenticationSuccessListener.php
│   ├── EventSubscriber/
│   │   └── LostPositionSubscriber.php
│   └── DataTransformer/
│       └── UserToPlayerTransformer.php
├── Presentation/
│   ├── Admin/                      # 13 Admin Sonata
│   ├── Web/
│   │   └── Controller/             # 16 Web controllers
│   ├── Form/                       # Formulaires
│   ├── Block/                      # Block services
│   └── Resources/
│       ├── views/                  # Templates Twig
│       └── translations/           # i18n
└── Tests/
```

## Entités Principales

### Player (Joueur)
```php
// Propriétés clés
- id: Identifiant unique
- pseudo: Pseudo unique (3-50 caractères)
- userId: Relation avec User (contexte User)
- avatar: Fichier avatar (défaut: "default.jpg")
- status: PlayerStatusEnum (Member, Admin, Moderator, etc.)
- slug: Généré automatiquement

// Relations principales
- playerCharts: OneToMany -> PlayerChart
- playerGame: OneToMany -> PlayerGame
- team: ManyToOne -> Team (contexte Team)
- friends: ManyToMany -> Player (auto-relation)

// Statistiques de ranking
- rankProof, rankCountry, rankPointChart, rankPointGame
- rankMedal, rankCup, rankBadge
- nbChartMax, nbChartWithPlatform

// Méthodes de permissions
- isAdmin(), isModerator()
- canManageProofs(), canManageGames()
```

### Game (Jeu)
```php
// Propriétés clés
- id: Identifiant unique
- libGameEn, libGameFr: Noms bilingues
- status: GameStatus (CREATED, ADD_SCORE, ADD_PICTURE, COMPLETED, ACTIVE, INACTIVE)
- publishedAt: Date de publication
- igdbGame: Relation avec IgdbGame (contexte Igdb)
- slug: Généré automatiquement

// Relations principales
- groups: OneToMany -> Group (cascade persist/remove)
- platforms: ManyToMany -> Platform
- badge: OneToOne -> Badge (contexte Badge)
- forum: OneToOne -> Forum (contexte Forum)
- rules: ManyToMany -> Rule
- playerGame: OneToMany -> PlayerGame

// Méthodes
- getName(locale): Retourne le nom selon la locale
- getUrl(): URL du jeu
- getGenres(), getReleaseDate(), getSummary(): Données IGDB
```

### Group (Groupe de classements)
```php
// Propriétés clés
- id: Identifiant unique
- libGroupEn, libGroupFr: Noms bilingues
- orderBy: GroupOrderBy (ordre d'affichage)
- slug: Généré automatiquement

// Relations
- game: ManyToOne -> Game
- charts: OneToMany -> Chart (cascade persist)
```

### Chart (Classement)
```php
// Propriétés clés
- id: Identifiant unique
- libChartEn, libChartFr: Noms bilingues
- isProofVideoOnly: Boolean (preuves vidéo obligatoires)
- slug: Généré automatiquement

// Relations
- group: ManyToOne -> Group
- libs: OneToMany -> ChartLib
- playerCharts: OneToMany -> PlayerChart
- proofs: OneToMany -> Proof (contexte Proof)

// Shortcuts en cache
- playerChart1: Référence au classé #1
- playerChartP: Référence au chart du joueur courant
```

### PlayerChart (Score joueur)
```php
// Propriétés clés
- id: Identifiant unique
- rank: Position dans le classement
- pointChart: Points obtenus
- pointPlatform: Points plateforme
- isTopScore: Boolean (premier score)
- status: PlayerChartStatusEnum (NONE, PROVISIONAL, VERIFIED, REJECTED, INVESTIGATION)

// Relations
- chart: ManyToOne -> Chart (EAGER)
- player: ManyToOne -> Player
- proof: OneToOne -> Proof
- platform: ManyToOne -> Platform
- libs: OneToMany -> PlayerChartLib

// Contraintes
- Unique sur (player, chart)
- Index sur rank, pointChart, isTopScore, lastUpdate
```

## Value Objects et Enums

### PlayerStatusEnum
```php
// 15 statuts avec permissions
- Member, Admin, Moderator, ...
- Méthodes: getLabel(), getFrenchLabel(), getClass()
- Permissions: isAdmin(), isModerator(), canManageProofs(), canManageGames()
```

### PlayerChartStatusEnum
```php
// États des scores
- NONE: Pas de preuve
- PROVISIONAL: En attente de vérification
- VERIFIED: Vérifié
- REJECTED: Rejeté
- INVESTIGATION: En investigation
```

### GameStatus
```php
// États d'un jeu
- CREATED: Créé
- ADD_SCORE: Ajout de scores en cours
- ADD_PICTURE: Ajout d'images en cours
- COMPLETED: Complété
- ACTIVE: Actif
- INACTIVE: Inactif
```

## Commandes CLI

### GenerateRankingsCommand
```bash
# Générer les classements
php bin/console vgr:rankings:generate game|player week|month|year [--year=X] [--month=X] [--week=X] [--clean]

# Exemples
php bin/console vgr:rankings:generate player week
php bin/console vgr:rankings:generate game month --year=2024 --month=6
```

### UpdatePlayerChartRankCommand
```bash
# Mise à jour du ranking des PlayerChart
php bin/console vgr:player-chart:update-rank
```

### IgdbGameMatchingCommand
```bash
# Appairage des jeux avec IGDB
php bin/console vgr:igdb:match-games
```

## Architecture CQRS

### Messages
Le contexte utilise Symfony Messenger pour les mises à jour asynchrones :

```php
// Messages disponibles
- UpdatePlayerRank
- UpdatePlayerChartRank
- UpdatePlayerCountryRank
- UpdatePlayerGameRank
- UpdatePlayerGroupRank
- UpdatePlayerPlatformRank
- UpdatePlayerSerieRank
```

### Handlers
Chaque message a son handler correspondant dans `Application/MessageHandler/Player/`.

## Data Providers

11 Data Providers pour les rankings :

| Provider | Rôle |
|----------|------|
| PlayerRankingProvider | Rankings généraux des joueurs |
| PlayerChartRankingProvider | Rankings par classement |
| PlayerGameRankingProvider | Rankings par jeu |
| PlayerGroupRankingProvider | Rankings par groupe |
| PlayerPlatformRankingProvider | Rankings par plateforme |
| PlayerSerieRankingProvider | Rankings par série |
| PlayerCountryRankingProvider | Rankings par pays |
| GameRankingProvider | Rankings des jeux |
| TopScoreProvider | Les meilleurs scores |

## Doctrine Event Listeners

7 listeners pour synchroniser les données automatiquement :

| Listener | Rôle |
|----------|------|
| PlayerListener | Synchronisation des données joueur |
| GameListener | Synchronisation des données jeu |
| GroupListener | Synchronisation des données groupe |
| ChartListener | Synchronisation des données classement |
| PlayerChartListener | Calculs lors des mises à jour de scores |
| SerieListener | Synchronisation des données série |

## Interfaces Admin (Sonata)

13 admin classes pour le backoffice :

- GameAdmin, GroupAdmin, ChartAdmin
- PlayerAdmin, PlayerChartAdmin
- PlatformAdmin, SerieAdmin
- RuleAdmin, CountryAdmin, DiscordAdmin
- ChartLibAdmin, ChartTypeAdmin, PlayerChartLibAdmin

## Web Controllers

### Game Controllers
- `Game/Show.php` : Affiche un jeu
- `Game/GameOfDay.php` : Jeu du jour
- `Game/List/ByLetter.php` : Liste par lettre

### Player Controllers
- `Player/Profile/Overview.php` : Vue d'ensemble du profil
- `Player/Gamercard/Classic.php` : Carte classique
- `Player/Gamercard/Mini.php` : Carte minimale

### Ranking Controllers
- `Ranking/TopPlayersController.php` : Classement des meilleurs joueurs

### Autres
- `Platform/Index.php`, `Platform/Show.php`
- `Chart/Show.php`, `Group/Show.php`
- `PictureController.php`

## Relations Cross-Context

### Avec User
- Player.userId -> User
- AuthenticationSuccessListener synchronise à la connexion
- UserProvider récupère Player depuis User

### Avec Igdb
- Game.igdbGame -> IgdbGame
- Méthodes: getGenres(), getReleaseDate(), getSummary()

### Avec Badge
- Game.badge -> Badge
- Platform.badge -> Badge
- Serie.badge -> Badge

### Avec Proof
- Chart -> Proof (OneToMany)
- PlayerChart -> Proof (OneToOne)

### Avec Team
- Player.team -> Team
- Game -> TeamGame, Serie -> TeamSerie

### Avec Forum
- Game.forum -> Forum

## Flux de Données Clés

### Création d'un Score

```
1. Joueur soumet un score
2. PlayerChart créé avec status PROVISIONAL
3. PlayerChartListener déclenche les calculs
4. LostPositionEvent dispatché si perte de position
5. Messages de mise à jour envoyés à Messenger
6. MessageHandlers traitent les updates async
7. DataProviders recalculent les rankings
```

### Génération des Rankings

```
1. Commande CLI lancée (cron)
2. Service GameRankingService ou PlayerRankingService
3. Requête sur les entités avec tri
4. Création/Mise à jour des TopRanking
5. DataProviders fournissent les données
```

## Hiérarchie des Données

```
Game -> Group -> Chart -> PlayerChart <- Player
  |                                        |
  v                                        v
Platform                              PlayerGame
  |                                   PlayerGroup
  v                                   PlayerPlatform
PlayerPlatform                        PlayerSerie
```

## Traits Utilisés (du contexte Shared)

Le contexte utilise massivement les traits pour les statistiques :

**Statistiques de base :**
- NbChartTrait, NbGameTrait, NbPlayerTrait, NbTeamTrait
- NbPostTrait, NbVideoTrait

**Points :**
- PointChartTrait, PointGameTrait, PointBadgeTrait

**Rankings :**
- RankPointChartTrait, RankPointGameTrait, RankPointBadgeTrait
- ChartRank0-5Trait, GameRank0-3Trait
- RankCupTrait, RankMedalTrait

**Moyennes :**
- AverageChartRankTrait, AverageGameRankTrait

**Autres :**
- PictureTrait, IsDlcTrait, IsRankTrait
- LastUpdateTrait, NbEqualTrait

## Performance

### Indexation Doctrine

**Player :**
- idx_point_game sur `point_game`
- idx_chart_rank sur `chart_rank0-3`
- idx_game_rank sur `game_rank0-3`

**PlayerChart :**
- idx_rank sur `rank`
- idx_point_chart sur `point_chart`
- idx_top_score sur `is_top_score`
- idx_last_update_player sur `last_update`, `player_id`
- idx_status sur `status`

### Stratégies de Chargement

- PlayerChart: fetch EAGER pour Chart
- Collections: EXTRA_LAZY pour éviter les N+1
- Two EntityManagers: principal + DWH pour analytics

## Bonnes Pratiques

### 1. Scores et Rankings
- Utiliser les Messages pour les mises à jour asynchrones
- Ne pas calculer les rankings en temps réel
- Utiliser les DataProviders pour l'affichage

### 2. Relations
- Toujours passer par les repositories pour les requêtes complexes
- Utiliser les index définis pour les filtres
- Préférer les jointures aux requêtes multiples

### 3. Events
- Dispatcher LostPositionEvent pour les pertes de position
- Écouter les events Doctrine pour la synchronisation
- Utiliser Messenger pour les traitements lourds

### 4. Sécurité
- Vérifier les permissions via PlayerStatusEnum
- Utiliser les voters Symfony pour les accès
- Valider les inputs via les contraintes Doctrine
