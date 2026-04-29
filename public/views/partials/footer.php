<footer class="card footer-card">
    <p>
        <?= htmlspecialchars($t('footer.brand')) ?> -
        <a href="https://www.rss-fusion.fr" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($t('footer.based_on')) ?></a> -
        <a href="<?= htmlspecialchars($privacyUrl) ?>"><?= htmlspecialchars($t('footer.privacy')) ?></a>
    </p>
    <p>
        <a href="https://github.com/GreenEffect/Sympli-RSS-Fusion/blob/main/docs/INSTALL.md" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($t('footer.create_instance')) ?></a>
        - 
        <a href="https://github.com/GreenEffect/Sympli-RSS-Fusion" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($t('footer.source_code')) ?></a>
    </p>

    <?php if (!empty($versionUpdateAvailable)): ?>
        <small class="footer-update-note">
            <a href="<?= htmlspecialchars((string) ($versionRepoUrl ?? 'https://github.com/GreenEffect/Sympli-RSS-Fusion')) ?>" target="_blank" rel="noopener noreferrer">
                <?= htmlspecialchars($t('footer.update_available')) ?>
            </a>
        </small>
    <?php endif; ?>

    <?php if (!empty($localVersion)): ?>
        <small class="footer-version-note">v<?= htmlspecialchars((string) $localVersion) ?></small>
    <?php endif; ?>
</footer>
