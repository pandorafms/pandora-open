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

// Header.
ui_print_standard_header(
    __('%s registered consoles', $config['rb_product_name']),
    '',
    false,
    '',
    true,
    [],
    [
        [
            'link'  => '',
            'label' => __('Servers'),
        ],
    ]
);


if (empty($message) === false) {
    echo $message;
}

// Auxiliar to display deletion modal.
echo '<div id="delete_modal" class="invisible"></div>';
echo '<div id="msg" class="invisible"></div>';


// Consoles list.
try {
    $columns = [
        'id_console',
        'description',
        'version',
        'last_execution',
        'console_type',
        'timezone',
        'public_url',
        'options',
    ];

    $column_names = [
        __('Console ID'),
        __('Description'),
        __('Version'),
        __('Last Execution'),
        __('Console type'),
        __('Timezone'),
        __('Public URL'),
        [
            'text'  => __('Options'),
            'class' => 'action_buttons',
        ],
    ];


    $tableId = 'consoles_list';
    // Load datatables user interface.
    ui_print_datatable(
        [
            'id'                  => $tableId,
            'class'               => 'info_table',
            'style'               => 'width: 100%',
            'columns'             => $columns,
            'column_names'        => $column_names,
            'ajax_url'            => 'include/ajax/consoles.ajax',
            'ajax_data'           => ['get_all_datatables_formatted' => 1],
            'ajax_postprocess'    => 'process_datatables_item(item)',
            'no_sortable_columns' => [-1],
            'order'               => [
                'field'     => 'id',
                'direction' => 'asc',
            ],
        ]
    );
} catch (Exception $e) {
    echo $e->getMessage();
}

?>
<script type="text/javascript">
    /**
    * Process datatable item before draw it.
    */
    function process_datatables_item(item) {
        item.options = '<a href="javascript:" onclick="delete_key(\'';
        item.options += item.id;
        item.options += '\')" ><?php echo html_print_image('images/cross.png', true, ['title' => __('Delete'), 'class' => 'invert_filter']); ?></a>';
    }

    /**
     * Delete selected key
     */
    function delete_key(id) {
        $('#delete_modal').empty();
        $('#delete_modal').html('<?php echo __('<span>Are you sure?</span><br><br><i>WARNING: you also need to delete config.php options in your console or delete the whole console.</i>'); ?>');
        $('#delete_modal').dialog({
            title: '<?php echo __('Delete'); ?>',
            buttons: [
                {
                    class: 'ui-widget ui-state-default ui-corner-all ui-button-text-only sub upd submit-cancel',
                    text: '<?php echo __('Cancel'); ?>',
                    click: function(e) {
                        $(this).dialog('close');
                        cleanupDOM();

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
                                page: 'include/ajax/consoles.ajax',
                                delete: 1,
                                id
                            },
                            datatype: "json",
                            success: function (data) {
                                showMsg(data);
                            },
                            error: function(e) {
                                showMsg(e);
                            }
                        });
                    }
                }
            ]
        });
    }

    /**
    * Process ajax responses and shows a dialog with results.
    */
    function showMsg(data) {
        var title = "<?php echo __('Success'); ?>";
        var dt_satellite_agents = $("#<?php echo $tableId; ?>").DataTable();
        dt_<?php echo $tableId; ?>.draw(false);

        var text = '';
        var failed = 0;
        try {
            data = JSON.parse(data);
            text = data['result'];
        } catch (err) {
            title =  "<?php echo __('Failed'); ?>";
            text = err.message;
            failed = 1;
        }
        if (!failed && data['error'] != undefined) {
            title =  "<?php echo __('Failed'); ?>";
            text = data['error'];
            failed = 1;
        }
        if (data['report'] != undefined) {
            data['report'].forEach(function (item){
                text += '<br>'+item;
            });
        }

        $('#msg').empty();
        $('#msg').html(text);
        $('#msg').dialog({
            width: 450,
            position: {
                my: 'center',
                at: 'center',
                of: window,
                collision: 'fit'
            },
            title: title,
            buttons: [
                {
                    class: "ui-widget ui-state-default ui-corner-all ui-button-text-only sub ok submit-next",
                    text: 'OK',
                    click: function(e) {
                        if (!failed) {
                            $(".ui-dialog-content").dialog("close");
                            $('.info').hide();
                        } else {
                            $(this).dialog('close');
                        }
                    }
                }
            ]
        });
    }

</script>