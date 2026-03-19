<?php
/**
 * Pandora FMS OpenSource
 * Copyright (c) 2004-2025 Pandora FMS Community
 * https://pandorafms.org
 *
 * Este programa es software libre; puedes redistribuirlo y/o modificarlo bajo
 * los términos de la Licencia Pública General de GNU publicada por la Free
 * Software Foundation para la versión 2. Este programa se distribuye con la
 * esperanza de que sea útil, pero SIN NINGUNA GARANTÍA; ni siquiera con la
 * garantía implícita de COMERCIABILIDAD o IDONEIDAD PARA UN PROPÓSITO
 * PARTICULAR. Consulta la Licencia Pública General de GNU para más detalles.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation for version 2. This program is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without any implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General
 * Public License for more details.
 *
 * Эта программа является свободным программным обеспечением; вы можете
 * распространять и/или изменять её в соответствии с условиями Стандартной
 * общественной лицензии GNU (GPL), опубликованной Фондом свободного
 * программного обеспечения (Free Software Foundation) для версии 2. Эта
 * программа распространяется в надежде, что она будет полезной, НО БЕЗ
 * КАКИХ-ЛИБО ГАРАНТИЙ, даже без подразумеваемой гарантии КОММЕРЧЕСКОЙ
 * ПРИГОДНОСТИ или ПРИГОДНОСТИ ДЛЯ КОНКРЕТНОЙ ЦЕЛИ. Подробнее см. Стандартную
 * общественную лицензию GNU.
 *
 * Ce programme est un logiciel libre ; vous pouvez le redistribuer et/ou le
 * modifier selon les termes de la Licence Publique Générale GNU, publiée par
 * la Free Software Foundation pour la version 2. Ce programme est distribué
 * dans l'espoir qu'il sera utile, mais SANS AUCUNE GARANTIE, même sans la
 * garantie implicite de QUALITÉ MARCHANDE ou D'ADÉQUATION À UN USAGE
 * PARTICULIER. Consultez la Licence Publique Générale GNU pour plus de détails.
 *
 * このプログラムはフリーソフトウェアです。GNU一般公衆利用許諾書
 * （Free Software Foundationによって公開されたバージョン2）の条件の下で、
 * 自由に再配布および改変することができます。本プログラムは有用であることを
 * 願って配布されますが、いかなる保証もありません。商品性や特定目的への適合性の
 * 保証も含まれません。詳しくはGNU一般公衆利用許諾書をご覧ください。
 * ============================================================================
 */

// Begin.
check_login();

global $config;

if (! check_acl($config['id_user'], 0, 'AW')) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access massive operation section'
    );
    include 'general/noaccess.php';
    return;
}

require_once $config['homedir'].'/include/functions_agents.php';
require_once $config['homedir'].'/include/functions_alerts.php';
require_once $config['homedir'].'/include/functions_modules.php';
require_once $config['homedir'].'/include/functions_massive_operations.php';
$tab = (string) get_parameter('tab', 'massive_agents');
$option = (string) get_parameter('option');

$url = 'index.php?sec=gmassive&sec2=godmode/massive/massive_operations';


$options_alerts = [
    'add_alerts'            => __('Bulk alert add'),
    'delete_alerts'         => __('Bulk alert delete'),
    'add_action_alerts'     => __('Bulk alert actions add'),
    'delete_action_alerts'  => __('Bulk alert actions delete'),
    'enable_disable_alerts' => __('Bulk alert enable/disable'),
    'standby_alerts'        => __('Bulk alert setting standby'),
];

$options_agents = [
    'edit_agents'   => __('Bulk agent edit'),
    'delete_agents' => __('Bulk agent delete'),
];

if (check_acl($config['id_user'], 0, 'UM')) {
    $options_users = [
        'edit_users' => __('Edit users in bulk'),
    ];

        $options_profiles = [
            'add_profiles'    => __('Bulk profile add'),
            'delete_profiles' => __('Bulk profile delete'),
        ];

        $options_users = array_merge(array_slice($options_users, 0, count($options_users)), $options_profiles, array_slice($options_users, count($options_users)));
    
} else {
    $options_users = [];
}

$options_modules = [
    'edit_modules'   => __('Bulk module edit'),
    'copy_modules'   => __('Bulk module copy'),
    'delete_modules' => __('Bulk module delete'),
];

$options_plugins = [
    'edit_plugins' => __('Bulk plugin edit'),
];

if (! check_acl($config['id_user'], 0, 'AW')) {
    unset($options_modules['edit_modules']);
}

$options_snmp = [];

$options_satellite = [];

if (in_array($option, array_keys($options_alerts)) === true) {
    $tab = 'massive_alerts';
} else if (in_array($option, array_keys($options_agents)) === true) {
    $tab = 'massive_agents';
} else if (in_array($option, array_keys($options_users)) === true) {
    $tab = 'massive_users';
} else if (in_array($option, array_keys($options_modules)) === true) {
    $tab = 'massive_modules';
} else if (in_array($option, array_keys($options_snmp)) === true) {
    $tab = 'massive_snmp';
} else if (in_array($option, array_keys($options_satellite)) === true) {
    $tab = 'massive_satellite';
} else if (in_array($option, array_keys($options_plugins)) === true) {
    $tab = 'massive_plugins';
}

if ($tab === 'massive_agents' && empty($option) === true) {
    $option = 'edit_agents';
    
}

if ($tab == 'massive_modules' && $option == '') {
    $option = 'edit_modules';
}

switch ($option) {
    case 'edit_agents':
        $help_header = 'massive_agents_tab';
    break;

    case 'edit_modules':
        $help_header = 'massive_modules_tab';
    break;

    default:
        $help_header = '';
    break;
}

switch ($tab) {
    case 'massive_alerts':
        $options = $options_alerts;
    break;

    case 'massive_agents':
        $options = $options_agents;
    break;

    case 'massive_modules':
        $options = $options_modules;
    break;

    case 'massive_users':
        $options = $options_users;
    break;

    case 'massive_snmp':
        $options = $options_snmp;
    break;

    case 'massive_satellite':
        $options = $options_satellite;
    break;

    case 'massive_plugins':
        $options = $options_plugins;
    break;

    default:
        // Default.
    break;
}

// Set the default option of the category.
if ($option == '') {
    $option = array_shift(array_keys($options));
}

$alertstab = [
    'text'   => '<a href="'.$url.'&tab=massive_alerts">'.html_print_image(
        'images/alert@svg.svg',
        true,
        [
            'title' => __('Alerts operations'),
            'class' => 'invert_filter main_menu_icon',
        ]
    ).'</a>',
    'active' => $tab == 'massive_alerts',
];

$userstab = [
    'text'   => '<a href="'.$url.'&tab=massive_users">'.html_print_image(
        'images/user.svg',
        true,
        [
            'title' => __('Users operations'),
            'class' => 'invert_filter main_menu_icon',
        ]
    ).'</a>',
    'active' => $tab == 'massive_users',
];

$agentstab = [
    'text'   => '<a href="'.$url.'&tab=massive_agents">'.html_print_image(
        'images/agents@svg.svg',
        true,
        [
            'title' => __('Agents operations'),
            'class' => 'invert_filter main_menu_icon',
        ]
    ).'</a>',
    'active' => $tab == 'massive_agents',
];

$modulestab = [
    'text'   => '<a href="'.$url.'&tab=massive_modules">'.html_print_image(
        'images/modules@svg.svg',
        true,
        [
            'title' => __('Modules operations'),
            'class' => 'invert_filter main_menu_icon',
        ]
    ).'</a>',
    'active' => $tab == 'massive_modules',
];

$pluginstab = [
    'text'   => '<a href="'.$url.'&tab=massive_plugins">'.html_print_image(
        'images/plugins@svg.svg',
        true,
        [
            'title' => __('Plugins operations'),
            'class' => 'invert_filter main_menu_icon',
        ]
    ).'</a>',
    'active' => $tab == 'massive_plugins',
];

$snmptab = '';
$satellitetab = '';

$onheader = [];
$onheader['massive_agents'] = $agentstab;
$onheader['massive_modules'] = $modulestab;
$onheader['massive_plugins'] = $pluginstab;
if (check_acl($config['id_user'], 0, 'UM')) {
    $onheader['user_agents'] = $userstab;
}

if (isset($servicestab) === false) {
    $servicestab = '';
}

$onheader['massive_alerts'] = $alertstab;
$onheader['snmp'] = $snmptab;
$onheader['satellite'] = $satellitetab;
$onheader['services'] = $servicestab;


// Header.

    ui_print_standard_header(
        __('Bulk operations').' - '.$options[$option],
        'images/gm_massive_operations.png',
        false,
        $help_header,
        false,
        [
            $agentstab,
            $modulestab,
            $pluginstab,
            $userstab,
            $alertstab,
            $snmptab,
            $satellitetab,
            $servicestab,
        ],
        [
            [
                'link'  => '',
                'label' => __('Configuration'),
            ],
            [
                'link'  => '',
                'label' => __('Bulk operations'),
            ],
        ]
    );



// Checks if the PHP configuration is correctly.
if ((get_cfg_var('max_execution_time') != 0)
    || (get_cfg_var('max_input_time') != -1)
) {
    echo '<div id="notify_conf" class="notify">';
    echo __('In order to perform massive operations, PHP needs a correct configuration in timeout parameters. Please, open your PHP configuration file (php.ini) for example: <i>sudo vi /etc/php5/apache2/php.ini;</i><br> And set your timeout parameters to a correct value: <br><i> max_execution_time = 0</i> and <i>max_input_time = -1</i>');
    echo '</div>';
}

// Catch all submit operations in this view to display Wait banner.
$submit_action = get_parameter('go');
$submit_update = get_parameter('updbutton');
$submit_del = get_parameter('del');
$submit_template_disabled = get_parameter('id_alert_template_disabled');
$submit_template_enabled = get_parameter('id_alert_template_enabled');
$submit_template_not_standby = get_parameter('id_alert_template_not_standby');
$submit_template_standby = get_parameter('id_alert_template_standby');
$submit_add = get_parameter('crtbutton');
// Waiting spinner.
ui_print_spinner(__('Loading'));
// Modal for show messages.
html_print_div(
    [
        'id'      => 'massive_modal',
        'content' => '',
    ]
);

// Load common JS files.
ui_require_javascript_file('massive_operations');

?>

<script language="javascript" type="text/javascript">
/* <![CDATA[ */
    $(document).ready (function () {
        $('#button-go').click( function(e) {
            var limitParametersMassive = <?php echo $config['limit_parameters_massive']; ?>;
            var thisForm = e.target.form.id;

            var get_parameters_count = window.location.href.slice(
                window.location.href.indexOf('?') + 1).split('&').length;
            var post_parameters_count = $('#'+thisForm).serializeArray().length;
            var totalCount = get_parameters_count + post_parameters_count;

            var contents = {};

            contents.html = '<?php echo __('No changes have been made because they exceed the maximum allowed (%d). Make fewer changes or contact the administrator.', $config['limit_parameters_massive']); ?>';
            contents.title = '<?php echo __('Massive operations'); ?>';
            contents.question = '<?php echo __('Are you sure?'); ?>';
            contents.ok = '<?php echo __('OK'); ?>';
            contents.cancel = '<?php echo __('Cancel'); ?>';

            var operation = massiveOperationValidation(contents, totalCount, limitParametersMassive, thisForm);

            if (operation == false) {
                return false;
            }
        });
    });
/* ]]> */
</script>

<?php

$tip = '';
if ($option === 'edit_agents' || $option === 'edit_modules') {
    $tip = ui_print_help_tip(__('The blank fields will not be updated'), true);
}

global $SelectAction;

$SelectAction = '<form id="form_necesario" method="post" id="form_options" action="'.$url.'">';
$SelectAction .= '<span class="mrgn_lft_10px mrgn_right_10px">'.__('Action').'</span>';
$SelectAction .= html_print_select(
    $options,
    'option',
    $option,
    'this.form.submit()',
    '',
    0,
    true,
    false,
    false
).$tip;

$SelectAction .= '</form>';

switch ($option) {
    case 'delete_alerts':
        include_once $config['homedir'].'/godmode/massive/massive_delete_alerts.php';
    break;

    case 'add_alerts':
        include_once $config['homedir'].'/godmode/massive/massive_add_alerts.php';
    break;

    case 'delete_action_alerts':
        include_once $config['homedir'].'/godmode/massive/massive_delete_action_alerts.php';
    break;

    case 'add_action_alerts':
        include_once $config['homedir'].'/godmode/massive/massive_add_action_alerts.php';
    break;

    case 'enable_disable_alerts':
        include_once $config['homedir'].'/godmode/massive/massive_enable_disable_alerts.php';
    break;

    case 'standby_alerts':
        include_once $config['homedir'].'/godmode/massive/massive_standby_alerts.php';
    break;

    case 'add_profiles':
        include_once $config['homedir'].'/godmode/massive/massive_add_profiles.php';
    break;

    case 'delete_profiles':
        include_once $config['homedir'].'/godmode/massive/massive_delete_profiles.php';
    break;

    case 'delete_agents':
        include_once $config['homedir'].'/godmode/massive/massive_delete_agents.php';
    break;

    case 'edit_agents':
        include_once $config['homedir'].'/godmode/massive/massive_edit_agents.php';
    break;

    case 'delete_modules':
        include_once $config['homedir'].'/godmode/massive/massive_delete_modules.php';
    break;

    case 'edit_modules':
        include_once $config['homedir'].'/godmode/massive/massive_edit_modules.php';
    break;

    case 'copy_modules':
        include_once $config['homedir'].'/godmode/massive/massive_copy_modules.php';
    break;

    case 'edit_plugins':
        include_once $config['homedir'].'/godmode/massive/massive_edit_plugins.php';
    break;

    case 'edit_users':
        include_once $config['homedir'].'/godmode/massive/massive_edit_users.php';
    break;

    default:
        include_once $config['homedir'].'/godmode/massive/massive_config.php';
    break;
}
