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
require_once $config['homedir'].'/include/class/CredentialStore.class.php';
require_once $config['homedir'].'/operation/snmpconsole/snmp_browser.php';
require_once $config['homedir'].'/include/functions_snmp_browser.php';
$snmp_browser_path = '';
$snmp_browser_path .= 'include/javascript/pandora_snmp_browser.js';
$array_credential_identifier = CredentialStore::getKeys('CUSTOM');

echo '<script type="text/javascript" src="'.$snmp_browser_path.'?v='.$config['current_package'].'"></script>';

// Define a custom action to save the OID selected
// in the SNMP browser to the form.
html_print_input_hidden(
    'custom_action',
    urlencode(
        base64_encode(
            '&nbsp;<a href="javascript:setOID()"><img src="'.ui_get_full_url('images').'/input_filter.disabled.png" title="'.__('Use this OID').'" class="vertical_middle"></img></a>'
        )
    ),
    false
);

$disabledBecauseInPolicy = false;
$disabledTextBecauseInPolicy = '';
$classdisabledBecauseInPolicy = '';
$largeclassdisabledBecauseInPolicy = '';
$page = get_parameter('page', '');
if (strstr($page, 'policy_modules') === false) {
    $disabledBecauseInPolicy = false;

    if ($disabledBecauseInPolicy) {
        $disabledTextBecauseInPolicy = 'readonly = "yes"';
        $classdisabledBecauseInPolicy = 'readonly';
        $largeclassdisabledBecauseInPolicy = 'readonly';
    }
}

define('ID_NETWORK_COMPONENT_TYPE', 2);

if (empty($edit_module) === true) {
    // Function in module_manager_editor_common.php.
    add_component_selection(ID_NETWORK_COMPONENT_TYPE);
}

$extra_title = __('Network server module');

$data = [];
$data[0] = __('Target IP');
if ((int) $id_module_type !== 6 && $id_module_type !== 7) {
    $data[1] = __('Port');
}

$table_simple->rowclass['caption_target_ip'] = 'w50p';
push_table_simple($data, 'caption_target_ip');

$data = [];

if ($ip_target === 'auto') {
    $ip_target = agents_get_address($id_agente);
}

$data[0] = html_print_input_text('ip_target', $ip_target, '', 0, 60, true, false, false, '', 'w100p');

// In ICMP modules, port is not configurable.
if ($id_module_type !== 6 && $id_module_type !== 7) {
    $tcp_port = (empty($tcp_port) === false) ? $tcp_port : get_parameter('tcp_port');
    $data[1] = html_print_input_text(
        'tcp_port',
        $tcp_port,
        '',
        0,
        20,
        true,
        false,
        false,
        '',
        $classdisabledBecauseInPolicy.' w100p',
    );
} else {
    $data[1] = '';
}

$table_simple->rowclass['target_ip'] = 'w50p';
push_table_simple($data, 'target_ip');

$user_groups = users_get_groups(false, 'AR');
if (users_is_admin() === true || isset($user_groups[0]) === true) {
    $credentials = db_get_all_rows_sql(
        'SELECT identifier FROM tcredential_store WHERE product LIKE "SNMP"'
    );
} else {
    $credentials = db_get_all_rows_sql(
        sprintf(
            'SELECT identifier FROM tcredential_store WHERE product LIKE "SNMP" AND id_group IN (%s)',
            implode(',', array_keys($user_groups))
        )
    );
}

if (empty($credentials) === false) {
    $fields = [];
    foreach ($credentials as $key => $value) {
        $fields[$value['identifier']] = $value['identifier'];
    }

    $data = [];
    $data[0] = __('Credential store');
    push_table_simple($data, 'caption_snmp_credentials');

    $data = [];
    $data[0] = html_print_select(
        $fields,
        'credentials',
        0,
        '',
        __('None'),
        0,
        true,
        false,
        false,
        '',
        false,
        false,
        '',
        false
    );
    push_table_simple($data, 'snmp_credentials');
}

$data = [];
$data[0] = __('SNMP community');
$data[1] = __('SNMP version');
$data[2] = __('SNMP OID');
$data[2] .= ui_print_help_icon('snmpwalk', true);
$table_simple->cellclass['snmp_1'][0] = 'w25p';
$table_simple->cellclass['snmp_1'][1] = 'w25p';
$table_simple->cellclass['snmp_1'][2] = 'w50p';
push_table_simple($data, 'snmp_1');
$adopt = false;

if ($adopt === false) {
    $snmpCommunityInput = html_print_input_text(
        'snmp_community',
        $snmp_community,
        '',
        0,
        60,
        true,
        false,
        false,
        '',
        $classdisabledBecauseInPolicy.' w100p'
    );
} else {
    $snmpCommunityInput = html_print_input_text(
        'snmp_community',
        $snmp_community,
        '',
        0,
        60,
        true,
        false,
        false,
        '',
        'w100p'
    );
}

$snmp_versions['1'] = 'v. 1';
$snmp_versions['2c'] = 'v. 2c';
$snmp_versions['3'] = 'v. 3';

$snmpVersionsInput = html_print_select(
    $snmp_versions,
    'snmp_version',
    ($id_module_type >= 15 && $id_module_type <= 18) ? $snmp_version : '2c',
    '',
    '',
    '',
    true,
    false,
    false,
    '',
    false,
    'width: 100%',
    '',
    $classdisabledBecauseInPolicy.' w100p'
);

if ($disabledBecauseInPolicy === true) {
    if ($id_module_type >= 15 && $id_module_type <= 18) {
        $snmpVersionsInput .= html_print_input_hidden('snmp_version', $tcp_send, true);
    }
}

$data = [];
$table_simple->cellclass['snmp_2'][0] = 'w25p';
$table_simple->cellclass['snmp_2'][1] = 'w25p';
$table_simple->cellclass['snmp_2'][2] = 'w50p';

$data[0] = $snmpCommunityInput;
$data[1] = $snmpVersionsInput;
$data[2] = html_print_input_text(
    'snmp_oid',
    $snmp_oid,
    '',
    0,
    255,
    true,
    false,
    false,
    '',
    $classdisabledBecauseInPolicy
);
$data[2] .= '<span class="invisible" id="oid">';
$data[2] .= html_print_select(
    [],
    'select_snmp_oid',
    $snmp_oid,
    '',
    '',
    0,
    true,
    false,
    false,
    '',
    $disabledBecauseInPolicy
);
$data[2] .= html_print_image(
    'images/edit.png',
    true,
    [
        'class' => 'invisible clickable',
        'id'    => 'edit_oid',
    ]
);
$data[2] .= '</span>';
$data[2] .= html_print_button(
    __('SNMP Walk'),
    'snmp_walk',
    false,
    'snmpBrowserWindow('.$id_agente.')',
    [ 'mode' => 'link' ],
    true
);

push_table_simple($data, 'snmp_2');

// Advanced stuff.
$data = [];
$data[0] = __('TCP send');
$data[1] = __('TCP receive');

push_table_simple($data, 'caption_tcp_send_receive');

$data = [];
$data[0] = html_print_textarea(
    'tcp_send',
    2,
    65,
    $tcp_send,
    $disabledTextBecauseInPolicy,
    true,
    $largeclassdisabledBecauseInPolicy
);
$data[1] = html_print_textarea(
    'tcp_rcv',
    2,
    65,
    $tcp_rcv,
    $disabledTextBecauseInPolicy,
    true,
    $largeclassdisabledBecauseInPolicy
);

push_table_simple($data, 'tcp_send_receive');

if ($id_module_type < 8 || $id_module_type > 11) {
    // NOT TCP.
    $table_simple->rowstyle['caption_tcp_send_receive'] = 'display: none;';
    $table_simple->rowstyle['tcp_send_receive'] = 'display: none;';
}

if ($id_module_type < 15 || $id_module_type > 18) {
    // NOT SNMP.
    $table_simple->rowstyle['snmp_1'] = 'display: none';
    $table_simple->rowstyle['snmp_2'] = 'display: none';
    $table_simple->rowstyle['snmp_credentials'] = 'display: none';
}

// For a policy.
if (isset($id_agent_module) === false || $id_agent_module === false) {
    $snmp3_auth_user = '';
    $snmp3_auth_pass = '';
    $snmp_version = 1;
    $snmp3_privacy_method = '';
    $snmp3_privacy_pass = '';
    $snmp3_auth_method = '';
    $snmp3_security_level = '';
    $command_text = '';
    $command_os = 'inherited';
    $command_credential_identifier = '';
}

$data = [];
$data[0] = __('Auth user');
$data[1] = html_print_input_text(
    'snmp3_auth_user',
    $snmp3_auth_user,
    '',
    15,
    60,
    true,
    false,
    false,
    '',
    $classdisabledBecauseInPolicy
);
$data[2] = __('Auth password').ui_print_help_tip(__('The pass length must be eight character minimum.'), true);
$data[3] = html_print_input_password(
    'snmp3_auth_pass',
    $snmp3_auth_pass,
    '',
    15,
    60,
    true,
    false,
    false,
    $largeclassdisabledBecauseInPolicy,
    'off',
    true
);
$data[3] .= html_print_input_hidden_extended('active_snmp_v3', 0, 'active_snmp_v3_mmen', true);
if ($snmp_version != 3) {
    $table_simple->rowstyle['field_snmpv3_row1'] = 'display: none;';
}

push_table_simple($data, 'field_snmpv3_row1');

$data = [];
$data[0] = __('Privacy method');
$data[1] = html_print_select(
    [
        'DES' => __('DES'),
        'AES' => __('AES'),
    ],
    'snmp3_privacy_method',
    $snmp3_privacy_method,
    '',
    '',
    '',
    true,
    false,
    false,
    '',
    $disabledBecauseInPolicy
);
$data[2] = __('Privacy pass').ui_print_help_tip(__('The pass length must be eight character minimum.'), true);
$data[3] = html_print_input_password(
    'snmp3_privacy_pass',
    $snmp3_privacy_pass,
    '',
    15,
    60,
    true,
    false,
    false,
    $largeclassdisabledBecauseInPolicy,
    'off',
    true
);

if ($snmp_version != 3) {
    $table_simple->rowstyle['field_snmpv3_row2'] = 'display: none;';
}

push_table_simple($data, 'field_snmpv3_row2');

$data = [];
$data[0] = __('Auth method');
$data[1] = html_print_select(
    [
        'MD5' => __('MD5'),
        'SHA' => __('SHA'),
    ],
    'snmp3_auth_method',
    $snmp3_auth_method,
    '',
    '',
    '',
    true,
    false,
    false,
    '',
    $disabledBecauseInPolicy
);
$data[2] = __('Security level');
$data[3] = html_print_select(
    [
        'noAuthNoPriv' => __('Not auth and not privacy method'),
        'authNoPriv'   => __('Auth and not privacy method'),
        'authPriv'     => __('Auth and privacy method'),
    ],
    'snmp3_security_level',
    $snmp3_security_level,
    '',
    '',
    '',
    true,
    false,
    false,
    '',
    $disabledBecauseInPolicy
);
if ($snmp_version != 3) {
    $table_simple->rowstyle['field_snmpv3_row3'] = 'display: none;';
}

push_table_simple($data, 'field_snmpv3_row3');

$data = [];
$data[0] = __('Command');
$data[0] .= ui_print_help_tip(
    __(
        'Please use single quotation marks when necessary. '."\n".'
If double quotation marks are needed, please escape them with a backslash (\&quot;)'
    ),
    true
);
push_table_simple($data, 'caption-row-cmd-row-1');

$data = [];
$data[0] = html_print_input_text_extended(
    'command_text',
    $command_text,
    'command_text',
    '',
    0,
    10000,
    false,
    '',
    $largeClassDisabledBecauseInPolicy.' class="w100p"',
    true
);
$table_simple->rowclass['row-cmd-row-1'] = 'w100p';
push_table_simple($data, 'row-cmd-row-1');

$data = [];
$data[0] = __('Credential identifier');
$data[1] = __('Connection method');
// $table_simple->rowclass['row-cmd-row-1'] = 'w100p';
$table_simple->cellclass['caption-row-cmd-row-2'][0] = 'w50p';
$table_simple->cellclass['caption-row-cmd-row-2'][1] = 'w50p';
push_table_simple($data, 'caption-row-cmd-row-2');

$data = [];
$data[0] = html_print_select(
    $array_credential_identifier,
    'command_credential_identifier',
    $command_credential_identifier,
    '',
    __('None'),
    '',
    true,
    false,
    false,
    '',
    false,
    'width: 100%;'
);

$data[0] .= html_print_button(
    __('Manage credentials'),
    'manage_credentials_button',
    false,
    'window.location.assign("index.php?sec=gmodules&sec2=godmode/groups/group_list&tab=credbox")',
    [ 'mode' => 'link' ],
    true
);

$array_os = [
    'inherited' => __('Inherited'),
    'linux'     => __('SSH'),
    'windows'   => __('Windows remote'),
];

$data[1] = html_print_select(
    $array_os,
    'command_os',
    $command_os,
    '',
    '',
    '',
    true,
    false,
    false,
    '',
    false,
    'width: 100%;'
);
$table_simple->cellclass['row-cmd-row-2'][0] = 'w50p';
$table_simple->cellclass['row-cmd-row-2'][1] = 'w50p';
push_table_simple($data, 'row-cmd-row-2');

if ($id_module_type !== 34
    && $id_module_type !== 35
    && $id_module_type !== 36
    && $id_module_type !== 37
) {
    $table_simple->rowstyle['caption-row-cmd-row-1'] = 'display: none;';
    $table_simple->rowstyle['row-cmd-row-1'] = 'display: none;';
    $table_simple->rowstyle['caption-row-cmd-row-2'] = 'display: none;';
    $table_simple->rowstyle['row-cmd-row-2'] = 'display: none;';
}

snmp_browser_print_container(false, '100%', '60%', 'display:none');

?>
<script type="text/javascript">
$(document).ready (function () {
    $("#id_module_type").change(function (){
        if ((this.value == "17") ||
            (this.value == "18") ||
            (this.value == "16") ||
            (this.value == "15")
        ) {
            if ($("#snmp_version").val() == "3"){
                $("#simple-field_snmpv3_row1").attr("style", "");
                $("#simple-field_snmpv3_row2").attr("style", "");
                $("#simple-field_snmpv3_row3").attr("style", "");
                $("input[name=active_snmp_v3]").val(1);
                $("input[name=snmp_community]").attr("disabled", true);
            }
        } else {
            $("#simple-field_snmpv3_row1").css("display", "none");
            $("#simple-field_snmpv3_row2").css("display", "none");
            $("#simple-field_snmpv3_row3").css("display", "none");
            $("input[name=active_snmp_v3]").val(0);
            $("input[name=snmp_community]").removeAttr('disabled');
        }

        if((this.value == "34") ||
            (this.value == "35") ||
            (this.value == "36") ||
            (this.value == "37")
        ) {
            $("#simple-row-cmd-row-1").attr("style", "");
            $("#simple-caption-row-cmd-row-1").attr("style", "");
            $("#simple-row-cmd-row-2").attr("style", "");
            $("#simple-caption-row-cmd-row-2").attr("style", "");
        } else {
            $("#simple-caption-row-cmd-row-1").css("display", "none");
            $("#simple-row-cmd-row-1").css("display", "none");
            $("#simple-caption-row-cmd-row-2").css("display", "none");
            $("#simple-row-cmd-row-2").css("display", "none");
        }
    });

    $("#snmp_version").change(function () {
        if (this.value == "3") {
            $("#simple-field_snmpv3_row1").attr("style", "");
            $("#simple-field_snmpv3_row2").attr("style", "");
            $("#simple-field_snmpv3_row3").attr("style", "");
            $("input[name=active_snmp_v3]").val(1);
        }
        else {
            $("#simple-field_snmpv3_row1").css("display", "none");
            $("#simple-field_snmpv3_row2").css("display", "none");
            $("#simple-field_snmpv3_row3").css("display", "none");
            $("input[name=active_snmp_v3]").val(0);
        }
    });

    $("#select_snmp_oid").click (
        function () {
            $(this).css ("width", "auto");
            $(this).css ("min-width", "180px");
        });

    $("#select_snmp_oid").blur (function () {
        $(this).css ("width", "180px");
    });

    $("#credentials").change (function() {
        if ($('#credentials').val() !== '0') {
            $.ajax({
                method: "post",
                url: "<?php echo ui_get_full_url('ajax.php', false, false, false); ?>",
                data: {
                    page: "godmode/agentes/agent_wizard",
                    method: "getCredentials",
                    identifier: $('#credentials').val()
                },
                datatype: "json",
                success: function(data) {
                    data = JSON.parse(data);
                    extra = JSON.parse(data['extra_1']);
                    $('#snmp_version').val(extra['version']);
                    $('#snmp_version').trigger('change');
                    $('#text-snmp_community').val(extra['community']);

                    if (extra['version'] === '3') {
                        $('#snmp3_security_level').val(extra['securityLevelV3']);
                        $('#snmp3_security_level').trigger('change');
                        $('#text-snmp3_auth_user').val(extra['authUserV3']);

                        if (extra['securityLevelV3'] === 'authNoPriv' || extra['securityLevelV3'] === 'authPriv') {
                            $('#snmp3_auth_method').val(extra['authMethodV3']);
                            $('#snmp3_auth_method').trigger('change');
                            $('#password-snmp3_auth_pass').val(extra['authPassV3']);

                            if (extra['securityLevelV3'] === 'authPriv') {
                                $('#snmp3_privacy_method').val(extra['privacyMethodV3']);
                                $('#snmp3_privacy_method').trigger('change');
                                $('#password-snmp3_privacy_pass').val(extra['privacyPassV3']);
                            }
                        }
                    }
                },
                error: function(e) {
                    console.error(e);
                }
            });
        }
    });

    $("#id_module_type").click (
        function () {
            $(this).css ("width", "auto");
            $(this).css ("min-width", "180px");
        }
    );

    $("#id_module_type").blur (function () {
        $(this).css ("width", "180px");
    });

    // Keep elements in the form and the SNMP browser synced
    $('#text-ip_target').keyup(function() {
        $('#text-target_ip').val($(this).val());
    });

    $('#text-snmp_community').keyup(function() {
        $('#text-community').val($(this).val());
    });
    $('#snmp_version').change(function() {
        $('#snmp_browser_version').val($(this).val());
        // Display or collapse the SNMP browser's v3 options
        checkSNMPVersion ();
    });
    $('#snmp3_auth_user').keyup(function() {
        $('#snmp3_browser_auth_user').val($(this).val());
    });
    $('#snmp3_security_level').change(function() {
        $('#snmp3_browser_security_level').val($(this).val());
    });
    $('#snmp3_auth_method').change(function() {
        $('#snmp3_browser_auth_method').val($(this).val());
    });
    $('#snmp3_auth_pass').keyup(function() {
        $('#snmp3_browser_auth_pass').val($(this).val());
    });
    $('#snmp3_privacy_method').change(function() {
        $('#snmp3_browser_privacy_method').val($(this).val());
    });
    $('#snmp3_privacy_pass').keyup(function() {
        $('#snmp3_browser_privacy_pass').val($(this).val());
    });

    var custom_ip_target = "<?php echo $custom_ip_target; ?>";
    var ip_target = "<?php echo $ip_target; ?>";
    if(ip_target === 'custom'){
        $("#text-custom_ip_target").show();
    } else {
        $("#text-custom_ip_target").hide();
    }

    $('#ip_target').change(function() {
        if($(this).val() === 'custom') {
            $("#text-custom_ip_target").show();
        }
        else{
            $("#text-custom_ip_target").hide();
        }
    });
});


</script>
