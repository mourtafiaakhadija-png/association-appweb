# Plateforme Web de Gestion d'Association

Plateforme web permettant à l'**Association de Génération Créative** (جمعية الجيل المبدع, Taroudant) de gérer ses activités internes (Ressources Humaines, Projets, Bénévoles, Comités, Dons) et de présenter son travail au grand public via une vitrine en ligne entièrement en arabe.

> 📌 Projet réalisé dans le cadre d'un stage PFE (Projet de Fin d'Études), et destiné à un usage réel par l'association.

---

## Description du projet

L'application se compose désormais de **trois espaces distincts** :

- **Côté Admin** (`admin/`) : gestion interne complète de l'association — RH, bureau, bénévoles, projets et leurs éditions, validation des contenus, comités, dons, messages, candidatures, statistiques.
- **Côté Bénévole** (`benevole/`) : espace personnel pour chaque bénévole accepté — gestion des projets dont il est responsable, création d'éditions (soumises à validation admin), upload de rapports, réponse aux appels à bénévoles, historique de ses propres dons.
- **Côté Public** (`public/`) : vitrine de l'association, entièrement en arabe (RTL) — présentation, projets avec historique de leurs éditions successives, formulaire de don, contact, candidature bénévole.

---

## Stack technique

| Couche | Technologie |
|---|---|
| Front-end | HTML5, CSS3, JavaScript (vanilla) |
| Back-end | PHP (procédural) |
| Base de données | MySQL (via PDO, requêtes préparées) |
| Envoi d'email | PHPMailer (SMTP Gmail) |
| Authentification | Sessions PHP séparées par espace (`user_id` / `benevole_id`) |
| Upload de fichiers | `move_uploaded_file()` + validation MIME réelle (photos et rapports Word/PDF) |
| Graphiques | Chart.js (tableau de bord admin) |
| Icônes | Font Awesome |
| Environnement de développement | XAMPP (Apache + PHP + MySQL) |

---

## Rôles du système

| Rôle | Description |
|---|---|
| Admin | Super-utilisateur : gère RH, bureau, bénévoles, projets, éditions, comités, dons, messages, candidatures |
| Bénévole | Personne acceptée après candidature "Join us" — compte créé automatiquement, accès à son propre espace |
| Membre du Bureau | Personne listée en interne (nom, fonction, photo) — non affichée publiquement |
| Donateur | Visiteur qui fait un don sur un projet (pas de compte requis, suivi par email) |
| Collaborateur | Partenaire externe listé |
| Visiteur / public | Toute personne consultant le site sans compte |

---

## Fonctionnalités principales

### Côté Admin
- Gestion des Ressources Humaines (CRUD Bureau, Bénévoles, Donateurs, Collaborateurs)
- Gestion des projets : chaque projet est désormais une **fiche d'identité** (titre, catégorie, responsable, cible), le contenu concret vivant dans ses **éditions**
- **Système d'éditions multiples par projet** : chaque édition (ex: "Kafala Yatim 2024", "2025"...) a sa propre description, son propre budget, ses propres photos, sans jamais écraser l'historique des éditions précédentes
- **Workflow de validation** des éditions soumises par les bénévoles : modifier avant publication, ou renvoyer avec commentaire pour correction
- **Système de comités** : ouverture d'appels à bénévoles par édition, suivi des disponibilités déclarées, confirmation de l'équipe finale
- Gestion des candidatures "Join us" (accepter → création automatique du compte bénévole + email d'identifiants ; rejeter → email de réponse)
- Gestion des dons reçus (par projet et par édition)
- Gestion des messages de contact
- Modération des commentaires publics
- **Tableau de bord** avec statistiques clés et graphiques (dons par mois, répartition des projets par catégorie)
- Interface entièrement en arabe (RTL), dictionnaire de traduction centralisé (`includes/i18n_admin.php`)

### Côté Bénévole (nouveau)
- Connexion séparée de l'admin (`benevole/login.php`)
- Tableau de bord personnel : projets dont le bénévole est responsable, statistiques par projet
- Création et modification d'éditions de ses projets (statut "brouillon" → soumission pour validation)
- Upload du rapport de projet (Word/PDF), y compris après publication
- Réponse aux appels à bénévoles ouverts par l'admin ("je suis disponible")
- Historique de ses propres dons (reconnu automatiquement via son compte, sans ressaisir son email)
- Gestion de son mot de passe

### Côté Public
- Page d'accueil avec projets "à la une" (mis en avant par l'admin ou les plus récents)
- Liste des projets filtrable par catégorie, avec barre de progression du financement
- Page détail d'un projet : édition actuelle en avant + **frise chronologique de toutes les éditions passées validées** (évolution du projet dans le temps)
- Présélection automatique du projet dans le formulaire de don lors d'un clic depuis sa page détail
- Formulaire de don (suivi par email, sans compte requis)
- Formulaire de contact et candidature bénévole
- Commentaires publics par projet, avec protection contre les doubles identités (un même email conserve toujours le même nom affiché)
- Animations d'entrée du hero et apparition progressive des cartes au scroll (dégradation propre si JS indisponible)

---

## Structure du projet

```
association-appweb/
├── config/          # Connexion base de données + configuration SMTP (non versionnés)
├── includes/        # Éléments communs : headers/footers par espace, auth_check, mailer, i18n_admin, upload_helper
├── admin/           # Espace administrateur
├── benevole/        # Espace bénévole (nouveau)
├── public/          # Espace visiteur
├── uploads/         # Fichiers uploadés (images projets, rapports bénévoles)
└── database/        # schema.sql, schema_editions.sql, seed.sql, scripts de traduction/migration
```

---

## Base de données

Tables principales : `users`, `bureau_membres`, `candidatures_benevoles`, `categories_projets`, `projets`, **`projet_editions`** (nouveau), **`participations_comite`** (nouveau), `photos_projets`, `historique_projets`, `dons`, `messages_contact`, `collaborateurs`, `commentaires_projets`.

Schéma réparti sur plusieurs fichiers, à exécuter dans l'ordre :
1. [`database/schema.sql`](database/schema.sql) — schéma de base (10 tables)
2. [`database/schema_update.sql`](database/schema_update.sql) — table des commentaires
3. [`database/schema_editions.sql`](database/schema_editions.sql) — système d'éditions, comités, et migration automatique des projets existants en "édition 1" (aucune perte de données)
4. [`database/traduction_categories.sql`](database/traduction_categories.sql) — traduction du contenu des catégories en arabe

**Point de sécurité important** : les clés étrangères de `dons` vers `projets`/`projet_editions` sont en `ON DELETE SET NULL` (pas `CASCADE`) — la suppression d'un projet ou d'une édition ne supprime jamais l'historique financier des dons déjà reçus.

---

## Sécurité

- Mots de passe hachés (`password_hash`/`password_verify`), jamais stockés en clair
- Requêtes SQL exclusivement préparées (PDO)
- Upload de fichiers validé par type MIME réel (pas juste l'extension), taille limitée
- `uploads/.htaccess` interdisant l'exécution de scripts dans le dossier de stockage
- Sessions distinctes entre espace admin et espace bénévole
- Vérification systématique de propriété (`responsable_id`) avant toute action bénévole sur un projet
- Identifiants de connexion (base de données, SMTP) exclus du dépôt via `.gitignore`

---

## État d'avancement

Le projet est découpé en **7 sprints** sur une durée d'un mois, complétés par des itérations de finition (design, sécurité, fonctionnalités demandées par l'association en cours de route) :

| Sprint | Objectif | Statut |
|---|---|---|
| Sprint 0 | Environnement (XAMPP, base de données, arborescence) | ✅ |
| Sprint 1 | Authentification admin | ✅ |
| Sprint 2 | Module Ressources Humaines | ✅ |
| Sprint 3 | Module Projets | ✅ |
| Sprint 4 | Vitrine publique | ✅ |
| Sprint 5 | Module Don | ✅ |
| Sprint 6 | Module Contact + Join us | ✅ |
| — | Système d'éditions multiples par projet + workflow de validation | ✅ |
| — | Espace bénévole complet (tableau de bord, éditions, rapports, comités) | ✅ |
| — | Système de comités et appels à bénévoles | ✅ |
| — | Traduction arabe de l'espace admin + refonte visuelle (admin, bénévole, public) | ✅ |
| — | Tableau de bord admin avec statistiques et graphiques | ✅ |
| Sprint 7 | Tests, sécurisation finale, déploiement | ⏳ en cours |

---

## Identité visuelle

| Couleur | Usage |
|---|---|
| `#1E3E8C` (bleu) | Couleur institutionnelle principale |
| `#E8622C` (orange) | Couleur d'accent chaleureuse (dons, appels à l'action) |
| `#F0B429` (doré) | Couleur de mise en valeur (progression, chiffres clés, page active) |

Typographies : **Cairo** (titres) et **Tajawal** (texte courant). Les trois espaces (admin, bénévole, public) sont désormais entièrement en arabe (RTL) avec une identité visuelle cohérente (headers en dégradé bleu, pages de connexion en verre dépoli).

---

## Auteur

Projet réalisé par **Khadija Mourtafiaa** dans le cadre d'un stage PFE, pour l'Association de Génération Créative (Taroudant, Maroc).
