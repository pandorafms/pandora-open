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

require_once '../include/config.php';

require_once '../include/functions.php';
require_once '../include/functions_html.php';
?>
<html class="help_pname"><head><title>
<?php
echo __('%s help system', get_product_name());
?>
</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
</head>
<?php echo '<link rel="stylesheet" href="../include/styles/'.$config['style'].'.css?v='.$config['current_package'].'" type="text/css">'; ?>
<body class="height_100p bg_333">
<?php
$id = get_parameter('id');
$id_user = get_parameter('id_user');

$user_language = get_user_language($id_user);

if (file_exists('../include/languages/'.$user_language.'.mo')) {
    $l10n = new gettext_reader(new CachedFileReader('../include/languages/'.$user_language.'.mo'));
    $l10n->load_tables();
}

// Possible file locations
$safe_language = safe_url_extraclean($user_language, 'en');

$safe_id = safe_url_extraclean($id, '');
$files = [
    $config['homedir'].'/include/help/'.$safe_language.'/help_'.$safe_id.'.php',
    $config['homedir'].'/include/help/en/help_'.$safe_id.'.php',
];
$help_file = '';
foreach ($files as $file) {
    if (file_exists($file)) {
        $help_file = $file;
        break;
    }
}

$logo = ui_get_custom_header_logo(true);

if (! $id || ! file_exists($help_file)) {
    echo '<div id="main_help">';

    echo html_print_image($logo, true, ['border' => '0']);

    echo '</div>';
    echo '<div id="parent_dic">';
    echo '<div  class="databox bg-white font_12px no_border">';
    echo '<hr class="mgn_tp_0">';
    echo '<h1 class="pdd_l_30px">';
    echo __('Help system error');
    echo '</h1>';
    echo "<div class='center bg-white'>";

    echo '</div>';
    echo '<div class="msg msg_pandora_help">'.__("%s help system has been called with a help reference that currently don't exist. There is no help content to show.", get_product_name()).'</div></div></div>';
    echo '<br /><br />';
    echo '<div id="footer_help">';
    // include 'footer.php';
    return;
}

// Show help
echo '<div id="main_help_new">';
echo html_print_image($logo, true, ['border' => '0']);

echo '</div>';
echo '<div id="main_help_new_content">';
ob_start();
require_once $help_file;
$help = ob_get_contents();
ob_end_clean();

// Add a line after H1 tags
echo $help;
echo '</div>';
echo '<div id="footer_help">';
// require 'footer.php';
echo '</div>';
?>
</body>
</html>
