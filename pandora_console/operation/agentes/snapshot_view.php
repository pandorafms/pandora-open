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

require_once '../../include/config.php';
require_once $config['homedir'].'/include/auth/mysql.php';
require_once $config['homedir'].'/include/functions.php';
require_once $config['homedir'].'/include/functions_db.php';
require_once $config['homedir'].'/include/functions_reporting.php';
require_once $config['homedir'].'/include/functions_graph.php';
require_once $config['homedir'].'/include/functions_modules.php';
require_once $config['homedir'].'/include/functions_agents.php';
require_once $config['homedir'].'/include/functions_tags.php';
check_login();

$user_language = get_user_language($config['id_user']);
if (file_exists('../../include/languages/'.$user_language.'.mo')) {
    $l10n = new gettext_reader(new CachedFileReader('../../include/languages/'.$user_language.'.mo'));
    $l10n->load_tables();
}

$id = get_parameter('id');
$id_node = get_parameter('id_node', 0);

$row_module = modules_get_agentmodule($id);

// Retrieve data
$utimestamp = get_parameter('timestamp', '');
if ($utimestamp == '') {
    // Retrieve last data
    $row_state = db_get_row('tagente_estado', 'id_agente_modulo', $id);
    $last_timestamp = date('Y-m-d H:i:s', $row_state['utimestamp']);
} else {
    // Retrieve target data
    $state = db_get_row('tagente_estado', 'id_agente_modulo', $id, ['id_agente']);
    $row_state = db_get_row_filter('tagente_datos_string', ['id_agente_modulo' => $id, 'utimestamp' => $utimestamp], false, 'AND', 1);
    $row_state['id_agente'] = $state['id_agente'];
    $last_timestamp = date('Y-m-d H:i:s', $row_state['utimestamp']);
}

// Build the info
$label = get_parameter('label', io_safe_output($row_module['module_name']));
$last_data = io_safe_output($row_state['datos']);
$refresh = (int) get_parameter('refr', $row_state['current_interval']);

// ACL check
$all_groups = agents_get_all_groups_agent($row_state['id_agente']);
if (!check_acl_one_of_groups($config['id_user'], $all_groups, 'AR')) {
    include $config['homedir'].'/general/noaccess.php';
    exit;
}
?>
<html>
    <head>
        <?php
        // Parsing the refresh before sending any header
        if ($refresh > 0) {
            $query = ui_get_url_refresh(false);
            echo '<meta http-equiv="refresh" content="'.$refresh.'; URL='.$query.'" />';
            if ($config['style'] === 'pandora_black') {
                echo '<link rel="stylesheet" href="../../include/styles/pandora_black.css?v='.$config['current_package'].'" type="text/css"/>';
            } else {
                echo '<link rel="stylesheet" href="../../include/styles/pandora.css?v='.$config['current_package'].'" type="text/css"/>';
            }
        }
        ?>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?php echo __('%s Snapshot data view for module (%s)', get_product_name(), $label); ?></title>
        <script type='text/javascript' src='../../include/javascript/jquery.current.js?v=<?php echo $config['current_package']; ?>'></script>
    </head>
    <body class=''>
        <?php
        echo "<h2 class='center' id='title_snapshot_view'>";
            echo __('Current data at %s', $last_timestamp);
        echo '</h2>';
        if (is_image_data($last_data)) {
            echo '<center><img src="'.$last_data.'" alt="image" class="w100p" /></center>';
        } else {
            $last_data = preg_replace('/</', '&lt;', $last_data);
            $last_data = preg_replace('/>/', '&gt;', $last_data);
            $last_data = preg_replace('/\n/i', '<br>', $last_data);
            $last_data = preg_replace('/\s/i', '&nbsp;', $last_data);
            echo "<div id='result_div' class='result_div mono'>";
            echo $last_data;
            echo '</div>';
            ?>
        <script type="text/javascript">
            function getScrollbarWidth() {
                var div = $('<div></div>');
                $('body').append(div);
                var w1 = $('div', div).innerWidth();
                div.css('overflow-y', 'auto');
                var w2 = $('div', div).innerWidth();
                $(div).remove();

                return (w1 - w2);
            }

            $(document).ready(function() {
                width = $("#result_div").css("width");
                width = width.replace("px", "");
                width = parseInt(width);
                $("#result_div").css("width", (width - getScrollbarWidth()) + "px");

                height = $("#result_div").css("height");
                height = height.replace("px", "");
                height = parseInt(height);
                $("#result_div").css("height", (height - getScrollbarWidth() - $("#title_snapshot_view").height() - 16) + "px");
            });
        </script>
            <?php
        }
        ?>
    </body>
</html>
