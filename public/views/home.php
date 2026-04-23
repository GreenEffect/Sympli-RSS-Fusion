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
                        <h3><?= htmlspecialchars($t('home.options_import_title')) ?></h3>
                        <p class="muted"><?= htmlspecialchars($t('home.options_import_text')) ?></p>
                        <input type="file" name="import_master_file" accept="application/json,.json" required>
                        <button type="submit"><?= htmlspecialchars($t('home.options_import_button')) ?></button>
                    </form>
                    <form method="get" action="/export-master" class="options-form">
                        <h3><?= htmlspecialchars($t('home.options_export_title')) ?></h3>
                        <p class="muted"><?= htmlspecialchars($t('home.options_export_text')) ?></p>
                        <input name="token" type="text" placeholder="<?= htmlspecialchars($t('home.open_existing_placeholder')) ?>" required>
                        <button type="submit" class="secondary"><?= htmlspecialchars($t('home.options_export_button')) ?></button>
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
            <button type="button" class="secondary" onclick="addSource()"><?= htmlspecialchars($t('form.add_source')) ?></button>
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
                <button type="button" class="secondary" onclick="previewSource(this)"><?= htmlspecialchars($t('source.preview')) ?></button>
                <button type="button" class="danger" onclick="removeSource(this)"><?= htmlspecialchars($t('source.remove')) ?></button>
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

<script>
const sourcesRoot = document.getElementById('sources');
const tpl = document.getElementById('source-template');
const optionsToggle = document.getElementById('options-toggle');
const optionsPanel = document.getElementById('options-panel');
const I18N = <?= $clientI18n ?: '{}' ?>;

function t(key) {
    return I18N[key] || key;
}

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

function toFlag(input) {
    return input && input.checked ? '1' : '0';
}

function buildPreviewUrl(block, url) {
    const params = new URLSearchParams();
    params.set('url', url);

    const blackWords = block.querySelector('input[name="black_words[]"]');
    const starWords = block.querySelector('input[name="star_words[]"]');
    params.set('black_words', blackWords ? blackWords.value : '');
    params.set('star_words', starWords ? starWords.value : '');

    params.set('black_target_title', toFlag(block.querySelector('input[name^="black_target_title["]')));
    params.set('black_target_description', toFlag(block.querySelector('input[name^="black_target_description["]')));
    params.set('black_target_content', toFlag(block.querySelector('input[name^="black_target_content["]')));
    params.set('star_target_title', toFlag(block.querySelector('input[name^="star_target_title["]')));
    params.set('star_target_description', toFlag(block.querySelector('input[name^="star_target_description["]')));
    params.set('star_target_content', toFlag(block.querySelector('input[name^="star_target_content["]')));

    return '/preview-source?' + params.toString();
}

function syncIndices() {
    [...sourcesRoot.querySelectorAll('.source-block')].forEach((block, idx) => {
        block.querySelector('.index').textContent = idx + 1;
        block.querySelectorAll('input[type="checkbox"]').forEach((input) => {
            input.name = input.name.replace(/\[\d+\]/, '[' + idx + ']').replace('[index]', '[' + idx + ']');
        });
    });
}

function addSource() {
    const clone = tpl.content.cloneNode(true);
    sourcesRoot.appendChild(clone);
    syncIndices();
}

function removeSource(button) {
    button.closest('.source-block').remove();
    syncIndices();
}

async function previewSource(button) {
    const block = button.closest('.source-block');
    const urlInput = block.querySelector('input[name="source_url[]"]');
    const box = block.querySelector('.preview-box');
    const url = (urlInput.value || '').trim();

    if (!url) {
        box.hidden = false;
        box.innerHTML = '<p>' + escapeHtml(t('error.invalid_url')) + '</p>';
        return;
    }

    box.hidden = false;
    box.innerHTML = '<p>' + escapeHtml(t('ui.preview_loading')) + '</p>';

    try {
        const response = await fetch(buildPreviewUrl(block, url));
        const data = await response.json();

        if (!response.ok || data.error) {
            box.innerHTML = '<p>' + escapeHtml(data.error || t('ui.preview_error')) + '</p>';
            return;
        }

        const header = data.feed_title
            ? '<p><strong>' + escapeHtml(t('ui.preview_feed')) + '</strong> ' + escapeHtml(data.feed_title) + '</p>'
            : '';

        if (!Array.isArray(data.items) || data.items.length === 0) {
            box.innerHTML = header + '<p>' + escapeHtml(t('ui.preview_empty')) + '</p>';
            return;
        }

        const list = data.items.map((item) => {
            const title = item.title ? escapeHtml(item.title) : escapeHtml(t('ui.preview_untitled_item'));
            const link = item.link ? escapeHtml(item.link) : '#';
            return '<li><a href="' + link + '" target="_blank" rel="noopener noreferrer">' + title + '</a></li>';
        }).join('');

        box.innerHTML = header + '<ul>' + list + '</ul>';
    } catch (e) {
        box.innerHTML = '<p>' + escapeHtml(t('ui.preview_error')) + '</p>';
    }
}

optionsToggle.addEventListener('click', () => {
    const open = optionsPanel.hasAttribute('hidden');
    optionsToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (open) {
        optionsPanel.hidden = false;
        requestAnimationFrame(() => optionsPanel.classList.add('open'));
    } else {
        optionsPanel.classList.remove('open');
        setTimeout(() => {
            if (!optionsPanel.classList.contains('open')) {
                optionsPanel.hidden = true;
            }
        }, 180);
    }
});

addSource();
</script>
</body>
</html>
