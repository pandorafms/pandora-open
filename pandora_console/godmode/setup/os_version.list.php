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

// Datatables list.
try {
    $columns = [
        'product',
        'version',
        'end_of_support',
        'options',
    ];

    $column_names = [
        __('Product'),
        __('Version'),
        __('End of support date'),
        [
            'text'  => __('Options'),
            'class' => 'w100px table_action_buttons',
        ],
    ];

    $tableId = 'os_version_table';
    // Load datatables user interface.
    ui_print_datatable(
        [
            'id'                  => $tableId,
            'class'               => 'info_table',
            'style'               => 'width: 100%',
            'columns'             => $columns,
            'column_names'        => $column_names,
            'ajax_url'            => 'include/ajax/os',
            'ajax_data'           => ['method' => 'drawOSVersionTable'],
            'ajax_postprocess'    => 'process_datatables_item(item)',
            'no_sortable_columns' => [-1],
            'order'               => [
                'field'     => 'id',
                'direction' => 'asc',
            ],
            'search_button_class' => 'sub filter float-right',
            'form'                => [
                'inputs' => [
                    [
                        'label' => __('Free search'),
                        'type'  => 'text',
                        'class' => 'w25p',
                        'id'    => 'free_search',
                        'name'  => 'free_search',
                    ],
                ],
            ],
            'filter_main_class'   => 'box-flat white_table_graph fixed_filter_bar',
            'dom_elements'        => 'lftpB',
        ]
    );
} catch (Exception $e) {
    echo $e->getMessage();
}

echo '<div id="aux" class="invisible"></div>';

echo '<form method="post" action="index.php?sec=gagente&sec2=godmode/setup/os&tab=manage_version&action=edit">';

html_print_action_buttons(
    html_print_submit_button(__('Create OS version'), 'update_button', false, ['icon' => 'next'], true),
    ['type' => 'form_action']
);

echo '</form>';

echo '<form id="redirect-form" method="post" action="index.php?sec=view&sec2=operation/agentes/estado_agente">';
html_print_input_hidden('os_type_regex', '');
html_print_input_hidden('os_version_regex', '');

echo '</form>';

?>

<script language="javascript" type="text/javascript">
    function process_datatables_item(item) {
        id = item.id_os_version;

        idrow = '<b><a href="javascript:" onclick="show_form(\'';
        idrow += item.id_os_version;
        idrow += '\')" >'+item.id_os_version+'</a></b>';
        item.id_os_version = idrow;
        item.options = '<div class="table_action_buttons">';
        item.options += '<a href="index.php?sec=gagente&amp;sec2=godmode/setup/os&amp;tab=manage_version&amp;action=edit&amp;id_os=';
        item.options += id;
        item.options += '" ><?php echo html_print_image('images/edit.svg', true, ['title' => __('Edit'), 'class' => 'main_menu_icon invert_filter']); ?></a>';

        item.options += '<a href="javascript:" onclick="redirect_to_agents_by_version(\'';
        item.options += item.product;
        item.options += '\',\'';
        item.options += item.version;
        item.options += '\')" ><?php echo html_print_image('images/agents.svg', true, ['title' => __('Show agents'), 'class' => 'main_menu_icon invert_filter']); ?></a>';

        item.options += '<a href="javascript:" onclick="delete_os_version(\'';
        item.options += id;
        item.options += '\')" ><?php echo html_print_image('images/delete.svg', true, ['title' => __('Delete'), 'class' => 'main_menu_icon invert_filter']); ?></a>';
        item.options += '</div>';

        item.options += '<form method="post" action="?sec=view&sec2=operation/agentes/estado_agente"></form>';
    }

    function redirect_to_agents_by_version(product, version) {
        $('#hidden-os_type_regex').val(product);
        $('#hidden-os_version_regex').val(version);
        $('#redirect-form').submit();
    }

    /**
     * Delete selected OS version
     */
    function delete_os_version(id) {
        $('#aux').empty();
        $('#aux').text('<?php echo __('Are you sure?'); ?>');
        $('#aux').dialog({
            title: '<?php echo __('Delete'); ?> ' + id,
            buttons: [
                {
                    class: 'ui-widget ui-state-default ui-corner-all ui-button-text-only sub upd submit-cancel',
                    text: '<?php echo __('Cancel'); ?>',
                    click: function(e) {
                        $(this).dialog('close');
                    }
                },
                {
                    text: 'Delete',
                    class: 'ui-widget ui-state-default ui-corner-all ui-button-text-only sub ok submit-next',
                    click: function(e) {
                        $.ajax({
                            method: 'post',
                            url: '<?php echo ui_get_full_url('ajax.php', false, false, false); ?>',
                            data: {
                                page: 'include/ajax/os',
                                method: 'deleteOSVersion',
                                id_os_version: id
                            },
                            datatype: "json",
                            success: function (data) {
                                var r = JSON.parse(data);
                                if (r.deleted === false) {
                                    $('#aux').text('<?php echo __('Not deleted. Error deleting data'); ?>');
                                } else {
                                    $('#aux').dialog('close');
                                    location.reload();
                                }
                            },
                            error: function(e) {
                                $('#aux').text('<?php echo __('Not deleted. Error deleting data'); ?>');
                            }
                        });
                    }
                }
            ]
        });
    }
</script>