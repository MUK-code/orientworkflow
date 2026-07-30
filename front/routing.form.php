<?php

include('../../../inc/includes.php');

Session::checkRight('config', UPDATE);

Html::header(
    __('Routing Rule', 'orientworkflow'),
    '',
    'plugins',
    'orientworkflow'
);

echo "<div class='center'>";

echo "<form method='post' action=''>";

echo "<table class='tab_cadre_fixe'>";

echo "<tr><th colspan='2'>Add Routing Rule</th></tr>";

/* Branch */

echo "<tr>";
echo "<td width='200'>Branch</td>";
echo "<td>";
Dropdown::showFromArray(
    'branch',
    [
        '' => '-----',
        'Head Office' => 'Head Office',
        'FSD' => 'FSD'
    ]
);
echo "</td>";
echo "</tr>";

/* Service */

echo "<tr>";
echo "<td>Service</td>";
echo "<td>";
Dropdown::showFromArray(
    'service',
    [
        '' => '-----',
        'IT Support' => 'IT Support',
        'SAP Support' => 'SAP Support'
    ]
);
echo "</td>";
echo "</tr>";

/* Category */

echo "<tr>";
echo "<td>Category</td>";
echo "<td>";
Dropdown::showFromArray(
    'category',
    [
        '' => '-----'
    ]
);
echo "</td>";
echo "</tr>";

/* Group */

echo "<tr>";
echo "<td>Assign Group</td>";
echo "<td>";
Dropdown::show(
    'Group',
    [
        'name' => 'group_id'
    ]
);
echo "</td>";
echo "</tr>";

/* Technician */

echo "<tr>";
echo "<td>Assign Technician</td>";
echo "<td>";

User::dropdown([
    'name'      => 'technician_id',
    'groups_id' => 1
]);

echo "</td>";
echo "</tr>";

/* ITIL Category */

echo "<tr>";
echo "<td>ITIL Category</td>";
echo "<td>";
ITILCategory::dropdown(
    [
        'name' => 'itilcategories_id'
    ]
);
echo "</td>";
echo "</tr>";

/* Priority */

echo "<tr>";
echo "<td>Priority</td>";
echo "<td>";
Ticket::dropdownPriority(
    [
        'name' => 'priority'
    ]
);
echo "</td>";
echo "</tr>";

/* SLA */

echo "<tr>";
echo "<td>SLA</td>";
echo "<td>";
Dropdown::show(
    'SLA',
    [
        'name' => 'sla_id'
    ]
);
echo "</td>";
echo "</tr>";

/* Active */

echo "<tr>";
echo "<td>Active</td>";
echo "<td>";
Dropdown::showYesNo(
    'is_active',
    1
);
echo "</td>";
echo "</tr>";

echo "<tr>";
echo "<td colspan='2' class='center'>";

echo Html::submit(
    __('Save')
);

echo "</td>";
echo "</tr>";

echo "</table>";

Html::closeForm();

echo "</div>";

Html::footer();