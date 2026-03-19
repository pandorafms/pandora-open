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

$url_help = 'https://pandoraopen.io/';

// Prints help dialog information
echo '<div id="login_help_dialog" title="PHP UPDATE REQUIRED" class="invisible">';
    echo '<div class="login_help_dialog">';
        echo "<p><b class='font_10'>".__('For a correct operation of PandoraFMS, PHP must be updated to version 8.0 or higher.').'</b></p>';
        echo "<p class='font_10'><b>".__('Otherwise, functionalities will be lost.').'</b></p>';
        echo '<ul>';
            echo "<li class='pdd_5px'>".__('Report download in PDF format').'</li>';
            echo "<li class='pdd_5px'>".__('Emails Sending').'</li>';
            echo "<li class='pdd_5px'>".'...'.'</li>';
        echo '</ul>';
        echo '<p><a target="blank" href="'.$url_help.'"><b>'.__('Access Help').'</b></a></p>';
    echo '</div>';
    echo "<div class='mrg_top_80'>";
        echo html_print_image('images/icono_warning_mr.png', true, ['alt' => __('Warning php version'), 'border' => 0]);
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
        height: 320,
        width: 550,
        overlay: {
            opacity: 0.5,
            background: "black"
        }
    });
});

/* ]]> */
</script>
