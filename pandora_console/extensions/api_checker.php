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


/**
 * Api Execution.
 *
 * @param string $url         Url.
 * @param string $ip          Ip.
 * @param string $pandora_url Pandora_url.
 * @param string $apipass     Apipass.
 * @param string $user        User.
 * @param string $password    Password.
 * @param string $op          Op.
 * @param string $op2         Op2.
 * @param string $id          Id.
 * @param string $id2         Id2.
 * @param string $return_type Return_type.
 * @param string $other       Other.
 * @param string $other_mode  Other_mode.
 * @param string $token       Token.
 *
 * @return array.
 */
function api_execute(
    string $url,
    string $ip,
    string $pandora_url,
    string $apipass,
    string $user,
    string $password,
    string $op,
    string $op2,
    string $id='',
    string $id2='',
    string $return_type='',
    string $other='',
    string $other_mode='',
    string $token=''
) {
    $data = [];

    if (empty($url) === true) {
        $url = 'http://'.$ip.$pandora_url.'/include/api.php?';
    } else {
        $url_schema = parse_url($url);
        $url = $url_schema['scheme'].'://'.$url_schema['host'].$pandora_url.'/include/api.php?';
    }

    if (empty($op) === false) {
        $data['op'] = $op;
    }

    if (empty($op2) === false) {
        $data['op2'] = $op2;
    }

    if (empty($id) === false) {
        $data['id'] = $id;
    }

    if (empty($id2) === false) {
        $data['id2'] = $id2;
    }

    if (empty($return_type) === false) {
        $data['return_type'] = $return_type;
    }

    if (empty($other) === false) {
        $data['other_mode'] = $other_mode;
        $data['other'] = $other;
    }

    // If token is not reported,use old method.
    if (empty($token) === true) {
        $data['apipass'] = $apipass;
        $data['user'] = $user;
        $data['pass'] = $password;
    }

    $url_protocol = parse_url($url)['scheme'];

    if ($url_protocol !== 'http' && $url_protocol !== 'https') {
        return [
            'url'    => $url,
            'result' => '',
        ];
    }

    $curlObj = curl_init($url);
    if (empty($data) === false) {
        $url .= http_build_query($data);
    }

    // set the content type json
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer '.$token,
    ];

    curl_setopt($curlObj, CURLOPT_URL, $url);
    curl_setopt($curlObj, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($curlObj);
    curl_close($curlObj);

    return [
        'url'    => $url,
        'result' => $result,
    ];
}


/**
 * Perform API Checker
 *
 * @return void.
 */
function extension_api_checker()
{
    global $config;

    check_login();

    if ((bool) check_acl($config['id_user'], 0, 'PM') === false) {
        db_pandora_audit(
            AUDIT_LOG_ACL_VIOLATION,
            'Trying to access Profile Management'
        );
        include 'general/noaccess.php';
        return;
    }

    $url = io_safe_output(get_parameter('url', ''));
    $ip = io_safe_output(get_parameter('ip', '127.0.0.1'));
    $pandora_url = io_safe_output(get_parameter('pandora_url', $config['homeurl_static']));
    $apipass = io_safe_output(get_parameter('apipass', ''));
    $user = io_safe_output(get_parameter('user', $config['id_user']));
    $password = io_safe_output(get_parameter('password', ''));

    $op = io_safe_output(get_parameter('op', 'get'));
    $op2 = io_safe_output(get_parameter('op2', 'test'));
    $id = io_safe_output(get_parameter('id', ''));
    $id2 = io_safe_output(get_parameter('id2', ''));
    $return_type = io_safe_output(get_parameter('return_type', ''));
    $other = io_safe_output(get_parameter('other', ''));
    $other_mode = io_safe_output(get_parameter('other_mode', 'url_encode_separator_|'));
    $token = get_parameter('token');

    $api_execute = (bool) get_parameter('api_execute', false);

    if ($url !== '') {
        $validate_url = parse_url($url);
        if ($validate_url['scheme'] === 'http' || $validate_url['scheme'] === 'https') {
            ui_print_success_message(__('Request successfully processed'));
        } else {
            ui_print_error_message(__('Incorrect URL'));
            $url = '';
            $api_execute = false;
        }
    }

    $return_call_api = '';
    if ($api_execute === true) {
        $return_call_api = api_execute(
            $url,
            $ip,
            $pandora_url,
            $apipass,
            $user,
            $password,
            $op,
            $op2,
            urlencode($id),
            urlencode($id2),
            $return_type,
            urlencode($other),
            $other_mode,
            $token
        );
    }

    // Header.
    ui_print_standard_header(
        __('Extensions'),
        'images/extensions.png',
        false,
        '',
        true,
        [],
        [
            [
                'link'  => '',
                'label' => __('Admin tools'),
            ],
            [
                'link'  => '',
                'label' => __('Extension manager'),
            ],
            [
                'link'  => '',
                'label' => __('API checker'),
            ],
        ]
    );

    $table = new stdClass();
    $table->width = '100%';
    $table->class = 'databox filters filter-table-adv';
    $table->size[0] = '50%';
    $table->size[1] = '50%';
    $table->data = [];

    $row = [];
    $row[] = html_print_label_input_block(
        __('IP'),
        html_print_input_text('ip', $ip, '', 50, 255, true)
    );

    $row[] = html_print_label_input_block(
        __('%s Console URL', get_product_name()),
        html_print_input_text('pandora_url', $pandora_url, '', 50, 255, true)
    );
    $table->data[] = $row;

    $row = [];
    $row[] = html_print_label_input_block(
        __('API Token').ui_print_help_tip(__('Use API Token instead API Pass, User and Password.'), true),
        html_print_input_text('token', $token, '', 50, 255, true)
    );

    $row[] = html_print_label_input_block(
        __('API Pass'),
        html_print_input_password('apipass', $apipass, '', 50, 255, true)
    );
    $table->data[] = $row;

    $row = [];
    $row[] = html_print_label_input_block(
        __('User'),
        html_print_input_text('user', $user, '', 50, 255, true)
    );

    $row[] = html_print_label_input_block(
        __('Password'),
        html_print_input_password('password', $password, '', 50, 255, true)
    );
    $table->data[] = $row;

    $table2 = new stdClass();
    $table2->width = '100%';
    $table2->class = 'databox filters filter-table-adv';
    $table2->size[0] = '50%';
    $table2->size[1] = '50%';
    $table2->data = [];

    $row = [];
    $row[] = html_print_label_input_block(
        __('Action (get or set)'),
        html_print_input_text('op', $op, '', 50, 255, true)
    );

    $row[] = html_print_label_input_block(
        __('Operation'),
        html_print_input_text('op2', $op2, '', 50, 255, true)
    );
    $table2->data[] = $row;

    $row = [];
    $row[] = html_print_label_input_block(
        __('ID'),
        html_print_input_text('id', $id, '', 50, 255, true)
    );

    $row[] = html_print_label_input_block(
        __('ID 2'),
        html_print_input_text('id2', $id2, '', 50, 255, true)
    );
    $table2->data[] = $row;

    $row = [];
    $row[] = html_print_label_input_block(
        __('Return Type'),
        html_print_input_text('return_type', $return_type, '', 50, 255, true)
    );

    $row[] = html_print_label_input_block(
        __('Other'),
        html_print_input_text('other', $other, '', 50, 255, true)
    );
    $table2->data[] = $row;

    $row = [];
    $row[] = html_print_label_input_block(
        __('Other Mode'),
        html_print_input_text('other_mode', $other_mode, '', 50, 255, true)
    );
    $table2->data[] = $row;

    $table3 = new stdClass();
    $table3->width = '100%';
    $table3->class = 'databox filters filter-table-adv';
    $table3->size[0] = '50%';
    $table3->size[1] = '50%';
    $table3->data = [];

    $row = [];
    $row[] = html_print_label_input_block(
        __('Raw URL'),
        html_print_input_text('url', $url, '', 50, 2048, true)
    );
    $table3->data[] = $row;

    echo "<form method='post' class='max_floating_element_size'>";
    echo '<fieldset class="mrgn_btn_10px">';
    echo '<legend>'.__('Credentials').'</legend>';
    html_print_table($table);
    echo '</fieldset>';

    echo '<fieldset class="mrgn_btn_10px">';
    echo '<legend>'.__('Call parameters').' '.ui_print_help_tip(__('Action: get Operation: module_last_value id: 63'), true).'</legend>';
    html_print_table($table2);
    echo '</fieldset>';
    echo "<div class='right'>";
    echo '</div>';

    echo '<fieldset class="mrgn_btn_10px">';
    echo '<legend>'.__('Custom URL').'</legend>';
    html_print_table($table3);
    echo '</fieldset>';

    html_print_input_hidden('api_execute', 1);

    html_print_action_buttons(
        html_print_submit_button(
            __('Call'),
            'submit',
            false,
            [ 'icon' => 'next' ],
            true
        )
    );

    echo '</form>';

    if ($api_execute === true) {
        echo '<fieldset class="mrgn_0px mrgn_btn_10px pdd_15px" style="max-width: 1122px;">';
        echo '<legend>'.__('Result').'</legend>';
        echo html_print_label_input_block(
            __('URL'),
            html_print_input_password('url', $return_call_api['url'], '', 150, 255, true, true, false, 'mrgn_top_10px'),
            ['label_class' => 'font-title-font']
        );
        echo '<br />';
        echo html_print_label_input_block(
            __('Result'),
            html_print_textarea('result', 30, 20, $return_call_api['result'], 'readonly="readonly"', true, 'w100p mrgn_top_10px'),
            ['label_class' => 'font-title-font']
        );
        echo '</fieldset>';
    }
    ?>
    <script>
    function show_url() {
        if ($("#password-url").attr('type') == 'password') {
            $("#password-url").attr('type', 'text');
            $("#show_icon").attr('title', '<?php echo __('Hide URL'); ?>');
        }
        else {
            $("#password-url").attr('type', 'password');
            $("#show_icon").attr('title', '<?php echo __('Show URL'); ?>');
        }
    }
    </script>
    <?php
}


extensions_add_godmode_function('extension_api_checker');
extensions_add_godmode_menu_option(__('API checker'), 'PM', 'gextensions', null, 'v1r1');

