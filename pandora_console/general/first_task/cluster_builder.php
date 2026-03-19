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

check_login();

if (! check_acl($config['id_user'], 0, 'AR') && ! check_acl($config['id_user'], 0, 'AW')) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access Inventory'
    );
    include 'general/noaccess.php';
    return;
}

// Header.
ui_print_standard_header(
    __('Clusters'),
    'images/chart.png',
    false,
    '',
    false,
    [],
    [
        [
            'link'  => '',
            'label' => __('Monitoring'),
        ],
    ]
);

ui_require_css_file('first_task');

$msg = __('A cluster is a group of devices that provide the same service in high availability.').'<br>';

$msg .= __('Depending on how they provide that service, we can find two types:').'<br><br>';

$msg .= __('Clusters to balance the service load: these are active - active (A/A) mode clusters. It means that all the nodes (or machines that compose it) are working. They must be working because if one stops working, it will overload the others.').'<br><br>';

$msg .= __('Clusters to guarantee service: these are active - passive (A/P) mode clusters. It means that one of the nodes (or machines that make up the cluster) will be running (primary) and another will not (secondary). When the primary goes down, the secondary must take over and give the service instead. Although many of the elements of this cluster are active-passive, it will also have active elements in both of them that indicate that the passive node is "online", so that in the case of a service failure in the master, the active node collects this information.');



$button = false;
if (check_acl($config['id_user'], 0, 'AW')) {
    $button = "
    <form action='index.php?sec=estado&sec2=operation/cluster/cluster&op=new' method='post'>
        <input type='submit' class='button_task button_task_mini mrgn_0px_imp' value='".__('Create cluster')."' />
    </form>";
}

echo ui_print_empty_view(
    __('There are no defined clusters'),
    $msg,
    'clusters.svg',
    $button
);
