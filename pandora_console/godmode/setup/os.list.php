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
        'id_os',
        'icon_img',
        'name',
        'description',
        'options',
    ];

    $column_names = [
        [
            'text'  => __('ID'),
            'class' => 'w50px table_action_buttons',
        ],
        [
            'text'  => __('Icon'),
            'class' => 'w10px table_action_buttons',
        ],
        __('Name'),
        __('Description'),
        [
            'text'  => __('Options'),
            'class' => 'w20px table_action_buttons',
        ],
    ];

    $tableId = 'os_table';
    // Load datatables user interface.
    ui_print_datatable(
        [
            'id'                  => $tableId,
            'class'               => 'info_table',
            'style'               => 'width: 100%',
            'columns'             => $columns,
            'column_names'        => $column_names,
            'ajax_url'            => 'include/ajax/os',
            'ajax_data'           => ['method' => 'drawOSTable'],
            'pagination_options'  => [
                [
                    $config['block_size'],
                    10,
                    25,
                    100,
                    200,
                    500,
                ],
                [
                    $config['block_size'],
                    10,
                    25,
                    100,
                    200,
                    500,
                ],
            ],
            'ajax_postprocess'    => 'process_datatables_item(item)',
            'no_sortable_columns' => [
                -1,
                1,
            ],
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

$buttons = '';

    $buttons .= '<form method="post" action="index.php?sec=gagente&sec2=godmode/setup/os&tab=manage_os&action=edit">';
    $buttons .= html_print_submit_button(__('Create OS'), 'update_button', false, ['icon' => 'next'], true);
    $buttons .= '</form>';


html_print_action_buttons(
    $buttons,
    [
        'type'  => 'data_table',
        'class' => 'fixed_action_buttons',
    ]
);

echo '<div id="aux" class="invisible"></div>';

?>
<script language="javascript" type="text/javascript">
    function process_datatables_item(item) {
        item.options = '<div class="table_action_buttons">';
        if (item.enable_delete === true) {
            var delete_id = item.id_os;
            item.options += '<a href="javascript:" onclick="delete_os(\'';
            item.options += delete_id;
            item.options += '\')" ><?php echo html_print_image('images/delete.svg', true, ['title' => __('Delete'), 'class' => 'main_menu_icon invert_filter']); ?></a>';
        }
        item.options += '</div>';
    }

    /**
     * Delete selected OS
     */
    function delete_os(id) {
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
                                method: 'deleteOS',
                                id_os: id
                            },
                            datatype: "json",
                            success: function (data) {
                                var r = JSON.parse(data);
                                if (r.deleted === false) {
                                    $('#aux').text('<?php echo __('Not deleted. Error deleting data'); ?>');
                                } else {
                                    $('#aux').dialog('close');
                                    let url = new URL(window.location.href);
                                    url.searchParams.set('message', r.url_message)
                                    window.location.href = url.href;
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
