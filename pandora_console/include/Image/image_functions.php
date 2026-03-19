<?php
/**
 * @category Images
 * @package  Image_Graph
 */

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
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
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

// INI Lines of code for direct url call script
if (isset($_GET['getFile'])) {
    $file = $_GET['file'];

    if (isset($_GET['thumb'])) {
        if (!isset($_GET['thumb_size'])) {
            $newWidth = $newHeight = '50%';
        } else {
            $new_size_values = explode('x', $_GET['thumb_size']);
            $newWidth = (integer) $new_size_values[0];
            $newHeight = (integer) $new_size_values[1];
        }

        $temp = explode('/', $file);
        $fileName = end($temp);

        $fileTemp = sys_get_temp_dir().'/tumb_'.$newWidth.'x'.$newHeight.'_'.$fileName;

        if (is_file($fileTemp)) {
            if (!is_readable($fileTemp)) {
                $fileTemp = sys_get_temp_dir().'/tumb_'.$newWidth.'x'.$newHeight.'_'.uniqid().'_'.$fileName;
                createthumb($_SERVER['DOCUMENT_ROOT'].$file, $fileTemp, $newWidth, $newHeight);
            }
        } else {
            createthumb($file, $fileTemp, $newWidth, $newHeight);
        }

        getFile($fileName, $fileTemp);
        unlink($fileTemp);
    }
}

// END Lines of code for direct url call script


/**
 * Get a file change http header and write as binary output for
 * redirect to file content.
 *
 * @param string destFileName Name for rename file when the browser get the file.
 * @param string fileLocation The real location the file in server.
 */
function getFile($destFileName, $fileLocation)
{
    error_reporting(0);

    // NOTICE: mime_conent_type() IS NOT SUPPORTED IN PHP >= 5.2.11 anymore
    // THIS DOESNT WORK ON SUSE 11.x !
    // header('Content-type: aplication/octet-stream;');
    // header('Content-type: ' . mime_content_type($fileLocation) . ';');
    // header( "Content-Length: " . filesize($fileLocation));
    // header('Content-Disposition: attachment; filename="' . $destFileName . '"');
    // Do not send any header, rely on browser
    readfile($fileLocation);
}


/**
 * Create a thumb of image file in filesystem of server.
 *
 * @param string origFileName Original image
 * @param string destFileName Thumb image
 * @param integer newWidth Thumb width
 * @param integer newHeight Thumb height
 */
function createthumb($origFileName, $destFileName, $newWidth, $newHeight)
{
    // TODO $newWidth and $newHeight values as percent.
    preg_match('/\.(jpg|jpeg|png)$/', $origFileName, $match);
    $extension = '';
    if (!empty($match)) {
        $extension = $match[1];
    }

    if (preg_match('/jpg|jpeg/', $extension)) {
        $src_img = imagecreatefromjpeg($origFileName);
    }

    if (preg_match('/png/', $extension)) {
        $src_img = imagecreatefrompng($origFileName);
    }

    $oldWidth = imagesx($src_img);
    $oldHeight = imagesy($src_img);

    $dst_img = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $newWidth, $newHeight, $oldWidth, $oldHeight);
    if (preg_match('/png/', $extension)) {
        imagepng($dst_img, $destFileName);
    } else {
        imagejpeg($dst_img, $destFileName);
    }

    imagedestroy($dst_img);
    imagedestroy($src_img);
}
