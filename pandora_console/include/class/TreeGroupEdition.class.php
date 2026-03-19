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

require_once $config['homedir'].'/include/class/Tree.class.php';

/**
 * Tree group edition.
 */
class TreeGroupEdition extends TreeGroup
{


    /**
     * Construct.
     *
     * @param string  $type           Type.
     * @param string  $rootType       Root.
     * @param integer $id             Id.
     * @param integer $rootID         Root Id.
     * @param boolean $serverID       Server.
     * @param string  $childrenMethod Method children.
     * @param string  $access         Access ACL.
     */
    public function __construct(
        $type,
        $rootType='',
        $id=-1,
        $rootID=-1,
        $serverID=false,
        $childrenMethod='on_demand',
        $access='AR'
    ) {
        global $config;

        parent::__construct(
            $type,
            $rootType,
            $id,
            $rootID,
            $serverID,
            $childrenMethod,
            $access
        );
    }


    /**
     * Get data.
     *
     * @return void
     */
    protected function getData()
    {
        if ($this->id == -1) {
            $this->getFirstLevel();
        }
    }


    /**
     * Get process group.
     *
     * @return mixed
     */
    protected function getProcessedGroups()
    {
        // Index and process the groups.
        $groups = $this->getGroups();
        foreach ($groups as $id => $group) {
            if (isset($groups[$id]['parent']) === true
                && ($groups[$id]['parent'] != 0)
            ) {
                $parent = $groups[$id]['parent'];
                // Parent exists.
                if (isset($groups[$parent]['children']) === false) {
                    $groups[$parent]['children'] = [];
                }

                // Store a reference to the group into the parent.
                $groups[$parent]['children'][] = &$groups[$id];
                // This group was introduced into a parent.
                $groups[$id]['have_parent'] = true;
            }
        }

        // Sort the children groups.
        foreach ($groups as $id => $group) {
            if (isset($groups[$id]['children']) === true) {
                usort($groups[$id]['children'], ['Tree', 'cmpSortNames']);
            }
        }

        // Filter groups and eliminates the reference
        // to children groups out of her parent.
        $groups = array_filter(
            $groups,
            function ($group) {
                return !($group['have_parent'] ?? false);
            }
        );

        // Filter groups that user has permission.
        $groups = array_filter(
            $groups,
            function ($group) {
                global $config;
                return check_acl($config['id_user'], $group['id'], 'AR');
            }
        );

        usort($groups, ['Tree', 'cmpSortNames']);
        return $groups;
    }


    /**
     * Get group counters.
     *
     * @return mixed
     */
    protected function getGroups()
    {
        $group_acl = '';
        if (users_can_manage_group_all('AR') === false) {
            $user_groups_str = implode(',', $this->userGroupsArray);
            $group_acl = sprintf(
                'AND id_grupo IN (%s)',
                $user_groups_str
            );
        }

        $sql = sprintf(
            'SELECT id_grupo AS gid,
            nombre as name,
            parent,
            icon
            FROM tgrupo
            WHERE 1=1 
            %s',
            $group_acl
        );

        $stats = db_get_all_rows_sql($sql);
        $group_stats = [];
        foreach ($stats as $group) {
            $group_stats[$group['gid']]['name']   = $group['name'];
            $group_stats[$group['gid']]['parent'] = $group['parent'];
            $group_stats[$group['gid']]['icon']   = $group['icon'];
            $group_stats[$group['gid']]['id']     = $group['gid'];
            $group_stats[$group['gid']]['type']   = 'group';

            $group_stats[$group['gid']] = $this->getProcessedItem(
                $group_stats[$group['gid']]
            );
                $messages = [
                    'confirm' => __('Confirm'),
                    'cancel'  => __('Cancel'),
                    'messg'   => __('Are you sure?'),
                ];

                $group_stats[$group['gid']]['delete']['messages'] = $messages;
                $group_stats[$group['gid']]['edit']   = 1;

            $group_stats[$group['gid']]['alerts'] = '';
        }

        return $group_stats;
    }


}
