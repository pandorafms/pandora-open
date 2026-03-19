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

if ((bool) is_ajax() === true) {
    include_once $config['homedir'].'/include/class/Prd.class.php';

    $getResource = (bool) get_parameter('getResource', false);
    $exportPrd = (bool) get_parameter('exportPrd', false);
    $deleteFile = (bool) get_parameter('deleteFile', false);

    $prd = new Prd();

    if ($getResource === true) {
        $type = (string) get_parameter('type', '');
        $result = false;

        $data = $prd->getOnePrdData($type);
        if (empty($data) === false) {
            $sql = sprintf(
                'SELECT %s FROM %s',
                reset($data['items']['value']).', '.reset($data['items']['show']),
                $data['items']['table']
            );
            $result = html_print_label_input_block(
                $data['label'],
                io_safe_output(
                    html_print_select_from_sql(
                        $sql,
                        'select_value',
                        '',
                        '',
                        '',
                        0,
                        true,
                        false,
                        true,
                        false,
                        false,
                        false,
                        GENERIC_SIZE_TEXT,
                        'w90p',
                    ),
                ),
                [
                    'div_style' => 'display: flex; flex-direction: column; width: 50%',
                    'div_id'    => 'resource_type',
                ],
            );
        }

        echo $result;
        return;
    }

    if ($exportPrd === true) {
        $type = (string) get_parameter('type', '');
        $value = (int) get_parameter('value', 0);
        $name = (string) get_parameter('name', '');
        $filename = (string) get_parameter('filename', '');

        try {
            $data = $prd->exportPrd($type, $value, $name);
        } catch (\Exception $e) {
            $data = '';
        }

        $return = [];

        if (empty($data) === false) {
            $filename_download = date('YmdHis').'-'.$type.'-'.$name.'.prd';
            $file = $config['attachment_store'].'/'.$filename;

            $file_pointer = fopen($file, 'a');
            if ($file_pointer !== false) {
                $write = fwrite($file_pointer, $data);

                if ($write === false) {
                    $return['error'] = -2;
                    unlink($config['attachment_store'].'/'.$filename);
                } else {
                    $return['name'] = $filename;
                    $return['name_download'] = $filename_download;
                }

                fclose($file_pointer);
            } else {
                $return['error'] = -1;
            }
        }

        echo json_encode($return);

        return;
    }

    if ($deleteFile === true) {
        $filename = (string) get_parameter('filename', '');

        unlink($config['attachment_store'].'/'.$filename);
    }
}
