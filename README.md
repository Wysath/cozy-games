# Cozy Gaming — Structure et Documentation

Bienvenue dans le projet **Cozy Gaming**, un site WordPress conçu pour une guilde gaming bienveillante. Ce projet inclut des fonctionnalités avancées telles que la gestion d'événements, des setups gaming, des articles enrichis, et des rôles personnalisés.

## 📂 Structure des fichiers
cozy-gaming/
├── assets/
│   ├── css/
│   │   ├── [main.css](http://_vscodecontentref_/0)
│   │   ├── [cozy-hero.css](http://_vscodecontentref_/1)
│   │   ├── [cozy-articles.css](http://_vscodecontentref_/2)
│   │   └── ...
│   ├── js/
│   │   ├── [main.js](http://_vscodecontentref_/3)
│   │   ├── [cozy-homepage.js](http://_vscodecontentref_/4)
│   │   └── ...
│   └── images/
├── inc/
│   ├── [cozy-articles.php](http://_vscodecontentref_/5)
│   ├── [cozy-setups.php](http://_vscodecontentref_/6)
│   ├── [cozy-friend-codes.php](http://_vscodecontentref_/7)
│   ├── ...
├── template-parts/
│   ├── [content.php](http://_vscodecontentref_/8)
│   └── ...
├── [front-page.php](http://_vscodecontentref_/9)
├── [single.php](http://_vscodecontentref_/10)
├── [search.php](http://_vscodecontentref_/11)
└── ...

### 1. **Thème principal : `cozy-gaming`**
Le thème contient les fichiers principaux pour le rendu du site.

- **`functions.php`** : Configuration du thème, chargement des modules, gestion des rôles.
- **`header.php` / `footer.php`** : En-tête et pied de page.
- **`front-page.php`** : Template de la page d'accueil.
- **`single.php`** : Template des articles individuels.
- **`search.php`** : Résultats de recherche.
- **`comments.php`** : Gestion des commentaires.
- **`template-parts/`** : Contient les parties réutilisables comme les cartes d'articles.

#### **CSS**
- **`assets/css/main.css`** : Styles globaux.
- **`assets/css/cozy-hero.css`** : Section hero.
- **`assets/css/cozy-articles.css`** : Articles et grimoire.
- **`assets/css/cozy-setups.css`** : Galerie setups.
- **`assets/css/cozy-contact.css`** : Formulaire de contact.

#### **JS**
- **`assets/js/main.js`** : Script principal (menu, animations).
- **`assets/js/cozy-homepage.js`** : Interactions spécifiques à la page d'accueil.
- **`assets/js/cozy-setups.js`** : Gestion des setups (upload, suppression, lightbox).

---

### 2. **Plugin : `cozy-events`**
Le plugin gère les événements de la guilde.

- **`cozy-events.php`** : Fichier principal du plugin.
- **`includes/`** :
  - **`cpt.php`** : Enregistrement du Custom Post Type `cozy_event`.
  - **`meta-boxes.php`** : Champs personnalisés pour les événements.
  - **`registration.php`** : Gestion des inscriptions aux événements.
  - **`charter.php`** : Charte de bienveillance.
  - **`shortcodes.php`** : Shortcodes pour afficher les événements.
- **`templates/`** :
  - **`archive-event.php`** : Liste des événements.
  - **`single-event.php`** : Détail d'un événement.
- **`assets/`** :
  - **`style.css`** : Styles des événements.
  - **`script.js`** : Scripts front-end (inscription AJAX, calendrier).

---

### 3. **Modules personnalisés**
Les modules sont inclus dans le thème via `functions.php`.

- **`cozy-articles.php`** : Gestion des articles enrichis (ACF).
- **`cozy-setups.php`** : Galerie setups gaming.
- **`cozy-friend-codes.php`** : Codes ami par plateforme.
- **`cozy-social-profiles.php`** : Profils sociaux (Discord, Twitch).
- **`cozy-contact.php`** : Formulaire de contact.
- **`cozy-dashboard-widgets.php`** : Widgets personnalisés pour le tableau de bord.

---

## 🚀 Fonctionnalités principales

### 1. **Gestion des événements**
- Création d'événements avec des champs personnalisés (date, heure, places disponibles).
- Inscriptions avec validation AJAX.
- Charte de bienveillance à accepter avant de s'inscrire.

### 2. **Articles enrichis**
- Fiches de jeu avec notes par critère (gameplay, direction artistique, etc.).
- Verdict résumé avec points forts/faibles.
- Taxonomies personnalisées : `cozy_article_type` et `cozy_game`.

### 3. **Galerie setups**
- Upload de photos de setups gaming avec titre et description.
- Grille masonry (style Pinterest).
- Lightbox pour agrandir les images.

### 4. **Rôles personnalisés**
- Gestion fine des permissions par rôle (administrateur, éditeur, auteur, animateur, etc.).
- Accès limité aux modules selon le rôle.

### 5. **Page d'accueil dynamique**
- Section hero avec statistiques dynamiques.
- Prochains événements, derniers articles, et galerie setups.
- Bandeau CTA pour inciter les visiteurs à s'inscrire.

---

## 🛠️ Installation

1. **Cloner le dépôt :**
   ```bash
   git clone https://github.com/votre-repo/cozy-gaming.git