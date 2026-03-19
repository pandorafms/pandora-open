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
require_once $config['homedir'].'/include/functions_extensions.php';
check_login();

$user_language = get_user_language($config['id_user']);
if (file_exists('../../include/languages/'.$user_language.'.mo')) {
    $l10n = new gettext_reader(
        new CachedFileReader('../../include/languages/'.$user_language.'.mo')
    );
    $l10n->load_tables();
}

if ($config['style'] === 'pandora_black') {
    ui_require_css_file('pandora_black', 'include/styles/', true);
}

echo '<link rel="stylesheet" href="../../include/styles/pandora.css?v='.$config['current_package'].'" type="text/css"/>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <?php
        // Parsing the refresh before sending any header.
        $refresh = (int) get_parameter('refresh', -1);
        if ($refresh > 0) {
            $query = ui_get_url_refresh(false);
            echo '<meta http-equiv="refresh" content="'.$refresh.'; URL='.$query.'" />';
        }
        ?>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?php echo __('%s Realtime Module Graph', get_product_name()); ?></title>
        <link rel="stylesheet" href="../../include/styles/pandora_minimal.css?v=<?php echo $config['current_package']; ?>" type="text/css" />
        <link rel="stylesheet" href="../../include/styles/js/jquery-ui.min.css?v=<?php echo $config['current_package']; ?>" type="text/css" />
        <script type='text/javascript' src='../../include/javascript/pandora.js?v=<?php echo $config['current_package']; ?>'></script>
        <script type='text/javascript' src='../../include/javascript/pandora_ui.js?v=<?php echo $config['current_package']; ?>'></script>
        <script type='text/javascript' src='../../include/javascript/jquery.current.js?v=<?php echo $config['current_package']; ?>'></script>
        <script type='text/javascript' src='../../include/javascript/jquery.pandora.js?v=<?php echo $config['current_package']; ?>'></script>
        <script type='text/javascript' src='../../include/javascript/jquery-ui.min.js?v=<?php echo $config['current_package']; ?>'></script>
        <?php
        // Include the javascript for the js charts library.
            require_once $config['homedir'].'/include/graphs/functions_flot.php';
            include_javascript_dependencies_flot_graph();
        ?>
    </head>
    <?php
    if ($config['style'] === 'pandora_black') {
    }
    ?>
    <body bgcolor="#ffffff" class='bg_white'>
        <?php
        if (!check_acl($config['id_user'], 0, 'AR')) {
            include $config['homedir'].'/general/noaccess.php';
            exit;
        }

            $config['extensions'] = extensions_get_extensions('../../');
        if (!extensions_is_enabled_extension('realtime_graphs.php')) {
                ui_print_error_message(__('Realtime extension is not enabled.'));
            return;
        } else {
            include_once '../../extensions/realtime_graphs.php';
        }

            pandora_realtime_graphs();
        ?>

    </body>
</html>
