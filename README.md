# Cozy Gaming — Structure et Documentation

Bienvenue dans le projet **Cozy Gaming**, un site WordPress conçu pour une guilde gaming bienveillante. Ce projet inclut des fonctionnalités avancées telles que la gestion d'événements, des setups gaming, des articles enrichis, et des rôles personnalisés.

## 📂 Structure des fichiers

cozy-gaming/
- **assets/** : Fichiers statiques (CSS, JS, images)
  - **css/** : Feuilles de style
    - `main.css` : Styles globaux
    - `cozy-hero.css` : Styles pour la section hero
    - `cozy-articles.css` : Styles pour les articles et le grimoire
    - `cozy-setups.css` : Styles pour la galerie setups
    - `cozy-contact.css` : Styles pour le formulaire de contact
    - ... : Autres styles spécifiques
  - **js/** : Scripts JavaScript
    - `main.js` : Script principal (menu, animations)
    - `cozy-homepage.js` : Interactions spécifiques à la page d'accueil
    - `cozy-setups.js` : Gestion des setups (upload, suppression, lightbox)
    - ... : Autres scripts spécifiques
  - **images/** : Images statiques utilisées dans le thème
- **inc/** : Modules PHP personnalisés
  - `cozy-articles.php` : Gestion des articles enrichis (ACF)
  - `cozy-setups.php` : Gestion de la galerie setups
  - `cozy-friend-codes.php` : Gestion des codes ami par plateforme
  - `cozy-contact.php` : Gestion du formulaire de contact
  - `cozy-dashboard-widgets.php` : Widgets personnalisés pour le tableau de bord
  - ... : Autres modules
- **template-parts/** : Templates réutilisables
  - `content.php` : Template pour le contenu des articles
  - ... : Autres templates
- `front-page.php` : Template de la page d'accueil
- `single.php` : Template des articles individuels
- `search.php` : Template des résultats de recherche
- `comments.php` : Gestion des commentaires
- `functions.php` : Configuration principale du thème
- `header.php` : En-tête du site
- `footer.php` : Pied de page du site
- `style.css` : Feuille de style principale du thème


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
```