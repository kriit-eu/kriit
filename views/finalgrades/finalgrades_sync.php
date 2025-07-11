<?php
// views/finalgrades/finalgrades_sync.php
// This view is shown after syncing final grades via API
?>
<div class="container mt-5">
    <div class="alert alert-success">
        Final grades have been synced successfully.<br>
        <strong>Inserted/Updated rows:</strong> <?= isset($inserted) ? htmlspecialchars($inserted) : 'N/A' ?>
    </div>
</div>