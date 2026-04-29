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

$feedUrl = $feed ? ($appUrl . '/rss/' . $feed['token']) : '';
$manageUrl = $feed ? ($appUrl . '/manage/' . $feed['token']) : '';
$pageTitle = $appName;
?>
<?php require __DIR__ . '/partials/head.php'; ?>
<main class="container">
    <section class="card">
        <div class="eyebrow"><span class="dot"></span><?= htmlspecialchars($t('home.eyebrow')) ?></div>
        <h1><?= htmlspecialchars($appName) ?></h1>
        <p class="lead"><?= htmlspecialchars($t('home.lead')) ?></p>

        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($flash): ?>
            <div class="alert success"><?= htmlspecialchars($flash) ?></div>
        <?php endif; ?>

        <form method="get" class="inline-form">
            <label for="token"><?= htmlspecialchars($t('home.open_existing_label')) ?></label>
            <input id="token" name="token" type="text" placeholder="<?= htmlspecialchars($t('home.open_existing_placeholder')) ?>">
            <button type="submit"><?= htmlspecialchars($t('home.open_existing_button')) ?></button>
        </form>

        <div class="options-wrap">
            <button type="button" class="secondary" id="options-toggle" aria-expanded="false"><?= htmlspecialchars($t('home.options_toggle')) ?></button>
            <div id="options-panel" class="options-panel" hidden>
                <div class="grid options-grid">
                    <form method="post" action="/import-master" enctype="multipart/form-data" class="options-form">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
                        <h3><?= htmlspecialchars($t('home.options_import_title')) ?></h3>
                        <p class="muted"><?= htmlspecialchars($t('home.options_import_text')) ?></p>
                        <input type="file" name="import_master_file" accept="application/json,.json" required>
                        <button type="submit"><?= htmlspecialchars($t('home.options_import_button')) ?></button>
                    </form>
                    <form method="post" action="/import-master-opml" enctype="multipart/form-data" class="options-form">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
                        <h3><?= htmlspecialchars($t('home.options_import_opml_title')) ?></h3>
                        <p class="muted"><?= htmlspecialchars($t('home.options_import_opml_text')) ?></p>
                        <input type="file" name="import_master_opml_file" accept=".opml,text/x-opml,application/xml,text/xml" required>
                        <button type="submit"><?= htmlspecialchars($t('home.options_import_opml_button')) ?></button>
                    </form>
                    <form method="get" action="/export-master" class="options-form">
                        <h3><?= htmlspecialchars($t('home.options_export_title')) ?></h3>
                        <p class="muted"><?= htmlspecialchars($t('home.options_export_text')) ?></p>
                        <input name="token" type="text" placeholder="<?= htmlspecialchars($t('home.open_existing_placeholder')) ?>" required>
                        <button type="submit" class="secondary"><?= htmlspecialchars($t('home.options_export_button')) ?></button>
                    </form>
                    <form method="get" action="/export-master-opml" class="options-form">
                        <h3><?= htmlspecialchars($t('home.options_export_opml_title')) ?></h3>
                        <p class="muted"><?= htmlspecialchars($t('home.options_export_opml_text')) ?></p>
                        <input name="token" type="text" placeholder="<?= htmlspecialchars($t('home.open_existing_placeholder')) ?>" required>
                        <button type="submit" class="secondary"><?= htmlspecialchars($t('home.options_export_opml_button')) ?></button>
                    </form>
                </div>
            </div>
        </div>

        <?php if ($feed): ?>
            <div class="result">
                <p><strong><?= htmlspecialchars($t('home.detected_feed')) ?></strong> <?= htmlspecialchars((string) $feed['title']) ?></p>
                <p><a href="<?= htmlspecialchars($manageUrl) ?>"><?= htmlspecialchars($t('home.edit_feed')) ?></a> | <a href="<?= htmlspecialchars($feedUrl) ?>"><?= htmlspecialchars($t('home.view_rss')) ?></a></p>
            </div>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2><?= htmlspecialchars($t('home.create_title')) ?></h2>
        <form method="post" action="/create" id="feed-form">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
            <div class="grid">
                <div>
                    <label><?= htmlspecialchars($t('form.master_title')) ?></label>
                    <input type="text" name="title" required value="<?= htmlspecialchars((string) ($old['title'] ?? '')) ?>">
                </div>
                <div>
                    <label><?= htmlspecialchars($t('form.description')) ?></label>
                    <input type="text" name="description" value="<?= htmlspecialchars((string) ($old['description'] ?? '')) ?>">
                </div>
            </div>

            <div id="sources"></div>
            <button type="button" class="secondary" data-action="add-source"><?= htmlspecialchars($t('form.add_source')) ?></button>
            <button type="submit"><?= htmlspecialchars($t('form.generate_unique')) ?></button>
        </form>
    </section>

    <?php require __DIR__ . '/partials/footer.php'; ?>
</main>

<template id="source-template">
    <article class="source-block">
        <div class="source-header">
            <h3><?= htmlspecialchars($t('ui.source')) ?> <span class="index"></span></h3>
            <div class="source-actions">
                <button type="button" class="secondary" data-action="preview"><?= htmlspecialchars($t('source.preview')) ?></button>
                <button type="button" class="danger" data-action="remove"><?= htmlspecialchars($t('source.remove')) ?></button>
            </div>
        </div>
        <div class="grid">
            <div>
                <label><?= htmlspecialchars($t('source.name')) ?></label>
                <input type="text" name="source_name[]" required>
            </div>
            <div>
                <label><?= htmlspecialchars($t('source.url')) ?></label>
                <input type="url" name="source_url[]" required placeholder="https://example.com/feed.xml">
            </div>
        </div>
        <div class="grid">
            <div>
                <label><?= htmlspecialchars($t('source.black_words')) ?></label>
                <input type="text" name="black_words[]" placeholder="ads, Elon Musk, botshit">
            </div>
            <div class="checks">
                <label><input type="checkbox" name="black_target_title[index]" checked><?= htmlspecialchars($t('target.title')) ?></label>
                <label><input type="checkbox" name="black_target_description[index]" checked><?= htmlspecialchars($t('target.description')) ?></label>
                <label><input type="checkbox" name="black_target_content[index]"><?= htmlspecialchars($t('target.content')) ?></label>
            </div>
        </div>
        <div class="grid">
            <div>
                <label><?= htmlspecialchars($t('source.star_words')) ?></label>
                <input type="text" name="star_words[]" placeholder="php, privacy, Framasoft, datalove">
            </div>
            <div class="checks">
                <label><input type="checkbox" name="star_target_title[index]" checked><?= htmlspecialchars($t('target.title')) ?></label>
                <label><input type="checkbox" name="star_target_description[index]" checked><?= htmlspecialchars($t('target.description')) ?></label>
                <label><input type="checkbox" name="star_target_content[index]"><?= htmlspecialchars($t('target.content')) ?></label>
            </div>
        </div>
        <div class="preview-box" hidden></div>
    </article>
</template>

<script type="application/json" id="i18n-data"><?= $clientI18n ?: '{}' ?></script>
<script type="module" src="/js/home.js"></script>
</body>
</html>
