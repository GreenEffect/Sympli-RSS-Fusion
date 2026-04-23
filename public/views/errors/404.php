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

$pageTitle = $title;
?>
<?php require __DIR__ . '/../partials/head.php'; ?>
<main class="container">
    <section class="card error-card">
        <h1><?= htmlspecialchars((string) $status) ?></h1>
        <p class="lead"><?= htmlspecialchars($message) ?></p>
        <p><a class="btn" href="<?= htmlspecialchars($homeUrl) ?>"><?= htmlspecialchars($t('ui.back_home')) ?></a></p>
    </section>

    <?php require __DIR__ . '/../partials/footer.php'; ?>
</main>
</body>
</html>
