# Plateforme Web de Gestion d'Association

Plateforme web permettant à une association de gérer ses activités internes (Ressources Humaines, Projets, Bénévoles, Dons) et de présenter son travail au grand public via une vitrine en ligne.

> 📌 Projet réalisé dans le cadre d'un stage PFE (Projet de Fin d'Études).

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

Tables principales : `users`, `bureau_membres`, `candidatures_benevoles`, `categories_projets`, `projets`, `photos_projets`, `historique_projets`, `dons`, `messages_contact`, `collaborateurs`, `commentaires_projets`.

Le schéma complet est disponible dans [`database/schema.sql`](database/schema.sql) (+ [`database/schema_update_sprint4.sql`](database/schema_update_sprint4.sql) pour la table des commentaires, ajoutée en cours de développement).

---

##  État d'avancement (mis à jour chaque semaine)

### — Fondations
- [x] **Sprint 0** : mise en place de l'environnement XAMPP, arborescence du projet, `schema.sql` complet (10 tables)
- [x] **Sprint 1** : authentification admin sécurisée (`password_hash`, sessions PHP, requêtes préparées PDO, protection contre la fixation de session)

### — Back-office
- [x] **Sprint 2** : module RH — CRUD complet des membres du bureau (avec upload de photo sécurisé) et des collaborateurs ; suivi automatique des bénévoles/donateurs
- [x] **Sprint 3** : module Projets — CRUD complet avec catégories, cibles (`famille`/`village`/`ecole`/`orphelin`), budgets, photos multiples, et historique/rapport par projet. Intégration du **contenu réel de l'association** (18 projets, catégories et photos authentiques)

### — Vitrine publique
- [x] **Sprint 4** : site public complet (accueil, à propos, projets filtrables, détail projet, bureau, membres, galerie), design responsive aux couleurs de l'association (bleu `#1E3E8C` / orange `#E8622C` / doré `#F0B429`), navbar transparente, et **fonctionnalité additionnelle** : système de commentaires publics sur chaque projet (hors cahier des charges initial, ajouté sur demande)

### À venir
- [ ] **Sprint 5** : module Don (formulaire, enregistrement, mise à jour automatique du budget collecté, email de confirmation)
- [ ] **Sprint 6** : formulaire de contact + candidature bénévole ("Join us") avec emails automatiques (PHPMailer)
- [ ] **Sprint 7** : tests, sécurisation finale, déploiement

---

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

##  Identité visuelle

| Couleur | Usage |
|---|---|
| `#1E3E8C` (bleu) | Couleur institutionnelle principale |
| `#E8622C` (orange) | Couleur d'accent chaleureuse (dons, appels à l'action) |
| `#F0B429` (doré) | Couleur de mise en valeur (progression, chiffres clés) |

Typographies : **Cairo** (titres) et **Tajawal** (texte courant), site entièrement en arabe (RTL).

---

##  Auteur

Projet réalisé par **khadija MOURTAFIAA** dans le cadre d'un stage PFE.
