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

function menu()
{
    global $config;

    ?>
    <div id="top_menu">
        <div id="menu">
            <a href="index.php?page=tactical"><img class="icon_menu" alt="<?php echo __('Dashboard'); ?>" title="<?php echo __('Dashboard'); ?>" src="../images/house.png" /></a>
            <a href="index.php?page=agents"><img class="icon_menu" alt="<?php echo __('Agents'); ?>" title="<?php echo __('Agents'); ?>" src="../images/bricks.png" /></a>
            <a href="index.php?page=monitor"><img class="icon_menu" alt="<?php echo __('Monitor'); ?>" title="<?php echo __('Monitor'); ?>" src="../images/data.png" /></a>
            <a href="index.php?page=events"><img class="icon_menu" alt="<?php echo __('Events'); ?>" title="<?php echo __('Events'); ?>" src="../images/lightning_go.png" /></a>
            <a href="index.php?page=alerts"><img class="icon_menu" alt="<?php echo __('Alerts'); ?>" title="<?php echo __('Alerts'); ?>" src="../images/bell.png" /></a>
            <a href="index.php?page=groups"><img class="icon_menu" alt="<?php echo __('Groups'); ?>" title="<?php echo __('Groups'); ?>" src="../images/world.png" /></a>
            <a href="index.php?page=servers"><img class="icon_menu" alt="<?php echo __('Servers'); ?>" title="<?php echo __('Servers'); ?>" src="../images/god5.png" /></a>
            <a href="index.php?action=logout"><img class="icon_menu" alt="<?php echo __('Logout'); ?>" title="<?php echo __('Logout'); ?>" src="<?php echo 'images/header_logout.png'; ?>" /></a>
        </div>
        <div id="down_button">
            <a class="button_menu" id="button_menu_down" href="javascript: toggleMenu();"><img id="img_boton_menu" width="20" height="20" src="<?php echo 'images/down.png'; ?>" /></a>
        </div>
    </div>
    <script type="text/javascript">
        var open = 0;
    
        function toggleMenu() {
            if (document.getElementById) {
                var div = document.getElementById('menu');
                var boton_up = document.getElementById('button_menu_up');
                var boton_down = document.getElementById('button_menu_down');
                var boton_img = document.getElementById('img_boton_menu');

                if (open == 0) {
                    boton_img.src = 'images/up.png';
                    div.style.display = 'block';
//                    boton_up.style.display = 'block';
//                    boton_down.style.display = 'none';
                    open = 1;
                }
                else {
                    open = 0;
                    boton_img.src = 'images/down.png';
                    div.style.display = 'none';
//                    boton_down.style.display = 'block';
//                    boton_up.style.display = 'none';
                }
            }
        }
    </script>
    <?php
}


function footer()
{
    global $pandora_version, $build_version;

    if (isset($_SERVER['REQUEST_TIME'])) {
        $time = $_SERVER['REQUEST_TIME'];
    } else {
        $time = get_system_time();
    }
    ?>
    <div id="footer" style="background: url('../images/pandora.ico.gif') no-repeat left #000;">
        <?php
        echo sprintf(__('Pandora FMS %s - Build %s', $pandora_version, $build_version)).'<br />';
        echo __('Generated at').' '.ui_print_timestamp($time, true, ['prominent' => 'timestamp']);
        ?>
    </div>
    <?php
}

