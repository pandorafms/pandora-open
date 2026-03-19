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

check_login();

if (!isset($id_agente)) {
    include 'general/noaccess.php';
    exit;
}

require_once 'include/functions_events.php';
ui_require_css_file('events');

$event_count = db_get_row('tevento', 'id_agente', $id_agente);
html_print_div(
    [
        'class'   => 'agent_details_line',
        'content' => ui_toggle(
            '<div class=\'w100p\' id=\'event_list\'>'.html_print_image('images/spinner.gif', true).'</div>',
            '<span class="subsection_header_title">'.__('Latest events for this agent').'</span>',
            __('Latest events for this agent'),
            'latest_events_agent',
            ($event_count) ? false : true,
            true,
            '',
            'box-flat white-box-content no_border',
            'box-flat white_table_graph w100p'
        ),
    ],
);

?>
<script type="text/javascript">
    $(document).ready(function() {
        events_table(0);
    });

    function events_table(all_events_24h){
        var parameters = {};
        parameters["table_events"] = 1;
        parameters["id_agente"] = <?php echo $id_agente; ?>;
        parameters["page"] = "include/ajax/events";
        parameters["all_events_24h"] = all_events_24h;
        
        jQuery.ajax ({
            data: parameters,
            type: 'POST',
            url: "ajax.php",
            dataType: 'html',
            success: function (data) {
                $("#event_list").empty();
                $("#event_list").html(data);
                $('#checkbox-all_events_24h').on('change',function(){
                    if( $('#checkbox-all_events_24h').is(":checked") ){
                        $('#checkbox-all_events_24h').val(1);
                    }
                    else{
                        $('#checkbox-all_events_24h').val(0);
                    }
                    all_events_24h = $('#checkbox-all_events_24h').val();
                    events_table(all_events_24h);
                });
            }
        });
    }
</script>
