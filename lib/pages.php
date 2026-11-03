<?php

declare(strict_types=1);

require_once __DIR__.'/layout.php';

function g_page(string $locale, string $page): void
{
    $ctx = g_content($locale);
    $copy = $ctx['copy'];
    $company = $ctx['company'];
    $portfolio = $ctx['portfolio'];
    $prefix = $locale === 'bg' ? '/bg' : '';
    $meta = $copy['meta'] ?? [];
    $suffix = $meta['title_suffix'] ?? 'GOALGUS LTD';

    ob_start();

    if ($page === 'home') {
        $h = $copy['home'] ?? [];
        ?>
        <section class="hero">
            <div class="hero__media" aria-hidden="true">
                <canvas data-binary-rain></canvas>
                <div class="hero__veil"></div>
            </div>
            <div class="shell hero__content">
                <p class="hero__brand">GOALGUS</p>
                <p class="eyebrow"><?= g_esc($h['eyebrow'] ?? '') ?></p>
                <h1><?= g_esc($h['headline'] ?? '') ?></h1>
                <p class="lead"><?= g_esc($h['lead'] ?? '') ?></p>
                <div class="cta-row">
                    <a class="btn" href="<?= g_esc($prefix.'/contact/') ?>"><?= g_esc($h['cta_primary'] ?? '') ?></a>
                    <a class="btn btn-ghost" href="<?= g_esc($prefix.'/portfolio/') ?>"><?= g_esc($h['cta_secondary'] ?? '') ?></a>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="shell">
                <div class="section-head reveal">
                    <h2><?= g_esc($h['strip_title'] ?? '') ?></h2>
                    <p><?= g_esc($h['strip_lead'] ?? '') ?></p>
                </div>
                <div class="pillars">
                    <?php foreach (($h['pillars'] ?? []) as $pillar): ?>
                        <article class="pillar reveal">
                            <div class="code"><?= g_esc($pillar['code'] ?? '') ?></div>
                            <h3><?= g_esc($pillar['title'] ?? '') ?></h3>
                            <p><?= g_esc($pillar['text'] ?? '') ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="shell">
                <div class="section-head reveal">
                    <h2><?= g_esc($h['portfolio_title'] ?? '') ?></h2>
                    <p><?= g_esc($h['portfolio_lead'] ?? '') ?></p>
                </div>
                <div class="work-list">
                    <?php foreach ($portfolio as $item): ?>
                        <?php
                        $title = $locale === 'bg' ? ($item['title_bg'] ?? '') : ($item['title_en'] ?? '');
                        $tag = $locale === 'bg' ? ($item['tag_bg'] ?? '') : ($item['tag_en'] ?? '');
                        $summary = $locale === 'bg' ? ($item['summary_bg'] ?? '') : ($item['summary_en'] ?? '');
                        ?>
                        <a class="work-item reveal" href="<?= g_esc($item['url'] ?? '#') ?>" rel="noopener" target="_blank">
                            <span class="tag"><?= g_esc($tag) ?></span>
                            <h3><?= g_esc($title) ?></h3>
                            <p><?= g_esc($summary) ?></p>
                        </a>
                    <?php endforeach; ?>
                </div>
                <p style="margin-top:1.5rem">
                    <a class="btn btn-ghost" href="<?= g_esc($prefix.'/portfolio/') ?>"><?= g_esc($h['portfolio_cta'] ?? '') ?></a>
                </p>
            </div>
        </section>
        <?php
        $title = ($meta['home_title'] ?? 'GOALGUS').' · '.$suffix;
        $desc = $meta['home_description'] ?? '';
    } elseif ($page === 'about') {
        $a = $copy['about'] ?? [];
        ?>
        <section class="page-hero shell">
            <p class="eyebrow">GOALGUS</p>
            <h1><?= g_esc($a['headline'] ?? '') ?></h1>
            <p class="lead"><?= g_esc($a['lead'] ?? '') ?></p>
        </section>
        <section class="section">
            <div class="shell reveal">
                <?php foreach (($a['body'] ?? []) as $para): ?>
                    <p class="lead"><?= g_esc($para) ?></p>
                <?php endforeach; ?>
                <h2 style="font-family:var(--font-display);margin-top:2.5rem"><?= g_esc($a['facts_title'] ?? '') ?></h2>
                <?php
                $fact = static function (array $company, string $locale, string $enKey, string $bgKey = '') {
                    $bgKey = $bgKey !== '' ? $bgKey : $enKey.'_bg';
                    if ($locale === 'bg' && ! empty($company[$bgKey])) {
                        return (string) $company[$bgKey];
                    }

                    return (string) ($company[$enKey] ?? '');
                };
                $copyLabel = $locale === 'bg' ? 'Копирай' : 'Copy';
                $copiedLabel = $locale === 'bg' ? 'Копирано' : 'Copied';
                $facts = [
                    ['label' => $locale === 'bg' ? 'Правно име' : 'Legal name', 'value' => $fact($company, $locale, 'legal_name')],
                    ['label' => $locale === 'bg' ? 'ЕИК' : 'UIC', 'value' => (string) ($company['uic'] ?? '')],
                    ['label' => $locale === 'bg' ? 'ДДС номер' : 'VAT ID', 'value' => (string) ($company['vat_id'] ?? '')],
                    ['label' => $locale === 'bg' ? 'Правна форма' : 'Legal form', 'value' => $fact($company, $locale, 'legal_form')],
                    ['label' => $locale === 'bg' ? 'Управител' : 'Managing director', 'value' => $fact($company, $locale, 'managing_director')],
                    ['label' => $locale === 'bg' ? 'Телефон' : 'Phone', 'value' => (string) ($company['phone'] ?? '')],
                    ['label' => $locale === 'bg' ? 'Адрес' : 'Registered address', 'value' => $fact($company, $locale, 'registered_address')],
                    ['label' => $locale === 'bg' ? 'Основана' : 'Established', 'value' => $fact($company, $locale, 'established')],
                ];
                ?>
                <dl class="facts">
                    <?php foreach ($facts as $row): ?>
                        <?php if ($row['value'] === '') { continue; } ?>
                        <div class="fact">
                            <dt><?= g_esc($row['label']) ?></dt>
                            <dd>
                                <span class="fact__value"><?= g_esc($row['value']) ?></span>
                                <button
                                    type="button"
                                    class="fact__copy"
                                    data-copy="<?= g_esc($row['value']) ?>"
                                    data-label-copy="<?= g_esc($copyLabel) ?>"
                                    data-label-copied="<?= g_esc($copiedLabel) ?>"
                                ><?= g_esc($copyLabel) ?></button>
                            </dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
                <h2 style="font-family:var(--font-display);margin-top:2.5rem"><?= g_esc($a['scope_title'] ?? '') ?></h2>
                <p class="lead"><?= g_esc($fact($company, $locale, 'scope_of_business')) ?></p>
            </div>
        </section>
        <?php
        $title = ($meta['about_title'] ?? 'About').' · '.$suffix;
        $desc = $meta['about_description'] ?? '';
    } elseif ($page === 'services') {
        $s = $copy['services'] ?? [];
        ?>
        <section class="page-hero shell">
            <p class="eyebrow">GOALGUS</p>
            <h1><?= g_esc($s['headline'] ?? '') ?></h1>
            <p class="lead"><?= g_esc($s['lead'] ?? '') ?></p>
        </section>
        <section class="section">
            <div class="shell">
                <div class="services-grid">
                    <?php foreach (($s['items'] ?? []) as $item): ?>
                        <article class="service reveal">
                            <h3><?= g_esc($item['title'] ?? '') ?></h3>
                            <p><?= g_esc($item['text'] ?? '') ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php
        $title = ($meta['services_title'] ?? 'Services').' · '.$suffix;
        $desc = $meta['services_description'] ?? '';
    } elseif ($page === 'portfolio') {
        $p = $copy['portfolio'] ?? [];
        ?>
        <section class="page-hero shell">
            <p class="eyebrow">GOALGUS</p>
            <h1><?= g_esc($p['headline'] ?? '') ?></h1>
            <p class="lead"><?= g_esc($p['lead'] ?? '') ?></p>
        </section>
        <section class="section">
            <div class="shell">
                <div class="work-list">
                    <?php foreach ($portfolio as $item): ?>
                        <?php
                        $titleItem = $locale === 'bg' ? ($item['title_bg'] ?? '') : ($item['title_en'] ?? '');
                        $tag = $locale === 'bg' ? ($item['tag_bg'] ?? '') : ($item['tag_en'] ?? '');
                        $summary = $locale === 'bg' ? ($item['summary_bg'] ?? '') : ($item['summary_en'] ?? '');
                        ?>
                        <a class="work-item reveal" href="<?= g_esc($item['url'] ?? '#') ?>" rel="noopener" target="_blank">
                            <span class="tag"><?= g_esc($tag) ?></span>
                            <h3><?= g_esc($titleItem) ?></h3>
                            <p><?= g_esc($summary) ?></p>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php
        $title = ($meta['portfolio_title'] ?? 'Portfolio').' · '.$suffix;
        $desc = $meta['portfolio_description'] ?? '';
    } elseif ($page === 'founder') {
        $f = $copy['founder'] ?? [];
        $email = (string) ($company['contact_email'] ?? 'contact@goalgus.bg');
        $photo = '/assets/images/brand/stiliyan-stoyanov.jpg';
        ?>
        <section class="page-hero shell founder-hero">
            <div class="founder-hero__media reveal">
                <img
                    src="<?= g_esc($photo) ?>"
                    alt="<?= g_esc($f['name'] ?? 'Stiliyan Stoyanov') ?>"
                    width="320"
                    height="320"
                    loading="eager"
                >
            </div>
            <div class="founder-hero__copy reveal">
                <p class="eyebrow"><?= g_esc($f['eyebrow'] ?? 'Founder') ?></p>
                <h1><?= g_esc($f['name'] ?? '') ?></h1>
                <p class="founder-role"><?= g_esc($f['role'] ?? '') ?></p>
                <p class="lead"><?= g_esc($f['location'] ?? '') ?> · <a href="mailto:<?= g_esc($email) ?>"><?= g_esc($email) ?></a></p>
                <ul class="founder-chips" aria-label="Skills">
                    <?php foreach (($f['skills'] ?? []) as $skill): ?>
                        <li><?= g_esc((string) $skill) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </section>

        <section class="section">
            <div class="shell reveal">
                <div class="section-head">
                    <h2><?= g_esc($f['about_title'] ?? '') ?></h2>
                </div>
                <p class="lead"><?= g_esc($f['intro'] ?? '') ?></p>
            </div>
        </section>

        <section class="section">
            <div class="shell">
                <div class="section-head reveal">
                    <h2><?= g_esc($f['experience_title'] ?? '') ?></h2>
                </div>
                <ol class="founder-timeline">
                    <?php foreach (($f['experience'] ?? []) as $job): ?>
                        <li class="founder-job reveal">
                            <div class="founder-job__meta">
                                <h3><?= g_esc($job['role'] ?? '') ?></h3>
                                <p class="founder-job__org"><?= g_esc($job['org'] ?? '') ?></p>
                                <p class="founder-job__dates"><?= g_esc($job['dates'] ?? '') ?></p>
                            </div>
                            <?php if (! empty($job['bullets']) && is_array($job['bullets'])): ?>
                                <ul>
                                    <?php foreach ($job['bullets'] as $bullet): ?>
                                        <li><?= g_esc((string) $bullet) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </section>

        <section class="section">
            <div class="shell">
                <div class="section-head reveal">
                    <h2><?= g_esc($f['work_title'] ?? '') ?></h2>
                </div>
                <div class="services-grid">
                    <?php foreach (($f['work'] ?? []) as $item): ?>
                        <article class="service reveal">
                            <h3><?= g_esc($item['title'] ?? '') ?></h3>
                            <p><?= g_esc($item['text'] ?? '') ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="shell">
                <div class="section-head reveal">
                    <h2><?= g_esc($f['downloads_title'] ?? '') ?></h2>
                    <p><?= g_esc($f['downloads_lead'] ?? '') ?></p>
                </div>
                <ul class="founder-downloads">
                    <?php foreach (($f['downloads'] ?? []) as $dl): ?>
                        <li class="reveal">
                            <a class="founder-download" href="<?= g_esc($dl['file'] ?? '#') ?>" download>
                                <span class="founder-download__label"><?= g_esc($dl['label'] ?? 'CV') ?></span>
                                <span class="founder-download__hint"><?= g_esc($dl['hint'] ?? '') ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </section>

        <section class="section">
            <div class="shell founder-cta reveal">
                <h2><?= g_esc($f['cta_title'] ?? '') ?></h2>
                <p class="lead"><?= g_esc($f['cta_lead'] ?? '') ?></p>
                <div class="cta-row">
                    <a class="btn" href="<?= g_esc($prefix.'/contact/') ?>"><?= g_esc($f['cta_button'] ?? '') ?></a>
                    <a class="btn btn-ghost" href="mailto:<?= g_esc($email) ?>"><?= g_esc($email) ?></a>
                </div>
                <?php if (! empty($f['note'])): ?>
                    <p class="founder-note muted"><?= g_esc($f['note']) ?></p>
                <?php endif; ?>
            </div>
        </section>
        <?php
        $title = ($meta['founder_title'] ?? 'Founder').' · '.$suffix;
        $desc = $meta['founder_description'] ?? '';
    } else {
        $c = $copy['contact'] ?? [];
        ?>
        <section class="page-hero shell">
            <p class="eyebrow">GOALGUS</p>
            <h1><?= g_esc($c['headline'] ?? '') ?></h1>
            <p class="lead"><?= g_esc($c['lead'] ?? '') ?></p>
        </section>
        <section class="section">
            <div class="shell contact-grid">
                <form class="contact-form reveal" data-contact-form novalidate>
                    <label>
                        <?= g_esc($c['name'] ?? 'Name') ?>
                        <input type="text" name="name" required maxlength="120" autocomplete="name">
                    </label>
                    <label>
                        <?= g_esc($c['email'] ?? 'Email') ?>
                        <input type="email" name="email" required maxlength="255" autocomplete="email">
                    </label>
                    <label>
                        <?= g_esc($c['company'] ?? 'Company') ?>
                        <input type="text" name="company" maxlength="160" autocomplete="organization">
                    </label>
                    <label>
                        <?= g_esc($c['message'] ?? 'Message') ?>
                        <textarea name="message" required maxlength="5000"></textarea>
                    </label>
                    <div class="hp" aria-hidden="true">
                        <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                    </div>
                    <button class="btn" type="submit"><?= g_esc($c['submit'] ?? 'Send') ?></button>
                    <p class="form-privacy muted"><?= g_esc($c['privacy'] ?? '') ?></p>
                    <p class="form-status" data-form-status role="status" aria-live="polite"></p>
                </form>
                <aside class="direct-contact reveal">
                    <p><?= g_esc($c['direct'] ?? '') ?></p>
                    <p><a href="mailto:<?= g_esc($company['contact_email'] ?? '') ?>"><?= g_esc($company['contact_email'] ?? '') ?></a></p>
                    <p><a href="tel:<?= g_esc((string) ($company['phone_e164'] ?? preg_replace('/\s+/', '', (string) ($company['phone'] ?? '')) ?: '')) ?>"><?= g_esc($company['phone'] ?? '') ?></a></p>
                    <p><?= g_esc($locale === 'bg' && ! empty($company['registered_address_bg']) ? $company['registered_address_bg'] : ($company['registered_address'] ?? '')) ?></p>
                </aside>
            </div>
        </section>
        <?php
        $title = ($meta['contact_title'] ?? 'Contact').' · '.$suffix;
        $desc = $meta['contact_description'] ?? '';
    }

    $html = (string) ob_get_clean();
    g_render_layout($ctx, $page, $html, $title, $desc);
}
