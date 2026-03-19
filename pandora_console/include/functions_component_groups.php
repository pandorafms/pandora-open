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
 * @subpackage Component groups
 */


/**
 * Format components groups in tree way.
 *
 * @param array   $groups The list of groups to create the treefield list.
 * @param integer $parent The id_group of parent actual scan branch.
 * @param integer $deep   The level of profundity in the branch.
 *
 * @return array The treefield list of components groups.
 */
function component_groups_get_groups_tree_recursive($groups, $parent=0, $deep=0)
{
    $return = [];

    foreach ($groups as $key => $group) {
        if ($group['parent'] == $parent) {
            $group['deep'] = $deep;

            $branch = component_groups_get_groups_tree_recursive($groups, $key, ($deep + 1));
            if (empty($branch)) {
                $group['hash_branch'] = false;
            } else {
                $group['hash_branch'] = true;
            }

            $return = ($return + [$key => $group] + $branch);
        }
    }

    return $return;
}


/**
 * Return a array of id_group of childrens (to branches down)
 *
 * @param integer $parent The id_group parent to search the childrens.
 * @param array   $groups The groups, its for optimize the querys to DB.
 */
function component_groups_get_childrens($parent, $groups=null)
{
    if (empty($groups)) {
        $groups = db_get_all_rows_in_table('tnetwork_component_group');
    }

    $return = [];

    foreach ($groups as $key => $group) {
        if ($group['id_sg'] == 0) {
            continue;
        }

        if ($group['parent'] == $parent) {
            $return = ($return + [$group['id_sg'] => $group] + component_groups_get_childrens($group['id_sg'], $groups));
        }
    }

    return $return;
}
