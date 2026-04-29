<?php

/*
 * Configuration view
 */

$pageTitle = $t('configuration.page_title') . ' - ' . $appName;
?>
<?php require __DIR__ . '/partials/head.php'; ?>
<main class="container">
    <section class="card">
        <div class="eyebrow"><span class="dot"></span><?= htmlspecialchars($t('configuration.eyebrow')) ?></div>
        <h1><?= htmlspecialchars($t('configuration.title')) ?></h1>

        <dl>
            <dt><?= htmlspecialchars($t('configuration.local_version')) ?></dt>
            <dd><?= htmlspecialchars($localVersion ?: '-') ?></dd>

            <dt><?= htmlspecialchars($t('configuration.remote_version')) ?></dt>
            <dd><?= htmlspecialchars($remoteVersion ?: $t('configuration.not_available') ) ?></dd>

            <dt><?= htmlspecialchars($t('configuration.language')) ?></dt>
            <dd><?= htmlspecialchars($langVal ?? $lang) ?></dd>

            <dt><?= htmlspecialchars($t('configuration.theme')) ?></dt>
            <dd><?= htmlspecialchars($themeName) ?></dd>

            <dt><?= htmlspecialchars($t('configuration.cache_ttl')) ?></dt>
            <dd><?= htmlspecialchars((string) $cacheTtl) ?></dd>

            <dt><?= htmlspecialchars($t('configuration.auto_prune_enabled')) ?></dt>
            <dd><?= htmlspecialchars($autoPruneEnabled ? $t('ui.yes') : $t('ui.no')) ?></dd>

            <dt><?= htmlspecialchars($t('configuration.auto_prune_days')) ?></dt>
            <dd><?= htmlspecialchars((string) $autoPruneDays) ?></dd>
        </dl>

        <p><a class="btn" href="/"><?= htmlspecialchars($t('configuration.back_home')) ?></a></p>
    </section>

    <?php require __DIR__ . '/partials/footer.php'; ?>
</main>
</body>
</html>
