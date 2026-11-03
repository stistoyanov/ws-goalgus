<?php

declare(strict_types=1);

function g_load_json(string $file): array
{
    $path = dirname(__DIR__).'/data/'.$file;
    if (! is_file($path)) {
        return [];
    }

    $data = json_decode((string) file_get_contents($path), true);

    return is_array($data) ? $data : [];
}

function g_content(string $locale): array
{
    $locale = $locale === 'bg' ? 'bg' : 'en';
    $copy = g_load_json($locale.'.json');
    $company = g_load_json('company.json');
    $portfolio = g_load_json('portfolio.json');

    return [
        'locale' => $locale,
        'copy' => $copy,
        'company' => $company,
        'portfolio' => $portfolio,
    ];
}

function g_path_for_locale(string $locale, string $page = 'home'): string
{
    $map = [
        'home' => '',
        'about' => 'about/',
        'founder' => 'founder/',
        'services' => 'services/',
        'portfolio' => 'portfolio/',
        'contact' => 'contact/',
    ];
    $suffix = $map[$page] ?? '';

    if ($locale === 'bg') {
        return '/bg/'.($suffix !== '' ? $suffix : '');
    }

    return '/'.($suffix !== '' ? $suffix : '');
}

function g_alt_locale_path(string $locale, string $page): string
{
    return g_path_for_locale($locale === 'bg' ? 'en' : 'bg', $page);
}

/**
 * Canonical host: BG content on goalgus.bg, EN on goalgus.com.
 */
function g_site_origin(string $locale): string
{
    return $locale === 'bg' ? 'https://goalgus.bg' : 'https://goalgus.com';
}

function g_canonical(string $locale, string $page): string
{
    $path = g_path_for_locale($locale, $page);

    return rtrim(g_site_origin($locale).$path, '/').'/';
}

/**
 * @return array{bg:string,en:string}
 */
function g_hreflang_pair(string $page): array
{
    return [
        'bg' => g_canonical('bg', $page),
        'en' => g_canonical('en', $page),
    ];
}

/**
 * Emit Organization JSON-LD for GOALGUS LTD.
 *
 * @param  array<string, mixed>  $company
 */
function g_json_ld(array $company, string $locale, string $description, string $ogImage): void
{
    $locale = $locale === 'bg' ? 'bg' : 'en';
    $name = (string) ($company['legal_name'] ?? 'GOALGUS LTD');
    $address = $locale === 'bg' && ! empty($company['registered_address_bg'])
        ? (string) $company['registered_address_bg']
        : (string) ($company['registered_address'] ?? '');

    $data = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $name,
        'legalName' => $name,
        'url' => 'https://goalgus.com/',
        'logo' => $ogImage,
        'image' => $ogImage,
        'description' => $description,
        'email' => (string) ($company['contact_email'] ?? 'contact@goalgus.bg'),
        'telephone' => (string) ($company['phone_e164'] ?? ($company['phone'] ?? '')),
        'vatID' => (string) ($company['vat_id'] ?? ''),
        'taxID' => (string) ($company['uic'] ?? ''),
        'foundingDate' => (string) ($company['established'] ?? ''),
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => $address,
            'addressLocality' => 'Sofia',
            'postalCode' => '1421',
            'addressCountry' => 'BG',
        ],
        'sameAs' => [
            'https://goalgus.bg/',
            'https://goalgus.com/',
        ],
        'contactPoint' => [
            '@type' => 'ContactPoint',
            'contactType' => 'customer support',
            'email' => (string) ($company['contact_email'] ?? 'contact@goalgus.bg'),
            'telephone' => (string) ($company['phone_e164'] ?? ''),
            'availableLanguage' => ['en', 'bg'],
        ],
    ];

    echo '<script type="application/ld+json">'.json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP).'</script>'."\n";
}

/**
 * Emit Person JSON-LD for the Founder page.
 *
 * @param  array<string, mixed>  $company
 */
function g_person_json_ld(array $company, string $locale, string $description, string $image, string $url): void
{
    $orgName = (string) ($company['legal_name'] ?? 'GOALGUS LTD');
    $email = (string) ($company['contact_email'] ?? 'contact@goalgus.bg');

    $data = [
        '@context' => 'https://schema.org',
        '@type' => 'Person',
        'name' => 'Stiliyan Stoyanov',
        'jobTitle' => $locale === 'bg'
            ? 'Основател · Lead Software Engineer · Solution Architect'
            : 'Founder · Lead Software Engineer · Solution Architect',
        'url' => $url,
        'image' => $image,
        'email' => $email,
        'telephone' => (string) ($company['phone_e164'] ?? ($company['phone'] ?? '')),
        'description' => $description,
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => 'Sofia',
            'addressCountry' => 'BG',
        ],
        'worksFor' => [
            '@type' => 'Organization',
            'name' => $orgName,
            'url' => 'https://goalgus.com/',
        ],
        'knowsLanguage' => ['bg', 'en'],
    ];

    echo '<script type="application/ld+json">'.json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP).'</script>'."\n";
}
