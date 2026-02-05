# Schema Base de Donnees - Relations Doctrine

## Vue d'ensemble

La base de donnees compte environ **50 tables** reparties sur 6 prefixes correspondant aux bounded contexts :

| Prefixe | Contexte | Tables |
|---------|----------|--------|
| `pnu_` | User | 4 |
| `pna_` | Article | 3 |
| `pnf_` | Forum | 7 |
| `pnm_` | Message | 1 |
| `vgr_` | VideoGamesRecords (Core, Badge, Proof, Team) | ~30 |
| `igdb_` | VideoGamesRecords.Igdb | 5 |

---

## Diagramme des relations principales

```
                    ┌──────────┐
                    │ pnu_user │
                    └────┬─────┘
                         │
          ┌──────────────┼──────────────────┐
          │              │                  │
          ▼              ▼                  ▼
    ┌───────────┐  ┌───────────┐    ┌─────────────┐
    │vgr_player │  │pna_article│    │ pnf_topic   │
    └─────┬─────┘  └───────────┘    └─────────────┘
          │
    ┌─────┼──────────────┬──────────────┐
    │     │              │              │
    ▼     ▼              ▼              ▼
┌──────┐┌───────────┐┌──────────┐┌──────────┐
│ team ││player_game││player_   ││player_   │
│      ││           ││chart     ││badge     │
└──────┘└─────┬─────┘└────┬─────┘└──────────┘
              │            │
              ▼            ▼
        ┌──────────┐ ┌──────────┐
        │ vgr_game │ │vgr_chart │
        └────┬─────┘ └────┬─────┘
             │             │
             ▼             ▼
       ┌──────────┐  ┌──────────┐
       │vgr_group │  │vgr_proof │
       └──────────┘  └──────────┘
```

---

## 1. User (`pnu_`)

### pnu_user

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| username | string(100) | UNIQUE, NOT NULL |
| email | string(180) | UNIQUE, NOT NULL |
| enabled | bool | default: true |
| roles | json | |
| password | string(255) | |
| last_login | datetime | nullable |
| confirmation_token | string(255) | UNIQUE, nullable |
| password_requested_at | datetime | nullable |
| nb_connexion | int | default: 0 |
| nb_forum_message | int | default: 0 |
| avatar | string(255) | default: 'default.png' |
| comment | string(1000) | nullable |
| language | string(2) | default: 'en' |
| slug | string(128) | Gedmo slug |
| created_at | datetime | |
| updated_at | datetime | |

**Relations :**
- **ManyToMany** → `pnu_group` via `pnu_user_group` (user_id, group_id)

### pnu_group

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| name | string(100) | UNIQUE, NOT NULL |
| roles | json | |

### pnu_security_event

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| user_id | int | FK → pnu_user, NOT NULL, ON DELETE CASCADE |
| event_type | string(50) | |
| created_at | datetime | |
| event_data | json | nullable |
| ip_address | string(45) | nullable |
| user_agent | string(255) | nullable |

**Index :** `search_idx` (user_id, event_type, created_at)

### pnu_refresh_tokens

Herite de `Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken`.

---

## 2. Article (`pna_`)

### pna_article

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| author_id | int | FK → pnu_user, NOT NULL |
| status | enum (ArticleStatus) | |
| nb_comment | int | default: 0 |
| views | int | default: 0 |
| published_at | datetime | nullable |
| slug | string(255) | |
| created_at | datetime | |
| updated_at | datetime | |

**Relations :**
- **ManyToOne** → `pnu_user` (author_id), EAGER
- **OneToMany** → `pna_comment`
- **OneToMany** → `pna_article_translation` (cascade: persist, remove, orphanRemoval)

### pna_article_translation

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| translatable_id | int | FK → pna_article, NOT NULL, ON DELETE CASCADE |
| locale | string(5) | |
| title | string(255) | NOT NULL |
| content | text | NOT NULL |

**Unique :** (translatable_id, locale)

### pna_comment

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| article_id | int | FK → pna_article, NOT NULL |
| user_id | int | FK → pnu_user, NOT NULL |
| content | text | NOT NULL |
| created_at | datetime | |
| updated_at | datetime | |

---

## 3. Forum (`pnf_`)

### pnf_category

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| name | string(50) | NOT NULL |
| position | int | default: 0 |
| display_on_home | bool | default: true |
| created_at | datetime | |
| updated_at | datetime | |

**Relations :**
- **OneToMany** → `pnf_forum` (ORDER BY position ASC)

### pnf_forum

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| category_id | int | FK → pnf_category, nullable |
| lib_forum | string(255) | NOT NULL |
| lib_forum_fr | string(255) | nullable |
| position | int | default: 0 |
| status | string(20) | default: 'PUBLIC' |
| role | string(50) | nullable |
| nb_message | int | default: 0 |
| nb_topic | int | default: 0 |
| max_message_id | int | FK → pnf_message, nullable, ON DELETE SET NULL |
| slug | string(128) | Gedmo slug |
| created_at | datetime | |
| updated_at | datetime | |

**Index :** `idx_position`, `idx_lib_forum`

### pnf_topic

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| forum_id | int | FK → pnf_forum, NOT NULL |
| user_id | int | FK → pnu_user, NOT NULL |
| type_id | int | FK → pnf_topic_type, nullable |
| max_message_id | int | FK → pnf_message, nullable, ON DELETE SET NULL |
| name | string(255) | NOT NULL |
| nb_message | int | default: 0 |
| slug | string(255) | Gedmo slug |
| bool_archive | bool | default: false |
| created_at | datetime | |
| updated_at | datetime | |

### pnf_message

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| topic_id | int | FK → pnf_topic, NOT NULL |
| user_id | int | FK → pnu_user, NOT NULL |
| message | text | NOT NULL |
| position | int | default: 1 |
| created_at | datetime | |
| updated_at | datetime | |

### pnf_topic_type

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| name | string(30) | NOT NULL |
| position | int | default: 0 |

### pnf_forum_user_last_visit

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| user_id | int | FK → pnu_user, NOT NULL, ON DELETE CASCADE |
| forum_id | int | FK → pnf_forum, NOT NULL, ON DELETE CASCADE |
| last_visited_at | datetime | NOT NULL |

**Unique :** (user_id, forum_id)
**Index :** (user_id, last_visited_at), (forum_id, last_visited_at)

### pnf_topic_user_last_visit

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| user_id | int | FK → pnu_user, NOT NULL, ON DELETE CASCADE |
| topic_id | int | FK → pnf_topic, NOT NULL, ON DELETE CASCADE |
| last_visited_at | datetime | NOT NULL |
| is_notify | bool | default: false |

**Unique :** (user_id, topic_id)
**Index :** (user_id, last_visited_at), (topic_id, last_visited_at)

---

## 4. Message (`pnm_`)

### pnm_message

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| sender_id | int | FK → pnu_user, NOT NULL |
| recipient_id | int | FK → pnu_user, NOT NULL |
| object | string(255) | NOT NULL |
| message | text | nullable |
| type | string(50) | default: 'DEFAULT' |
| is_opened | bool | default: false |
| is_deleted_sender | bool | default: false |
| is_deleted_recipient | bool | default: false |
| created_at | datetime | |
| updated_at | datetime | |

**Index :**
- `idx_inbox` (recipient_id, is_deleted_recipient)
- `idx_outbox` (sender_id, is_deleted_sender)
- `idx_newMessage` (recipient_id, is_opened)
- `idx_inbox_type` (recipient_id, is_deleted_recipient, type)
- `idx_outbox_type` (sender_id, is_deleted_sender, type)
- `idx_inbox_opened` (recipient_id, is_deleted_recipient, is_opened)
- `idx_outbox_opened` (sender_id, is_deleted_sender, is_opened)

---

## 5. VideoGamesRecords.Core (`vgr_`)

### vgr_player

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| user_id | int | NOT NULL |
| team_id | int | FK → vgr_team, nullable, ON DELETE SET NULL |
| pseudo | string(50) | UNIQUE, NOT NULL |
| avatar | string(100) | default: 'default.jpg' |
| gamer_card | string(50) | nullable |
| rank_proof | int | default: 0 |
| rank_country | int | default: 0 |
| nb_chart_max | int | |
| nb_chart_with_platform | int | |
| nb_chart_disabled | int | |
| last_login | datetime | nullable |
| nb_connexion | int | default: 0 |
| bool_maj | bool | default: false |
| has_donate | bool | default: false |
| status | enum (PlayerStatusEnum) | |
| slug | string(128) | Gedmo slug |
| created_at | datetime | |
| updated_at | datetime | |

**Index :** `idx_point_game`, `idx_chart_rank`, `idx_game_rank`

**Relations :**
- **ManyToOne** → `vgr_team` (team_id)
- **OneToMany** → `vgr_player_game`, `vgr_player_chart`
- **ManyToMany** → `vgr_player` (self, amis) via `vgr_friend` (player_id, friend_id)

### vgr_game

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| igdb_game_id | int | FK → igdb_game, nullable |
| serie_id | int | FK → vgr_serie, nullable |
| badge_id | int | FK → vgr_badge, NOT NULL |
| forum_id | int | FK → pnf_forum, NOT NULL |
| last_score_id | int | FK → vgr_player_chart, nullable |
| lib_game_en | string(255) | NOT NULL |
| lib_game_fr | string(255) | NOT NULL |
| download_url | string(255) | nullable |
| status | string(30) | default: 'CREATED' |
| published_at | datetime | nullable |
| slug | string(255) | Gedmo slug |
| created_at | datetime | |
| updated_at | datetime | |

**Index :** `idx_lib_game_fr`, `idx_lib_game_en`, `status`

**Relations :**
- **ManyToOne** → `igdb_game`, `vgr_serie`
- **OneToOne** → `vgr_badge` (cascade: persist), `pnf_forum` (cascade: persist)
- **OneToMany** → `vgr_group` (cascade: persist, remove, orphanRemoval)
- **ManyToMany** → `vgr_platform` via `vgr_game_platform`
- **ManyToMany** → `vgr_rule` via `vgr_rule_game`
- **ManyToMany** → `vgr_discord` via `vgr_game_discord`
- **OneToMany** → `vgr_player_game`, `vgr_team_game`

### vgr_serie

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| badge_id | int | FK → vgr_badge, nullable, ON DELETE SET NULL |
| lib_serie | string(255) | NOT NULL |
| status | string | default: 'INACTIVE' |
| slug | string(128) | Gedmo slug |
| created_at | datetime | |
| updated_at | datetime | |

**Relations :**
- **OneToMany** → `vgr_game`
- **OneToOne** → `vgr_badge` (cascade: persist, remove)
- **OneToMany** → `vgr_serie_translation` (cascade: persist, remove, orphanRemoval)

### vgr_serie_translation

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| translatable_id | int | FK → vgr_serie, NOT NULL, ON DELETE CASCADE |
| locale | string(5) | |
| description | text | |

**Unique :** (translatable_id, locale)

### vgr_platform

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| badge_id | int | FK → vgr_badge, nullable |
| name | string(100) | NOT NULL |
| picture | string(255) | nullable |
| status | string(30) | default: 'INACTIF' |
| slug | string(255) | Gedmo slug |

**Relations :**
- **ManyToMany** → `vgr_game` (mappedBy)
- **OneToOne** → `vgr_badge` (cascade: persist)

### vgr_group

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| game_id | int | FK → vgr_game, NOT NULL |
| lib_group_en | string(255) | NOT NULL |
| lib_group_fr | string(255) | NOT NULL |
| order_by | string(30) | default: 'NAME' |
| slug | string(128) | Gedmo slug |
| created_at | datetime | |
| updated_at | datetime | |

**Index :** `idx_lib_group_fr`, `idx_lib_group_en`

**Relations :**
- **ManyToOne** → `vgr_game`
- **OneToMany** → `vgr_chart` (cascade: persist)

### vgr_chart

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| group_id | int | FK → vgr_group, NOT NULL, ON DELETE CASCADE |
| lib_chart_en | string(255) | NOT NULL |
| lib_chart_fr | string(255) | NOT NULL |
| is_proof_video_only | bool | default: false |
| slug | string(255) | Gedmo slug |
| created_at | datetime | |
| updated_at | datetime | |

**Relations :**
- **ManyToOne** → `vgr_group`
- **OneToMany** → `vgr_chartlib` (cascade: persist, remove, orphanRemoval)
- **OneToMany** → `vgr_player_chart` (fetch: EXTRA_LAZY)
- **OneToMany** → `vgr_proof` (cascade: persist, remove, orphanRemoval)
- **OneToMany** → `vgr_lostposition`

### vgr_charttype

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| name | string(100) | nullable |
| mask | string(100) | NOT NULL |
| order_by | string(10) | default: 'ASC' |

### vgr_chartlib

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| chart_id | int | FK → vgr_chart, NOT NULL, ON DELETE CASCADE |
| type_id | int | FK → vgr_charttype, NOT NULL |
| name | string(100) | nullable |
| created_at | datetime | |
| updated_at | datetime | |

### vgr_player_chart

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| chart_id | int | FK → vgr_chart, NOT NULL, ON DELETE CASCADE |
| player_id | int | FK → vgr_player, NOT NULL |
| proof_id | int | FK → vgr_proof, nullable, ON DELETE SET NULL |
| platform_id | int | FK → vgr_platform, nullable |
| rank | int | nullable |
| point_chart | int | default: 0 |
| point_platform | int | default: 0 |
| is_top_score | bool | default: false |
| date_investigation | date | nullable |
| status | enum (PlayerChartStatusEnum) | |
| created_at | datetime | |
| updated_at | datetime | |

**Unique :** (player_id, chart_id)
**Index :** `idx_rank`, `idx_point_chart`, `idx_top_score`, `idx_last_update_player`, `idx_player_chart_last_update`, `idx_status`

**Relations :**
- **ManyToOne** → `vgr_chart` (EAGER), `vgr_player`, `vgr_platform`
- **OneToOne** → `vgr_proof` (inversedBy)
- **OneToMany** → `vgr_player_chartlib` (cascade: persist, remove, EAGER, orphanRemoval)

### vgr_player_chartlib

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| player_chart_id | int | FK → vgr_player_chart, NOT NULL, ON DELETE CASCADE |
| chartlib_id | int | FK → vgr_chartlib, NOT NULL |
| value | bigint | NOT NULL |

**Unique :** (player_chart_id, chartlib_id)

### vgr_player_game

| Colonne | Type | Contraintes |
|---------|------|-------------|
| player_id | int | PK, FK → vgr_player, ON DELETE CASCADE |
| game_id | int | PK, FK → vgr_game, ON DELETE CASCADE |
| point_chart_without_dlc | int | default: 0 |
| point_game | int | default: 0 |
| last_update | datetime | NOT NULL |

**PK composite :** (player_id, game_id)
**Index :** `idx_last_update` (player_id, last_update)

### vgr_player_group

| Colonne | Type | Contraintes |
|---------|------|-------------|
| player_id | int | PK, FK → vgr_player, ON DELETE CASCADE |
| group_id | int | PK, FK → vgr_group, ON DELETE CASCADE |

**PK composite :** (player_id, group_id)

### vgr_player_platform

| Colonne | Type | Contraintes |
|---------|------|-------------|
| player_id | int | PK, FK → vgr_player, ON DELETE CASCADE |
| platform_id | int | PK, FK → vgr_platform |
| rank_point_platform | int | default: 0 |
| point_platform | int | default: 0 |

**PK composite :** (player_id, platform_id)

### vgr_player_serie

| Colonne | Type | Contraintes |
|---------|------|-------------|
| player_id | int | PK, FK → vgr_player, ON DELETE CASCADE |
| serie_id | int | PK, FK → vgr_serie, ON DELETE CASCADE |
| point_chart_without_dlc | int | default: 0 |
| point_game | int | default: 0 |

**PK composite :** (player_id, serie_id)

### vgr_country

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| badge_id | int | FK → vgr_badge, nullable |
| code_iso2 | string(2) | NOT NULL |
| code_iso3 | string(3) | NOT NULL |
| code_iso_numeric | int | NOT NULL |
| slug | string(255) | NOT NULL |

**Relations :**
- **OneToOne** → `vgr_badge` (cascade: persist)
- **OneToMany** → `vgr_country_translation` (cascade: persist, remove, EAGER, orphanRemoval)

### vgr_country_translation

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| translatable_id | int | FK → vgr_country, NOT NULL, ON DELETE CASCADE |
| locale | string(5) | |
| name | string(255) | NOT NULL |

### vgr_rule

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| player_id | int | FK → vgr_player, nullable |
| name | string(100) | UNIQUE, NOT NULL |
| created_at | datetime | |
| updated_at | datetime | |

**Relations :**
- **ManyToMany** → `vgr_game` (mappedBy)
- **OneToMany** → `vgr_rule_translation` (cascade: persist, remove, orphanRemoval)

### vgr_rule_translation

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| translatable_id | int | FK → vgr_rule, NOT NULL, ON DELETE CASCADE |
| locale | string(5) | |
| content | text | NOT NULL |

**Unique :** (translatable_id, locale)

### vgr_discord

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| name | string(255) | NOT NULL |
| url | string(500) | NOT NULL |
| created_at | datetime | |
| updated_at | datetime | |

**Relations :**
- **ManyToMany** → `vgr_game` via `vgr_game_discord`

### vgr_lostposition

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| player_id | int | FK → vgr_player, NOT NULL, ON DELETE CASCADE |
| chart_id | int | FK → vgr_chart, NOT NULL, ON DELETE CASCADE |
| old_rank | int | default: 0 |
| new_rank | int | default: 0 |
| created_at | datetime | |
| updated_at | datetime | |

### vgr_player_top_ranking

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| player_id | int | FK → vgr_player, NOT NULL, ON DELETE CASCADE |
| rank | int | NOT NULL |
| nb_post | int | default: 0 |
| position_change | int | nullable |
| period_type | string(10) | NOT NULL ('week', 'month', 'year') |
| period_value | string(20) | NOT NULL |
| created_at | datetime | |
| updated_at | datetime | |

**Index :** (player_id, period_type, period_value), (period_type, period_value, rank)

### vgr_game_top_ranking

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| game_id | int | FK → vgr_game, NOT NULL, ON DELETE CASCADE |
| rank | int | NOT NULL |
| nb_post | int | default: 0 |
| position_change | int | nullable |
| period_type | string(10) | NOT NULL ('week', 'month', 'year') |
| period_value | string(20) | NOT NULL |
| created_at | datetime | |
| updated_at | datetime | |

**Index :** (game_id, period_type, period_value), (period_type, period_value, rank)

### Tables de jointure (Core)

| Table | Colonnes | Relation |
|-------|----------|----------|
| `vgr_game_platform` | game_id, platform_id | Game ↔ Platform |
| `vgr_rule_game` | rule_id, game_id | Rule ↔ Game |
| `vgr_game_discord` | game_id, discord_id | Game ↔ Discord |
| `vgr_friend` | player_id, friend_id | Player ↔ Player (self) |

---

## 6. VideoGamesRecords.Igdb (`igdb_`)

### igdb_game

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK (fourni par IGDB, pas auto-increment) |
| version_parent_id | int | FK → igdb_game (self), nullable |
| name | string(255) | NOT NULL |
| slug | string(255) | nullable |
| storyline | text | nullable |
| summary | text | nullable |
| url | string(500) | nullable |
| checksum | string(255) | nullable |
| first_release_date | int | nullable (timestamp Unix) |
| created_at | datetime_immutable | |
| updated_at | datetime_immutable | |

**Relations :**
- **ManyToOne** → `igdb_game` (self, version_parent_id)
- **ManyToMany** → `igdb_genre` via `igdb_game_genre`
- **ManyToMany** → `igdb_platform` via `igdb_game_platform`

### igdb_genre

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK (fourni par IGDB) |
| name | string(255) | NOT NULL |
| slug | string(255) | NOT NULL |
| url | text | nullable |
| checksum | string(36) | NOT NULL |
| created_at | datetime_immutable | |
| updated_at | datetime_immutable | |

### igdb_platform

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK (fourni par IGDB) |
| platform_type_id | int | FK → igdb_platform_type, nullable |
| platform_logo_id | int | FK → igdb_platform_logo, nullable |
| name | string(255) | NOT NULL |
| abbreviation | string(255) | nullable |
| alternative_name | string(255) | nullable |
| generation | int | nullable |
| slug | string(255) | nullable |
| summary | text | nullable |
| url | string(500) | nullable |
| checksum | string(255) | nullable |
| created_at | datetime_immutable | |
| updated_at | datetime_immutable | |

### igdb_platform_type

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK (fourni par IGDB) |
| name | string(255) | NOT NULL |
| checksum | string(255) | nullable |
| created_at | datetime_immutable | |
| updated_at | datetime_immutable | |

### igdb_platform_logo

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK (fourni par IGDB) |
| alpha_channel | bool | NOT NULL |
| animated | bool | NOT NULL |
| checksum | string(255) | nullable |
| height | int | NOT NULL |
| image_id | string(255) | NOT NULL |
| url | string(500) | nullable |
| width | int | NOT NULL |
| created_at | datetime_immutable | |
| updated_at | datetime_immutable | |

### Tables de jointure (Igdb)

| Table | Colonnes | Relation |
|-------|----------|----------|
| `igdb_game_genre` | game_id, genre_id | Game ↔ Genre |
| `igdb_game_platform` | game_id, platform_id | Game ↔ Platform |

---

## 7. VideoGamesRecords.Badge (`vgr_`)

### vgr_badge

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| type | enum (BadgeType) | |
| picture | string(100) | default: 'default.gif' |
| value | int | default: 0 |

**Index :** `idx_type`, `idx_value`

Referencee par : `vgr_game`, `vgr_serie`, `vgr_platform`, `vgr_country` (OneToOne)

### vgr_player_badge

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| player_id | int | FK → vgr_player, NOT NULL, ON DELETE CASCADE |
| badge_id | int | FK → vgr_badge, NOT NULL, ON DELETE CASCADE |
| ended_at | datetime | nullable |
| mb_order | int | nullable, default: 0 |
| created_at | datetime | |
| updated_at | datetime | |

### vgr_team_badge

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| team_id | int | FK → vgr_team, NOT NULL, ON DELETE CASCADE |
| badge_id | int | FK → vgr_badge, NOT NULL, ON DELETE CASCADE |
| ended_at | datetime | nullable |
| mb_order | int | nullable, default: 0 |
| created_at | datetime | |
| updated_at | datetime | |

---

## 8. VideoGamesRecords.Proof (`vgr_`)

### vgr_proof

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| player_id | int | FK → vgr_player, NOT NULL |
| chart_id | int | FK → vgr_chart, NOT NULL, ON DELETE CASCADE |
| picture_id | int | FK → vgr_picture, nullable |
| video_id | int | FK → vgr_video, nullable, ON DELETE CASCADE |
| proof_request_id | int | FK → vgr_proof_request, nullable |
| responding_player_id | int | FK → vgr_player, nullable |
| status | string(30) | default: 'IN_PROGRESS' |
| response | text | nullable |
| checked_at | datetime | nullable |
| created_at | datetime | |
| updated_at | datetime | |

**Relations :**
- **OneToOne** ← `vgr_player_chart` (mappedBy: proof)

### vgr_picture

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| path | string(255) | NOT NULL |
| metadata | text | nullable |
| hash | string(255) | NOT NULL |
| created_at | datetime | |
| updated_at | datetime | |

### vgr_video

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| game_id | int | FK → vgr_game, nullable, ON DELETE SET NULL |
| type | string(50) | default: 'YOUTUBE' |
| external_id | string(50) | NOT NULL |
| url | string(255) | UNIQUE, NOT NULL |
| nb_comment | int | default: 0 |
| slug | string(255) | Gedmo slug |
| created_at | datetime | |
| updated_at | datetime | |

**Unique :** (type, external_id)

**Relations :**
- **OneToMany** → `vgr_video_comment`
- **ManyToMany** → `vgr_tag` via `vgr_video_tag`

### vgr_video_comment

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| video_id | int | FK → vgr_video, NOT NULL, ON DELETE CASCADE |
| content | text | NOT NULL |
| created_at | datetime | |
| updated_at | datetime | |

### vgr_proof_request

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| player_chart_id | int | FK → vgr_player_chart, NOT NULL, ON DELETE CASCADE |
| requesting_player_id | int | FK → vgr_player, NOT NULL |
| responding_player_id | int | FK → vgr_player, nullable |
| status | string(50) | default: 'IN_PROGRESS' |
| response | text | nullable |
| message | text | nullable |
| date_acceptance | datetime | nullable |
| created_at | datetime | |
| updated_at | datetime | |

### vgr_tag

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| name | string(100) | NOT NULL |
| category | string(50) | nullable |
| is_official | bool | default: false |

**Index :** `idx_category`, `idx_is_official`

---

## 9. VideoGamesRecords.Team (`vgr_`)

### vgr_team

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| leader_id | int | FK → vgr_player, NOT NULL |
| lib_team | string(50) | NOT NULL |
| tag | string(10) | NOT NULL |
| site_web | string(255) | nullable |
| logo | string(30) | default: 'default.png' |
| presentation | text | nullable |
| status | string(30) | default: 'CLOSED' |
| slug | string(128) | Gedmo slug |
| created_at | datetime | |
| updated_at | datetime | |

**Relations :**
- **ManyToOne** → `vgr_player` (leader_id)
- **OneToMany** → `vgr_player` (mappedBy: team, ORDER BY pseudo ASC)
- **OneToMany** → `vgr_team_game`, `vgr_team_badge`

### vgr_team_game

| Colonne | Type | Contraintes |
|---------|------|-------------|
| team_id | int | PK, FK → vgr_team, ON DELETE CASCADE |
| game_id | int | PK, FK → vgr_game, ON DELETE CASCADE |

**PK composite :** (team_id, game_id)

### vgr_team_group

| Colonne | Type | Contraintes |
|---------|------|-------------|
| team_id | int | PK, FK → vgr_team, ON DELETE CASCADE |
| group_id | int | PK, FK → vgr_group, ON DELETE CASCADE |

**PK composite :** (team_id, group_id)

### vgr_team_chart

| Colonne | Type | Contraintes |
|---------|------|-------------|
| team_id | int | PK, FK → vgr_team, ON DELETE CASCADE |
| chart_id | int | PK, FK → vgr_chart, ON DELETE CASCADE |

**PK composite :** (team_id, chart_id)

### vgr_team_serie

| Colonne | Type | Contraintes |
|---------|------|-------------|
| team_id | int | PK, FK → vgr_team, ON DELETE CASCADE |
| serie_id | int | PK, FK → vgr_serie, ON DELETE CASCADE |

**PK composite :** (team_id, serie_id)

### vgr_team_request

| Colonne | Type | Contraintes |
|---------|------|-------------|
| id | int | PK, auto-increment |
| team_id | int | FK → vgr_team, NOT NULL, ON DELETE CASCADE |
| player_id | int | FK → vgr_player, NOT NULL, ON DELETE CASCADE |
| status | string(30) | default: 'ACTIVE' |
| created_at | datetime | |
| updated_at | datetime | |

---

## Relations inter-contextes

Les relations entre bounded contexts passent par des cles etrangeres directes au niveau base de donnees, meme si l'isolation est respectee au niveau code :

```
User ──────── Player (user_id)
                │
                ├── PlayerChart ──── Chart ──── Group ──── Game
                │                     │
                │                     └── Proof
                │
                ├── PlayerBadge ──── Badge ◄── Game, Serie, Platform, Country
                │
                ├── PlayerGame ──── Game ──── IgdbGame
                │
                └── Team
                    ├── TeamGame ──── Game
                    ├── TeamBadge ──── Badge
                    └── TeamRequest

Game ──── Forum (forum_id, OneToOne)
Game ──── IgdbGame (igdb_game_id, ManyToOne)
```

### Liens cles entre contextes

| Source | Cible | Type | Colonne |
|--------|-------|------|---------|
| `vgr_player` | `pnu_user` | FK | user_id |
| `vgr_game` | `pnf_forum` | OneToOne | forum_id |
| `vgr_game` | `igdb_game` | ManyToOne | igdb_game_id |
| `vgr_game` | `vgr_badge` | OneToOne | badge_id |
| `vgr_serie` | `vgr_badge` | OneToOne | badge_id |
| `vgr_platform` | `vgr_badge` | OneToOne | badge_id |
| `vgr_country` | `vgr_badge` | OneToOne | badge_id |
