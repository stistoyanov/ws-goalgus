<?php

declare(strict_types=1);

function g_esc(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function g_config(): array
{
    static $config;
    if ($config !== null) {
        return $config;
    }

    $path = dirname(__DIR__).'/config.php';
    $sample = dirname(__DIR__).'/config.sample.php';
    $config = is_file($path) ? require $path : (is_file($sample) ? require $sample : []);

    return is_array($config) ? $config : [];
}

function g_base_path(): string
{
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    // When using router.php, SCRIPT_NAME is /router.php — treat as root.
    if (str_ends_with($script, '/router.php') || $script === '/router.php') {
        return '';
    }

    $dir = str_replace('\\', '/', dirname($script));
    if ($dir === '/' || $dir === '.' || $dir === '') {
        return '';
    }

    // Strip /bg or page folders for asset base — pages pass $base explicitly.
    return rtrim($dir, '/');
}

/**
 * Data-driven locale list for the language dropdown (extend later without UI rewrite).
 *
 * @return list<array{code:string,label:string,href:string}>
 */
function g_locale_options(string $page): array
{
    $pair = g_hreflang_pair($page);

    return [
        ['code' => 'bg', 'label' => 'Български', 'href' => $pair['bg']],
        ['code' => 'en', 'label' => 'English', 'href' => $pair['en']],
    ];
}

/**
 * @param  list<array{code:string,label:string,href:string}>  $locales
 */
function g_render_lang_switch(string $current, array $locales, string $ariaLabel): void
{
    $current = $current === 'bg' ? 'bg' : 'en';
    ?>
<details class="lang-menu" data-locale-switch>
    <summary class="lang-menu__trigger" aria-label="<?= g_esc($ariaLabel) ?>">
        <svg class="lang-menu__globe" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
        <span class="lang-menu__code"><?= g_esc($current) ?></span>
        <svg class="lang-menu__chevron" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
    </summary>
    <div class="lang-menu__menu" role="listbox" aria-label="<?= g_esc($ariaLabel) ?>">
        <?php foreach ($locales as $item):
            $code = (string) ($item['code'] ?? '');
            $active = $code === $current;
            ?>
            <a
                class="lang-menu__option<?= $active ? ' is-active' : '' ?>"
                href="<?= g_esc((string) ($item['href'] ?? '#')) ?>"
                hreflang="<?= g_esc($code) ?>"
                lang="<?= g_esc($code) ?>"
                role="option"
                <?php if ($active): ?>aria-current="true" aria-selected="true"<?php else: ?>aria-selected="false"<?php endif; ?>
            >
                <span class="lang-menu__name"><?= g_esc((string) ($item['label'] ?? $code)) ?></span>
                <span class="lang-menu__meta">
                    <span class="lang-menu__opt-code"><?= g_esc($code) ?></span>
                    <?php if ($active): ?><span class="lang-menu__check" aria-hidden="true">✓</span><?php endif; ?>
                </span>
            </a>
        <?php endforeach; ?>
    </div>
</details>
    <?php
}
