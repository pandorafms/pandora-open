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

if (is_ajax() === true) {
    global $config;

    // Login check.
    check_login();

    include_once $config['homedir'].'/include/class/Tree.class.php';
    include_once $config['homedir'].'/include/class/TreeOS.class.php';
    include_once $config['homedir'].'/include/class/TreeModuleGroup.class.php';
    include_once $config['homedir'].'/include/class/TreeModule.class.php';
    include_once $config['homedir'].'/include/class/TreeTag.class.php';
    include_once $config['homedir'].'/include/class/TreeGroup.class.php';
    include_once $config['homedir'].'/include/class/TreeGroupEdition.class.php';
    include_once $config['homedir'].'/include/functions_reporting.php';
    include_once $config['homedir'].'/include/functions_os.php';

    $getChildren = (bool) get_parameter('getChildren', 0);
    $getGroupStatus = (bool) get_parameter('getGroupStatus', 0);
    $getDetail = (bool) get_parameter('getDetail');

    if ($getChildren === true) {
        $type = get_parameter('type', 'group');
        $rootType = get_parameter('rootType', '');
        $id = get_parameter('id', -1);
        $rootID = get_parameter('rootID', -1);
        $serverID = get_parameter('serverID', false);
        $metaID = (int) get_parameter('metaID', 0);
        $childrenMethod = get_parameter('childrenMethod', 'on_demand');


        $default_filters = [
            'searchAgent'  => '',
            'statusAgent'  => AGENT_STATUS_ALL,
            'searchModule' => '',
            'statusModule' => AGENT_MODULE_STATUS_ALL,
            'groupID'      => 0,
            'tagID'        => 0,
        ];
        $filter = get_parameter('filter', $default_filters);


        $agent_a = check_acl($config['id_user'], 0, 'AR');
        $agent_w = check_acl($config['id_user'], 0, 'AW');
        $access = ($agent_a === true) ? 'AR' : (($agent_w === true) ? 'AW' : 'AR');
        $switch_type = (empty($rootType) === false) ? $rootType : $type;
        switch ($switch_type) {
            case 'os':
                $tree = new TreeOS(
                    $type,
                    $rootType,
                    $id,
                    $rootID,
                    $serverID,
                    $childrenMethod,
                    $access
                );
            break;

            case 'module_group':
                $tree = new TreeModuleGroup(
                    $type,
                    $rootType,
                    $id,
                    $rootID,
                    $serverID,
                    $childrenMethod,
                    $access
                );
            break;

            case 'module':
                $tree = new TreeModule(
                    $type,
                    $rootType,
                    $id,
                    $rootID,
                    $serverID,
                    $childrenMethod,
                    $access
                );
            break;

            case 'tag':
                $tree = new TreeTag(
                    $type,
                    $rootType,
                    $id,
                    $rootID,
                    $serverID,
                    $childrenMethod,
                    $access
                );
            break;

            case 'group':

                    $tree = new TreeGroup(
                        $type,
                        $rootType,
                        $id,
                        $rootID,
                        $serverID,
                        $childrenMethod,
                        $access
                    );
                
            break;

            case 'policies':
                if (class_exists('TreePolicies') === false) {
                    break;
                }

                $tree = new TreePolicies(
                    $type,
                    $rootType,
                    $id,
                    $rootID,
                    $serverID,
                    $childrenMethod,
                    $access
                );
            break;

            case 'group_edition':
                $tree = new TreeGroupEdition(
                    $type,
                    $rootType,
                    $id,
                    $rootID,
                    $serverID,
                    $childrenMethod,
                    $access
                );
            break;

            case 'services':
                $tree = new TreeService(
                    $type,
                    $rootType,
                    $id,
                    $rootID,
                    $serverID,
                    $childrenMethod,
                    $access,
                    $metaID,
                    $filter['groupID']
                );
            break;

            case 'IPAM_supernets':
                $tree = new TreeIPAMSupernet(
                    $type,
                    $rootType,
                    $id,
                    $rootID,
                    $serverID,
                    $childrenMethod,
                    $access
                );
            break;

            default:
                // No error handler.
            return;
        }

        $tree->setFilter($filter);
        ob_clean();

        $tree_json = json_encode(['success' => 1, 'tree' => $tree->getArray()]);

        echo $tree_json;
        return;
    }

    if ($getDetail === true) {
        include_once $config['homedir'].'/include/functions_treeview.php';

        $id = (int) get_parameter('id');
        $type = (string) get_parameter('type');

        $server = [];
        

        ob_clean();

        echo '<style type="text/css">';
        include_once __DIR__.'/../styles/progress.css';
        echo '</style>';

        echo '<div class="left_align backgrund_primary_important">';
        if (empty($id) === false && empty($type) === false) {
            switch ($type) {
                case 'agent':
                    treeview_printTable($id, $server, true);
                break;

                case 'module':
                    treeview_printModuleTable($id, $server, true);
                break;

                case 'alert':
                    treeview_printAlertsTable($id, $server, true);
                break;

                default:
                    // Nothing.
                break;
            }
        }

        echo '<br></div>';

        return;
    }

    return;
}
