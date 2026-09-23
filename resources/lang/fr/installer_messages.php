<?php

return [

    /*
     *
     * Shared translations.
     *
     */
    'title' => 'Installateur de Laravel',
    'next' => 'Suivant',
    'back' => 'Précédent',
    'finish' => 'Installer',
    'forms' => [
        'errorTitle' => 'Les erreurs suivantes sont survenues:',
    ],

    /*
     *
     * Home page translations.
     *
     */
    'welcome' => [
        'title'   => 'Bienvenue dans l’installateur...',
        'message' => 'Assistant d\'installation et de configuration facile.',
        'next'    => 'Vérifier les prérequis',
    ],

    /*
     *
     * Requirements page translations.
     *
     */
    'requirements' => [
        'templateTitle' => 'Étape 1 | Prérequis du serveur',
        'title' => 'Prérequis du serveur',
        'next'    => 'Vérifier les Permissions',
    ],

    /*
     *
     * Permissions page translations.
     *
     */
    'permissions' => [
        'templateTitle' => 'Étape 2 | Permissions',
        'title' => 'Permissions',
        'next' => 'Configurer l’environnement',
    ],

    /*
     *
     * Environment page translations.
     *
     */
    'environment' => [
        'menu' => [
            'templateTitle' => 'Étape 3 | Paramètres d\'environnement',
            'title' => 'Paramètres d\'environnement',
            'desc' => 'Choisissez comment configurer le fichier <code>.env</code> de l’application.',
            'wizard-button' => 'Utiliser l’assistant de configuration',
            'classic-button' => 'Éditeur de texte classique',
        ],
        'wizard' => [
            'templateTitle' => 'Étape 3 | Paramètres d\'environnement | Assistant guidé',
            'title' => 'Assistant de configuration du fichier <code>.env</code>',
            'tabs' => [
                'environment' => 'Environnement',
                'database' => 'Base de données',
                'application' => 'Application',
            ],
            'form' => [
                'name_required' => 'Un nom d\'environnement est requis.',
                'app_name_label' => 'Nom de l’application',
                'app_name_placeholder' => 'Nom de l’application',
                'app_environment_label' => 'Environnement de l’application',
                'app_environment_label_local' => 'Local',
                'app_environment_label_developement' => 'Développement',
                'app_environment_label_qa' => 'Assurance qualité',
                'app_environment_label_production' => 'Production',
                'app_environment_label_other' => 'Autre',
                'app_environment_placeholder_other' => 'Entrez votre environnement...',
                'app_debug_label' => 'Mode débogage',
                'app_debug_label_true' => 'Activé',
                'app_debug_label_false' => 'Désactivé',
                'app_log_level_label' => 'Niveau de journalisation',
                'app_log_level_label_debug' => 'Débogage',
                'app_log_level_label_info' => 'Information',
                'app_log_level_label_notice' => 'Notification',
                'app_log_level_label_warning' => 'Avertissement',
                'app_log_level_label_error' => 'Erreur',
                'app_log_level_label_critical' => 'Critique',
                'app_log_level_label_alert' => 'Alerte',
                'app_log_level_label_emergency' => 'Urgence',
                'app_url_label' => 'URL de l’application',
                'app_url_placeholder' => 'URL de l’application',
                'db_connection_label' => 'Type de base de données',
                'db_connection_label_mysql' => 'mysql',
                'db_connection_label_sqlite' => 'sqlite',
                'db_connection_label_pgsql' => 'pgsql',
                'db_connection_label_sqlsrv' => 'sqlsrv',
                'db_host_label' => 'Serveur de base de données',
                'db_host_placeholder' => 'Serveur de base de données',
                'db_port_label' => 'Port de la base de données',
                'db_port_placeholder' => 'Port de la base de données',
                'db_name_label' => 'Nom de la base de données',
                'db_name_placeholder' => 'Nom de la base de données',
                'db_username_label' => 'Nom d’utilisateur de la base de données',
                'db_username_placeholder' => 'Nom d’utilisateur de la base de données',
                'db_password_label' => 'Mot de passe de la base de données',
                'db_password_placeholder' => 'Mot de passe de la base de données',

                'app_tabs' => [
                    'more_info' => 'Plus d\'informations',
                    'broadcasting_title' => 'Diffusion, cache, sessions et file d’attente',
                    'broadcasting_label' => 'Pilote de diffusion',
                    'broadcasting_placeholder' => 'Pilote de diffusion',
                    'cache_label' => 'Pilote de cache',
                    'cache_placeholder' => 'Pilote de cache',
                    'session_label' => 'Pilote de sessions',
                    'session_placeholder' => 'Pilote de sessions',
                    'queue_label' => 'Pilote de file d’attente',
                    'queue_placeholder' => 'Pilote de file d’attente',
                    'redis_label' => 'Pilote Redis',
                    'redis_host' => 'Serveur Redis',
                    'redis_password' => 'Mot de passe Redis',
                    'redis_port' => 'Port Redis',

                    'mail_label' => 'Messagerie',
                    'mail_driver_label' => 'Pilote de messagerie',
                    'mail_driver_placeholder' => 'Pilote de messagerie',
                    'mail_host_label' => 'Serveur de messagerie',
                    'mail_host_placeholder' => 'Serveur de messagerie',
                    'mail_port_label' => 'Port de messagerie',
                    'mail_port_placeholder' => 'Port de messagerie',
                    'mail_username_label' => 'Nom d’utilisateur de messagerie',
                    'mail_username_placeholder' => 'Nom d’utilisateur de messagerie',
                    'mail_password_label' => 'Mot de passe de messagerie',
                    'mail_password_placeholder' => 'Mot de passe de messagerie',
                    'mail_encryption_label' => 'Chiffrement de la messagerie',
                    'mail_encryption_placeholder' => 'Chiffrement de la messagerie',

                    'pusher_label' => 'Pusher',
                    'pusher_app_id_label' => 'Identifiant de l’application Pusher',
                    'pusher_app_id_palceholder' => 'Identifiant de l’application Pusher',
                    'pusher_app_key_label' => 'Clé de l’application Pusher',
                    'pusher_app_key_palceholder' => 'Clé de l’application Pusher',
                    'pusher_app_secret_label' => 'Clé secrète de l’application Pusher',
                    'pusher_app_secret_palceholder' => 'Clé secrète de l’application Pusher',
                ],
                'buttons' => [
                    'setup_database' => 'Configurer la base de données',
                    'setup_application' => 'Configuration de l\'application',
                    'install' => 'Installer',
                ],
            ],
        ],
        'classic' => [
            'templateTitle' => 'Étape 3 | Paramètres d\'environnement | Editeur Classique',
            'title' => 'Éditeur de texte classique',
            'save' => 'Enregistrer .env',
            'back' => 'Utiliser le formulaire',
            'install' => 'Enregistrer et installer',
        ],
        'success' => 'Vos paramètres de fichier .env ont été enregistrés.',
        'errors' => 'Impossible de sauvegarder le fichier .env, veuillez le créer manuellement.',
    ],

    'install' => 'Installer',

    /*
     *
     * Final page translations.
     *
     */
    'final' => [
        'title' => 'Terminé',
        'templateTitle' => 'Installation terminée',
        'finished' => 'L’application a été installée avec succès.',
        'migration' => 'Résultat des migrations et du chargement des données initiales :',
        'console' => 'Sortie de la console de l’application :',
        'log' => 'Journal d’installation :',
        'env' => 'Fichier .env final :',
        'exit' => 'Cliquez ici pour quitter',
    ],

    /*
     *
     * Update specific translations
     *
     */
    'updater' => [
        /*
         *
         * Shared translations.
         *
         */
        'title' => 'Mise à jour de Laravel',

        /*
         *
         * Welcome page translations for update feature.
         *
         */
        'welcome' => [
            'title'   => 'Bienvenue dans l’assistant de mise à jour…',
            'message' => 'Bienvenue dans le programme de mise à jour.',
        ],

        /*
         *
         * Welcome page translations for update feature.
         *
         */
        'overview' => [
            'title'   => 'Aperçu',
            'message' => 'Il y a 1 mise à jour.|Il y a :number mises à jour.',
            'install_updates' => 'Installer la mise à jour',
        ],

        /*
         *
         * Final page translations.
         *
         */
        'final' => [
            'title' => 'Terminé',
            'finished' => 'L’application a été mise à jour avec succès.',
            'exit' => 'Cliquez ici pour quitter',
        ],

        'log' => [
            'success_message' => 'L\'installateur Laravel a été mis à jour avec succès le ',
        ],
    ],
];
