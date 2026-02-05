# Architecture DDD - Domain-Driven Design

## Vue d'ensemble

Ce projet est une plateforme de suivi de records de jeux vidéo construite avec **Symfony** et organisée selon les principes du **Domain-Driven Design (DDD)**. Le code est structuré en **Bounded Contexts** autonomes, un **SharedKernel** transversal, et des couches DDD distinctes (Domain, Application, Infrastructure, Presentation).

```
src/
├── SharedKernel/                    # Code partagé entre tous les contextes
└── BoundedContext/                  # Contextes métier délimités
    ├── Article/
    ├── Forum/
    ├── Message/
    ├── User/
    └── VideoGamesRecords/
        ├── Core/                    # Domaine principal (joueurs, jeux, classements)
        ├── Igdb/                    # Intégration API IGDB
        ├── Badge/                   # Système de badges
        ├── Proof/                   # Vérification de preuves
        ├── Team/                    # Gestion des équipes
        └── Shared/                  # Traits et outils partagés VGR
```

**~516 fichiers PHP** repartis sur **10 bounded contexts** et un SharedKernel.

---

## Couches DDD

Chaque bounded context suit un découpage en 4 couches avec des dépendances unidirectionnelles :

```
Presentation  -->  Application  -->  Domain
      |                |
      v                v
Infrastructure  -->  Domain
```

| Couche | Responsabilite | Contenu typique |
|--------|---------------|-----------------|
| **Domain** | Logique metier pure | Entites, Value Objects, Enums, Events, Interfaces de Repository |
| **Application** | Orchestration | Services applicatifs, Commands CLI, Messages/Handlers CQRS, DTOs, Mappers |
| **Infrastructure** | Details techniques | Repositories Doctrine, Clients API, Event Listeners, DataProviders |
| **Presentation** | Interfaces utilisateur | Controllers (Web/Api), Admin Sonata, Forms, Templates Twig |

---

## Bounded Contexts

### User (43 fichiers)

Gestion des utilisateurs, authentification, groupes et audit de securite.

**Entites :** User, Group, SecurityEvent, RefreshToken

**Services :** UserManager, UserRegistrationService, SecurityHistoryManager

**Presentation :**
- Controllers Web : SecurityController, Register, GetUserAvatar, ResetPassword, Profile
- Controllers Api : endpoints utilisateur
- Admin Sonata : UserAdmin, GroupAdmin, SecurityEventAdmin

---

### Article (17 fichiers)

Gestion des articles/blog avec traductions et commentaires.

**Entites :** Article, ArticleTranslation, Comment

**Value Objects :** ArticleStatus

**Services :** ViewCounterService, ArticleAnalyticsService

**Presentation :**
- Controllers Web : ArticleController, TopNewsController
- Admin Sonata : ArticleAdmin, CommentAdmin

---

### Forum (26 fichiers)

Systeme de forum avec categories, topics et messages.

**Entites :** Forum, Category, Topic, TopicType, Message, ForumUserLastVisit, TopicUserLastVisit

**Value Objects :** ForumStatus, TopicStatus

**Services :** TopicReadService

**Presentation :**
- Controllers Web : ForumController, TopicController
- Admin Sonata : ForumAdmin, CategoryAdmin, TopicAdmin, MessageAdmin

---

### Message (9 fichiers)

Messagerie privee entre utilisateurs.

**Entites :** Message

**Value Objects :** MessageStatus

**Presentation :**
- Controllers Web : MessageController
- Admin Sonata : MessageAdmin

---

### VideoGamesRecords.Core (186 fichiers)

Domaine principal de la plateforme : joueurs, jeux, classements et records.

**Entites (23) :** Player, Game, Group, Chart, ChartType, ChartLib, PlayerChart, PlayerChartLib, Platform, Serie, Country, Rule, Discord, PlayerGame, PlayerGroup, PlayerPlatform, PlayerSerie, PlayerTopRanking, GameTopRanking, LostPosition, CountryTranslation, SerieTranslation, RuleTranslation

**Enums :** PlayerStatusEnum, PlayerChartStatusEnum, GameStatus, SerieStatus, GroupOrderBy

**Services :** PlayerRankingService, GameRankingService, GameManager, GameOfDayManager, LostPositionManager, ScoreManager, PlayerScoreFormService

**Patterns specifiques :**
- **CQRS** via Symfony Messenger (Messages + Handlers pour mise a jour des classements)
- **API Platform** avec pattern DTO/Mapper/DataProvider (Country, Game, Player)
- 11 DataProviders de classement

**Presentation :**
- 16 Controllers Web organises par sous-domaine (Game, Player, Ranking, Chart, Score, etc.)
- 13 Admin Sonata
- Controllers Api pour Platform, Game, Player

Documentation detaillee : `src/BoundedContext/VideoGamesRecords/Core/CLAUDE.md`

---

### VideoGamesRecords.Igdb (25 fichiers)

Integration avec l'API IGDB pour l'import de donnees de jeux video.

**Entites :** Game, Genre, Platform, PlatformType, PlatformLogo

**Services :** IgdbImportService

**Commands d'import :** ImportPlatformTypesCommand, ImportPlatformLogosCommand, ImportPlatformsCommand, ImportGenresCommand, ImportGamesCommand, SearchAndImportGamesCommand

**Infrastructure :** IgdbClient avec systeme d'Endpoints

**Presentation :** 5 Admin Sonata en lecture seule

Documentation detaillee : `src/BoundedContext/VideoGamesRecords/Igdb/CLAUDE.md`

---

### VideoGamesRecords.Badge (18 fichiers)

Systeme de badges et recompenses.

**Entites :** Badge, PlayerBadge, TeamBadge

**Value Objects :** BadgeType

**Events :** PlayerBadgeObtainedEvent

**Presentation :**
- Admin Sonata : BadgeAdmin, PlayerBadgeAdmin
- Controller : GetPicture (images de badges)

---

### VideoGamesRecords.Proof (42 fichiers)

Systeme de verification et de preuves pour les scores.

**Entites :** Proof, ProofRequest, Picture, Video, VideoComment, Tag

**Value Objects :** ProofStatus, PictureStatus, VideoStatus

**Events :** ProofVerifiedEvent, ProofRequestCreatedEvent

**Services :** VideoRecommendationService, VideoRelevanceScorer

**Presentation :**
- Admin Sonata : ProofAdmin, ProofRequestAdmin, PictureAdmin, VideoAdmin, TagAdmin
- Controllers Api et Web pour la gestion des preuves
- Composants Twig dedies

---

### VideoGamesRecords.Team (42 fichiers)

Gestion des equipes de joueurs.

**Entites :** Team, TeamChart, TeamGame, TeamGroup, TeamSerie, TeamRequest

**Value Objects :** TeamStatus, TeamRequestStatus

**Patterns :** CQRS via Symfony Messenger pour les mises a jour d'equipe

**Presentation :**
- Admin Sonata : TeamAdmin, TeamRequestAdmin
- Controllers Web : Team, Ranking, Profile, Avatar

---

### VideoGamesRecords.Shared (60 fichiers)

Code partage entre les sous-contextes VideoGamesRecords (traits, outils, composants).

**Domain :**
- Contracts/Ranking : interfaces de classement
- Traits/Entity/Player : PlayerTrait, PlayerPropertiesTrait, PlayerMethodsTrait, etc.
- Traits/Entity/Game : GameTrait, GamePropertiesTrait, GameMethodsTrait
- Tools : classes utilitaires

**Presentation :**
- LeaderboardController
- Composants Twig partages (leaderboard, components)

---

## SharedKernel (48 fichiers)

Code transversal partage entre tous les bounded contexts.

### Domain
- **Entity Traits :** TimestampableTrait, TranslatableTrait, CurrentLocaleTrait
- **Contracts :** interfaces partagees
- **Security :** SecurityEventTypeEnum
- **Events :** evenements de domaine partages
- **Exceptions :** exceptions partagees

### Infrastructure
- **Doctrine :** repositories de base, event listeners (User, Game)
- **EventSubscriber/Notify :** subscribers de notification (ProofRequest, PlayerChart, Badge, Proof)
- **Messaging :** entites de messagerie
- **FileSystem/Manager :** gestion de fichiers
- **Messenger/Middleware :** middleware Symfony Messenger
- **DependencyInjection/Compiler :** compiler passes

### Presentation
- **BaseAdmin :** classe de base pour tous les Admin Sonata
- **AbstractLocalizedController :** controller de base avec gestion i18n
- **AbstractCRUDController :** controller CRUD de base pour l'admin
- **Controllers Web :** HomeController, RootController, StaticController
- **Form/Type :** types de formulaire partages
- **Twig :** extensions et composants
- **Resources/views :** layouts admin, composants reutilisables, pages statiques

### Resources
- **translations/** : traductions partagees
- **img/gamercard/** : images de gamercard
- **fonts/** : polices partagees

---

## Patterns architecturaux

### CQRS (Command Query Responsibility Segregation)

Utilise dans les contextes **Core** et **Team** via Symfony Messenger.

```
Message (Command/Query)  -->  MessageHandler  -->  Domain Service/Repository
```

Exemples : `UpdatePlayerRank`, `UpdatePlayerGameRank` avec leurs handlers associes.

### API Platform avec DTOs

Pattern Entity-Mapper-DTO-DataProvider dans le contexte **Core**.

```
Entity  -->  Mapper  -->  DTO  -->  DataProvider  -->  API Response
```

Applique pour Country, Game et Player.

### Event-Driven Architecture

Communication inter-contextes via des evenements de domaine :

```
Contexte A  --DomainEvent-->  SharedKernel EventSubscriber  -->  Contexte B
```

Exemples : `PlayerBadgeObtainedEvent`, `ProofVerifiedEvent`, `LostPositionEvent`.

### Repository Pattern

Interface definie dans la couche Domain, implementation dans Infrastructure :

```
Domain/Repository/FooRepositoryInterface.php       # Contrat
Infrastructure/Doctrine/Repository/FooRepository.php  # Implementation
```

### Admin Pattern (Sonata Admin)

`BaseAdmin` dans le SharedKernel, specialise dans chaque contexte :

```
SharedKernel/Presentation/Admin/BaseAdmin.php       # Classe de base
BoundedContext/*/Presentation/Admin/*Admin.php       # Specialisations
```

---

## Communication entre contextes

Les bounded contexts sont isoles. La communication se fait via :

1. **Events de domaine** : un contexte emet un evenement, un subscriber dans le SharedKernel ou un autre contexte le consomme
2. **SharedKernel** : code commun (traits, interfaces, enums) partage sans couplage direct
3. **Traits partages (VGR.Shared)** : traits d'entites reutilises par les sous-contextes VideoGamesRecords

Les dependances directes entre contextes sont evitees.

---

## Tests

Les tests utilisent **Foundry** (zenstruck/foundry) pour les factories et stories :

| Contexte | Factories | Stories |
|----------|-----------|---------|
| User | UserFactory, GroupFactory | UserStory |
| VideoGamesRecords.Core | PlayerFactory, GameFactory, etc. | DefaultPlayerStory, DefaultGameStory |
| VideoGamesRecords.Igdb | GameFactory, GenreFactory, etc. | IgdbStory |
| VideoGamesRecords.Badge | BadgeFactory, PlayerBadgeFactory | BadgeStory |

---

## Ajout d'un nouveau contexte

1. Creer la structure sous `src/BoundedContext/NouveauContexte/` :

```
src/BoundedContext/NouveauContexte/
├── Domain/
│   ├── Entity/
│   └── Repository/
├── Application/
│   └── Service/
├── Infrastructure/
│   └── Doctrine/
│       └── Repository/
└── Presentation/
    ├── Admin/
    ├── Web/Controller/
    └── Resources/views/
```

2. Ajouter le namespace Twig dans `config/packages/twig.yaml` :

```yaml
twig:
    paths:
        '%kernel.project_dir%/src/BoundedContext/NouveauContexte/Resources/views': 'NouveauContexte'
```

3. Optionnel : ajouter un `CLAUDE.md` dans le contexte pour documenter les specificites.