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

global $config;

// Includes.
require_once $config['homedir'].'/include/functions_notifications.php';

// Load the header.
$headerTitle = __('User notifications');
require $config['homedir'].'/operation/users/user_edit_header.php';

echo '<div id="user-notifications-wrapper" class="white_box table_div table_three_columns padding-2">
        <div class="table_thead">
            <div class="table_th"></div>
            <div class="table_th">'.__('Console notifications').'</div>
            <div class="table_th">'.__('E-mail notifications').'</div>
        </div>';

$sources = notifications_get_all_sources();

$disabled_flag = [];
$user_login = $config['id_user'];

foreach ($sources as $source) {
    // Enabled notification user.
    $users_notification = notifications_get_user_sources(
        [
            'id_source' => $source['id'],
            'id_user'   => $user_login,
        ],
        ['id_user']
    );

    if ((boolean) $source['enabled'] === true && $users_notification[0]['id_user'] === $user_login) {
        echo '<div class="table_tbody">';
        $table_content = [
            notifications_print_user_switch($source, $id, 'enabled'),
            notifications_print_user_switch($source, $id, 'also_mail'),
        ];
        $notifications_enabled = notifications_print_user_switch($source, $id, 'enabled');
        $notifications_also_mail = notifications_print_user_switch($source, $id, 'also_mail');

        $disabled_flag[] = true;

        echo '<div class="table_td">'.$source['description'].'</div>';
        echo '<div class="table_td">'.$notifications_enabled['switch'].'</div>';
        echo '<div class="table_td">'.$notifications_also_mail['switch'].'</div>';
        echo '</div>';
    }
}

if (count($disabled_flag) === 0) {
    ui_print_warning_message(
        __('Controls have been disabled by the system administrator')
    );
}

echo '</div>';

// Print id user to handle it on js.
html_print_input_hidden('id_user', $id);

?>
<script>
// Encapsulate the code.
(function() {
    function notifications_change_label(event) {
        event.preventDefault();
        var check = document.getElementById(event.target.id);
        if (check === null) return;
        var match = /notifications-user-([0-9]+)-label-(.*)/
            .exec(event.target.id);
        jQuery.post ("ajax.php",
            {
                //"page" : "operation/users/user_edit_notifications",
                "page" : 'include/ajax/notifications.ajax',
                "change_label" : 1,
                "label" : match[2],
                "source" : match[1],
                "user" : document.getElementById('hidden-id_user').value,
                "value": check.checked ? 1 : 0
            },
            function (data, status) {
                if (!data.result) {
                    console.error("Error changing configuration in database.");
                } else {
                    check.checked = !check.checked;
                }
            },
            "json"
        ).done(function(m){})
        .fail(function(xhr, textStatus, errorThrown){
            console.error(
                "Cannot change configuration in database. Server error.",
                xhr.responseText
            );
        });

    }
    var all_labels = document.getElementsByClassName(
        'notifications-user-label_individual'
    );
    for (var i = 0; i < all_labels.length; i++) {
        all_labels[i].addEventListener(
            'click', notifications_change_label, false
        );
    }
}());

</script>
