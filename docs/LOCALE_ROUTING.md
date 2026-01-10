# Locale Routing - Guide d'utilisation

## 🎯 Comment ça marche ?

Grâce au `LocaleSubscriber` et au `RootController`, le système gère automatiquement la détection et la persistance de la langue de l'utilisateur.

### 🌐 Détection automatique de la langue

Quand un utilisateur visite `/` pour la première fois, le système :

1. **Vérifie la session** : Si l'utilisateur a déjà visité le site, on utilise sa langue précédente
2. **Détecte la langue du navigateur** : Via l'en-tête `Accept-Language`
3. **Redirige vers la bonne langue** :
   - Navigateur en français → `/fr/`
   - Navigateur en anglais → `/en/`
   - Autre langue → `/en/` (par défaut)

### 📌 Persistance de la langue

- Une fois sur `/fr/` ou `/en/`, la langue est **stockée en session**
- Tous les liens générés utilisent automatiquement cette langue
- L'utilisateur garde sa langue même en naviguant sur le site

Symfony détecte automatiquement la locale depuis l'URL et l'utilise pour générer toutes les URLs.

## ✅ Dans Twig (Templates)

### ❌ AVANT (à ne PAS faire)
```twig
{# Ne fais PAS ça ! #}
<a href="{{ path('static_faq', {'_locale': app.request.locale}) }}">FAQ</a>
<a href="{{ path('home', {'_locale': 'fr'}) }}">Accueil</a>
```

### ✅ MAINTENANT (ce qu'il faut faire)
```twig
{# La locale est automatiquement ajoutée ! #}
<a href="{{ path('static_faq') }}">FAQ</a>
<a href="{{ path('home') }}">Accueil</a>

{# Si l'utilisateur est sur /fr/quelque-chose, tous les liens seront en /fr/ #}
{# Si l'utilisateur est sur /en/quelque-chose, tous les liens seront en /en/ #}
```

## ✅ Dans les Controllers (PHP)

### ❌ AVANT (à ne PAS faire)
```php
// Ne fais PAS ça !
public function myAction(Request $request): Response
{
    $url = $this->generateUrl('static_faq', ['_locale' => $request->getLocale()]);
    return $this->redirectToRoute('home', ['_locale' => 'fr']);
}
```

### ✅ MAINTENANT (ce qu'il faut faire)
```php
// La locale est automatiquement ajoutée !
public function myAction(): Response
{
    $url = $this->generateUrl('static_faq');
    return $this->redirectToRoute('home');
}
```

## 🔄 Changement de langue

Si tu veux créer un sélecteur de langue :

```twig
{# Lien pour changer la langue vers le français #}
<a href="{{ path(app.request.attributes.get('_route'), app.request.attributes.get('_route_params')|merge({'_locale': 'fr'})) }}">
    🇫🇷 Français
</a>

{# Lien pour changer la langue vers l'anglais #}
<a href="{{ path(app.request.attributes.get('_route'), app.request.attributes.get('_route_params')|merge({'_locale': 'en'})) }}">
    🇬🇧 English
</a>
```

Cela garde la même page mais change juste la langue.

## 🛠️ Comment ça fonctionne en interne ?

1. **URL visitée** : `/fr/faq`
2. **LocaleSubscriber détecte** : `_locale = 'fr'` depuis l'URL
3. **Locale définie** : `$request->setLocale('fr')`
4. **Toutes les URLs générées** utilisent automatiquement `fr` :
   - `path('home')` → `/fr/`
   - `path('static_faq')` → `/fr/faq`
   - `generateUrl('home')` → `/fr/`

## 📝 Créer un nouveau controller avec locale

```php
<?php

declare(strict_types=1);

namespace App\SharedKernel\Presentation\Web\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// 1. Ajoute l'attribut Route avec /{_locale} sur la CLASSE
#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
class MyController extends AbstractLocalizedController
{
    // 2. Ajoute tes routes normalement, sans /{_locale}
    #[Route('/my-page', name: 'my_page')]
    public function index(string $_locale): Response
    {
        // 3. Utilise $_locale si besoin (pour les templates, traductions, etc.)
        // Route finale : /{_locale}/my-page

        // Génère des URLs sans spécifier la locale
        $faqUrl = $this->generateUrl('static_faq'); // Automatiquement /en/faq ou /fr/faq

        return $this->render('@SharedKernel/my-page.html.twig', [
            'locale' => $_locale
        ]);
    }

    #[Route('/my-other-page', name: 'my_other_page')]
    public function other(): Response
    {
        // Pas besoin de récupérer $_locale si tu ne l'utilises pas
        // Route finale : /{_locale}/my-other-page
        return $this->render('@SharedKernel/other.html.twig');
    }
}
```

## 🎨 Templates multilingues

```twig
{# faq.fr.html.twig #}
{% extends 'base.html.twig' %}

{% block title %}Foire Aux Questions{% endblock %}

{% block body %}
    <h1>FAQ en Français</h1>

    {# Tous les liens gardent automatiquement la locale 'fr' #}
    <a href="{{ path('home') }}">Retour à l'accueil</a>
    <a href="{{ path('static_faq') }}">FAQ</a>
{% endblock %}
```

```twig
{# faq.en.html.twig #}
{% extends 'base.html.twig' %}

{% block title %}Frequently Asked Questions{% endblock %}

{% block body %}
    <h1>FAQ in English</h1>

    {# Tous les liens gardent automatiquement la locale 'en' #}
    <a href="{{ path('home') }}">Back to home</a>
    <a href="{{ path('static_faq') }}">FAQ</a>
{% endblock %}
```

## 🔄 Flux complet de détection de langue

### Scénario 1 : Première visite
```
1. Utilisateur visite https://videogamesrecords.com/
2. RootController détecte la langue du navigateur : "fr-FR, fr;q=0.9, en;q=0.8"
3. Langue "fr" trouvée dans les langues disponibles
4. Stockage de "fr" dans la session
5. Redirection vers https://videogamesrecords.com/fr/
6. Tous les liens utilisent maintenant /fr/
```

### Scénario 2 : Visite suivante
```
1. Utilisateur visite https://videogamesrecords.com/
2. RootController trouve "fr" dans la session
3. Redirection immédiate vers https://videogamesrecords.com/fr/
4. Pas de détection du navigateur nécessaire
```

### Scénario 3 : Navigateur en espagnol (langue non disponible)
```
1. Utilisateur visite https://videogamesrecords.com/
2. RootController détecte la langue : "es-ES, es;q=0.9"
3. Langue "es" non disponible (seulement en/fr)
4. Utilisation de la langue par défaut "en"
5. Redirection vers https://videogamesrecords.com/en/
```

### Scénario 4 : Lien direct avec locale
```
1. Utilisateur clique sur un lien : https://videogamesrecords.com/fr/faq
2. LocaleSubscriber détecte "_locale=fr" dans l'URL
3. Stockage de "fr" dans la session
4. Tous les liens sur la page utilisent /fr/
5. Même si l'utilisateur va sur /, il sera redirigé vers /fr/
```

## ✅ Résumé

**Tu n'as JAMAIS besoin de passer `_locale` manuellement !**

- ✅ Visite de `/` → Redirection automatique vers `/en/` ou `/fr/` selon le navigateur
- ✅ `path('route_name')` → Symfony ajoute automatiquement la locale
- ✅ `generateUrl('route_name')` → Symfony ajoute automatiquement la locale
- ✅ Tous les controllers qui étendent `AbstractLocalizedController` ont automatiquement le préfixe `/{_locale}`
- ✅ La locale est persistée dans la session, donc l'utilisateur garde sa langue préférée
- ✅ Changement de langue possible via le sélecteur de langue
- ✅ La langue persiste même après fermeture du navigateur (tant que la session est active)

**C'est tout automatique ! 🎉**
