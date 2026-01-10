# Architecture de Détection et Gestion des Locales

## 📋 Vue d'ensemble

Le système de gestion des locales est composé de 3 composants principaux qui travaillent ensemble pour offrir une expérience utilisateur optimale :

```
┌─────────────────────────────────────────────────────────────┐
│                     Utilisateur visite /                     │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│                    RootController                            │
│  - Vérifie la session                                        │
│  - Détecte la langue du navigateur (Accept-Language)        │
│  - Redirige vers /{locale}/                                 │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│                  LocaleSubscriber                            │
│  - Intercepte la requête                                    │
│  - Extrait _locale de l'URL                                 │
│  - Stocke _locale dans la session                           │
│  - Configure Request::setLocale()                           │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│            AbstractLocalizedController                       │
│  - Préfixe automatique /{_locale} sur toutes les routes     │
│  - Injection automatique du paramètre $_locale              │
│  - Tous les controllers web en héritent                     │
└─────────────────────────────────────────────────────────────┘
```

## 🔧 Composants

### 1. RootController

**Fichier :** `src/SharedKernel/Presentation/Web/Controller/RootController.php`

**Responsabilités :**
- Gère la route `/` (sans locale)
- Détecte la langue préférée de l'utilisateur
- Redirige vers la bonne version localisée du site

**Ordre de détection :**
1. Session (langue précédemment choisie)
2. Accept-Language du navigateur
3. Langue par défaut (en)

**Code simplifié :**
```php
#[Route('/', name: 'root')]
public function index(Request $request): Response
{
    // 1. Session
    if ($session->has('_locale')) {
        return $this->redirectToRoute('home', ['_locale' => $session->get('_locale')]);
    }

    // 2. Navigateur
    $locale = $request->getPreferredLanguage(['en', 'fr']);

    // 3. Default
    $locale = $locale ?: 'en';

    $session->set('_locale', $locale);
    return $this->redirectToRoute('home', ['_locale' => $locale]);
}
```

### 2. LocaleSubscriber

**Fichier :** `src/SharedKernel/Infrastructure/EventSubscriber/LocaleSubscriber.php`

**Responsabilités :**
- Intercepte chaque requête HTTP
- Extrait le paramètre `_locale` de l'URL
- Stocke la locale dans la session pour persistance
- Configure la locale de la requête

**Priorité :** 20 (avant le LocaleListener par défaut de Symfony)

**Code simplifié :**
```php
public function onKernelRequest(RequestEvent $event): void
{
    $request = $event->getRequest();

    if ($locale = $request->attributes->get('_locale')) {
        $request->setLocale($locale);
        $request->getSession()->set('_locale', $locale);
    } else {
        $request->setLocale($request->getSession()->get('_locale', 'en'));
    }
}
```

### 3. AbstractLocalizedController

**Fichier :** `src/SharedKernel/Presentation/Web/Controller/AbstractLocalizedController.php`

**Responsabilités :**
- Classe de base pour tous les controllers web
- Applique le préfixe `/{_locale}` automatiquement
- Inject le paramètre `$_locale` dans les méthodes

**Important :** En Symfony 8, l'attribut #[Route] n'est PAS hérité automatiquement. Il faut l'ajouter sur chaque classe concrète.

**Usage :**
```php
#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
class MyController extends AbstractLocalizedController
{
    #[Route('/my-page', name: 'my_page')]
    public function index(string $_locale): Response
    {
        // Route finale : /{_locale}/my-page
        return $this->render('...');
    }
}
```

## 🌍 Langues Disponibles

Configurées dans : `RootController::AVAILABLE_LOCALES`

```php
private const AVAILABLE_LOCALES = ['en', 'fr'];
private const DEFAULT_LOCALE = 'en';
```

Pour ajouter une nouvelle langue :
1. Ajouter la locale dans `AVAILABLE_LOCALES`
2. Mettre à jour les requirements dans `AbstractLocalizedController`
3. Créer les templates correspondants (ex: `faq.es.html.twig`)

## 🔄 Flux Utilisateur

### Premier visiteur (navigateur en français)

```
1. GET /
   └─> RootController::index()
       ├─> Pas de session
       ├─> Accept-Language: fr-FR,fr;q=0.9,en;q=0.8
       ├─> getPreferredLanguage(['en', 'fr']) → 'fr'
       ├─> Session::set('_locale', 'fr')
       └─> Redirect → /fr/

2. GET /fr/
   └─> LocaleSubscriber::onKernelRequest()
       ├─> _locale = 'fr' (depuis URL)
       ├─> Request::setLocale('fr')
       ├─> Session::set('_locale', 'fr')
       └─> Continue

3. HomeController::index($_locale = 'fr')
   └─> Render home page en français

4. Utilisateur clique sur "FAQ"
   └─> path('static_faq') génère automatiquement → /fr/faq
```

### Visiteur qui revient (même session)

```
1. GET /
   └─> RootController::index()
       ├─> Session::get('_locale') → 'fr'
       └─> Redirect immédiat → /fr/

2. GET /fr/
   └─> (même flux qu'avant)
```

### Visiteur avec navigateur en espagnol (non supporté)

```
1. GET /
   └─> RootController::index()
       ├─> Accept-Language: es-ES,es;q=0.9
       ├─> getPreferredLanguage(['en', 'fr']) → null
       ├─> Fallback → 'en'
       ├─> Session::set('_locale', 'en')
       └─> Redirect → /en/
```

### Changement de langue manuel

```
1. Utilisateur sur /fr/faq
2. Clique sur le sélecteur de langue → English
3. GET /en/faq
   └─> LocaleSubscriber::onKernelRequest()
       ├─> _locale = 'en' (depuis URL)
       ├─> Session::set('_locale', 'en') ← Override du 'fr'
       └─> Continue

4. Tous les liens utilisent maintenant /en/
5. Si l'utilisateur va sur /, il sera redirigé vers /en/
```

## 🎨 Composants UI

### Sélecteur de Langue

**Fichier :** `src/SharedKernel/Presentation/Resources/views/components/language_switcher.html.twig`

**Usage dans un template :**
```twig
{% include '@SharedKernel/components/language_switcher.html.twig' %}
```

**Comportement :**
- Affiche la langue courante
- Permet de basculer entre en/fr
- Reste sur la même page (change juste la locale)

## 📊 Tableau de Routage

| Route           | Pattern         | Controller              | Description                    |
|-----------------|-----------------|-------------------------|--------------------------------|
| `root`          | `/`             | RootController::index   | Détection et redirection       |
| `home`          | `/{_locale}/`   | HomeController::index   | Page d'accueil localisée       |
| `static_faq`    | `/{_locale}/faq`| StaticController::faq   | FAQ en français ou anglais     |

## 🧪 Tests de Détection

### Test avec curl

```bash
# Simuler un navigateur français
curl -H "Accept-Language: fr-FR,fr;q=0.9" http://localhost:8000/ -L

# Simuler un navigateur anglais
curl -H "Accept-Language: en-US,en;q=0.9" http://localhost:8000/ -L

# Simuler un navigateur espagnol (fallback vers en)
curl -H "Accept-Language: es-ES,es;q=0.9" http://localhost:8000/ -L
```

### Test avec Symfony CLI

```bash
# Tester la route root
php bin/console router:match /

# Tester les routes localisées
php bin/console router:match /en/
php bin/console router:match /fr/faq

# Lister toutes les routes
php bin/console debug:router | grep -E "root|home|static"
```

## 💡 Avantages de cette Architecture

1. **Expérience Utilisateur Optimale**
   - Détection automatique de la langue
   - Persistance de la préférence
   - Pas de rechargement inutile

2. **Simplicité de Développement**
   - Pas besoin de passer `_locale` manuellement
   - URL generation automatique
   - Code propre et maintenable

3. **SEO Friendly**
   - URLs distinctes par langue (`/en/`, `/fr/`)
   - Facile à indexer par Google
   - Possibilité d'ajouter des hreflang tags

4. **Extensibilité**
   - Facile d'ajouter de nouvelles langues
   - Architecture modulaire
   - Pas de duplication de code

## 🚀 Prochaines Améliorations Possibles

- [ ] Ajouter des tests automatisés
- [ ] Implémenter le cache de détection de langue
- [ ] Ajouter des hreflang tags pour le SEO
- [ ] Créer un middleware pour détecter les bots (pas besoin de redirection)
- [ ] Ajouter des métriques de langue préférée des utilisateurs
- [ ] Support de nouvelles langues (de, es, it, etc.)
