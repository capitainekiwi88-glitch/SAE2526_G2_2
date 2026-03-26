# SAE2526_G2_2 — Placement d'examens

Application web de gestion du placement aléatoire des étudiants en salles d'examen, développée à l'IUT de Metz (département Informatique).

**Équipe** : FEISTHAUER Simon, SCHOU Lilian, TOPAL Fatih, TOK Mikail

**Lien DevWeb** : [Accéder à l'application](https://devweb.iutmetz.univ-lorraine.fr/~e61222u/321654987123456789951357852465/DevWeb/321654987123456789/SAE401/SAE2526_G2_2/Placement/public/index.php?p=login)

---

## Objectif du projet

Le projet `Placement_old/` contenait une application PHP fonctionnelle mais vieillissante : code procédural, HTML mélangé au PHP, un fichier par page, pas de séparation claire entre logique métier et affichage.

L'objectif était de repartir de cette base pour produire une version modernisée dans `Placement/`, avec une architecture propre et maintenable.

---

## Structure du dépôt

```
Placement/          → Version modernisée (MVC)
Placement_old/      → Ancienne version (procédurale)
Rapports/           → Rapports hebdomadaires (PDF)
versions/           → Archives de versions (.zip)
```

---

## Placement/ — Nouvelle version

### Stack technique
- **PHP** avec autoloading PSR-4 (Composer)
- **Twig** pour le templating
- **PDO** pour l'accès base de données
- **FPDF** pour l'export PDF
- **Architecture en couches** : Entités, DAOs, Services, Routes, Templates

### Organisation

```
Placement/
├── public/             → Point d'entrée unique (index.php), CSS, images, exports PDF
├── routes/             → Routage (placement, gestion)
│   └── gestion/        → Sous-routes par entité (salle, enseignant, matière, etc.)
├── src/
│   ├── Modele/         → Entités + DAOs (14 entités : Salle, Etudiant, Enseignant, etc.)
│   └── Service/        → Logique métier (PlacementService)
├── templates/          → Templates Twig (login, navbar, gestion, placement)
└── lib/                → Bibliothèques (FPDF)
```

### Points clés
- **Front controller** : tout passe par `public/index.php` qui dispatche vers les fichiers de routes
- **Séparation des responsabilités** : les DAOs gèrent la BDD, les Services contiennent la logique métier, les Templates Twig gèrent l'affichage
- **Sécurité** : requêtes préparées (PDO), `password_hash()`, gestion de sessions avec contrôle d'accès

---

## Placement_old/ — Ancienne version

- ~40 fichiers PHP à la racine, un par page
- HTML/CSS/JS/SQL mélangés dans les fichiers PHP
- jQuery 1.7.1, CSS par page (`s_*.css`), JS par page
- Bibliothèques PDF embarquées (FPDF + EZpdf)

Conservé dans le dépôt comme référence.

---

## Améliorations apportées

| Aspect | Ancienne version | Nouvelle version |
|---|---|---|
| Architecture | Un fichier par page | Front controller, architecture en couches |
| Templating | HTML inline dans PHP | Twig |
| Base de données | SQL dispersé dans les pages | DAOs dédiés + Entités typées |
| Autoloading | `include` manuels | PSR-4 (Composer) |
| Organisation CSS | ~20 fichiers `s_*.css` | CSS intégré dans les templates Twig |
| Sécurité | PDO basique | Requêtes préparées, hash de mots de passe |

---

## Rapports

Les rapports hebdomadaires de suivi sont dans `Rapports/`.

**Rapport final** : `Rapports/` *(à venir)*
