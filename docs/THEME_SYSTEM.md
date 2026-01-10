# Système de Thèmes (Light/Dark Mode)

## 🎨 Vue d'ensemble

Le site utilise le système de thèmes natif de Bootstrap 5 avec support du mode sombre (dark mode). L'utilisateur peut basculer entre deux thèmes :

- **Light** : Thème clair par défaut
- **Dark** : Thème sombre

## 🔧 Comment ça marche ?

### Architecture

```
┌─────────────────────────────────────────────────────────────┐
│              Page Load (DOMContentLoaded)                    │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│              getPreferredTheme()                             │
│  1. Vérifier localStorage                                    │
│  2. Si vide, détecter préférence système                     │
│     (prefers-color-scheme: dark)                             │
│  3. Fallback: 'light'                                        │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│                  setTheme(theme)                             │
│  - Applique data-bs-theme="light|dark" sur <html>           │
│  - Met à jour l'icône du bouton toggle                       │
└─────────────────────────────────────────────────────────────┘
```

### Flux utilisateur

**1. Première visite**
```
1. Page charge
2. Pas de thème dans localStorage
3. Détection de la préférence système (prefers-color-scheme)
4. Si système en dark mode → theme = 'dark'
5. Sinon → theme = 'light'
6. Application du thème
```

**2. Toggle manuel**
```
1. Utilisateur clique sur le bouton 🌙/☀️
2. toggleTheme() détecte le thème actuel
3. Bascule vers l'autre thème
4. Sauvegarde dans localStorage
5. Met à jour l'icône du bouton
```

**3. Visite suivante**
```
1. Page charge
2. Thème trouvé dans localStorage
3. Application immédiate du thème sauvegardé
4. Préférence système ignorée
```

**4. Changement de préférence système**
```
1. Utilisateur change son OS en dark mode
2. Event listener détecte le changement
3. SI aucun thème manuel défini
   ALORS applique la nouvelle préférence système
   SINON garde le choix manuel de l'utilisateur
```

## 📁 Fichiers impliqués

### 1. Template de base
**Fichier :** `templates/base.html.twig`

```html
<html lang="{{ app.request.locale }}" data-bs-theme="light">
```

L'attribut `data-bs-theme` contrôle le thème Bootstrap :
- `light` → Thème clair
- `dark` → Thème sombre

### 2. JavaScript
**Fichier :** `assets/app.js`

Fonctions principales :

```javascript
// Récupère le thème stocké
function getStoredTheme()

// Stocke le thème
function setStoredTheme(theme)

// Détermine le thème préféré (localStorage ou système)
function getPreferredTheme()

// Applique le thème au document
function setTheme(theme)

// Bascule entre light et dark
window.toggleTheme()
```

### 3. Bouton de toggle
**Fichier :** `templates/base.html.twig` (header)

```html
<button class="nav-link btn btn-link" id="theme-toggle" onclick="toggleTheme()">
    <i class="bi bi-moon-fill"></i>
</button>
```

**Icônes :**
- 🌙 `bi-moon-fill` → En mode light (pour passer en dark)
- ☀️ `bi-sun-fill` → En mode dark (pour passer en light)

## 🎨 Personnalisation des couleurs

Bootstrap 5 gère automatiquement les couleurs selon le thème :

### Variables CSS automatiques

```scss
// Light mode (par défaut)
--bs-body-bg: #ffffff
--bs-body-color: #212529
--bs-primary: #0d6efd
// ... etc

// Dark mode (data-bs-theme="dark")
--bs-body-bg: #212529
--bs-body-color: #dee2e6
--bs-primary: #0d6efd
// ... etc
```

### Personnaliser les couleurs

**Fichier :** `assets/styles/app.scss`

Pour personnaliser les couleurs du dark mode :

```scss
// Surcharger les variables dark mode
[data-bs-theme="dark"] {
  --bs-primary: #YOUR_COLOR;
  --bs-body-bg: #1a1a1a;
  --bs-body-color: #f8f9fa;

  // Exemple: navbar custom en dark mode
  .navbar {
    background-color: #2d2d2d !important;
  }
}

// Surcharger les variables light mode
[data-bs-theme="light"] {
  --bs-primary: #YOUR_COLOR;

  // Exemple: navbar custom en light mode
  .navbar {
    background-color: #f8f9fa !important;
  }
}
```

## 🔍 Détection de la préférence système

Le code utilise la Media Query `prefers-color-scheme` :

```javascript
// Détection initiale
window.matchMedia('(prefers-color-scheme: dark)').matches

// Écoute des changements
window.matchMedia('(prefers-color-scheme: dark)')
    .addEventListener('change', (e) => {
        if (!getStoredTheme()) {
            setTheme(e.matches ? 'dark' : 'light');
        }
    });
```

**Comportement :**
- Si l'utilisateur n'a jamais cliqué sur le toggle → Suit la préférence système
- Si l'utilisateur a cliqué sur le toggle → Garde son choix, ignore la système

## 📊 Stockage

### localStorage

```javascript
// Clé utilisée
'theme'

// Valeurs possibles
'light' | 'dark'

// Exemple
localStorage.getItem('theme'); // → 'dark'
```

**Avantages :**
- Persistance entre les sessions
- Pas de requête serveur
- Disponible immédiatement au chargement de la page

**Note :** Le localStorage est spécifique au domaine. Si l'utilisateur visite le site depuis un autre domaine (ex: www.example.com vs example.com), le thème ne sera pas partagé.

## 🧪 Test du système de thème

### Test manuel

1. **Tester le toggle :**
   - Cliquer sur le bouton 🌙 → Le thème bascule en dark
   - Cliquer sur le bouton ☀️ → Le thème bascule en light
   - L'icône change à chaque clic

2. **Tester la persistance :**
   - Basculer en dark mode
   - Rafraîchir la page (F5)
   - Le dark mode doit être toujours actif

3. **Tester la détection système :**
   - Vider le localStorage : `localStorage.removeItem('theme')`
   - Rafraîchir la page
   - Le thème doit correspondre à votre OS

### Test avec DevTools

```javascript
// Console du navigateur

// Voir le thème actuel
document.documentElement.getAttribute('data-bs-theme');

// Changer le thème manuellement
setTheme('dark');
setTheme('light');

// Voir le thème stocké
localStorage.getItem('theme');

// Effacer le thème stocké
localStorage.removeItem('theme');

// Tester le toggle
toggleTheme();
```

### Test de la préférence système

**Chrome/Edge :**
1. F12 → DevTools
2. Cmd/Ctrl + Shift + P
3. Taper "Rendering"
4. Sélectionner "Emulate CSS prefers-color-scheme: dark"

**Firefox :**
1. F12 → DevTools
2. Onglet "Inspector"
3. Icône ☀️ en haut à droite

## 🚀 Compilation des assets

Après modification du JavaScript ou SCSS :

```bash
# Development
npm run watch

# Production
npm run build
```

## 📱 Support mobile

Le système fonctionne sur tous les navigateurs modernes :
- ✅ Chrome/Edge (Desktop & Mobile)
- ✅ Firefox (Desktop & Mobile)
- ✅ Safari (Desktop & Mobile)
- ✅ Opera

**Note :** Internet Explorer n'est pas supporté (Bootstrap 5 ne le supporte pas).

## 🎯 Ordre de priorité

```
1. Choix manuel de l'utilisateur (localStorage)
   ↓ (si absent)
2. Préférence système (prefers-color-scheme)
   ↓ (si non détectable)
3. Thème par défaut (light)
```

## 🔐 Sécurité

Le système de thème n'a pas d'impact sur la sécurité :
- Stockage en localStorage uniquement (client-side)
- Pas de données sensibles
- Pas de requête serveur

## ✨ Améliorations futures possibles

- [ ] Ajouter un 3ème thème "Auto" qui suit toujours le système
- [ ] Transition smooth entre les thèmes (fade effect)
- [ ] Prévisualisation des thèmes avant application
- [ ] Sauvegarder le thème en base de données (pour utilisateurs connectés)
- [ ] Analytics : tracker les préférences des utilisateurs
- [ ] Thèmes personnalisés par utilisateur (couleurs custom)

## 🎨 Exemples de personnalisation

### Exemple 1 : Navbar différente selon le thème

```scss
[data-bs-theme="light"] {
  .navbar {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-bottom: 2px solid #764ba2;
  }
}

[data-bs-theme="dark"] {
  .navbar {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    border-bottom: 2px solid #0f3460;
  }
}
```

### Exemple 2 : Cards avec style différent

```scss
[data-bs-theme="dark"] {
  .card {
    background-color: #2d2d2d;
    border: 1px solid #3d3d3d;

    .card-header {
      background-color: #1a1a1a;
      border-bottom: 1px solid #3d3d3d;
    }
  }
}
```

### Exemple 3 : Animation de transition

```scss
html {
  transition: background-color 0.3s ease, color 0.3s ease;
}

[data-bs-theme="dark"] {
  * {
    transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
  }
}
```

## 📚 Ressources

- [Bootstrap 5 Color Modes](https://getbootstrap.com/docs/5.3/customize/color-modes/)
- [MDN: prefers-color-scheme](https://developer.mozilla.org/en-US/docs/Web/CSS/@media/prefers-color-scheme)
- [Web.dev: prefers-color-scheme](https://web.dev/prefers-color-scheme/)
