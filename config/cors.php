<?php

return [
    /*
     * Les routes Laravel qui acceptent les requêtes cross-origin
     * 'api/*' = toutes nos routes API sont concernées
     */
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    /*
     * Les méthodes HTTP autorisées depuis le frontend
     */
    'allowed_methods' => ['*'],

    /*
     * L'origine autorisée = notre frontend Nuxt sur le port 3000
     */
    'allowed_origins' => ['http://localhost:3000'],

    'allowed_origins_patterns' => [],

    /*
     * Les headers autorisés dans les requêtes
     */
    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    /*
     * Important pour Sanctum : autorise l'envoi des cookies
     * entre le frontend et le backend
     */
    'supports_credentials' => true,
];