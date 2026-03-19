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
 * @package    Include
 * @subpackage HTML
 */


/**
 * Get a list of CSS themes installed.
 *
 * @param bool List all css files of an specific path without filter "pandora*" pattern
 * Note: If you want to exclude a Css file from the resulting list put "Exclude css from visual styles" in the file header
 *
 * @return array An indexed array with the file name in the index and the theme
 * name (if available) as the value.
 */
function themes_get_css($path=false)
{
    if ($path) {
        $theme_dir = $path;
    } else {
        $theme_dir = 'include/styles/';
    }

    if ($path) {
        $files = list_files($theme_dir, 'pandora', 0, 0);
    } else {
        $files = list_files($theme_dir, 'pandora', 1, 0);
    }

    $retval = [];

    foreach ($files as $file) {
        if ($file === 'pandora_green_old.css') {
            continue;
        }

        if ($file === 'pandoraitsm.css') {
            continue;
        }

        // Skip '..' and '.' entries and files not ended in '.css'.
        if ($path && ($file == '.' || $file == '..' || strtolower(substr($file, (strlen($file) - 4))) !== '.css')) {
            continue;
        }

        $data = implode('', file($theme_dir.'/'.$file));
        if (preg_match('|Exclude css from visual styles|', $data)) {
            continue;
        }

        preg_match('|Name:(.*)$|mi', $data, $name);
        if (isset($name[1])) {
            $retval[$file] = trim($name[1]);
        } else {
            $retval[$file] = $file;
        }
    }

    return $retval;
}
