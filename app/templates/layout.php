<?php
/**
 * Example Template
 */
?>

<nav class="navbar navbar-default">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="<?php echo Sledgehammer\WEBROOT; ?>index.html">Plandown</a>
    </div>
</nav>

<div class="container">
<?php render($content); ?>
</div>