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
global $incident_w;
global $incident_m;
check_login();
ui_require_css_file('first_task');
?>
<?php
ui_print_info_message(['no_close' => true, 'message' => __('There are no incidents defined yet.') ]);

if ($incident_w || $incident_m) {
    ?>

<div class="new_task">
    <div class="image_task">
        <?php echo html_print_image('images/first_task/icono_grande_incidencia.png', true, ['title' => __('Incidents')]); ?>
    </div>
    <div class="text_task">
        <h3> <?php echo __('Create Incidents'); ?></h3><p id="description_task"> 
            <?php
            echo __(
                "Besides receiving and processing data to monitor systems or applications,
			you're also required to monitor possible incidents which might take place on these subsystems within the system's monitoring process.
			For it, the %s team has designed an incident manager from which any user is able to open incidents,
			that explain what's happened on the network, and update them with comments and files, at any time, in case there is a need to do so.
			This system allows users to work as a team, along with different roles and work-flow systems which allow an incident to be
			moved from one group to another, and members from different groups and different people could work on the same incident, sharing information and files.
		",
                get_product_name()
            );
            ?>
                                                                                    </p>
        <form action="index.php?sec=workspace&amp;sec2=operation/incidents/incident_detail&amp;insert_form=1" method="post">
            <input type="submit" class="button_task" value="<?php echo __('Create Incidents'); ?>" />
        </form>
    </div>
</div>
    <?php
}
