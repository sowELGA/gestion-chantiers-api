<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Domaine(s) autorisés à appeler l'API, séparés par des virgules.
    // En local : http://localhost:5173
    // En production : https://votre-frontend.vercel.app
    // On peut aussi utiliser la variable FRONTEND_URL déjà présente dans .env
    'allowed_origins' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('CORS_ALLOWED_ORIGINS', env('FRONTEND_URL', 'http://localhost:5173')))
    ))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
