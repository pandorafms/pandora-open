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
global $config;
global $vconsoles_write;
global $vconsoles_manage;
check_login();
ui_require_css_file('first_task');

if ($vconsoles_write || $vconsoles_manage) {
    $msg = __(
        '%s allows users to create visual maps on which each user is able to create his or her own monitoring map. The new visual console editor is much more practical, although the prior visual console editor had its advantages.',
        get_product_name()
    );

    $msg .= '<br><br>'.__(
        "On the new visual console, we have been successful in imitating the sensation and touch of a drawing application like GIMP. We have also simplified the editor by dividing it into several subject-divided tabs named 'Data', 'Preview', 'Wizard', 'List of Elements' and 'Editor'."
    );

    $msg .= '<br><br>'.__(
        " The items the %s Visual Map was designed to handle are 'static images', 'percentage bars', 'module graphs' and 'simple values'",
        get_product_name()
    );

    $url_new = 'index.php?sec=network&amp;sec2=godmode/reporting/visual_console_builder';
    $button = '<form action="'.$url_new.'" method="post">';
    $button .= html_print_input_hidden('edit_layout', 1);
    $button .= '<input type="submit" class="button_task button_task_mini mrgn_0px_imp" value="'.__('Create visual console').'" />';
    $button .= '</form>';

    echo ui_print_empty_view(
        __('There are no customized visual consoles'),
        $msg,
        'visual-console.svg',
        $button
    );
}
