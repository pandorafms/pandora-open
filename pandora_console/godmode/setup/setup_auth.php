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

global $config;

check_login();

if ((bool) check_acl($config['id_user'], 0, 'PM') === false && is_user_admin($config['id_user']) === false) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access Setup Management'
    );
    include 'general/noaccess.php';
    return;
}

if (is_ajax() === true) {
    $change_auth_metod = (bool) get_parameter('change_auth_metod');

    if ($change_auth_metod === true) {
        $table = new StdClass();
        $table->data = [];
        $table->width = '100%';
        $table->class = 'databox filters table_result_auth filter-table-adv';
        $table->size['name'] = '30%';
        $table->style['name'] = 'font-weight: bold';

        $type_auth = (string) get_parameter('type_auth', '');

        // Field for all types except mysql.
        if ($type_auth != 'mysql') {
            // Fallback to local authentication.
            $row = [];
            $row['name'] = __('Fallback to local authentication');
            $row['control'] = html_print_checkbox_switch(
                'fallback_local_auth',
                1,
                $config['fallback_local_auth'],
                true
            );
            $table->data['fallback_local_auth'] = $row;
        }

        switch ($type_auth) {
            case 'ldap':
                // LDAP server.
                $row = [];
                $row['name'] = __('LDAP server');
                $row['control'] = html_print_input_text(
                    'ldap_server',
                    $config['ldap_server'],
                    '',
                    30,
                    100,
                    true,
                    false,
                    false,
                    '',
                    'w400px'
                );
                $table->data['ldap_server'] = $row;

                // LDAP port.
                $row = [];
                $row['name'] = __('LDAP port');
                $row['control'] = html_print_input_text(
                    'ldap_port',
                    $config['ldap_port'],
                    '',
                    10,
                    100,
                    true,
                    false,
                    false,
                    '',
                    'w400px'
                );
                $table->data['ldap_port'] = $row;

                // LDAP version.
                $ldap_versions = [
                    1 => 'LDAPv1',
                    2 => 'LDAPv2',
                    3 => 'LDAPv3',
                ];
                $row = [];
                $row['name'] = __('LDAP version');
                $row['control'] = html_print_select(
                    $ldap_versions,
                    'ldap_version',
                    $config['ldap_version'],
                    '',
                    '',
                    0,
                    true,
                    false,
                    true,
                    'w400px'
                );
                $table->data['ldap_version'] = $row;

                // Start TLS.
                $row = [];
                $row['name'] = __('Start TLS');
                $row['control'] = html_print_checkbox_switch(
                    'ldap_start_tls',
                    1,
                    $config['ldap_start_tls'],
                    true
                );
                $table->data['ldap_start_tls'] = $row;

                // Base DN.
                $row = [];
                $row['name'] = __('Base DN');
                $row['control'] = html_print_input_text(
                    'ldap_base_dn',
                    $config['ldap_base_dn'],
                    '',
                    60,
                    100,
                    true
                );
                $table->data['ldap_base_dn'] = $row;

                // Login attribute.
                $row = [];
                $row['name'] = __('Login attribute');
                $row['control'] = html_print_input_text(
                    'ldap_login_attr',
                    $config['ldap_login_attr'],
                    '',
                    60,
                    100,
                    true
                );
                $table->data['ldap_login_attr'] = $row;

                // Admin LDAP login.
                $row = [];
                $row['name'] = __('Admin LDAP login');
                $row['control'] = html_print_input_text(
                    'ldap_admin_login',
                    $config['ldap_admin_login'],
                    '',
                    60,
                    100,
                    true
                );
                $table->data['ldap_admin_login'] = $row;

                // Admin LDAP password.
                $row = [];
                $row['name'] = __('Admin LDAP password');
                $row['control'] = html_print_input_password(
                    'ldap_admin_pass',
                    (empty(io_output_password($config['ldap_admin_pass'])) === false) ? '*****' : '',
                    $alt = '',
                    60,
                    100,
                    true,
                    false,
                    false,
                    'w400px-important',
                    'on',
                    false,
                    '',
                    true,
                    false,
                    true
                );

                $table->data['ldap_admin_pass'] = $row;

                // Ldapsearch timeout.
                // Default Ldapsearch timeout.
                set_when_empty($config['ldap_search_timeout'], 5);
                $row = [];
                $row['name'] = __('Ldap search timeout (secs)');
                $row['control'] = html_print_input_text(
                    'ldap_search_timeout',
                    $config['ldap_search_timeout'],
                    '',
                    10,
                    10,
                    true,
                    false,
                    false,
                    '',
                    'w400px'
                );
                $table->data['ldap_search_timeout'] = $row;

                // Enable/disable secondary ldap.
                // Set default value.
                set_unless_defined($config['secondary_ldap_enabled'], false);

                $row = [];
                $row['name'] = __('Enable secondary LDAP');
                $row['control'] .= html_print_checkbox_switch(
                    'secondary_ldap_enabled',
                    1,
                    $config['secondary_ldap_enabled'],
                    true,
                    false,
                    'showAndHide()'
                );

                $table->data['secondary_ldap_enabled'] = $row;
                $row = [];

                // LDAP server.
                $row = [];
                $row['name'] = __('Secondary LDAP server');
                $row['control'] = html_print_input_text(
                    'ldap_server_secondary',
                    $config['ldap_server_secondary'],
                    '',
                    30,
                    100,
                    true,
                    false,
                    false,
                    '',
                    'w400px'
                );
                $table->data['ldap_server_secondary'] = $row;

                // LDAP port.
                $row = [];
                $row['name'] = __('Secondary LDAP port');
                $row['control'] = html_print_input_text(
                    'ldap_port_secondary',
                    $config['ldap_port_secondary'],
                    '',
                    10,
                    100,
                    true,
                    false,
                    false,
                    '',
                    'w400px'
                );
                $table->data['ldap_port_secondary'] = $row;

                // LDAP version.
                $ldap_versions = [
                    1 => 'LDAPv1',
                    2 => 'LDAPv2',
                    3 => 'LDAPv3',
                ];
                $row = [];
                $row['name'] = __('Secondary LDAP version');
                $row['control'] = html_print_select(
                    $ldap_versions,
                    'ldap_version_secondary',
                    $config['ldap_version_secondary'],
                    '',
                    '',
                    0,
                    true,
                    false,
                    true,
                    'w400px'
                );
                $table->data['ldap_version_secondary'] = $row;

                // Start TLS.
                $row = [];
                $row['name'] = __('Secondary start TLS');
                $row['control'] = html_print_checkbox_switch(
                    'ldap_start_tls_secondary',
                    1,
                    $config['ldap_start_tls_secondary'],
                    true
                );
                $table->data['ldap_start_tls_secondary'] = $row;

                // Base DN.
                $row = [];
                $row['name'] = __('Secondary Base DN');
                $row['control'] = html_print_input_text(
                    'ldap_base_dn_secondary',
                    $config['ldap_base_dn_secondary'],
                    '',
                    60,
                    100,
                    true
                );
                $table->data['ldap_base_dn_secondary'] = $row;

                // Login attribute.
                $row = [];
                $row['name'] = __('Secondary Login attribute');
                $row['control'] = html_print_input_text(
                    'ldap_login_attr_secondary',
                    $config['ldap_login_attr_secondary'],
                    '',
                    60,
                    100,
                    true
                );
                $table->data['ldap_login_attr_secondary'] = $row;

                // Admin LDAP login.
                $row = [];
                $row['name'] = __('Admin secondary LDAP login');
                $row['control'] = html_print_input_text(
                    'ldap_admin_login_secondary',
                    $config['ldap_admin_login_secondary'],
                    '',
                    60,
                    100,
                    true
                );
                $table->data['ldap_admin_login_secondary'] = $row;

                // Admin LDAP password.
                $row = [];
                $row['name'] = __('Admin secondary LDAP password');
                $row['control'] = html_print_input_password(
                    'ldap_admin_pass_secondary',
                    (empty(io_output_password($config['ldap_admin_pass_secondary'])) === false) ? '*****' : '',
                    $alt = '',
                    60,
                    100,
                    true,
                    false,
                    false,
                    'w400px-important',
                    'on',
                    false,
                    '',
                    true,
                    false,
                    true
                );
                $table->data['ldap_admin_pass_secondary'] = $row;
            break;

            case 'pandora':
            case 'ad':
            case 'saml':
            case 'ITSM':
            break;

            case 'mysql':
            default:
                // Default case.
            break;
        }

        // Field for all types.
        // Enable double authentication.
        // Set default value.
        set_unless_defined($config['double_auth_enabled'], false);
        $row = [];
        $row['name'] = __('Double authentication');
        $row['control'] = html_print_checkbox_switch(
            'double_auth_enabled',
            1,
            $config['double_auth_enabled'],
            true,
            false,
            'showAndHide()'
        );
        $table->data['double_auth_enabled'] = $row;

        // Enable 2FA for all users.
        // Set default value.
        set_unless_defined($config['2FA_all_users'], false);
        $row = [];
        $row['name'] = __('Force 2FA for all users is enabled');
        $row['control'] = html_print_checkbox_switch(
            '2FA_all_users',
            1,
            $config['2FA_all_users'],
            true
        );

        if ((bool) $config['double_auth_enabled'] === false) {
            $table->rowclass['2FA_all_users'] = 'invisible';
        } else {
            $table->rowclass['2FA_all_users'] = '';
        }

        $table->data['2FA_all_users'] = $row;

        // Session timeout behavior.
        // Set default value.
        $row = [];
        $options = [
            'check_activity'  => __('Check activity'),
            'ignore_activity' => __('Ignore activity'),
        ];

        $row['name'] = __('Control of timeout session').ui_print_help_tip(__('Select \'ignore activity\' to ignore user activity when checking the session.'), true);
        $row['control'] = html_print_select(
            $options,
            'control_session_timeout',
            $config['control_session_timeout'],
            '',
            '',
            0,
            true
        );
        $table->data['session_timeouts'] = $row;


        // Session timeout.
        // Default session timeout.
        set_when_empty($config['session_timeout'], 90);
        $row = [];
        $row['name'] = __('Session timeout (mins)');
        $row['control'] = html_print_input_text(
            'session_timeout',
            $config['session_timeout'],
            '',
            10,
            10,
            true,
            false,
            false,
            '',
            'w400px'
        );
        $table->data['session_timeout'] = $row;

        html_print_table($table);
        return;
    }
}

require_once $config['homedir'].'/include/functions_profile.php';

$table = new StdClass();
$table->data = [];
$table->width = '100%';
$table->class = 'databox filters filter-table-adv';
$table->size['name'] = '30%';
$table->style['name'] = 'font-weight: bold';

// Auth methods added to the table (doesn't take in account mysql).
$auth_methods_added = [];

// Remote options row names.
// Fill this array for every matched row.
$remote_rows = [];

// Autocreate options row names.
// Fill this array for every matched row.
$autocreate_rows = [];
$no_autocreate_rows = [];

// LDAP data row names.
// Fill this array for every matched row.
$ldap_rows = [];

// Method.
$auth_methods = [
    'mysql' => __('Local %s', get_product_name()),
    'ldap'  => __('LDAP'),
];

$row = [];
$row['name'] = __('Authentication method');
$row['control'] = html_print_select(
    $auth_methods,
    'auth',
    $config['auth'],
    '',
    '',
    0,
    true,
    false,
    true,
    'w400px'
);
$table->data['auth'] = $row;

// Form.
echo '<form id="form_setup" class="max_floating_element_size" method="post">';

    html_print_input_hidden('update_config', 1);


html_print_csrf_hidden();

html_print_table($table);
html_print_div([ 'id' => 'table_auth_result' ]);
html_print_action_buttons(
    html_print_submit_button(
        __('Update'),
        'update_button',
        false,
        [
            'icon'    => 'update',
            'onclick' => 'onFormSubmit()',
        ],
        true
    )
);

echo '</form>';
echo ui_print_warning_message(
    [
        'message'     => __('Session timeout must be a number'),
        'force_class' => 'invisible js_warning_msg',
    ],
    '',
    true
);
?>

<script type="text/javascript">

    function onFormSubmit() {
        const isNumber = n => $.isNumeric(n);

        let session_timeout = $('#text-session_timeout').val()
        if(isNumber(session_timeout)) {
            if (session_timeout < 0) {

                session_timeout = -1;
            }
            if (session_timeout > 604800) {
                session_timeout = 604800;
            }
            $('#text-session_timeout').val(session_timeout);
        } else {
            $('.js_warning_msg').removeClass('invisible');
            event.preventDefault();
            return false;
        }
    }

    function showAndHide() {
        if ($('input[type=checkbox][name=double_auth_enabled]:checked').val() == 1) {
                $('#table1-2FA_all_users').removeClass('invisible');
                $('#table1-2FA_all_users-name').removeClass('invisible');
                $('#table1-2FA_all_users-control').removeClass('invisible');
                $('#table1-2FA_all_users').show();
            } else {
                $('#table1-2FA_all_users').hide();
        }

        if ($('input[type=checkbox][name=secondary_ldap_enabled]:checked').val() == 1) {
            $("tr[id*='ldap_'][id$='_secondary']").show();
        } else {
            $( "tr[id*='ldap_'][id$='_secondary']" ).hide();
        }

        if ($('input[type=checkbox][name=secondary_active_directory]:checked').val() == 1) {
            $("tr[id*='ad_'][id$='_secondary']").show();
        } else {
            $( "tr[id*='ad_'][id$='_secondary']" ).hide();
        }
    }
    $( document ).ready(function() {   

    });
    //For change autocreate remote users

    $('#auth').on('change', function(){
        type_auth = $('#auth').val();
        $.ajax({
            type: "POST",
            url: "<?php echo ui_get_full_url('ajax.php', false, false, false); ?>",
            data: "page=godmode/setup/setup_auth&change_auth_metod=1&type_auth=" + type_auth,
            dataType: "html",
            success: function(data) {
                $('.table_result_auth').remove();
                $('#table_auth_result').append(data);
                showAndHide();
            }
        });
    }).change();
</script>
