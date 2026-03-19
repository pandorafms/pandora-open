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

// Load global vars.
global $config;

check_login();

if (! check_acl($config['id_user'], 0, 'PM') && ! is_user_admin($config['id_user'])) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access Setup Management'
    );
    include 'general/noaccess.php';
    return;
}

ui_require_css_file('datepicker');
ui_require_jquery_file('ui.datepicker-'.get_user_language(), 'include/javascript/i18n/');
ui_include_time_picker();
ui_require_javascript_file('pandora');

if ($idOS > 0) {
    $os_version = db_get_row_filter('tconfig_os_version', ['id_os_version' => $idOS]);
    $product = $os_version['product'];
    $version = $os_version['version'];
    $end_of_life_date = $os_version['end_of_support'];
} else {
    $product = io_safe_input(strip_tags(io_safe_output((string) get_parameter('product'))));
    $version = io_safe_input(strip_tags(io_safe_output((string) get_parameter('version'))));
    $end_of_life_date = get_parameter('end_of_life_date', date('Y/m/d'));
}

$message = '';

    switch ($action) {
        case 'edit':
            if ($idOS > 0) {
                $actionHidden = 'update';
                $textButton = __('Update');
                $classButton = ['icon' => 'wand'];
            } else {
                $actionHidden = 'save';
                $textButton = __('Create');
                $classButton = ['icon' => 'next'];
            }
        break;

        case 'save':
            $values = [];
            // Product and version must be stored with no entities to be able to use REGEXP in queries.
            // CAREFUL! output of these fields must be encoded to avoid scripting vulnerabilities.
            $values['product'] = io_safe_output($product);
            $values['version'] = io_safe_output($version);
            $values['end_of_support'] = $end_of_life_date;

            $result = db_process_sql_insert('tconfig_os_version', $values);

            if ($result === false) {
                $message = 2;
            } else {
                $message = 1;
            }

            $tab = 'manage_version';

            header('Location:'.$config['homeurl'].'index.php?sec=gsetup&sec2=godmode/setup/os&tab='.$tab.'&message='.$message);
        break;

        case 'update':
            $product = io_safe_output(get_parameter('product'));
            $version = io_safe_output(get_parameter('version'));
            $end_of_life_date = get_parameter('end_of_life_date', 0);
            $values = [];
            $values['product'] = $product;
            $values['version'] = $version;
            $values['end_of_support'] = $end_of_life_date;
            $result = db_process_sql_update('tconfig_os_version', $values, ['id_os_version' => $idOS]);

            if ($result === false) {
                $message = 4;
            } else {
                $message = 3;
            }

            $tab = 'manage_version';

            header('Location:'.$config['homeurl'].'index.php?sec=gsetup&sec2=godmode/setup/os&tab='.$tab.'&message='.$message);
        break;

        case 'delete':
            $sql = 'SELECT COUNT(id_os) AS count FROM tagente WHERE id_os = '.$idOS;
            $count = db_get_all_rows_sql($sql);
            $count = $count[0]['count'];

            if ($count > 0) {
                $message = 5;
            } else {
                $result = (bool) db_process_sql_delete('tconfig_os', ['id_os' => $idOS]);
                if ($result) {
                    $message = 6;
                } else {
                    $message = 7;
                }
            }

                header('Location:'.$config['homeurl'].'index.php?sec=gsetup&sec2=godmode/setup/os&tab='.$tab.'&message='.$message);
            
        break;

        default:
        case 'new':
            $actionHidden = 'save';
            $textButton = __('Create');
            $classButton = ['icon' => 'next'];
        break;
    }

echo '<form id="form_setup" method="post">';
$table = new stdClass();
$table->width = '100%';
$table->class = 'databox filter-table-adv';

// $table->style[0] = 'width: 15%';
$table->data[0][] = html_print_label_input_block(
    __('Product'),
    html_print_input_text('product', io_safe_input($product), __('Product'), 20, 300, true, false, false, '', 'w250px')
);

$table->data[0][] = html_print_label_input_block(
    __('Version'),
    html_print_input_text('version', io_safe_input($version), __('Version'), 20, 300, true, false, false, '', 'w250px')
);

$timeInputs = [];

$timeInputs[] = html_print_div(
    [
        'id'      => 'end_of_life_date',
        'style'   => '',
        'content' => html_print_div(
            [
                'class'   => '',
                'content' => html_print_input_text(
                    'end_of_life_date',
                    $end_of_life_date,
                    '',
                    10,
                    10,
                    true
                ),
            ],
            true
        ),
    ],
    true
);

$table->data[1][] = html_print_label_input_block(
    __('End of life date'),
    implode('', $timeInputs)
);

html_print_table($table);

html_print_input_hidden('id_os', $idOS);
html_print_input_hidden('action', $actionHidden);

html_print_action_buttons(
    html_print_submit_button($textButton, 'update_button', false, $classButton, true),
    ['type' => 'form_action']
);

echo '</form>';

?>
<script language="javascript" type="text/javascript">
$(document).ready (function () {
    $("#text-end_of_life_date").datepicker({dateFormat: "<?php echo DATE_FORMAT_JS; ?>", showButtonPanel: true});

});
</script>