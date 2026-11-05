<?php

declare(strict_types=1);

return [
    // Public contact endpoint on api.goalgus.bg (no Bearer required).
    'api_base' => 'https://api.goalgus.bg',
    'contact_path' => '/api/v1/contact',

    // AI Tools product API (Bearer). Create under dashboard → AI Tools → API keys
    // for client "goalgus", then paste the raw key here. Never commit config.php.
    'ai_api_token' => 'PASTE_AI_API_TOKEN_HERE',
    'ai_request_timeout' => 120,
];
