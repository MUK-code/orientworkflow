<?php

include('../../../inc/includes.php');

Session::checkRight('config', UPDATE);

Html::header(
    __('Routing Rule', 'orientworkflow'),
    '',
    'plugins',
    'orientworkflow'
);

global $DB;

if (isset($_POST['save'])) {

    $data = [
        'branch'            => $_POST['branch'],
        'service'           => $_POST['service'],
        'category'          => $_POST['category'],
        'group_id'          => (int)$_POST['group_id'],
        'technician_id'     => !empty($_POST['technician_id']) ? (int)$_POST['technician_id'] : null,
        'itilcategories_id' => !empty($_POST['itilcategories_id']) ? (int)$_POST['itilcategories_id'] : null,
        'priority'          => !empty($_POST['priority']) ? (int)$_POST['priority'] : null,
        'sla_id'            => !empty($_POST['sla_id']) ? (int)$_POST['sla_id'] : null,
        'is_active'         => (int)$_POST['is_active']
    ];

    $DB->insert(
        'glpi_plugin_orientworkflow_routes',
        $data
    );

    Session::addMessageAfterRedirect("Routing Rule Saved");

    Html::redirect($_SERVER['PHP_SELF']);
}

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
        'name' => 'group_id',
        'id'   => 'group_id'
    ]
);
echo "</td>";
echo "</tr>";

/* Technician */

echo "<tr>";
echo "<td>Assign Technician</td>";
echo "<td>";


global $DB;

echo "<select id='technician_id' name='technician_id' class='form-select'>";

echo "<option value=''>-----</option>";

$iterator = $DB->request([
    'SELECT' => [
        'glpi_users.id',
        'glpi_users.name',
        'glpi_users.realname',
        'glpi_users.firstname'
    ],
    'FROM' => 'glpi_users',
    'INNER JOIN' => [
        'glpi_groups_users' => [
            'ON' => [
                'glpi_users' => 'id',
                'glpi_groups_users' => 'users_id'
            ]
        ]
    ],
    'WHERE' => [
        'glpi_groups_users.groups_id' => 1
    ],
    'ORDER' => 'glpi_users.name'
]);

foreach ($iterator as $user) {

    $label = trim(
        $user['firstname'] . " " . $user['realname']
    );

    echo "<option value='{$user['id']}'>$label</option>";
}

echo "</select>";

echo "</td>";

echo "</td>";
echo "</tr>";

/* Assignment Mode */

echo "<tr>";
echo "<td>Assignment Mode</td>";
echo "<td>";

Dropdown::showFromArray(
    'assignment_mode',
    [
        'FIXED'        => 'Fixed Technician',
        'ROUND_ROBIN' => 'Round Robin'
    ],
    [
        'value' => 'FIXED'
    ]
);

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
    __('Save'),
    [
        'name' => 'save'
    ]
);

echo "</td>";
echo "</tr>";

echo "</table>";

Html::closeForm();

echo "</div>";

?>
<script>

$(function () {

    $("select[name='group_id']").on("change", function () {

        let group = $(this).val();

        console.log("GROUP =", group);

        $.getJSON(
            "../ajax/get_technicians.php",
            { group_id: group },
            function (users) {

                console.log(users);

                let tech = $("#technician_id");

                tech.empty();

                tech.append(
                    $("<option>", {
                        value: "",
                        text: "-----"
                    })
                );

                $.each(users, function (_, user) {

                    tech.append(
                        $("<option>", {
                            value: user.id,
                            text: user.name
                        })
                    );

                });

            }
        );

    });

});

</script>
<?php

Html::footer();