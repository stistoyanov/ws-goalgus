<?php

declare(strict_types=1);

require_once __DIR__.'/helpers.php';
require_once __DIR__.'/content.php';

/**
 * @param  array{locale:string,copy:array,company:array,portfolio:array}  $ctx
 */
function g_render_layout(array $ctx, string $page, string $bodyHtml, string $metaTitle, string $metaDescription): void
{
    $locale = $ctx['locale'];
    $copy = $ctx['copy'];
    $company = $ctx['company'];
    $nav = $copy['nav'] ?? [];
    $theme = $copy['theme'] ?? [];
    $footer = $copy['footer'] ?? [];
    $prefix = $locale === 'bg' ? '/bg' : '';
    $alt = g_alt_locale_path($locale, $page);
    $altLabel = $locale === 'bg' ? 'EN' : 'BG';
    $legal = $company['legal_name'] ?? 'GOALGUS LTD';
    $year = date('Y');

    $pages = [
        'home' => ['href' => $prefix.'/', 'label' => $nav['home'] ?? 'Home'],
        'about' => ['href' => $prefix.'/about/', 'label' => $nav['about'] ?? 'About'],
        'founder' => ['href' => $prefix.'/founder/', 'label' => $nav['founder'] ?? 'Founder'],
        'services' => ['href' => $prefix.'/services/', 'label' => $nav['services'] ?? 'Services'],
        'portfolio' => ['href' => $prefix.'/portfolio/', 'label' => $nav['portfolio'] ?? 'Portfolio'],
        'contact' => ['href' => $prefix.'/contact/', 'label' => $nav['contact'] ?? 'Contact'],
    ];

    $config = g_config();
    $apiBase = rtrim((string) ($config['api_base'] ?? 'https://api.goalgus.bg'), '/');
    $contactPath = (string) ($config['contact_path'] ?? '/api/v1/contact');
    $canonical = g_canonical($locale, $page);
    $hreflang = g_hreflang_pair($page);
    $ogImage = $page === 'founder'
        ? g_site_origin($locale).'/assets/images/brand/stiliyan-stoyanov.jpg'
        : g_site_origin($locale).'/assets/images/brand/goalgus_shield.png';

    header('Content-Type: text/html; charset=UTF-8');
    ?>
<!DOCTYPE html>
<html lang="<?= g_esc($locale) ?>" data-theme="auto">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= g_esc($metaTitle) ?></title>
    <meta name="description" content="<?= g_esc($metaDescription) ?>">
    <link rel="canonical" href="<?= g_esc($canonical) ?>">
    <link rel="alternate" hreflang="bg" href="<?= g_esc($hreflang['bg']) ?>">
    <link rel="alternate" hreflang="en" href="<?= g_esc($hreflang['en']) ?>">
    <link rel="alternate" hreflang="x-default" href="<?= g_esc($hreflang['en']) ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="<?= $locale === 'bg' ? 'bg_BG' : 'en_GB' ?>">
    <meta property="og:site_name" content="GOALGUS">
    <meta property="og:title" content="<?= g_esc($metaTitle) ?>">
    <meta property="og:description" content="<?= g_esc($metaDescription) ?>">
    <meta property="og:url" content="<?= g_esc($canonical) ?>">
    <meta property="og:image" content="<?= g_esc($ogImage) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= g_esc($metaTitle) ?>">
    <meta name="twitter:description" content="<?= g_esc($metaDescription) ?>">
    <meta name="twitter:image" content="<?= g_esc($ogImage) ?>">
    <meta name="theme-color" content="#070b14" media="(prefers-color-scheme: dark)">
    <meta name="theme-color" content="#eef3f9" media="(prefers-color-scheme: light)">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/assets/images/brand/goalgus_shield.png" type="image/png">
    <link rel="apple-touch-icon" href="/assets/images/brand/goalgus_shield.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=JetBrains+Mono:wght@400;500&family=Syne:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/site.css?v=founder2">
    <?php
    g_json_ld($company, $locale, $metaDescription, g_site_origin($locale).'/assets/images/brand/goalgus_shield.png');
    if ($page === 'founder') {
        g_person_json_ld($company, $locale, $metaDescription, $ogImage, $canonical);
    }
    ?>
    <script>
      (function () {
        try {
          var key = 'goalgus-theme';
          var stored = localStorage.getItem(key) || 'auto';
          var hour = new Date().getHours();
          var autoDark = hour < 6 || hour >= 18;
          var theme = stored === 'light' || stored === 'dark'
            ? stored
            : (autoDark ? 'dark' : 'light');
          document.documentElement.setAttribute('data-theme', theme);
          document.documentElement.setAttribute('data-theme-mode', stored);
        } catch (e) {}
      })();
    </script>
</head>
<body>
    <a class="skip" href="#main">Skip to content</a>
    <header class="site-header">
        <div class="shell header-inner">
            <a class="brand" href="<?= g_esc($prefix.'/') ?>" aria-label="<?= g_esc($legal) ?>">
                <img src="/assets/images/brand/goalgus_shield.png" width="36" height="36" alt="">
                <span>GOALGUS</span>
            </a>
            <nav class="nav" aria-label="Primary">
                <?php foreach ($pages as $key => $item): ?>
                    <a href="<?= g_esc($item['href']) ?>"<?= $key === $page ? ' aria-current="page"' : '' ?>><?= g_esc($item['label']) ?></a>
                <?php endforeach; ?>
            </nav>
            <div class="header-tools">
                <div class="theme-switch" role="group" aria-label="<?= g_esc($theme['label'] ?? 'Theme') ?>">
                    <button type="button" data-theme-set="auto" title="<?= g_esc($theme['auto'] ?? 'Auto') ?>">A</button>
                    <button type="button" data-theme-set="light" title="<?= g_esc($theme['light'] ?? 'Light') ?>">L</button>
                    <button type="button" data-theme-set="dark" title="<?= g_esc($theme['dark'] ?? 'Dark') ?>">D</button>
                </div>
                <a class="lang-switch" href="<?= g_esc($alt) ?>" hreflang="<?= $locale === 'bg' ? 'en' : 'bg' ?>"><?= g_esc($altLabel) ?></a>
                <a class="btn btn-small" href="<?= g_esc($prefix.'/contact/') ?>"><?= g_esc($nav['cta'] ?? 'Contact') ?></a>
            </div>
            <button type="button" class="nav-toggle" aria-expanded="false" aria-controls="mobile-nav" data-nav-toggle>
                <span></span><span></span>
            </button>
        </div>
        <nav id="mobile-nav" class="mobile-nav" hidden>
            <?php foreach ($pages as $key => $item): ?>
                <a href="<?= g_esc($item['href']) ?>"<?= $key === $page ? ' aria-current="page"' : '' ?>><?= g_esc($item['label']) ?></a>
            <?php endforeach; ?>
            <div class="mobile-nav__tools">
                <div class="theme-switch" role="group" aria-label="<?= g_esc($theme['label'] ?? 'Theme') ?>">
                    <button type="button" data-theme-set="auto" title="<?= g_esc($theme['auto'] ?? 'Auto') ?>">A</button>
                    <button type="button" data-theme-set="light" title="<?= g_esc($theme['light'] ?? 'Light') ?>">L</button>
                    <button type="button" data-theme-set="dark" title="<?= g_esc($theme['dark'] ?? 'Dark') ?>">D</button>
                </div>
                <a class="lang-switch" href="<?= g_esc($alt) ?>" hreflang="<?= $locale === 'bg' ? 'en' : 'bg' ?>"><?= g_esc($altLabel) ?></a>
                <a class="btn btn-small" href="<?= g_esc($prefix.'/contact/') ?>"><?= g_esc($nav['cta'] ?? 'Contact') ?></a>
            </div>
        </nav>
    </header>

    <main id="main">
        <?= $bodyHtml ?>
    </main>

    <footer class="site-footer">
        <div class="shell footer-inner">
            <div>
                <a class="brand brand--footer" href="<?= g_esc($prefix.'/') ?>" aria-label="<?= g_esc($legal) ?>">
                    <img src="/assets/images/brand/goalgus_shield.png" width="32" height="32" alt="">
                    <span>GOALGUS</span>
                </a>
                <p><?= g_esc($footer['tagline'] ?? '') ?></p>
            </div>
            <div class="footer-meta">
                <p><?= g_esc($locale === 'bg' && ! empty($company['legal_name_bg']) ? $company['legal_name_bg'] : $legal) ?></p>
                <p><?= $locale === 'bg' ? 'ЕИК' : 'UIC' ?> <?= g_esc((string) ($company['uic'] ?? '')) ?></p>
                <?php if (! empty($company['vat_id'])): ?>
                    <p><?= $locale === 'bg' ? 'ДДС' : 'VAT' ?> <?= g_esc((string) $company['vat_id']) ?></p>
                <?php endif; ?>
                <p><a href="<?= g_esc($apiBase) ?>/" rel="noopener noreferrer"><?= g_esc($footer['api_label'] ?? 'api.goalgus.bg') ?></a></p>
                <p>© <?= g_esc($year) ?> <?= g_esc($legal) ?>. <?= g_esc($footer['rights'] ?? '') ?></p>
            </div>
        </div>
    </footer>

    <script>
      window.__GOALGUS__ = {
        locale: <?= json_encode($locale, JSON_UNESCAPED_UNICODE) ?>,
        contactUrl: <?= json_encode($apiBase.$contactPath, JSON_UNESCAPED_SLASHES) ?>,
        strings: <?= json_encode($copy['contact'] ?? [], JSON_UNESCAPED_UNICODE) ?>
      };
    </script>
    <script src="/assets/js/site.js?v=founder2" defer></script>
</body>
</html>
    <?php
}
