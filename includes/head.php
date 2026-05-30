<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : 'El dia de TU SUERTE 🍀'; ?></title>
  <link rel="shortcut icon" type="image/png" href="https://cdn.eldiadetusuerte.com/images/logos/logo.ico" />
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/styles.min.css" />
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/sidebar-layout.css" />
  <!-- AlertifyJS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/bootstrap.min.css"/>

<script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

  <?php if(isset($extra_css)) echo $extra_css; ?>
</head>

<body>