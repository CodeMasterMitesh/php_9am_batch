<?php
if (!isset($pageTitle)) { $pageTitle = 'Canteen Management'; }
$bodyClass = isset($bodyClass) ? trim($bodyClass) : '';
$extraHead = isset($extraHead) ? (string)$extraHead : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($pageTitle); ?></title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <!-- App main CSS -->
  <link rel="stylesheet" href="assets/css/main.css">
  <?php if ($extraHead) { echo $extraHead; } ?>
</head>
<body<?php echo $bodyClass !== '' ? ' class="' . htmlspecialchars($bodyClass) . '"' : ''; ?>>
