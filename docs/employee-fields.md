# Champs des employés

Le super administrateur dispose de **Paramètres → Champs des employés** (`/settings/employee-fields`). Les réglages concernent toutes les entreprises de l’installation, comme la visibilité des modules. Ils s’appliquent à la création, à la modification et à la conversion d’un candidat en employé.

- Tous les champs sont affichés par défaut.
- Le nom, l’adresse e-mail et le mot de passe restent visibles : ils identifient le compte et permettent sa connexion.
- Les autres champs peuvent être masqués. Une valeur par défaut peut être définie pour les champs de texte, les dates, les listes et le salaire.
- Un champ nullable sans valeur par défaut reste `NULL`. Aucune date de naissance ni coordonnée bancaire n’est inventée.
- Le statut de l’employé est non nullable : la valeur par défaut initiale est `active`, et une valeur valide reste obligatoire.
- Les affectations masquées restent sans affectation à la création. Aucun identifiant de site, département, poste, horaire ou règle de présence n’est partagé entre entreprises.
- Masquer le site masque aussi le département et le poste ; masquer le département masque aussi le poste.
- L’identifiant employé reste généré automatiquement. Le code biométrique masqué reste vide à la création.
- Une photo masquée n’est pas exigée. Masquer les documents désactive leur demande dans ce formulaire, y compris les types habituellement obligatoires.
- Lors d’une modification, les données et fichiers masqués sont conservés, même si le navigateur transmet une autre valeur. Les valeurs par défaut ne remplacent pas les valeurs existantes, y compris `NULL`.
- Un changement d’affectation parent est refusé si une affectation dépendante masquée existe, pour éviter une incohérence entre site, département et poste.
- Une étape entièrement masquée est ignorée dans la navigation du formulaire.

Ce réglage simplifie les formulaires de saisie. Il ne masque pas les données dans les fiches, rapports ou exports et ne remplace pas les permissions.

## Stockage et déploiement

Le catalogue des champs est défini dans `config/employee-fields.php`. La configuration est enregistrée dans la table `settings`, sous la clé `employee_fields`, pour le super administrateur principal. Aucune migration de base de données n’est nécessaire.

Après publication et récupération du code sur le serveur, reconstruire l’interface avec `npm ci` puis `npm run build` dans l’environnement de déploiement. Actualiser le cache Laravel avec `php artisan optimize:clear`, puis appliquer les commandes habituelles de mise en cache du serveur.

## Vérification

```sh
php vendor/pestphp/pest/bin/pest tests/Unit/EmployeeFieldsTest.php
node --test tests/employee-fields.test.cjs
```

Les tests PHP utilisent une base SQLite en mémoire ; ils ne modifient pas les employés de l’installation.
