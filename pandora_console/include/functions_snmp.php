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
 * Generates a trap
 *
 * @param string Destiny host address.
 * @param string Snmp community.
 * @param string Snmp OID.
 * @param string Snmp agent.
 * @param string Data of the trap.
 * @param string Snmp especific OID.
 */
function snmp_generate_trap($snmp_host_address, $snmp_community, $snmp_oid, $snmp_agent, $snmp_data, $snmp_type)
{
    global $config;
    // Call snmptrap
    if (empty($config['snmptrap'])) {
        switch (PHP_OS) {
            case 'FreeBSD':
                $snmptrap_bin = '/usr/local/bin/snmptrap';
            break;

            case 'NetBSD':
                $snmptrap_bin = '/usr/pkg/bin/snmptrap';
            break;

            default:
                $snmptrap_bin = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'C:\Windows\snmptrap.exe' : 'snmptrap';
            break;
        }
    } else {
        $snmptrap_bin = $config['snmptrap'];
    }

    $command = "$snmptrap_bin -v 1 -c ".escapeshellarg($snmp_community).' '.escapeshellarg($snmp_host_address).' '.escapeshellarg($snmp_oid).' '.escapeshellarg($snmp_agent).' '.escapeshellarg($snmp_type).' '.escapeshellarg($snmp_data).' 0 2>&1';

    $output = null;
    exec($command, $output, $return);

    if ($return == 0) {
        return true;
    } else {
        return implode(' ', $output);
    }
}


function snmp_get_default_translations()
{
    $return = [];
    $return['.1.3.6.1.4.1.2021.10.1.5.1'] = [
        'description'  => __('Load Average (Last minute)'),
        'post_process' => '1',
    ];
    $return['.1.3.6.1.4.1.2021.10.1.5.2'] = [
        'description'  => __('Load Average (Last 5 minutes)'),
        'post_process' => '1',
    ];
    $return['.1.3.6.1.4.1.2021.10.1.5.3'] = [
        'description'  => __('Load Average (Last 15 minutes)'),
        'post_process' => '1',
    ];
    $return['.1.3.6.1.4.1.2021.4.3.0'] = [
        'description'  => __('Total Swap Size configured for the host'),
        'post_process' => '1',
    ];
    $return['.1.3.6.1.4.1.2021.4.4.0'] = [
        'description'  => __('Available Swap Space on the host'),
        'post_process' => '1',
    ];
    $return['.1.3.6.1.4.1.2021.4.5.0'] = [
        'description'  => __('Total Real/Physical Memory Size on the host'),
        'post_process' => '1',
    ];
    $return['.1.3.6.1.4.1.2021.4.6.0'] = [
        'description'  => __('Available Real/Physical Memory Space on the host'),
        'post_process' => '1',
    ];
    $return['.1.3.6.1.4.1.2021.4.11.0'] = [
        'description'  => __('Total Available Memory on the host'),
        'post_process' => '1',
    ];
    $return['.1.3.6.1.4.1.2021.4.15.0'] = [
        'description'  => __('Total Cached Memory'),
        'post_process' => '1',
    ];
    $return['.1.3.6.1.4.1.2021.4.14.0'] = [
        'description'  => __('Total Buffered Memory'),
        'post_process' => '1',
    ];
    $return['.1.3.6.1.4.1.2021.11.3.0'] = [
        'description'  => __('Amount of memory swapped in from disk (kB/s)'),
        'post_process' => '1',
    ];
    $return['.1.3.6.1.4.1.2021.11.4.0'] = [
        'description'  => __('Amount of memory swapped to disk (kB/s)'),
        'post_process' => '1',
    ];
    $return['.1.3.6.1.4.1.2021.11.57.0'] = [
        'description'  => __('Number of blocks sent to a block device'),
        'post_process' => '1',
    ];
    $return['.1.3.6.1.4.1.2021.11.58.0'] = [
        'description'  => __('Number of blocks received from a block device'),
        'post_process' => '1',
    ];
    $return['.1.3.6.1.4.1.2021.11.59.0'] = [
        'description'  => __('Number of interrupts processed'),
        'post_process' => '1',
    ];
    $return['.1.3.6.1.4.1.2021.11.60.0'] = [
        'description'  => __('Number of context switches'),
        'post_process' => '1',
    ];
    $return['.1.3.6.1.4.1.2021.11.50.0'] = [
        'description'  => __('user CPU time'),
        'post_process' => '1',
    ];
    $return['.1.3.6.1.4.1.2021.11.52.0'] = [
        'description'  => __('system CPU time'),
        'post_process' => '1',
    ];
    $return['.1.3.6.1.4.1.2021.11.53.0'] = [
        'description'  => __('idle CPU time'),
        'post_process' => '1',
    ];
    $return['1.3.6.1.2.1.1.3.0'] = [
        'description'  => __('System Up time'),
        'post_process' => '0.00000011574074',
    ];

    return $return;
}


function snmp_get_user_translations()
{
    $row = db_get_row('tconfig', 'token', 'snmp_translations');

    if (empty($row)) {
        db_process_sql_insert(
            'tconfig',
            [
                'token' => 'snmp_translations',
                'value' => json_encode([]),
            ]
        );

        $return = [];
    } else {
        $return = json_decode($row['value'], true);
    }

    return $return;
}


function snmp_get_translation_wizard()
{
    $return = [];

    $snmp_default_translations = snmp_get_default_translations();
    $snmp_user_translations = snmp_get_user_translations();

    foreach ($snmp_default_translations as $oid => $translation) {
        $return[$oid] = array_merge($translation, ['readonly' => 1]);
    }

    foreach ($snmp_user_translations as $oid => $translation) {
        $return[$oid] = array_merge($translation, ['readonly' => 0]);
    }

    return $return;
}


function snmp_save_translation($oid, $description, $post_process)
{
    $row = db_get_row('tconfig', 'token', 'snmp_translations');

    if (empty($row)) {
        db_process_sql_insert(
            'tconfig',
            [
                'token' => 'snmp_translations',
                'value' => json_encode([]),
            ]
        );

        $snmp_translations = [];
    } else {
        $snmp_translations = json_decode($row['value'], true);
    }

    if (isset($snmp_translations[$oid])) {
        // exists the oid
        return false;
    } else {
        $snmp_translations[$oid] = [
            'description'  => $description,
            'post_process' => $post_process,
        ];

        return (bool) db_process_sql_update(
            'tconfig',
            ['value' => json_encode($snmp_translations)],
            ['token' => 'snmp_translations']
        );
    }
}


function snmp_delete_translation($oid)
{
    $row = db_get_row('tconfig', 'token', 'snmp_translations');

    if (empty($row)) {
        db_process_sql_insert(
            'tconfig',
            [
                'token' => 'snmp_translations',
                'value' => json_encode([]),
            ]
        );

        $snmp_translations = [];
    } else {
        $snmp_translations = json_decode($row['value'], true);
    }

    if (isset($snmp_translations[$oid])) {
        unset($snmp_translations[$oid]);

        return (bool) db_process_sql_update(
            'tconfig',
            ['value' => json_encode($snmp_translations)],
            ['token' => 'snmp_translations']
        );
    } else {
        // exists the oid
        return false;
    }
}


function snmp_get_translation($oid)
{
    $snmp_translations = snmp_get_translation_wizard();

    return $snmp_translations[$oid];
}


function snmp_update_translation($oid, $new_oid, $description, $post_process)
{
    $row = db_get_row('tconfig', 'token', 'snmp_translations');

    if (empty($row)) {
        db_process_sql_insert(
            'tconfig',
            [
                'token' => 'snmp_translations',
                'value' => json_encode([]),
            ]
        );

        $snmp_translations = [];
    } else {
        $snmp_translations = json_decode($row['value'], true);
    }

    if (isset($snmp_translations[$new_oid])) {
        return false;
    } else {
        if (isset($snmp_translations[$oid])) {
            unset($snmp_translations[$oid]);

            $snmp_translations[$new_oid] = [
                'description'  => $description,
                'post_process' => $post_process,
            ];

            return (bool) db_process_sql_update(
                'tconfig',
                ['value' => json_encode($snmp_translations)],
                ['token' => 'snmp_translations']
            );
        } else {
            return false;
        }
    }
}


/**
 * Retunr module type for snmp data type
 *
 * @param  [type] $snmp_data_type
 * @return void
 */
function snmp_module_get_type(string $snmp_data_type)
{
    if (preg_match('/INTEGER/i', $snmp_data_type)) {
        $type = 'remote_snmp';
    } else if (preg_match('/Integer32/i', $snmp_data_type)) {
        $type = 'remote_snmp';
    } else if (preg_match('/octect string/i', $snmp_data_type)) {
        $type = 'remote_snmp';
    } else if (preg_match('/bits/i', $snmp_data_type)) {
        $type = 'remote_snmp';
    } else if (preg_match('/object identifier/i', $snmp_data_type)) {
        $type = 'remote_snmp_string';
    } else if (preg_match('/IpAddress/i', $snmp_data_type)) {
        $type = 'remote_snmp_string';
    } else if (preg_match('/Counter/i', $snmp_data_type)) {
        $type = 'remote_snmp_inc';
    } else if (preg_match('/Counter32/i', $snmp_data_type)) {
        $type = 'remote_snmp_inc';
    } else if (preg_match('/Gauge/i', $snmp_data_type)) {
        $type = 'remote_snmp';
    } else if (preg_match('/Gauge32/i', $snmp_data_type)) {
        $type = 'remote_snmp';
    } else if (preg_match('/Gauge64/i', $snmp_data_type)) {
        $type = 'remote_snmp';
    } else if (preg_match('/Unsigned32/i', $snmp_data_type)) {
        $type = 'remote_snmp_inc';
    } else if (preg_match('/TimeTicks/i', $snmp_data_type)) {
        $type = 'remote_snmp';
    } else if (preg_match('/Opaque/i', $snmp_data_type)) {
        $type = 'remote_snmp_string';
    } else if (preg_match('/Counter64/i', $snmp_data_type)) {
        $type = 'remote_snmp_inc';
    } else if (preg_match('/UInteger32/i', $snmp_data_type)) {
        $type = 'remote_snmp';
    } else if (preg_match('/BIT STRING/i', $snmp_data_type)) {
        $type = 'remote_snmp_string';
    } else if (preg_match('/STRING/i', $snmp_data_type)) {
        $type = 'remote_snmp_string';
    } else {
        $type = 'remote_snmp_string';
    }

    if (!$type) {
        $type = 'remote_snmp';
    }

    $type_id = modules_get_type_id($type);

    return $type_id;
}
