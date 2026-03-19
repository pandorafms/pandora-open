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



if (is_ajax()) {
    $skip_login_help = get_parameter('skip_login_help', 0);

    // Updates config['skip_login_help_dialog'] in order to don't show login help message
    if ($skip_login_help) {
        if (isset($config['skip_login_help_dialog'])) {
            $result_config = db_process_sql_update('tconfig', ['value' => 1], ['token' => 'skip_login_help_dialog']);
        } else {
            $result_config = db_process_sql_insert('tconfig', ['value' => 1, 'token' => 'skip_login_help_dialog']);
        }
    }

    return;
}

// Prints help dialog information
echo '<div id="login_help_dialog" title="'.__('Welcome to %s', get_product_name()).'"  >';

    echo '<div id="help_dialog">';
    echo __(
        "If this is your first time using %s, we suggest a few links that'll help you learn more about the software. Monitoring can be overwhelming, but take your time to learn how to harness the power of %s!",
        get_product_name(),
        get_product_name()
    );
    echo '</div>';

    echo '<div>';
        echo '<table cellspacing=0 cellpadding=0 class="border_solid_white w100p h100p">';
        echo '<tr>';
            echo '<td class="border_solid_white center">';
                echo '<a href="'.ui_get_full_url(false).'general/pandora_help.php?id=main_help" target="_blank" class="no_decoration">'.html_print_image(
                    'images/online_help.png',
                    true,
                    [
                        'alt'    => __('Online help'),
                        'border' => 0,
                    ]
                ).'</a>';
                echo '<br id="br_mb_40" />';
                echo '<a class="font_9pt" href="'.ui_get_full_url(false).'general/pandora_help.php?id=main_help" target="_blank">'.__('Online help').'</a>';
                echo '</td>';

                echo '<td class="border_solid_white center">';
                echo '<a href="https://pandoraopen.io/" target="_blank" class="no_decoration">'.html_print_image(
                    'images/support.png',
                    true,
                    [
                        'alt'    => __('Support'),
                        'border' => 0,
                    ]
                ).'</a>';
                echo '<br id="br_mb_40" />';
                echo '<a class="font_9pt" href="https://pandoraopen.io/" target="_blank">'.__('Support').' / '.__('Forums').'</a>';
                echo '</td>';

                echo '<td class="border_solid_white center">';
                echo '<a href="'.ui_get_full_external_url($config['custom_docs_url']).'" target="_blank" class="no_decoration">'.html_print_image(
                    'images/documentation.png',
                    true,
                    [
                        'alt'    => __('Documentation'),
                        'border' => 0,
                    ]
                ).'</a>';
                echo '<br id="br_mb_40" />';
                echo '<a clas="font_9pt" href="'.ui_get_full_external_url($config['custom_docs_url']).'" target="_blank">'.__('Documentation').'</span></a>';
                echo '</td>';
                echo '</tr>';
                echo '</table>';
                echo '</div>';

                echo '<div class="absolute help_dialog_login" ">';
                echo '<div class="skip_help_login">';
                html_print_checkbox('skip_login_help', 1, false, false, false, 'cursor: \'pointer\'');
                echo '&nbsp;<span class="font_12pt">'.__("Click here to don't show again this message").'</span>';
                echo '</div>';
                echo '<div class="float-right w20p">';
                html_print_submit_button('Ok', 'hide-login-help', false, 'class="ui-button-dialog ui-widget ui-state-default ui-corner-all ui-button-text-only sub ok w100p"');
                echo '</div>';
                echo '</div>';

                echo '</div>';
                ?>

<script type="text/javascript" language="javascript">
/* <![CDATA[ */

$(document).ready (function () {
    
    $("#login_help_dialog").dialog({
        resizable: true,
        draggable: true,
        modal: true,
        height: 350,
        width: 630,
        overlay: {
                opacity: 0.5,
                background: "black"
            }
    });
    
    
    $("#submit-hide-login-help").click (function () {
        
        $("#login_help_dialog" ).dialog('close');
        
        var skip_login_help = $("#checkbox-skip_login_help").is(':checked');
        
        // Update config['skip_login_help_dialog'] to don't display more this message
        if (skip_login_help) {
            jQuery.post ("ajax.php",
            {"page": "general/login_help_dialog",
             "skip_login_help": 1},
            function (data) {}
            );
        }
        
    });
});

/* ]]> */
</script>
