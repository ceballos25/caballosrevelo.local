<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['csrf_token'])): ?>
    <meta name="csrf-token" content="<?= htmlspecialchars((string)$_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <title>Caballos Revelo 🐎</title>
    <meta name="description"
        content="Pasion Equina Caballos Revelo, comienza aquí. Participa fácil, rápido y seguro desde cualquier lugar de Colombia.">
    <link rel="icon" type="image/png" href="<?= cr_site_favicon_href() ?>" data-site-favicon>
    <link rel="shortcut icon" type="image/png" href="<?= cr_site_favicon_href() ?>" data-site-favicon>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= htmlspecialchars(ASSETS_URL . '/css/app.css', ENT_QUOTES, 'UTF-8') ?>?v=27">

    <?php edts_meta_pixel_head(); ?>
</head>
