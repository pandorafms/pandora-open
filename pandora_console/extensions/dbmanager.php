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



function dbmanager_query($sql, &$error, $dbconnection)
{
    global $config;

    $retval = [];

    if ($sql == '') {
        return false;
    }

    $sql = html_entity_decode($sql, ENT_QUOTES);

    // Extract the text in quotes to add html entities before query db.
    $patttern = '/(?:"|\')+([^"\']*)(?:"|\')+/m';
    $sql = preg_replace_callback(
        $patttern,
        function ($matches) {
            return '"'.io_safe_input($matches[1]).'"';
        },
        $sql
    );

    if ($config['mysqli']) {
        $result = mysqli_query($dbconnection, $sql);
        if ($result === false) {
            $backtrace = debug_backtrace();
            $error = mysqli_error($dbconnection);
            return false;
        }
    }

    if ($result === true) {
        if ($config['mysqli']) {
            return mysqli_affected_rows($dbconnection);
        }
    }

    if ($config['mysqli']) {
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            array_push($retval, $row);
        }
    }

    if ($config['mysqli']) {
        mysqli_free_result($result);
    }

    if (! empty($retval)) {
        return $retval;
    }

    // Return false, check with === or !== .
    return 'Empty';
}


function dbmgr_extension_main()
{
    ui_require_css_file('dbmanager', 'extensions/dbmanager/');

    global $config;

    if (!is_user_admin($config['id_user'])) {
        db_pandora_audit(
            AUDIT_LOG_ACL_VIOLATION,
            'Trying to access Setup Management'
        );
        include 'general/noaccess.php';
        return;
    }

    $sql = (string) get_parameter('sql');
    $node_id = (int) get_parameter('node_id', -1);

    // Header.
    ui_print_standard_header(
        __('DB interface'),
        'images/gm_db.png',
        false,
        '',
        true,
        [],
        [
            [
                'link'  => '',
                'label' => __('Extensions'),
            ],
        ]
    );

        $img = 'images/warning_modern.png';
    

    $msg = '<div id="err_msg_centralised">'.html_print_image(
        $img,
        true
    );
    $msg .= '<div>'.__(
        'Warning, you are accessing the database directly. You can leave the system inoperative if you run an inappropriate SQL statement'
    ).'</div></div>';

    $warning_message = '<script type="text/javascript">
        $(document).ready(function () {
            infoMessage({
                title: \''.__('Warning').'\',
                text: \''.$msg.'\'    ,
                simple: true,
            })
        })
    </script>';

    if (empty($sql) === true) {
        echo $warning_message;
    }

    ui_print_warning_message(
        __(
            "This is an advanced extension to interface with %s database directly from WEB console
            using native SQL sentences. Please note that <b>you can damage</b> your %s installation
            if you don't know </b>exactly</b> what you are doing,
            this means that you can severily damage your setup using this extension.
            This extension is intended to be used <b>only by experienced users</b>
            with a depth knowledge of %s internals.",
            get_product_name(),
            get_product_name(),
            get_product_name()
        )
    );

    echo "<form method='post' action=''>";

    $table = new stdClass();
    $table->id = 'db_interface';
    $table->class = 'databox no_border filter-table-adv';
    $table->width = '100%';
    $table->data = [];
    $table->colspan = [];
    $table->style[0] = 'width: 30%;';
    $table->style[1] = 'width: 70%;';

    $table->colspan[1][0] = 2;

    $data[0][0] = "<b>Some samples of usage:</b> <blockquote><em>SHOW STATUS;<br />DESCRIBE tagente<br />SELECT * FROM tserver<br />UPDATE tagente SET id_grupo = 15 WHERE nombre LIKE '%194.179%'</em></blockquote>";
    $data[0][0] = html_print_label_input_block(
        __('Some samples of usage:'),
        "<blockquote><em>SHOW STATUS;<br />DESCRIBE tagente<br />SELECT * FROM tserver<br />UPDATE tagente SET id_grupo = 15 WHERE nombre LIKE '%194.179%'</em></blockquote>"
    );

    $data[1][0] = html_print_textarea(
        'sql',
        3,
        50,
        html_entity_decode($sql, ENT_QUOTES),
        'placeholder="'.__('Type your query here...').'"',
        true,
        'w100p'
    );

    $execute_button = html_print_submit_button(
        __('Execute SQL'),
        '',
        false,
        [ 'icon' => 'cog' ],
        true
    );

    $table->data = $data;
    // html_print_table($table);
    html_print_action_buttons($execute_button);
    ui_toggle(
        html_print_table($table, true),
        '<span class="subsection_header_title">'.__('SQL query').'</span>',
        __('SQL query'),
        'query',
        false,
        false,
        '',
        'white-box-content no_border',
        'box-flat white_table_graph fixed_filter_bar'
    );
    echo '</form>';

    // Processing SQL Code.
    if ($sql == '') {
        return;
    }

    try {
        $dbconnection = $config['dbconnection'];
        $error = '';
        $result = dbmanager_query($sql, $error, $dbconnection);
    } catch (\Exception $e) {
        $error = __('Error querying database node');
        $result = false;
    }

    if ($result === false) {
        echo '<strong>An error has occured when querying the database.</strong><br />';
        echo $error;

        db_pandora_audit(
            AUDIT_LOG_SYSTEM,
            'DB Interface Extension. Error in SQL',
            false,
            false,
            $sql
        );

        return;
    }

    if (is_array($result) === false) {
        echo '<strong>Output: <strong>'.$result;
        return;
    }

    db_pandora_audit(
        AUDIT_LOG_SYSTEM,
        'DB Interface Extension. SQL',
        false,
        false,
        $sql
    );

    echo "<div class='overflow'>";
    $table = new stdClass();
    $table->width = '100%';
    $table->class = 'info_table';
    $table->head = array_keys($result[0]);

    $table->data = $result;

    html_print_table($table);
    echo '</div>';

}




// This adds a option in the operation menu.
extensions_add_godmode_menu_option(__('DB interface'), 'PM', 'gextensions', 'dbmanager/icon.png', 'v1r1', 'gdbman');

// This sets the function to be called when the extension is selected in the operation menu.
extensions_add_godmode_function('dbmgr_extension_main');
