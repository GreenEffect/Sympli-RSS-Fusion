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
$importUrl = '/manage/' . $feed['token'] . '/import';
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
            <form method="post" action="<?= htmlspecialchars($importUrl) ?>" enctype="multipart/form-data" class="inline-import">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="file" name="import_file" accept="application/json,.json" required>
                <button type="submit" class="secondary"><?= htmlspecialchars($t('manage.import_button')) ?></button>
            </form>
            <form method="post" action="<?= htmlspecialchars($deleteUrl) ?>" onsubmit="return confirm('<?= htmlspecialchars($t('manage.delete_confirm')) ?>');">
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

            <?php foreach ($feed['sources'] as $i => $source): ?>
                <article class="source-block">
                    <div class="source-header">
                        <h3><?= htmlspecialchars($t('ui.source')) ?> <?= $i + 1 ?></h3>
                        <button type="button" class="secondary" onclick="previewSource(this)"><?= htmlspecialchars($t('source.preview')) ?></button>
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
            <button type="submit"><?= htmlspecialchars($t('manage.save')) ?></button>
        </form>
    </section>

    <?php require __DIR__ . '/partials/footer.php'; ?>
</main>

<script>
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

const copyBtn = document.getElementById('copy-url-btn');
const copyFeedback = document.getElementById('copy-url-feedback');
const feedLink = document.getElementById('feed-url-link');

copyBtn.addEventListener('click', async () => {
    const text = feedLink ? feedLink.href : '';
    if (!text) {
        copyFeedback.textContent = t('manage.copy_failed');
        return;
    }

    try {
        await navigator.clipboard.writeText(text);
        copyFeedback.textContent = t('manage.copy_done');
    } catch (e) {
        copyFeedback.textContent = t('manage.copy_failed');
    }
});
</script>
</body>
</html>
