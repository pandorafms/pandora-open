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

// Global variables.
global $config;

check_login();

require_once $config['homedir'].'/include/functions_token.php';
// Get parameters.
$tab = get_parameter('tab', 'token');
$pure = get_parameter('pure', 0);
$id_token = (int) get_parameter('id_token');

// Header.

    user_print_header($pure, $tab);
    $sec2 = 'gusuarios';


$url_list = 'index.php?sec='.$sec;
$url_list .= '&sec2=godmode/users/token_list';
$url_list .= '&pure='.$pure;

// Edit token.
if (empty($id_token) === true) {
    $label = '';
    $validity = '';
    $page_title = __('Create token');
} else {
    try {
        $token = get_user_token($id_token);
    } catch (\Exception $e) {
        ui_print_error_message(
            __('There was a problem get token, %s', $e->getMessage())
        );
    }
}

$table = new StdClass();
$table->width = '100%';
$table->class = 'databox filters';
$table->data = [];
$table->rowspan = [];
$table->colspan = [];

$table->data[0][0] = __('Token label');
$table->data[0][1] = html_print_input_text(
    'label',
    $token['label'],
    '',
    50,
    255,
    true
);

if ((bool) users_is_admin() === true) {
    $table->data[0][2] = __('User');
    $user_users = users_get_user_users(
        $config['id_user'],
        'AR',
        true
    );

    $table->data[0][3] = html_print_select(
        $user_users,
        'idUser',
        $config['id_user'],
        '',
        '',
        0,
        true
    );
}

$expiration_date = null;
$expiration_time = null;
if (empty($token['validity']) === false) {
    $array_date = explode(' ', io_safe_output($token['validity']));
    if (is_array($array_date) === true) {
        $expiration_date = $array_date[0];
        if (isset($array_date[1]) === true
            && empty($array_date[1]) === false
        ) {
            $expiration_time = $array_date[1];
        }
    }
}

$table->data[1][0] = __('Expiration');
$table->data[1][1] = html_print_input_text(
    'date-expiration',
    $expiration_date,
    '',
    50,
    255,
    true
).html_print_input_hidden('today_date', date('Y-m-d'), true);

$table->data[1][2] = __('Expiration Time');
$table->data[1][3] = html_print_input_text(
    'time-expiration',
    $expiration_time,
    '',
    50,
    255,
    true
).html_print_input_hidden('today_time', date('H:i:s'), true);

echo '<form class="max_floating_element_size" id="form_token" method="post" action="'.$url_list.'">';

html_print_table($table);

$actionButtons = [];

if (empty($id_token) === true) {
    $actionButtons[] = html_print_submit_button(
        __('Create'),
        'next',
        false,
        ['icon' => 'wand'],
        true
    );
    html_print_input_hidden('create_token', 1);
} else {
    $actionButtons[] = html_print_submit_button(
        __('Update'),
        'next',
        false,
        ['icon' => 'update'],
        true
    );

    html_print_input_hidden('id_token', $id_token);
    html_print_input_hidden('update_token', 1);
}

$actionButtons[] = html_print_go_back_button(
    ui_get_full_url($url_list),
    ['button_class' => ''],
    true
);

html_print_action_buttons(
    implode('', $actionButtons),
    ['type' => 'form_action']
);

echo '</form>';

ui_include_time_picker();
ui_require_jquery_file('ui.datepicker-'.get_user_language(), 'include/javascript/i18n/');

?>

<script type="text/javascript" language="javascript">
    $(document).ready (function () {
        $('#text-date-expiration').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
            showAnim: 'slideDown'
        });

        $('[id^=text-time-expiration]').timepicker({
            showSecond: true,
            timeFormat: '<?php echo TIME_FORMAT_JS; ?>',
            timeOnlyTitle: '<?php echo __('Choose time'); ?>',
            timeText: '<?php echo __('Time'); ?>',
            hourText: '<?php echo __('Hour'); ?>',
            minuteText: '<?php echo __('Minute'); ?>',
            secondText: '<?php echo __('Second'); ?>',
            currentText: '<?php echo __('Now'); ?>',
            closeText: '<?php echo __('Close'); ?>'
        });
    });

    function errordate() {
        confirmDialog({
            title: "<?php echo __('Error'); ?>",
            message: "<?php echo __('Expiration date must be later than today.'); ?>",
            hideCancelButton: true,
        });
    }

    $('#button-next').on('click', function() {
        event.preventDefault();
        var date = $('#text-date-expiration').val();
        var time = date+' '+$('#text-time-expiration').val();
        if (date !== '' && $('#text-time-expiration').val() !== '') {
            if (date < $('#hidden-today_date').val() || time < $('#hidden-today_date').val()+' '+$('#hidden-today_time').val()) {
                errordate();
            } else{
                $('#form_token').submit();
            }
        } else if (date !== '' && time === ' ') {
            if (date < $('#hidden-today_date').val()) {
                errordate();
            } else{
                $('#form_token').submit();
            }
        } else if (date === '' && time !== ' ') {
            errordate();
        } else if (date !== '' && $('#text-time-expiration').val() === '') {
            if (date < $('#hidden-today_date').val()) {
                errordate();
            } else{
                $('#form_token').submit();
            }
        }else {
            $('#form_token').submit();
        }
    })
</script>
