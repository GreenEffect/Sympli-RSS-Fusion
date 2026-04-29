const I18N = JSON.parse(document.getElementById('i18n-data')?.textContent || '{}');

const sourcesRoot = document.getElementById('sources');
const tpl = document.getElementById('source-template');
const optionsToggle = document.getElementById('options-toggle');
const optionsPanel = document.getElementById('options-panel');

const t = (key) => I18N[key] || key;

const escapeHtml = (value) => String(value)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#39;');

const toFlag = (input) => (input && input.checked ? '1' : '0');

const buildPreviewUrl = (block, url) => {
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
};

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

function syncIndices() {
    if (!sourcesRoot) return;
    [...sourcesRoot.querySelectorAll('.source-block')].forEach((block, idx) => {
        const index = block.querySelector('.index');
        if (index) index.textContent = idx + 1;
        block.querySelectorAll('input[type="checkbox"]').forEach((input) => {
            input.name = input.name.replace(/\[\d+\]/, '[' + idx + ']').replace('[index]', '[' + idx + ']');
        });
    });
}

function addSource() {
    if (!tpl || !sourcesRoot) return;
    const clone = tpl.content.cloneNode(true);
    sourcesRoot.appendChild(clone);
    syncIndices();
}

function removeSource(button) {
    const article = button.closest('.source-block');
    if (!article) return;
    article.remove();
    syncIndices();
}

// Delegated button handling
document.addEventListener('click', (e) => {
    const btn = e.target.closest('button[data-action]');
    if (!btn) return;
    const action = btn.dataset.action;
    if (action === 'preview') return previewSource(btn);
    if (action === 'remove') return removeSource(btn);
    if (action === 'add-source') return addSource();
});

// Toggle options panel
if (optionsToggle && optionsPanel) {
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
}

syncIndices();
