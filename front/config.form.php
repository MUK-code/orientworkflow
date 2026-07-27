<?php

include('../../../inc/includes.php');

Session::checkRight('config', UPDATE);

Html::header(
    __('Orient Workflow', 'orientworkflow'),
    '',
    'plugins',
    'orientworkflow'
);

echo "<div class='center'>";

echo "<h1>Orient Workflow</h1>";

echo "<p><strong>Version:</strong> 1.0.0</p>";

echo "<p>Plugin Installed Successfully.</p>";

echo "</div>";

Html::footer();