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

// Requires.
require_once '../../include/config.php';
require_once $config['homedir'].'/vendor/autoload.php';
require_once '../../include/functions_custom_graphs.php';

use PandoraFMS\User;

$hash = (string) get_parameter('hash');

// Check input hash.
// DO NOT move it after of get parameter user id.
if (User::validatePublicHash($hash) !== true) {
    db_pandora_audit(
        AUDIT_LOG_GRAPH_ANALYTICS_PUBLIC,
        'Trying to access public graph analytics'
    );
    include 'general/noaccess.php';
    exit;
}

$text_subtitle = isset($config['rb_product_name_alt']) ? '' : ' - '.__('the Flexible Monitoring System');
echo '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../images/pandora.ico" type="image/ico">
    <title>'.get_product_name().$text_subtitle.'</title>
';

// CSS.
ui_require_css_file('common', 'include/styles/', true);
ui_require_css_file('pandora', 'include/styles/', true);
ui_require_css_file('discovery', 'include/styles/', true);
ui_require_css_file('register', 'include/styles/', true);
ui_require_css_file('order_interpreter', 'include/styles/', true);
ui_require_css_file('graph_analytics', 'include/styles/', true);
ui_require_css_file('jquery-ui.min', 'include/styles/js/', true);
ui_require_css_file('jquery-ui_custom', 'include/styles/js/', true);
ui_require_css_file('introjs', 'include/styles/js/', true);
ui_require_css_file('events', 'include/styles/', true);

// JS.
ui_require_javascript_file('jquery.current', 'include/javascript/', true);
ui_require_javascript_file('jquery.pandora', 'include/javascript/', true);
ui_require_javascript_file('jquery-ui.min', 'include/javascript/', true);
ui_require_javascript_file('jquery.countdown', 'include/javascript/', true);
ui_require_javascript_file('pandora', 'include/javascript/', true);
ui_require_javascript_file('pandora_ui', 'include/javascript/', true);
ui_require_javascript_file('pandora_events', 'include/javascript/', true);
ui_require_javascript_file('select2.min', 'include/javascript/', true);
// ui_require_javascript_file('connection_check', 'include/javascript/', true);
ui_require_javascript_file('encode_decode_base64', 'include/javascript/', true);
ui_require_javascript_file('qrcode', 'include/javascript/', true);
ui_require_javascript_file('intro', 'include/javascript/', true);
ui_require_javascript_file('clippy', 'include/javascript/', true);
ui_require_javascript_file('underscore-min', 'include/javascript/', true);

echo '
<script type="text/javascript">
    var phpTimezone = "'.date_default_timezone_get().'";
    var configHomeurl = "'.$config['homeurl'].'";
</script>
';



ui_require_javascript_file('date', 'include/javascript/timezone/src/', true);
ui_require_javascript_file('jquery.flot.min', 'include/graphs/flot/', true);
ui_require_javascript_file('jquery.flot.time', 'include/graphs/flot/', true);
ui_require_javascript_file('jquery.flot.pie', 'include/graphs/flot/', true);
ui_require_javascript_file('jquery.flot.crosshair.min', 'include/graphs/flot/', true);
ui_require_javascript_file('jquery.flot.stack.min', 'include/graphs/flot/', true);
ui_require_javascript_file('jquery.flot.selection.min', 'include/graphs/flot/', true);
ui_require_javascript_file('jquery.flot.resize.min', 'include/graphs/flot/', true);
ui_require_javascript_file('jquery.flot.threshold', 'include/graphs/flot/', true);
ui_require_javascript_file('jquery.flot.threshold.multiple', 'include/graphs/flot/', true);
ui_require_javascript_file('jquery.flot.symbol.min', 'include/graphs/flot/', true);
ui_require_javascript_file('jquery.flot.exportdata.pandora', 'include/graphs/flot/', true);
ui_require_javascript_file('jquery.flot.axislabels', 'include/graphs/flot/', true);
ui_require_javascript_file('pandora.flot', 'include/graphs/flot/', true);
ui_require_javascript_file('chart', 'include/graphs/chartjs/', true);
ui_require_javascript_file('chartjs-plugin-datalabels.min', 'include/graphs/chartjs/', true);



ui_require_javascript_file('graph_analytics', 'include/javascript/', true);


echo '
</head>
<body>
';

// Content.
$right_content = '';

$right_content .= '
    <div id="droppable-graphs">
        <div class="droppable droppable-default-zone" data-modules="[]"><span class="drop-here">'.__('Drop here').'<span></div>
    </div>
';

$graphs_div = html_print_div(
    [
        'class'   => 'padding-div graphs-div-main',
        'content' => $right_content,
    ],
    true
);

html_print_div(
    [
        'class'   => 'white_box main-div graph-analytics-public',
        'content' => $graphs_div,
    ]
);

?>

<script>
const dropHere = "<?php echo __('Drop here'); ?>";

const titleNew = "<?php echo __('New graph'); ?>";
const messageNew = "<?php echo __('If you create a new graph, the current settings will be deleted. Please save the graph if you want to keep it.'); ?>";

const titleSave = "<?php echo __('Saved successfully'); ?>";
const messageSave = "<?php echo __('The filter has been saved successfully'); ?>";

const messageSaveEmpty = "<?php echo __('Empty graph'); ?>";
const messageSaveEmptyName = "<?php echo __('Empty name'); ?>";

const titleError = "<?php echo __('Error'); ?>";

const titleUpdate = "<?php echo __('Override filter?'); ?>";
const messageUpdate = "<?php echo __('Do you want to overwrite the filter?'); ?>";

const titleUpdateConfirm = "<?php echo __('Updated successfully'); ?>";
const messageUpdateConfirm = "<?php echo __('The filter has been updated successfully'); ?>";

const titleUpdateError = "<?php echo __('Error'); ?>";
const messageUpdateError = "<?php echo __('Empty graph'); ?>";

const titleLoad = "<?php echo __('Overwrite current graph?'); ?>";
const messageLoad = "<?php echo __('If you load a filter, it will clear the current graph'); ?>";

const titleLoadConfirm = "<?php echo __('Error'); ?>";
const messageLoadConfirm = "<?php echo __('Error loading filter'); ?>";


document.addEventListener("DOMContentLoaded", (event) => {
    const hash = "<?php echo get_parameter('hash'); ?>";
    const idFilter = atob("<?php echo get_parameter('id'); ?>");
    const idUser = "<?php echo get_parameter('id_user'); ?>";

    load_filter_values(idFilter, configHomeurl);
});

</script>

</body>
</html>