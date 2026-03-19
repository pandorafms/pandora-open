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

// Includes.
require_once $config['homedir'].'/include/class/HTML.class.php';

global $config;

if (empty($error) === false) {
    ui_print_error_message($error);
}

if (empty($customFields) === true) {
    ui_print_info_message(
        [
            'no_close' => true,
            'message'  => __('Incidence type not fields'),
        ]
    );
} else {
    $output = '<div class="incidence-type-custom-fields">';
    foreach ($customFields as $field) {
        $options = [
            'name'     => 'custom-fields['.$field['idIncidenceTypeField'].']',
            'required' => $field['isRequired'],
            'return'   => true,
        ];

        $class = '';

        switch ($field['type']) {
            case 'COMBO':
                $options['type'] = 'select';
                $fieldsValues = explode(',', $field['comboValue']);
                $options['fields'] = array_combine($fieldsValues, $fieldsValues);
                $options['selected'] = ($fieldsData[$field['idIncidenceTypeField']] ?? null);
            break;

            case 'TEXT':
                $options['value'] = ($fieldsData[$field['idIncidenceTypeField']] ?? null);
                $options['type'] = 'text';
            break;

            case 'CHECKBOX':
                $options['checked'] = ($fieldsData[$field['idIncidenceTypeField']] ?? null);
                $options['type'] = 'checkbox';
            break;

            case 'DATE':
                $options['value'] = ($fieldsData[$field['idIncidenceTypeField']] ?? null);
                $options['type'] = 'text';
            break;

            case 'NUMERIC':
                $options['value'] = ($fieldsData[$field['idIncidenceTypeField']] ?? null);
                $options['type'] = 'number';
            break;

            case 'TEXTAREA':
                $options['value'] = ($fieldsData[$field['idIncidenceTypeField']] ?? null);
                $options['type'] = 'textarea';
                $options['rows'] = 4;
                $options['columns'] = 0;
                $class = 'incidence-type-custom-fields-textarea';
            break;

            default:
                // Not posible.
            break;
        }

        $output .= html_print_label_input_block(
            $field['label'],
            html_print_input($options),
            ['div_class' => $class]
        );
    }

    $output .= '</div>';

    echo $output;
}
