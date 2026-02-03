# Editeur Quill (Rich Text)

## Vue d'ensemble

Le projet utilise **Quill.js 2.x** comme editeur de texte riche (WYSIWYG). Deux modes d'integration sont disponibles :

- **Stimulus Controller** (`quill_controller.js`) : pour les pages front (entry `app`)
- **Auto-initialisation** (`admin.js`) : pour les pages Sonata Admin (entry `admin`)

Les deux utilisent le theme **Snow** de Quill.

## Architecture

```
assets/
├── app.js                          # Charge Stimulus (dont quill_controller)
├── admin.js                        # Auto-initialisation pour Sonata Admin
├── styles/
│   └── app.scss                    # Import quill.snow.css
└── controllers/
    └── quill_controller.js         # Stimulus controller
```

## 1. Stimulus Controller (Front)

### Fonctionnement

Le controller Stimulus s'attache a un `<textarea>`. Au `connect()`, il :

1. Cree un `<div>` editeur juste avant le textarea
2. Masque le textarea (`display: none`)
3. Initialise Quill sur le div
4. Charge le contenu HTML existant du textarea dans l'editeur
5. Synchronise le contenu a chaque modification (`text-change`)

Au `disconnect()`, le div editeur est supprime.

### Utilisation dans un FormType Symfony

Ajouter les attributs `data-*` sur le champ textarea :

```php
->add('description', TextareaType::class, [
    'label' => 'Description',
    'required' => false,
    'attr' => [
        'data-controller' => 'quill',
        'data-quill-toolbar-value' => 'minimal',   // 'minimal' ou 'full'
        'data-quill-min-height-value' => '200px',   // hauteur minimale
    ],
])
```

### Utilisation directe dans un template Twig

```twig
<textarea
    name="content"
    data-controller="quill"
    data-quill-toolbar-value="full"
    data-quill-min-height-value="300px"
>{{ content }}</textarea>
```

### Parametres

| Attribut | Type | Default | Description |
|----------|------|---------|-------------|
| `data-quill-toolbar-value` | String | `minimal` | Type de toolbar : `minimal` ou `full` |
| `data-quill-min-height-value` | String | `200px` | Hauteur minimale de l'editeur |

### Toolbars disponibles

**Minimal** (defaut) :

```
[Bold] [Italic] [Underline]  |  [Liste ordonnee] [Liste a puces]  |  [Lien]  |  [Clean]
```

**Full** :

```
[Header 1-6]  |  [Font]  |  [Taille]
[Bold] [Italic] [Underline] [Strike]
[Couleur texte] [Couleur fond]
[Indice] [Exposant]
[Liste ordonnee] [Liste a puces] [Checklist]
[Indentation -/+]
[Alignement]  |  [Direction RTL]
[Citation] [Code]
[Lien] [Image] [Video]
[Clean]
```

## 2. Auto-initialisation Admin (Sonata)

### Fonctionnement

Le script `admin.js` detecte automatiquement tous les `<textarea>` ayant la classe CSS `rich-text-editor` et initialise un editeur Quill avec la toolbar **full**.

Il gere aussi les collections dynamiques Sonata via l'event `sonata-collection-item-added`.

### Utilisation dans un Admin Sonata

```php
// Dans la methode configureFormFields() d'un Admin
$form
    ->add('description', TextareaType::class, [
        'attr' => [
            'class' => 'rich-text-editor',
        ],
    ]);
```

La toolbar est toujours **full** dans l'admin. La hauteur minimale est fixee a 300px.

## Exemples concrets

### Champ profil joueur (toolbar minimale)

```php
// PlayerProfileFormType.php
->add('presentation', TextareaType::class, [
    'label' => 'profile.player.form.presentation.label',
    'required' => false,
    'attr' => [
        'data-controller' => 'quill',
        'data-quill-toolbar-value' => 'minimal',
        'data-quill-min-height-value' => '150px',
    ],
])
```

### Champ description de jeu (toolbar complete)

```php
->add('description', TextareaType::class, [
    'label' => 'Description',
    'attr' => [
        'data-controller' => 'quill',
        'data-quill-toolbar-value' => 'full',
        'data-quill-min-height-value' => '300px',
    ],
])
```

## Donnees

Le contenu est stocke en **HTML** dans la base de donnees. Le textarea cache contient le HTML genere par Quill, synchronise automatiquement a chaque frappe.

Quand le formulaire est charge avec des donnees existantes, le HTML du textarea est injecte dans l'editeur via `quill.root.innerHTML`.

## CSS

Le theme Snow de Quill est importe dans :

- `assets/styles/app.scss` : `@import "~quill/dist/quill.snow.css";`
- `assets/admin.js` : `import 'quill/dist/quill.snow.css';`

Aucun CSS custom supplementaire n'est necessaire.

## Compilation

Apres modification :

```bash
# Developpement
npm run watch

# Production
npm run build
```

## Ressources

- [Quill.js Documentation](https://quilljs.com/docs/)
- [Quill Toolbar Module](https://quilljs.com/docs/modules/toolbar/)
- [Stimulus Handbook](https://stimulus.hotwired.dev/handbook/introduction)
