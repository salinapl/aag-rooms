<?php
    $iframeUrl = get('url'); // read query parameter

    // Optional: sanitize or whitelist domains

?>
<div class="toolbar">
  <!-- your toolbar HTML -->
  <a href="<?= url() ?>">Back</a>
</div>

<?php if ($iframeUrl): ?>
  <iframe 
    src="<?= esc($iframeUrl) ?>" 
    style="width:100%; height:80vh; border:0;"
  ></iframe>
<?php else: ?>
  <p>No reservation URL provided.</p>
<?php endif; ?>
<?php echo "USING RESERVE TEMPLATE"; exit; ?>