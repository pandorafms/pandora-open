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

require_once $config['homedir'].'/include/functions_register.php';
require_once $config['homedir'].'/include/class/WelcomeWindow.class.php';
require_once $config['homedir'].'/include/class/TipsWindow.class.php';


if ((bool) is_ajax() === true) {
    // Parse responses, flow control.
    $configuration_wizard = get_parameter('save_required_wizard', 0);
    $change_language = get_parameter('change_language', 0);
    $cancel_wizard = get_parameter('cancel_wizard', 0);

    // Load wizards.
    $load_wizards = get_parameter('load_wizards', '');

    $feedback = [];

    // Load wizards.
    if ($load_wizards != '') {
        switch ($load_wizards) {
            case 'initial':
            return config_wiz_modal(false, false);

            case 'all':
                config_wiz_modal(false, false);
            return;

            default:
                // Ignore.
            break;
        }
    }

    // Configuration wizard process.
    if ($configuration_wizard) {
        $feedback = config_wiz_process();
    }

    if ($change_language) {
        // Change the language if is change in checkbox.
        config_update_value('language', $change_language);
    }

    if ($cancel_wizard) {
        config_update_value('initial_wizard', 1);
    }

    if (is_array($feedback) === true) {
        echo json_encode($feedback);
    }


    // Ajax calls finish here.
    exit();
}

ui_require_css_file('register');

$initial = isset($config['initial_wizard']) !== true
    || $config['initial_wizard'] != '1';

if ($initial && users_is_admin()) {
    // Show all forms in order.
    // 1- Ask for email, timezone, etc. Fullfill alerts and user mail.
    config_wiz_modal(
        false,
        true,
        null,
        true
    );
}

if (check_acl($config['id_user'], 0, 'AW') && empty($sec2)) {
    $welcome = !$initial;
    try {
        $welcome_window = new WelcomeWindow($welcome);
        if ($welcome_window !== null) {
            $welcome_window->run();
        }
    } catch (Exception $e) {
        $welcome = false;
    }
}

try {
    if (isset($_SESSION['showed_tips_window']) === false) {
        $tips_window = new TipsWindow();
        if ($tips_window !== null) {
            $tips_window->run();
        }
    }
} catch (Exception $e) {
}

$double_auth_enabled = (bool) db_get_value('id', 'tuser_double_auth', 'id_user', $config['id_user']);

if (isset($config['2FA_all_users']) === false) {
    $config['2FA_all_users'] = null;
}

if (!$double_auth_enabled
    && $config['2FA_all_users'] != ''
    && $config['2Fa_auth'] != '1'
    && $config['double_auth_enabled']
) {
    echo '<div id="doble_auth_window" class="invisible"; >';
    ?>
    <script type="text/javascript">
  var userID = "<?php echo $config['id_user']; ?>";

  var $loadingSpinner = $("<img src=\"<?php echo $config['homeurl']; ?>/images/spinner.gif\" />");
  var $dialogContainer = $("div#doble_auth_window");

  $dialogContainer.html($loadingSpinner);

  // Load the info page
  var request = $.ajax({
    url: "<?php echo ui_get_full_url('ajax.php', false, false, false); ?>",
    type: 'POST',
    dataType: 'html',
    data: {
      page: 'include/ajax/double_auth.ajax',
      id_user: userID,
      get_double_auth_info_page: 1,
      containerID: $dialogContainer.prop('id')
    },
    complete: function (xhr, textStatus) {

    },
    success: function (data, textStatus, xhr) {
      // isNaN = is not a number
      if (isNaN(data)) {
        $dialogContainer.html(data);
      }
      // data is a number, convert it to integer to do the compare
      else if (Number(data) === -1) {
        $dialogContainer.html("<?php echo '<b><div class=\"red\">'.__('Authentication error').'</div></b>'; ?>");
      }
      else {
        $dialogContainer.html("<?php echo '<b><div class=\"red\">'.__('Error').'</div></b>'; ?>");
      }
    },
    error: function (xhr, textStatus, errorThrown) {
      $dialogContainer.html("<?php echo '<b><div class=\"red\">'.__('There was an error loading the data').'</div></b>'; ?>");
    }
  });

    <?php config_update_value('2Fa_auth', ''); ?>
  $("div#doble_auth_window").dialog({
    resizable: true,
    draggable: true,
    modal: true,
    title: "<?php echo __('Double authentication activation'); ?>",
    overlay: {
      opacity: 0.5,
      background: "black"
    },
    width: 500,
    height: 'auto',
    close: function (event, ui) {
      // Abort the ajax request
      if (typeof request != 'undefined'){
        request.abort();
      }
      // Remove the contained html
      $dialogContainer.empty();

      //document.location.reload();
    }
  })
    .show();    
    // Don't allow close the dialog with X button
    // $('.ui-dialog-titlebar-close').css('display', 'none');

    </script>
    <?php
    echo '</div>';
}

?>
<script type="text/javascript">

$(document).ready (function () {

});



</script>
