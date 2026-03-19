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

// Begin.
require_once 'config.php';
require_once 'functions.php';
require_once 'functions_ui.php';
require_once 'functions_filemanager.php';

global $config;

check_login();

$auth_method = db_get_value('value', 'tconfig', 'token', 'auth');

if ($auth_method !== 'ad' && $auth_method !== 'ldap') {
    include_once 'auth/'.$auth_method.'.php';
}

$hash = get_parameter('hash');
$file_raw = get_parameter('file');

$file = base64_decode(urldecode($file_raw));
$secure_extension = true;
$extension = pathinfo($file, PATHINFO_EXTENSION);
if ($extension === 'php' || $extension === 'js') {
    $secure_extension = false;
}

$parse_all_queries = explode('&', parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY));
$parse_sec2_query = explode('=', $parse_all_queries[1]);
$dirname = dirname($file);

$path_traversal = strpos($file, '../');

// Avoid possible inifite loop with referer.
if (isset($_SERVER['HTTP_ORIGIN']) === false || (isset($_SERVER['HTTP_ORIGIN']) === true && $_SERVER['HTTP_REFERER'] === $_SERVER['HTTP_ORIGIN'].$_SERVER['REQUEST_URI'])) {
    $refererPath = ui_get_full_url('index.php');
} else {
    $refererPath = $_SERVER['HTTP_REFERER'];
}

if (empty($file) === true || empty($hash) === true || $hash !== md5($file_raw.$config['server_unique_identifier'])
    || isset($_SERVER['HTTP_REFERER']) === false || $path_traversal !== false || $secure_extension === false
) {
    $errorMessage = __('Security error. Please contact the administrator.');
} else {
    $downloadable_file = '';

    $main_file_manager = 'godmode/setup/file_manager';
    $main_collections = '';
    if ($parse_sec2_query[0] === 'sec2') {
        switch ($parse_sec2_query[1]) {
            case $main_file_manager:
            case 'operation/snmpconsole/snmp_mib_uploader':
                $downloadable_file = $_SERVER['DOCUMENT_ROOT'].'/pandora_console/'.$file;
            break;

            case 'godmode/files_repo/files_repo':
                $attachment_path = io_safe_output($config['attachment_store']);
                $downloadable_file = $attachment_path.'/files_repo/'.$file;
            break;

            case 'godmode/servers/plugin':
                $downloadable_file = $_SERVER['DOCUMENT_ROOT'].'/pandora_console/attachment/plugin/'.$file;
            break;

            case $main_collections:
                $downloadable_file = io_safe_output($config['attachment_store']).'/collection/'.$file;
            break;

            case 'godmode/setup/file_manager':
                $downloadable_file = ($dirname === 'image') ? $_SERVER['DOCUMENT_ROOT'].'/pandora_console/'.$file : '';

            default:
                // Wrong action.
                $downloadable_file = '';
            break;
        }
    }

    if (empty($downloadable_file) === true || file_exists($downloadable_file) === false) {
        $errorMessage = __('File is missing in disk storage. Please contact the administrator.');
    } else {
        // Everything went well.
        header('Content-type: aplication/octet-stream;');
        header('Content-type: '.mime_content_type($downloadable_file).';');
        header('Content-Length: '.filesize($downloadable_file));
        header('Content-Disposition: attachment; filename="'.basename($downloadable_file).'"');
        readfile($downloadable_file);
        return;
    }
}

?>

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function () {
        var refererPath = '<?php echo $refererPath; ?>';
        var errorFileOutput = '<?php echo $errorMessage; ?>';
        if(refererPath != ''){
        document.body.innerHTML = `<form action="` + refererPath + `" name="failedReturn" method="post" style="display:none;">
                    <input type="hidden" name="errorFileOutput" value="` + errorFileOutput + `" />
                    </form>`;

        document.forms['failedReturn'].submit();
        }
    }, false);
</script>
