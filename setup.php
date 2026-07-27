<?php

/**
 * -------------------------------------------------------------------------
 * Orient Workflow Plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * @copyright 2026
 * @author Muhammad Usman Khalid
 * @license GPL v2+
 * -------------------------------------------------------------------------
 */

define('PLUGIN_ORIENTWORKFLOW_VERSION', '1.0.0');

/**
 * Plugin initialization
 */
function plugin_init_orientworkflow()
{
    global $PLUGIN_HOOKS;

    // CSRF Protection
    $PLUGIN_HOOKS['csrf_compliant']['orientworkflow'] = true;

    // Plugin compatible with GLPI 11
    Plugin::registerClass('PluginOrientworkflowConfig');

    // Configuration page
    if (Session::haveRight('config', UPDATE)) {
        $PLUGIN_HOOKS['config_page']['orientworkflow'] = 'front/config.form.php';
    }
}

/**
 * Plugin information
 */
function plugin_version_orientworkflow()
{
    return [
        'name'           => 'Orient Workflow',
        'version'        => PLUGIN_ORIENTWORKFLOW_VERSION,
        'author'         => 'Muhammad Usman Khalid',
        'license'        => 'GPL v2+',
        'homepage'       => '',
        'requirements'   => [
            'glpi' => [
                'min' => '11.0.0',
                'max' => '11.99'
            ],
            'php' => [
                'min' => '8.2'
            ]
        ]
    ];
}

/**
 * Check prerequisites
 */
function plugin_orientworkflow_check_prerequisites()
{
    return true;
}

/**
 * Check configuration
 */
function plugin_orientworkflow_check_config()
{
    return true;
}