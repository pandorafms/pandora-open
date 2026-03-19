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

use PandoraFMS\Agent;


// Begin.
check_login();

if ((bool) check_acl($config['id_user'], 0, 'AW') === false) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access massive agent deletion section'
    );
    include 'general/noaccess.php';
    return;
}

require_once $config['homedir'].'/include/functions_agents.php';
require_once $config['homedir'].'/include/functions_alerts.php';
require_once $config['homedir'].'/include/functions_modules.php';
require_once $config['homedir'].'/include/functions_users.php';
require_once $config['homedir'].'/include/functions_massive_operations.php';
/**
 * Bulk operations Delete.
 *
 * @param array $id_agents Agents to delete.
 *
 * @return boolean
 */
function process_manage_delete($id_agents)
{
    if (empty($id_agents) === true) {
        ui_print_error_message(__('No agents selected'));
        return false;
    }

    $id_agents = (array) $id_agents;

    $count_deleted = 0;
    $agent_id_restore = 0;
    foreach ($id_agents as $id_agent) {
        try {
            $agent = new Agent($id_agent);
            $success = $agent->delete();
        } catch (\Exception $e) {
            // Unexistent agent.
            $success = false;
        }

        if ($success === false) {
            $agent_id_restore = $id_agent;
            break;
        }

        $count_deleted++;
    }

    if ($success === false) {
        $alias = agents_get_alias($agent_id_restore);

        ui_print_error_message(
            sprintf(
                __('There was an error deleting the agent, the operation has been cancelled Could not delete agent %s'),
                $alias
            )
        );

        return false;
    } else {
        ui_print_success_message(
            sprintf(
                __(
                    'Successfully deleted (%s)',
                    $count_deleted
                )
            )
        );

        return true;
    }
}


$id_group = (int) get_parameter('id_group');
$id_agents = get_parameter('id_agents');
$recursion = get_parameter('recursion');
$delete = (bool) get_parameter_post('delete');

if ($delete === true) {
    $result = process_manage_delete($id_agents);

    if (empty($id_agents) === true) {
        $info = '{"Agent":"empty"}';
    } else {
        $info = '{"Agent":"'.implode(',', $id_agents).'"}';
    }

    if ($result === true) {
        db_pandora_audit(
            AUDIT_LOG_MASSIVE_MANAGEMENT,
            'Delete agent ',
            false,
            false,
            $info
        );
    } else {
        db_pandora_audit(
            AUDIT_LOG_MASSIVE_MANAGEMENT,
            'Fail try to delete agent',
            false,
            false,
            $info
        );
    }
}


$url = 'index.php?sec=gmassive&sec2=godmode/massive/massive_operations&option=delete_agents';


echo '<form method="post" id="form_agent" action="'.$url.'">';

$params = [
    'id_group'  => $id_group,
    'recursion' => $recursion,
];
echo get_table_inputs_masive_agents($params);

attachActionButton('delete', 'delete', '100%', false, $SelectAction);

echo '</form>';

echo '<h3 class="error invisible" id="message"> </h3>';

ui_require_jquery_file('form');
ui_require_jquery_file('pandora.controls');
