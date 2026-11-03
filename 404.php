<?php

declare(strict_types=1);

http_response_code(404);

$path = (string) (parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '');
$locale = preg_match('#^/bg(/|$)#', $path) === 1 ? 'bg' : 'en';
$home = $locale === 'bg' ? '/bg/' : '/';

$copy = $locale === 'bg'
    ? [
        'title' => 'Страницата не е намерена | GOALGUS',
        'heading' => 'Страницата не е намерена',
        'body' => 'Линкът е невалиден или страницата е премахната.',
        'home' => 'Към началото',
    ]
    : [
        'title' => 'Page not found | GOALGUS',
        'heading' => 'Page not found',
        'body' => 'The link is broken or the page was removed.',
        'home' => 'Back to home',
    ];

$e = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="<?= $e($locale) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $e($copy['title']) ?></title>
    <meta name="robots" content="noindex">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700&family=DM+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/site.css?v=4041">
</head>
<body class="error-page">
    <main class="error-page__inner" id="main">
        <p class="error-page__code" aria-hidden="true">404</p>
        <h1 class="error-page__title"><?= $e($copy['heading']) ?></h1>
        <p class="error-page__body"><?= $e($copy['body']) ?></p>
        <a class="btn" href="<?= $e($home) ?>"><?= $e($copy['home']) ?></a>
    </main>
</body>
</html>
