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

// Load global vars.
global $config;

require_once '../../include/config.php';
require_once '../../include/auth/mysql.php';
require_once '../../include/functions.php';
require_once '../../include/functions_db.php';
require_once '../../include/functions_events.php';
require_once '../../include/functions_agents.php';
require_once '../../include/functions_groups.php';

$config['id_user'] = $_SESSION['id_usuario'];

if (! check_acl($config['id_user'], 0, 'ER')
    && ! check_acl($config['id_user'], 0, 'EW')
    && ! check_acl($config['id_user'], 0, 'EM')
) {
    exit;
}

// Loading l10n tables, because of being invoked not through index.php.
$l10n = null;
if (file_exists($config['homedir'].'/include/languages/'.$user_language.'.mo')) {
    $cfr = new CachedFileReader(
        $config['homedir'].'/include/languages/'.$user_language.'.mo'
    );
    $l10n = new gettext_reader($cfr);
    $l10n->load_tables();
}

$column_names = [
    'id_evento',
    'evento',
    'timestamp',
    'estado',
    'event_type',
    'utimestamp',
    'id_agente',
    'agent_name',
    'id_usuario',
    'id_grupo',
    'id_agentmodule',
    'id_alert_am',
    'criticity',
    'tags',
    'source',
    'id_extra',
    'critical_instructions',
    'warning_instructions',
    'unknown_instructions',
    'owner_user',
    'ack_utimestamp',
    'custom_data',
    'data',
    'module_status',
];

    $fields = [
        'te.id_evento',
        'te.evento',
        'te.timestamp',
        'te.estado',
        'te.event_type',
        'te.utimestamp',
        'te.id_agente',
        'ta.alias as agent_name',
        'te.id_usuario',
        'te.id_grupo',
        'te.id_agentmodule',
        'am.nombre as module_name',
        'te.id_alert_am',
        'te.criticity',
        'te.tags',
        'te.source',
        'te.id_extra',
        'te.critical_instructions',
        'te.warning_instructions',
        'te.unknown_instructions',
        'te.owner_user',
        'te.ack_utimestamp',
        'te.custom_data',
        'te.data',
        'te.module_status',
        'tg.nombre as group_name',
    ];


$now = date('Y-m-d');

// Download header.
header('Content-type: text/txt');
header('Content-Disposition: attachment; filename="export_events_'.$now.'.csv"');
setDownloadCookieToken();

try {
    $fb64 = get_parameter('fb64', null);
    $plain_filter = base64_decode($fb64);
    $filter = json_decode($plain_filter, true);
    if (json_last_error() != JSON_ERROR_NONE) {
        throw new Exception('Invalid filter. ['.$plain_filter.']');
    }

    if (key_exists('server_id', $filter) === true && is_array($filter['server_id']) === false) {
        $filter['server_id'] = explode(',', $filter['server_id']);
    }

    $filter['csv_all'] = true;

    $names = events_get_column_names($column_names);

    // Dump headers.
    foreach ($names as $n) {
        echo csv_format_delimiter(io_safe_output($n)).$config['csv_divider'];
    }

    

    echo chr(13);

    // Dump events.
    $events_per_step = 1000;
    $step = 0;
    while (1) {
        $events = events_get_all(
            $fields,
            $filter,
            (($step++) * $events_per_step),
            $events_per_step,
            'desc',
            'timestamp'
        );

        if ($events === false || empty($events) === true) {
            break;
        }

        foreach ($events as $row) {
            foreach ($column_names as $val) {
                $key = $val;
                if ($val == 'id_grupo') {
                    $key = 'group_name';
                } else if ($val == 'id_agentmodule') {
                    $key = 'module_name';
                }

                switch ($key) {
                    case 'module_status':
                        echo csv_format_delimiter(
                            events_translate_module_status(
                                $row[$key]
                            )
                        );
                    break;

                    case 'event_type':
                        echo csv_format_delimiter(
                            events_translate_event_type(
                                $row[$key]
                            )
                        );
                    break;

                    case 'criticity':
                        echo csv_format_delimiter(
                            events_translate_event_criticity(
                                $row[$key]
                            )
                        );
                    break;

                    case 'custom_data':
                        $custom_data_array = json_decode(
                            $row[$key],
                            true
                        );

                        $custom_data = '';
                        $separator = ($config['csv_divider'] === ';') ? ',' : ';';

                        if ($custom_data_array !== null) {
                            array_walk(
                                $custom_data_array,
                                function (&$value, $field) use ($separator) {
                                    if (is_array($value) === true) {
                                        $value = '['.implode($separator, $value).']';
                                    }

                                    $value = $field.'='.$value;
                                }
                            );

                            $custom_data = implode($separator, $custom_data_array);
                        }

                        echo csv_format_delimiter(io_safe_output($custom_data));
                    break;

                    case 'timestamp':
                        $target_timezone = date_default_timezone_get();
                        $utimestamp = $row['utimestamp'];
                        $datetime = new DateTime("@{$utimestamp}");
                        $new_datetime_zone = new DateTimeZone($target_timezone);
                        $datetime->setTimezone($new_datetime_zone);
                        $formatted_date = $datetime->format('Y-m-d H:i:s');

                        echo csv_format_delimiter($formatted_date);
                    break;

                    default:
                        echo csv_format_delimiter(io_safe_output($row[$key]));
                    break;
                }

                echo $config['csv_divider'];
            }

            

            echo chr(13);
        }
    }
} catch (Exception $e) {
    echo 'ERROR'.chr(13);
    echo $e->getMessage();
    exit;
}

exit;
