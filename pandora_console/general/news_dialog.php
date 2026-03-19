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


/**
 * @package General
 */

global $config;

$options = [];
$options['id_user'] = $config['id_user'];
$options['modal'] = true;
$news = get_news($options);

// Clean subject entities
foreach ($news as $k => $v) {
    $news[$k]['text'] = io_safe_output($v['text']);
    $news[$k]['subject'] = io_safe_output($v['subject']);
}

if (!empty($news)) {
    $options = [];
    $options['id'] = 'news_json';
    $options['hidden'] = 1;
    $options['content'] = base64_encode(json_encode($news));
    html_print_div($options);
}

// Prints news dialog template
echo '<div id="news_dialog" class="invisible">';
    echo '<div class="parent_new_dialog_tmplt">';
        echo '<span id="new_text"></span>';
        echo '<span id="new_author"></span>';
        echo '<span id="new_timestamp"></span>';
    echo '</div>';

    echo '<div id="div_btn_new_dialog">';
        echo '<div class="float-right w20p">';
        html_print_submit_button(
            'Ok',
            'hide-news-help',
            false,
            [
                'mode' => 'ui-widget ok mini',
                'icon' => 'wand',
            ]
        );
        echo '</div>';
        echo '</div>';

        echo '</div>';

        ui_require_javascript_file('encode_decode_base64');
        ?>

<script type="text/javascript" language="javascript">
/* <![CDATA[ */

$(document).ready (function () {
    if (typeof($('#news_json').html()) != "undefined") {
        
        var news_raw = Base64.decode($('#news_json').html());
        var news = JSON.parse(news_raw);
        var inew = 0;
        
        function show_new () {
            if (news[inew] != undefined) {
                $('#new_text').html(news[inew].text);
                $('#new_timestamp').html(news[inew].timestamp);
                $('#new_author').html(news[inew].author);
                
                $("#news_dialog").dialog({
                    resizable: true,
                    draggable: true,
                    modal: true,
                    closeOnEscape: false,
                    height: 450,
                    width: 630,
                    title: news[inew].subject,
                    overlay: {
                        opacity: 0.5,
                        background: "black"
                    }
                });
                    
                $('.ui-dialog-titlebar-close').hide();
            }
        }
        
        $("#button-hide-news-help").click (function () {
            $("#news_dialog" ).dialog('close');
            inew++;
            show_new();
        });
        
        show_new();
    }
});

/* ]]> */
</script>
