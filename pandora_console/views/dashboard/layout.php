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

// Css Files.
\ui_require_css_file('bootstrap.min');

\ui_require_css_file('gridstack.min');
\ui_require_css_file('gridstack-extra.min');
\ui_require_css_file('pandora');

\ui_require_css_file('dashboards');

// Js Files.
\ui_require_javascript_file('underscore-min');
\ui_require_javascript_file('gridstack');
\ui_require_javascript_file('gridstack.jQueryUI');
\ui_require_javascript_file('pandora_dashboards');
\ui_require_jquery_file('countdown');

$output = '';

// Div for modal update dashboard.
$output .= '<div id="modal-update-dashboard" style="display:none;"></div>';
$output .= '<div id="modal-add-widget" style="display:none;"></div>';
$output .= '<div id="modal-config-widget" style="display:none;"></div>';
$output .= '<div id="modal-slides-dialog" style="display:none;"></div>';

// Layout.
$output .= '<div class="container-fluid">';
$output .= '<div id="container-layout">';
$output .= '<div class="grid-stack"></div>';
$output .= '</div>';
$output .= '</div>';

echo $output;

?>
<script type="text/javascript">
    $(document).ready (function () {
        // Iniatilice Layout.
        initialiceLayout({
            page: '<?php echo $ajaxController; ?>',
            url: '<?php echo $url; ?>',
            dashboardId: '<?php echo $dashboardId; ?>',
            auth: {
                class: '<?php echo $class; ?>',
                hash: '<?php echo $hash; ?>',
                user: '<?php echo $config['id_user']; ?>'
            },
            title: '<?php echo __('New widget'); ?>',
        });

        // Mode for create new dashboard.
        var active = '<?php echo (int) $createDashboard; ?>';
        var cellId = '<?php echo (int) $cellIdCreate; ?>';
        if(active != 0){
            // Trigger simulate edit mode.
            setTimeout(() => {
                $("#checkbox-edit-mode").trigger("click");
            }, 300);
            // Trigger simulate new cell add widget.
            setTimeout(() => {
                $("#button-add-widget-"+cellId).trigger("click");
            }, 500);
        }

        // Paused and resume if edit mode is checked.
        paused_resume_dashboard_countdown();
    });
</script>
