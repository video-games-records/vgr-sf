# Contexte VideoGamesRecords.GamingPlatform

## Vue d'ensemble

Permet aux joueurs de lier leurs comptes de plateformes de jeux vidéo (Steam, Xbox, etc.) à leur profil VGR, et d'afficher les informations de ces plateformes sur leur profil public.

## Architecture

Pattern **Provider** : une interface commune `PlatformProviderInterface`, une implémentation par plateforme.

```
GamingPlatform/
├── Domain/
│   ├── Entity/PlatformConnection.php       # Entité de liaison joueur ↔ plateforme
│   ├── Repository/PlatformConnectionRepositoryInterface.php
│   ├── Provider/PlatformProviderInterface.php  # Contrat commun
│   └── ValueObject/
│       ├── PlatformEnum.php               # steam | xbox | psn | epic | gog | battlenet | nintendo | retro_achievements
│       ├── PlatformIdentity.php           # externalId + username (retour OAuth)
│       └── PlatformProfileData.php        # Données à afficher sur le profil
├── Application/Service/
│   ├── LinkPlatformService.php            # Crée ou met à jour une liaison
│   ├── UnlinkPlatformService.php          # Supprime une liaison
│   └── PlatformProviderRegistry.php       # Trouve le bon provider par PlatformEnum
├── Infrastructure/
│   ├── Doctrine/Repository/PlatformConnectionRepository.php
│   └── Provider/
│       └── SteamProvider.php              # OpenID 2.0 + Steam Web API
└── Presentation/
    ├── Web/Controller/
    │   ├── ConnectController.php          # GET /{locale}/platform/{platform}/connect
    │   ├── CallbackController.php         # GET /{locale}/platform/{platform}/callback
    │   ├── DisconnectController.php       # POST /{locale}/platform/{platform}/disconnect
    │   └── LinkedAccountsController.php   # GET /profile/platforms (onglet profil)
    └── Resources/
        ├── translations/VgrGamingPlatform.{lang}.yml  # 8 langues
        └── views/
            ├── profile/linked_accounts.html.twig      # Page de gestion des liaisons
            └── player/_platform_connections.html.twig  # Widget lecture seule (profil public)
```

## Ajouter une nouvelle plateforme

1. Ajouter la valeur dans `PlatformEnum` + `isSupported(): true`
2. Créer `Infrastructure/Provider/XxxProvider.php` qui implémente `PlatformProviderInterface`
3. Taguer le service dans `config/services/vgr/gaming_platform.yaml` :
   ```yaml
   App\...\XxxProvider:
       tags: ['vgr.gaming_platform.provider']
   ```
4. Ajouter les traductions dans les 8 fichiers `VgrGamingPlatform.{lang}.yml`

## Variables d'environnement

| Variable | Description |
|---|---|
| `STEAM_API_KEY` | Clé API Steam (steamcommunity.com/dev/apikey) |

## Test en développement local (OAuth)

Steam et les autres providers OAuth/OpenID **rejettent `localhost`** comme domaine de callback. Il faut exposer le serveur local via ngrok :

```bash
make ngrok   # expose le port 8000
```

Ngrok affiche une URL publique HTTPS (`https://xxx.ngrok-free.app`). Copie-la dans `.env.local` :

```env
DEFAULT_URI=https://xxx.ngrok-free.app
```

Puis vide le cache :
```bash
make cache-clear
```

## Points techniques importants

### PHP dots → underscores
PHP convertit les `.` en `_` dans les clés de query string (`$_GET`). Les paramètres Steam OpenID (`openid.mode`, `openid.claimed_id`...) seraient corrompus si on utilisait `$request->query->all()`. Le `SteamProvider` parse le raw query string via `$request->getQueryString()` pour préserver les clés.

### Table SQL
```sql
player_platform_connection (
    id, player_id, platform VARCHAR(50), external_id VARCHAR(255),
    username VARCHAR(255), linked_at DATETIME
)
-- UNIQUE KEY sur (player_id, platform)
-- FK vers vgr_player(id) ON DELETE CASCADE
```
