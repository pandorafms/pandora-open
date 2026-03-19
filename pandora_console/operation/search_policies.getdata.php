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

// Get global data.
global $config;
$searchpolicies = (bool) check_acl($config['id_user'], 0, 'AW');

if ($searchpolicies === false) {
    $totalPolicies = 0;
    return;
}

$selectpolicieIDUp = '';
$selectpolicieIDDown = '';
$selectNameUp = '';
$selectNameDown = '';
$selectDescriptionUp = '';
$selectDescriptionDown = '';
$selectId_groupUp = '';
$selectId_groupDown = '';
$selectStatusUp = '';
$selectStatusDown = '';

switch ($sortField) {
    case 'id':
        switch ($sort) {
            case 'up':
            default:
                $selectpolicieIDUp = $selected;
                $order = [
                    'field' => 'id',
                    'order' => 'ASC',
                ];
            break;

            case 'down':
                $selectpolicieIDDown = $selected;
                $order = [
                    'field' => 'id',
                    'order' => 'DESC',
                ];
            break;
        }
    break;

    case 'name':
        switch ($sort) {
            case 'up':
            default:
                $selectNameUp = $selected;
                $order = [
                    'field' => 'name',
                    'order' => 'ASC',
                ];
            break;

            case 'down':
                $selectNameDown = $selected;
                $order = [
                    'field' => 'name',
                    'order' => 'DESC',
                ];
            break;
        }
    break;

    case 'description':
        switch ($sort) {
            case 'up':
            default:
                $selectId_groupUp = $selected;
                $order = [
                    'field' => 'description',
                    'order' => 'ASC',
                ];
            break;

            case 'down':
                $selectDescriptionDown = $selected;
                $order = [
                    'field' => 'description',
                    'order' => 'DESC',
                ];
            break;
        }
    break;

    case 'last_contact':
        switch ($sort) {
            case 'up':
            default:
                $selectId_groupUp = $selected;
                $order = [
                    'field' => 'last_connect',
                    'order' => 'ASC',
                ];
            break;

            case 'down':
                $selectId_groupDown = $selected;
                $order = [
                    'field' => 'last_connect',
                    'order' => 'DESC',
                ];
            break;
        }
    break;

    case 'id_group':
        switch ($sort) {
            case 'up':
            default:
                $selectId_groupUp = $selected;
                $order = [
                    'field' => 'last_connect',
                    'order' => 'ASC',
                ];
            break;

            case 'down':
                $selectId_groupDown = $selected;
                $order = [
                    'field' => 'last_connect',
                    'order' => 'DESC',
                ];
            break;
        }
    break;

    case 'status':
        switch ($sort) {
            case 'up':
            default:
                $selectStatusUp = $selected;
                $order = [
                    'field' => 'is_admin',
                    'order' => 'ASC',
                ];
            break;

            case 'down':
                $selectStatusDown = $selected;
                $order = [
                    'field' => 'is_admin',
                    'order' => 'DESC',
                ];
            break;
        }
    break;

    default:
        $selectpolicieIDUp = $selected;
        $selectpolicieIDDown = '';
        $selectNameUp = '';
        $selectNameDown = '';
        $selectDescriptionUp = '';
        $selectDescriptionDown = '';
        $selectId_groupUp = '';
        $selectId_groupDown = '';
        $selectStatusUp = '';
        $selectStatusDown = '';

        $order = [
            'field' => 'id',
            'order' => 'ASC',
        ];
    break;
}


    $sql .= ' LIMIT '.$config['block_size'].' OFFSET '.get_parameter('offset', 0);

    $policies = db_process_sql($sql);

if ($policies !== false) {
    $totalPolicies = count($policies);

    if ($only_count === true) {
        unset($policies);
    }
} else {
    $totalPolicies = 0;
}
