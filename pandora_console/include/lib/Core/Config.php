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

// Begin.
namespace PandoraFMS\Core;

require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../functions_config.php';

/**
 * Config class to operate console configuration.
 */
final class Config
{

    /**
     * History database settings (tconfig).
     *
     * @var array
     */
    private static $settings = [];


    /**
     * Load history database settings.
     *
     * @return void
     */
    private static function loadHistoryDBSettings()
    {
        global $config;

        if ((bool) $config['history_db_enabled'] === false) {
            return;
        }

        // Connect if needed.
        if (isset($config['history_db_connection']) === false
            || $config['history_db_connection'] === false
        ) {
            ob_start();

            $link = mysqli_init();
            $link->options(MYSQLI_OPT_CONNECT_TIMEOUT, 2);
            $rc = mysqli_real_connect(
                $link,
                $config['history_db_host'],
                $config['history_db_user'],
                io_output_password($config['history_db_pass']),
                $config['history_db_name'],
                (int) $config['history_db_port']
            );

            if ($rc === false) {
                $config['history_db_connection'] = false;
            } else {
                $config['history_db_connection'] = db_connect(
                    $config['history_db_host'],
                    $config['history_db_name'],
                    $config['history_db_user'],
                    io_output_password($config['history_db_pass']),
                    (int) $config['history_db_port'],
                    false
                );
            }

            ob_get_clean();
        }

        if (isset($config['history_db_connection']) === true
            && $config['history_db_connection'] !== false
        ) {
            $data = \db_get_all_rows_sql(
                'SELECT * FROM `tconfig`',
                false,
                false,
                $config['history_db_connection']
            );
        }

        if (is_array($data) !== true) {
            return [];
        }

        self::$settings = array_reduce(
            $data,
            function ($carry, $item) {
                $carry[$item['token']] = $item['value'];
                return $carry;
            },
            []
        );
    }


    /**
     * Retrieve configuration token.
     *
     * @param string  $token      Token to retrieve.
     * @param mixed   $default    Default value if not found.
     * @param boolean $history_db Search for token in history_db.
     *
     * @return mixed Configuration token.
     */
    public static function get(
        string $token,
        $default=null,
        bool $history_db=false
    ) {
        if ($history_db === true) {
            self::loadHistoryDBSettings();

            if (isset(self::$settings[$token]) === true) {
                return self::$settings[$token];
            }

            return $default;
        } else {
            global $config;

            if (isset($config[$token]) === true) {
                return $config[$token];
            }
        }

        return $default;

    }


    /**
     * Set configuration token.
     *
     * @param string  $token      Token to set.
     * @param mixed   $value      Value to be.
     * @param boolean $history_db Save to history_db settings.
     *
     * @return boolean Success or not.
     */
    public static function set(string $token, $value, bool $history_db=false)
    {
        global $config;

        $rs = false;

        if ($history_db !== false) {
            if (self::get($token, null, $history_db) === null) {
                // Create.
                $rs = \db_process_sql(
                    sprintf(
                        'INSERT INTO `tconfig` (`token`, `value`)
                         VALUES ("%s", "%s")',
                        $token,
                        $value
                    ),
                    'affected_rows',
                    $config['history_db_connection']
                );
            } else {
                // Update.
                $rs = \db_process_sql(
                    sprintf(
                        'UPDATE `tconfig`
                         SET `value`= "%s"
                        WHERE `token` = "%s"',
                        $value,
                        $token
                    ),
                    'affected_rows',
                    $config['history_db_connection']
                );
            }
        } else {
            $rs = \config_update_value($token, $value);
        }

        return ($rs !== false);
    }


}
