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

$pageTitle = '500 - Sympli RSS Fusion';
?>
<?php require __DIR__ . '/../partials/head.php'; ?>
<main class="container">
    <section class="card error-card">
        <h1>500</h1>
        <p class="lead"><?= $lang === 'en' ? 'Internal server error.' : 'Erreur interne du serveur.' ?></p>
        <p><?= htmlspecialchars($message) ?></p>
        <?php if (!empty($trace)): ?>
            <pre class="trace-box"><?= htmlspecialchars($trace) ?></pre>
        <?php endif; ?>
        <p><a class="btn" href="/"><?= $lang === 'en' ? 'Back to home' : 'Retour à l\'accueil' ?></a></p>
    </section>

    <footer class="card footer-card">
        <p>
            Sympli RSS Fusion -
            <a href="https://www.rss-fusion.fr" target="_blank" rel="noopener noreferrer">RSS Fusion</a> -
            <a href="/privacy"><?= $lang === 'en' ? 'Personal data' : 'Données personnelles' ?></a>
        </p>
        <p>
            <a href="https://www.green-effect.fr" target="_blank" rel="noopener noreferrer"><?= $lang === 'en' ? 'Create your Sympli RSS Fusion instance' : 'Créer son instance Sympli RSS Fusion' ?></a>
        </p>
    </footer>
</main>
</body>
</html>
