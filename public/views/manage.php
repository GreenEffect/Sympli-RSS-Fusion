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

$feedUrl = $appUrl . '/rss/' . $feed['token'];
$deleteUrl = '/manage/' . $feed['token'] . '/delete';
$exportUrl = '/manage/' . $feed['token'] . '/export';
$exportOpmlUrl = '/manage/' . $feed['token'] . '/export-opml';
$importUrl = '/manage/' . $feed['token'] . '/import';
$importOpmlUrl = '/manage/' . $feed['token'] . '/import-opml';
$pageTitle = $t('manage.page_title') . ' - ' . (string) $feed['title'];
?>
<?php require __DIR__ . '/partials/head.php'; ?>
<main class="container">
    <section class="card">
        <div class="eyebrow"><span class="dot"></span><?= htmlspecialchars($t('manage.unique_link_eyebrow')) ?></div>
        <h1><?= htmlspecialchars((string) $feed['title']) ?></h1>
        <p class="lead"><?= htmlspecialchars($t('manage.unique_link_lead')) ?></p>
        <div class="result">
            <a id="feed-url-link" href="<?= htmlspecialchars($feedUrl) ?>"><?= htmlspecialchars($feedUrl) ?></a>
            <button type="button" id="copy-url-btn" class="secondary"><?= htmlspecialchars($t('manage.copy_url')) ?></button>
            <span id="copy-url-feedback" class="muted"></span>
        </div>
        <p><a href="/"><?= htmlspecialchars($t('manage.create_other')) ?></a></p>
    </section>

    <section class="card">
        <h2><?= htmlspecialchars($t('manage.edit_title')) ?></h2>
        <?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($flash): ?><div class="alert success"><?= htmlspecialchars($flash) ?></div><?php endif; ?>

        <div class="toolbar">
            <a class="btn" href="<?= htmlspecialchars($exportUrl) ?>"><?= htmlspecialchars($t('manage.export')) ?></a>
            <a class="btn" href="<?= htmlspecialchars($exportOpmlUrl) ?>"><?= htmlspecialchars($t('manage.export_opml')) ?></a>
            <form method="post" action="<?= htmlspecialchars($importUrl) ?>" enctype="multipart/form-data" class="inline-import">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="file" name="import_file" accept="application/json,.json" required>
                <button type="submit" class="secondary"><?= htmlspecialchars($t('manage.import_button')) ?></button>
            </form>
            <form method="post" action="<?= htmlspecialchars($importOpmlUrl) ?>" enctype="multipart/form-data" class="inline-import">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="file" name="import_opml_file" accept=".opml,text/x-opml,application/xml,text/xml" required>
                <button type="submit" class="secondary"><?= htmlspecialchars($t('manage.import_opml_button')) ?></button>
            </form>
            <form method="post" action="<?= htmlspecialchars($deleteUrl) ?>" data-confirm="<?= htmlspecialchars($t('manage.delete_confirm')) ?>">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
                <button type="submit" class="danger"><?= htmlspecialchars($t('manage.delete')) ?></button>
            </form>
        </div>

        <form method="post">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
            <div class="grid">
                <div>
                    <label><?= htmlspecialchars($t('form.master_title')) ?></label>
                    <input type="text" name="title" required value="<?= htmlspecialchars((string) $feed['title']) ?>">
                </div>
                <div>
                    <label><?= htmlspecialchars($t('form.description')) ?></label>
                    <input type="text" name="description" value="<?= htmlspecialchars((string) $feed['description']) ?>">
                </div>
            </div>

            <div id="sources">
            <?php foreach ($feed['sources'] as $i => $source): ?>
                <article class="source-block">
                    <div class="source-header">
                        <h3><?= htmlspecialchars($t('ui.source')) ?> <span class="index"><?= $i + 1 ?></span></h3>
                        <div class="source-actions">
                            <button type="button" class="secondary" data-action="preview"><?= htmlspecialchars($t('source.preview')) ?></button>
                            <button type="button" class="danger" data-action="remove"><?= htmlspecialchars($t('source.remove')) ?></button>
                        </div>
                    </div>
                    <div class="grid">
                        <div>
                            <label><?= htmlspecialchars($t('source.name')) ?></label>
                            <input type="text" name="source_name[]" required value="<?= htmlspecialchars((string) $source['name']) ?>">
                        </div>
                        <div>
                            <label><?= htmlspecialchars($t('source.url')) ?></label>
                            <input type="url" name="source_url[]" required value="<?= htmlspecialchars((string) $source['url']) ?>">
                        </div>
                    </div>
                    <div class="grid">
                        <div>
                            <label><?= htmlspecialchars($t('source.black_words')) ?></label>
                            <input type="text" name="black_words[]" value="<?= htmlspecialchars((string) $source['black_words']) ?>">
                        </div>
                        <div class="checks">
                            <label><input type="checkbox" name="black_target_title[<?= $i ?>]" <?= (int) $source['black_target_title'] === 1 ? 'checked' : '' ?>><?= htmlspecialchars($t('target.title')) ?></label>
                            <label><input type="checkbox" name="black_target_description[<?= $i ?>]" <?= (int) $source['black_target_description'] === 1 ? 'checked' : '' ?>><?= htmlspecialchars($t('target.description')) ?></label>
                            <label><input type="checkbox" name="black_target_content[<?= $i ?>]" <?= (int) $source['black_target_content'] === 1 ? 'checked' : '' ?>><?= htmlspecialchars($t('target.content')) ?></label>
                        </div>
                    </div>
                    <div class="grid">
                        <div>
                            <label><?= htmlspecialchars($t('source.star_words')) ?></label>
                            <input type="text" name="star_words[]" value="<?= htmlspecialchars((string) $source['star_words']) ?>">
                        </div>
                        <div class="checks">
                            <label><input type="checkbox" name="star_target_title[<?= $i ?>]" <?= (int) $source['star_target_title'] === 1 ? 'checked' : '' ?>><?= htmlspecialchars($t('target.title')) ?></label>
                            <label><input type="checkbox" name="star_target_description[<?= $i ?>]" <?= (int) $source['star_target_description'] === 1 ? 'checked' : '' ?>><?= htmlspecialchars($t('target.description')) ?></label>
                            <label><input type="checkbox" name="star_target_content[<?= $i ?>]" <?= (int) $source['star_target_content'] === 1 ? 'checked' : '' ?>><?= htmlspecialchars($t('target.content')) ?></label>
                        </div>
                    </div>
                    <div class="preview-box" hidden></div>
                </article>
            <?php endforeach; ?>
            </div>
            <button type="button" class="secondary" data-action="add-source"><?= htmlspecialchars($t('form.add_source')) ?></button>
            <button type="submit"><?= htmlspecialchars($t('manage.save')) ?></button>
        </form>
    </section>

    <?php require __DIR__ . '/partials/footer.php'; ?>
</main>

<script type="application/json" id="i18n-data"><?= $clientI18n ?: '{}' ?></script>
<script type="module" src="/js/manage.js"></script>

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

</body>
</html>
