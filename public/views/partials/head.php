<?php

/*
 * --------------------------------------------------------------------------------
 *  Sympli RSS Fusion
 * --------------------------------------------------------------------------------
 *  RSS Fusion [https://www.rss-fusion.fr] en mode KISS : Fusionner, filtrer, manipuler et gérer ses flux RSS
 *  en toute simplicite / Merge, filter, manipulate and manage your RSS feeds
 *  with simplicity
 *
 *  @project     Sympli RSS Fusion
 *  @description Fusion, filtrage et gestion simplifiee de flux RSS /
 *               Simplified RSS feed merging, filtering, and management
 *  @author      Erase - Green Effect <contact@green-effect.fr>
 *  @version     1.0
 *  @license     Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International
 *               https://creativecommons.org/licenses/by-nc-sa/4.0/
 * --------------------------------------------------------------------------------
 */

$pageTitle = isset($pageTitle) && is_string($pageTitle) && $pageTitle !== '' ? $pageTitle : 'Sympli RSS Fusion';
$appName = isset($appName) && is_string($appName) && $appName !== '' ? $appName : 'Sympli RSS Fusion';
$lang = isset($lang) && is_string($lang) && $lang !== '' ? $lang : 'fr';
$themeStylesheet = isset($themeStylesheet) && is_string($themeStylesheet) && $themeStylesheet !== ''
    ? $themeStylesheet
    : '/themes/default.css';
?>
<!doctype html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= htmlspecialchars($themeStylesheet) ?>">
    <link rel="icon" type="image/png" href="/images/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/images/favicon/favicon.svg" />
    <link rel="shortcut icon" href="/images/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/images/favicon/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Sympli RSS Fusion" />
    <link rel="manifest" href="/images/favicon/site.webmanifest" />
</head>
<body>
<div class="site-logo-wrap">
    <a class="site-logo-home" href="/" title="<?= htmlspecialchars($appName) ?> - accueil" aria-label="<?= htmlspecialchars($appName) ?> - accueil">
        <img src="/images/logo.svg" alt="<?= htmlspecialchars($appName) ?>" loading="eager" decoding="async">
        <span class="site-name"><?= htmlspecialchars($appName) ?></span>
    </a>
</div>
