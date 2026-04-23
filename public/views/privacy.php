<?php

/*
 * --------------------------------------------------------------------------------
 *  Sympli RSS Fusion
 * --------------------------------------------------------------------------------
 *  RSS Fusion [https://www.rss-fusion.fr] en mode KISS : Fusionner, filtrer, manipuler et gérer ses flux RSS
 *  en toute simplicité / Merge, filter, manipulate and manage your RSS feeds
 *  with simplicity
 *
 *  @project     Sympli RSS Fusion
 *  @description Fusion, filtrage et gestion simplifiée de flux RSS /
 *               Simplified RSS feed merging, filtering, and management
 *  @author      Erase ● Green Effect <contact@green-effect.fr>
 *  @version     1.0
 *  @license     Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International
 *               https://creativecommons.org/licenses/by-nc-sa/4.0/
 * --------------------------------------------------------------------------------
 */

$pageTitle = $t('privacy.page_title') . ' - ' . $appName;
?>
<?php require __DIR__ . '/partials/head.php'; ?>
<main class="container">
    <section class="card">
        <div class="eyebrow"><span class="dot"></span><?= htmlspecialchars($t('privacy.eyebrow')) ?></div>
        <h1><?= htmlspecialchars($t('privacy.title')) ?></h1>
        <p class="lead"><?= htmlspecialchars($t('privacy.lead')) ?></p>

        <h2><?= htmlspecialchars($t('privacy.section_data.title')) ?></h2>
        <p><?= htmlspecialchars($t('privacy.section_data.body')) ?></p>

        <h2><?= htmlspecialchars($t('privacy.section_storage.title')) ?></h2>
        <p><?= htmlspecialchars($t('privacy.section_storage.body')) ?></p>

        <h2><?= htmlspecialchars($t('privacy.section_logs.title')) ?></h2>
        <p><?= htmlspecialchars($t('privacy.section_logs.body')) ?></p>

        <h2><?= htmlspecialchars($t('privacy.section_rights.title')) ?></h2>
        <p><?= htmlspecialchars($t('privacy.section_rights.body')) ?></p>

        <p><a class="btn" href="/"><?= htmlspecialchars($t('privacy.back_home')) ?></a></p>
    </section>

    <?php require __DIR__ . '/partials/footer.php'; ?>
</main>
</body>
</html>
