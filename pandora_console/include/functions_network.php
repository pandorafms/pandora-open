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

// Write here requires and definitions.


/**
 * Get the possible actions on networking.
 *
 * @param boolean $network True if network. False if netflow.
 *
 * @return array With the actions to print in a select.
 */
function network_get_report_actions($network=true)
{
    $common_actions = [
        'listeners' => __('Top listeners'),
        'talkers'   => __('Top talkers'),
    ];

    if ($network) {
        return $common_actions;
    }

    return array_merge(
        $common_actions,
        [
            'tcp' => __('Top TCP protocols'),
            'udp' => __('Top UDP protocols'),
        ]
    );
}


/**
 * Print the header of the network
 *
 * @param string $title       Title of header.
 * @param string $order       Current ordering.
 * @param string $selected    Selected order.
 * @param array  $hidden_data All the data to hide into the button.
 *
 * @return string With HTML data.
 */
function network_print_explorer_header(
    $title,
    $order,
    $selected,
    $hidden_data
) {
    $cell = '<div class="flex_center">';
    $cell .= $title;
    $cell .= html_print_link_with_params(
        'images/arrow@svg.svg',
        array_merge($hidden_data, ['order_by' => $order]),
        'image',
        'rotate: 270deg; width: 20px; margin-top: 4px;'.(($selected === $order) ? '' : 'opacity: 0.5')
    );
    $cell .= '</div>';

    return $cell;
}


/**
 * Alias for format_for_graph to print bytes.
 *
 * @param integer $value Value to parse like bytes.
 *
 * @return string Number parsed.
 */
function network_format_bytes($value)
{
    if (!isset($value)) {
        $value = 0;
    }

    $value = (int) $value;

    return format_for_graph(
        $value,
        2,
        '.',
        ',',
        1024,
        'B'
    );
}


/**
 * Return the array to pass to constructor to NetworkMap.
 *
 * @param array $nodes     Nodes data structure.
 * @param array $relations Relations data structure.
 *
 * @return array To be passed to NetworMap class.
 */
function network_general_map_configuration($nodes, $relations)
{
    return [
        'nodes'           => $nodes,
        'relations'       => $relations,
        'pure'            => 1,
        'no_pandora_node' => 1,
        'no_popup'        => 1,
        'map_options'     => [
            'generation_method' => LAYOUT_SPRING1,
            'map_filter'        => [
                'node_radius'     => 40,
                'node_sep'        => 7,
                'node_separation' => 5,
            ],
        ],
    ];
}


/**
 * Added a relation to relations array
 *
 * @param array   $relations Relations array (passed by reference).
 * @param integer $parent    Parent id (numeric).
 * @param integer $child     Child id (numeric).
 * @param string  $text      Text to show at the end of edge (optional).
 *
 * @return void Relations will be modified (passed by reference).
 */
function network_init_relation_map(&$relations, $parent, $child, $text='')
{
    $index = $parent.'-'.$child;
    $relations[$index] = [
        'id_parent'   => $parent,
        'parent_type' => NODE_GENERIC,
        'child_type'  => NODE_GENERIC,
        'id_child'    => $child,
        'link_color'  => '#82B92E',
    ];

    if (empty($text) === false) {
        $relations[$index]['text_start'] = $text;
    }
}


/**
 * Initialize a node structure to NetworkMap class.
 *
 * @param string $name Node name.
 *
 * @return array Node data structure.
 */
function network_init_node_map($name)
{
    return [
        'name'   => $name,
        'type'   => NODE_GENERIC,
        'width'  => 40,
        'height' => 40,
        'status' => 0,
    ];
}
