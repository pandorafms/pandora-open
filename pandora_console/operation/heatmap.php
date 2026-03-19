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
// Login check.
check_login();

$agent_a = (bool) check_acl($config['id_user'], 0, 'AR');
$agent_w = (bool) check_acl($config['id_user'], 0, 'AW');

if ($agent_a === false && $agent_w === false) {
    db_pandora_audit('ACL Violation', 'Trying to access agent main list view');
    include 'general/noaccess.php';

    return;
}

require_once $config['homedir'].'/include/class/Heatmap.class.php';
use PandoraFMS\Heatmap;

$pure = (bool) get_parameter('pure', false);
$type = get_parameter('type', 0);
$randomId = get_parameter('randomId', null);
$refresh = get_parameter('refresh', SECONDS_5MINUTES);
$height = get_parameter('height', 0);
$width = get_parameter('width', 0);
$search = get_parameter('search', '');
$filter = get_parameter('filter', []);
$hash = get_parameter('auth_hash', '');

if (is_array($filter) === false) {
    $filter = explode(',', $filter);
}

$group_sent = (bool) get_parameter('group_sent');
if ($group_sent === true) {
    $group = (int) get_parameter('group');
} else {
    $group = (int) get_parameter('group', true);
}

$dashboard = (bool) get_parameter('dashboard', false);

$is_ajax = is_ajax();
if ($is_ajax === false && $pure === false) {
    $viewtab['config'] = '<a id="config" href="">'.html_print_image(
        'images/configuration@svg.svg',
        true,
        [
            'title' => __('Config'),
            'class' => 'main_menu_icon invert_filter',
        ]
    ).'</a>';

    $url = sprintf(
        'index.php?sec=view&sec2=operation/heatmap&pure=1&type=%s&refresh=%s&search=%s&filter=%s',
        $type,
        $refresh,
        $search,
        implode(',', $filter)
    );

    $viewtab['full_screen'] = '<a id="full_screen" href="'.$url.'">'.html_print_image(
        'images/fullscreen@svg.svg',
        true,
        [
            'title' => __('Full screen'),
            'class' => 'main_menu_icon invert_filter',
        ]
    ).'</a>';

    $header_name = __('Heatmap view');
    switch ($type) {
        case 3:
            $header_name .= ' - '.__('Agents');
        break;

        case 2:
            if (current($filter) == 0) {
                $header_name .= ' - '.__('Module group').': '.__('Not assigned');
            } else {
                $header_name .= ' - '.__('Module group').': '.modules_get_modulegroup_name(current($filter));
            }
        break;

        case 1:
            $tags_name = '';
            foreach ($filter as $key => $tag) {
                $tags_name .= tags_get_name($tag).', ';
            }

            $tags_name = trim($tags_name, ', ');
            $header_name .= ' - '.__('Tag').': '.$tags_name;
        break;

        case 0:
        default:
            if (current($filter) == 0) {
                $header_name .= ' - '.__('Group').': '.__('All');
            } else {
                $header_name .= ' - '.__('Group').': '.groups_get_name(current($filter));
            }
        break;
    }

    // Header.
    ui_print_standard_header(
        $header_name,
        '',
        false,
        '',
        false,
        $viewtab,
        [
            [
                'link'  => '',
                'label' => __('Monitoring'),
            ],
            [
                'link'  => '',
                'label' => __('Views'),
            ],
        ]
    );
}

if ($is_ajax === false && $pure === true) {
    // Floating menu - Start.
    echo '<div id="heatmap-controls" class="zindex999" style="max-height: 85px">';

    echo '<div id="menu_tab" method="post">';
    echo '<ul class="mn white-box-content box-shadow flex-row">';

    // Name.
    echo '<li class="nomn mx_height85">';

    html_print_div(
        [
            'class'   => 'heatmap-title',
            'content' => 'Heatmap',
        ]
    );

    echo '</li>';

    // Countdown.
    echo '<li class="nomn mx_height85">';
    echo '<div class="heatmap-refr">';

    echo '<div id="heatmap-refr-form">';
    echo '<form id="refr-form" class="refr-form" method="post">';
    echo __('Refresh').':';
    echo html_print_select(
        [
            '30'                      => __('30 seconds'),
            (string) SECONDS_1MINUTE  => __('1 minute'),
            '180'                     => __('3 minutes'),
            (string) SECONDS_5MINUTES => __('5 minutes'),
        ],
        'refresh-control',
        $refresh,
        '',
        '',
        0,
        true,
        false,
        false
    );
    // Hidden.
    html_print_input_hidden('refresh', $refresh);
    html_print_input_hidden('type', $type);
    html_print_input_hidden('search', $search);
    html_print_input_hidden('filter', implode(',', $filter));
    html_print_input_hidden('dashboard', $dashboard);
    echo '</form>';
    echo '</div>';
    echo '</div>';
    echo '</li>';

    // Quit fullscreen.
    echo '<li class="nomn">';
    $urlNoFull = sprintf(
        'index.php?sec=view&sec2=operation/heatmap&pure=0&type=%s&refresh=%s&search=%s&filter=%s',
        $type,
        $refresh,
        $search,
        implode(',', $filter)
    );

    echo '<a href="'.$urlNoFull.'">';
    echo html_print_image(
        'images/exit_fullscreen@svg.svg',
        true,
        [
            'title' => __('Back to normal mode'),
            'class' => 'main_menu_icon invert_filter',
        ]
    );
    echo '</a>';
    echo '</li>';

    echo '</ul>';

    // Hidden.
    echo '</div>';

    echo '</div>';
}

// Control call flow.
try {
    // Heatmap construct.
    $heatmap = new Heatmap($type, $filter, $randomId, $refresh, $width, $height, $search, $group, $dashboard, $hash);
} catch (Exception $e) {
    if (is_ajax() === true) {
        echo json_encode(['error' => '[Heatmap]'.$e->getMessage() ]);
        exit;
    } else {
        echo '[Heatmap]'.$e->getMessage();
    }

    // Stop this execution, but continue 'globally'.
    return;
}

// AJAX controller.
if ($is_ajax === true) {
    $method = get_parameter('method');

    if (method_exists($heatmap, $method) === true) {
        if ($heatmap->ajaxMethod($method) === true) {
            $heatmap->{$method}();
        } else {
            echo 'Unavailable method';
        }
    } else {
        echo 'Method not found';
    }

    // Stop any execution.
    return;
} else {
    // Run.
    $heatmap->run();

    // Dialog.
    echo '<div id="config_dialog" style="padding:15px" class="invisible"></div>';
}

?>

<script type="text/javascript">
    $(document).ready(function() {
        $('#config').click(function(e) {
            e.preventDefault();
            $('#config_dialog').empty();
            $("#config_dialog").dialog({
                resizable: false,
                draggable: false,
                modal: true,
                closeOnEscape: true,
                height: 500,
                width: 330,
                title: '<?php echo __('Config'); ?>',
                position: {
                    my: "right top",
                    at: "right bottom",
                    of: $('#config')
                },
                overlay: {
                    opacity: 0.5,
                    background: "black"
                },
                buttons:[{
                    class: 'ui-widget ui-state-default ui-corner-all ui-button-text-only sub upd submit-next',
                    text: "<?php echo __('Show'); ?>",
                    click: function() {
                        // Dialog close.
                        $(this).dialog("close");
                        $("#form_dialog").submit();
                    }
                }],
                open: function() {
                    $.ajax({
                        type: 'GET',
                        url: '<?php echo ui_get_full_url('ajax.php', false, false, false); ?>',
                        data: {
                            page: "include/ajax/heatmap.ajax",
                            getFilters: 1,
                            type: '<?php echo $type; ?>',
                            refresh: '<?php echo $refresh; ?>',
                            search: '<?php echo $search; ?>',
                            group: '<?php echo $group; ?>',
                        },
                        dataType: 'html',
                        success: function(data) {
                            $('#config_dialog').append(data);
                            $('#type').on('change', function() {
                                $.ajax({
                                    type: 'GET',
                                    url: '<?php echo ui_get_full_url('ajax.php', false, false, false); ?>',
                                    data: {
                                        page: "include/ajax/heatmap.ajax",
                                        getFilterType: 1,
                                        type: this.value,
                                        filter: <?php echo json_encode($filter); ?>
                                    },
                                    dataType: 'html',
                                    success: function(data) {
                                        $('#filter_type').remove();
                                        $('#form_dialog').append(data);
                                    }
                                });
                            });

                            $('#type').trigger('change');
                        }
                    });
                }
            });
        });

        const controls = document.getElementById('heatmap-controls');
        autoHideElement(controls, 1000);

        $('#refresh-control').change(function(e) {
            $('#hidden-refresh').val(this.value);
            $('#refr-form').submit();
        });
    });
</script>
