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

echo "<h1>Orient Workflow Configuration</h1>";

echo "<hr>";

echo "<h2>Routing Rules</h2>";

echo "<p>Manage automatic ticket routing.</p>";

echo "<br>";

echo "<a class='btn btn-primary'
href='routing.form.php'>
Add Routing Rule
</a>";

echo "</div>";

Html::footer();