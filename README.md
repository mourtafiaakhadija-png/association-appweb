# Plateforme Web de Gestion d'Association

Plateforme web permettant à une association de gérer ses activités internes (Ressources Humaines, Projets, Bénévoles, Dons) et de présenter son travail au grand public via une vitrine en ligne.

>  Projet réalisé dans le cadre d'un stage PFE (Projet de Fin d'Études).

---

##  Description du projet

L'application se compose de **deux espaces distincts** :

- **Côté Admin** : gestion interne de l'association (Ressources Humaines, membres du Bureau, Bénévoles, Projets, Responsables, Cibles, Dons, Messages) avec historisation complète des actions (traçabilité / rapports).
- **Côté Public** : vitrine de l'association — présentation, liste des projets, formulaire de don, formulaire de contact, candidature bénévole ("Join us").

---

##  Stack technique

| Couche | Technologie |
|---|---|
| Front-end | HTML5, CSS3, JavaScript |
| Back-end | PHP |
| Base de données | MySQL (via PDO) |
| Envoi d'email | PHPMailer |
| Authentification | Sessions PHP (`$_SESSION`) |
| Upload de fichiers | `move_uploaded_file()` (PHP natif) |
| Environnement de développement | XAMPP (Apache + PHP + MySQL) |

---

##  Rôles du système

| Rôle | Description |
|---|---|
| Admin | Super-utilisateur : gère RH, bureau, bénévoles, projets, dons, messages, historique |
| Membre du Bureau | Personne listée publiquement (nom, fonction, photo) |
| Bénévole | Personne acceptée après candidature "Join us" |
| Donateur | Visiteur qui fait un don sur un projet (pas forcément inscrit) |
| Collaborateur | Partenaire externe listé |
| Visiteur / User public | Toute personne consultant le site sans compte |

---

##  Fonctionnalités principales

### Côté Admin
- Gestion des Ressources Humaines (CRUD Bureau, Bénévoles, Donateurs, Collaborateurs)
- Gestion des projets (création détaillée : titre, catégorie, responsable, cible, budget, dates, statut)
- Historisation de chaque action sur un projet (génération de rapports)
- Gestion des candidatures "Join us" (accepter/rejeter avec email automatique)
- Gestion des dons reçus (filtrable par projet)
- Gestion des messages de contact

### Côté Public
- Page d'accueil, À propos, Galerie
- Liste des projets filtrable par catégorie + page détail
- Page Bureau / Membres
- Formulaire de don par projet
- Formulaire de contact
- Formulaire de candidature bénévole ("Join us")

---

##  Structure du projet

```
association-appweb/
├── config/          # Connexion à la base de données (db.php)
├── includes/        # Éléments communs : header, footer, auth_check, mailer
├── admin/           # Pages et logique de l'espace administrateur
├── public/          # Pages et logique de l'espace visiteur
├── uploads/         # Stockage des images uploadées
└── database/        # schema.sql, seed.sql
```

---

##  Base de données

Tables principales : `users`, `bureau_membres`, `candidatures_benevoles`, `categories_projets`, `projets`, `photos_projets`, `historique_projets`, `dons`, `messages_contact`, `collaborateurs`.

Le schéma complet est disponible dans [`database/schema.sql`](database/schema.sql).

---

##  Installation locale

1. Installer [XAMPP](https://www.apachefriends.org/) (Apache + PHP + MySQL)
2. Cloner ce dépôt dans le dossier `htdocs` de XAMPP :
   ```bash
   git clone https://github.com/mourtafiaakhadija-png/association-appweb.git
   ```
3. Démarrer Apache et MySQL depuis le panneau de contrôle XAMPP
4. Créer une base de données nommée `association_db` dans phpMyAdmin (collation `utf8mb4_unicode_ci`)
5. Importer le fichier `database/schema.sql` dans cette base via l'onglet **Import** de phpMyAdmin
6. Configurer la connexion à la base dans `config/db.php` (identifiants MySQL locaux)
7. Accéder au projet via `http://localhost/association-appweb/`

---

##  Planning de développement

Le projet est découpé en **7 sprints** sur une durée d'un mois :

| Sprint | Objectif |
|---|---|
| Sprint 0 | Mise en place de l'environnement (XAMPP, base de données, arborescence) |
| Sprint 1 | Authentification admin |
| Sprint 2 | Module Ressources Humaines |
| Sprint 3 | Module Projets |
| Sprint 4 | Vitrine publique |
| Sprint 5 | Module Don |
| Sprint 6 | Module Contact + Join us |
| Sprint 7 | Tests, corrections et déploiement |

---

##  Auteur

Projet réalisé par **khadija Mourtafiaa** dans le cadre d'un stage PFE.
