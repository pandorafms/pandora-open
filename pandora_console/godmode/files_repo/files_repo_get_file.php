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

$file_hash = (string) get_parameter('file');

// Only allow 1 parameter in the request.
$check_request = (count($_REQUEST) === 1) ? true : false;
$check_get = (count($_GET) === 1) ? true : false;
$check_post = (count($_POST) === 0) ? true : false;

// Only allow the parameter 'file'.
$check_parameter = (empty($file_hash) === false) ? true : false;
$check_string = (preg_match('/^[0-9a-zA-Z]{8}$/', $file_hash) === 1) ? true : false;

$checks = ($check_request && $check_get && $check_post && $check_parameter && $check_string);
if (!$checks) {
    throw_error(15);
}

// Get the db file row.
$file = db_get_row_filter('tfiles_repo', ['hash' => $file_hash]);
if (!$file) {
    throw_error(10);
}

// Case sensitive check.
$check_hash = ($file['hash'] == $file_hash) ? true : false;
if (!$check_hash) {
    throw_error(10);
}

// Get the location.
$files_repo_path = io_safe_output($config['attachment_store']).'/files_repo';
$location = $files_repo_path.'/'.$file['id'].'_'.$file['name'];
if (!file_exists($location) || !is_readable($location) || !is_file($location)) {
    throw_error(5);
}

// All checks are fine. Download the file!
header('Content-type: aplication/octet-stream;');
header('Content-Length: '.filesize($location));
header('Content-Disposition: attachment; filename="'.$file['name'].'"');
readfile($location);


/**
 * Show errors
 *
 * @param integer $time Sleep.
 *
 * @return void
 */
function throw_error($time=15)
{
    sleep($time);

    $styleError = 'background:url("../images/err.png") no-repeat scroll 0 0 transparent; padding:4px 1px 6px 30px; color:#CC0000;';
    echo "<h3 style='".$styleError."'>".__('Unreliable petition').'. '.__('Please contact the administrator').'</h3>';
    exit;
}
