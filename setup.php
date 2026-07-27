<?php

define('PLUGIN_ORIENTWORKFLOW_VERSION', '1.0.0');

/**
 * Plugin initialization
 */
function plugin_init_orientworkflow()
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['orientworkflow'] = true;
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
            ]
        ]
    ];
}

/**
 * Plugin prerequisites
 */
function plugin_orientworkflow_check_prerequisites()
{
    return true;
}

/**
 * Plugin configuration
 */
function plugin_orientworkflow_check_config()
{
    return true;
}