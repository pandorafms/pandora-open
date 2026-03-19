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


/**
 * Starts clippy.
 *
 * @param string $sec2 Section.

 * @return void
 */
function clippy_start($sec2)
{
    global $config;

    if ($sec2 === false) {
        $sec2 = 'homepage';
    }

    $sec2 = str_replace('/', '_', $sec2);

    // Avoid some case the other parameters in the url.
    if (strstr($sec2, '&') !== false) {
        $chunks = explode('&', $sec2);
        $sec2 = $chunks[0];
    }

    if ($sec2 != 'homepage') {
        if (is_file('include/help/clippy/'.$sec2.'.php')) {
            include 'include/help/clippy/'.$sec2.'.php';

            $tours = clippy_start_page();
            clippy_write_javascript_helps_steps($tours);
        }

        // Add homepage for all pages for to show the "task sugestions".
        include 'include/help/clippy/homepage.php';

        $tours = clippy_start_page_homepage();
        clippy_write_javascript_helps_steps($tours);
    } else {
        include 'include/help/clippy/homepage.php';

        $tours = clippy_start_page_homepage();
        clippy_write_javascript_helps_steps($tours);
    }
}


/**
 * Undocumented function
 *
 * @return void
 */
function clippy_clean_help()
{
    set_cookie('clippy', null);
}


/**
 * Undocumented function
 *
 * @param something $tours Tour.
 *
 * @return void
 */
function clippy_write_javascript_helps_steps($tours)
{
    global $config;

    $first_step_by_default = false;
    if (isset($tours['first_step_by_default'])) {
        $first_step_by_default = $tours['first_step_by_default'];
    }

    // For the help context instead the clippy.
    $help_context = false;
    if (isset($tours['help_context'])) {
        $help_context = $tours['help_context'];
    }

    if ($help_context) {
        $name_obj_js_tour = '{clippy_obj}';
    } else {
        $name_obj_js_tour = 'intro';
    }

    $clippy = get_cookie('clippy', false);
    set_cookie('clippy', null);

    // Get the help steps from a task.
    $steps = null;
    if (isset($tours['tours'][$clippy])) {
        $steps = $tours['tours'][$clippy]['steps'];
    }

    if ($first_step_by_default) {
        if (empty($steps)) {
            // Get the first by default.
            $temp = reset($tours['tours']);
            $steps = $temp['steps'];
        }
    }

    if ($help_context) {
        foreach ($steps as $iterator => $step) {
            $init_step_context = false;
            if (isset($step['init_step_context'])) {
                $init_step_context = $step['init_step_context'];
            }

            if ($init_step_context) {
                unset($steps[$iterator]['init_step_context']);
                $steps[$iterator]['element'] = '{clippy}';
            }
        }
    }

    $conf = null;
    if (isset($tours['tours'][$clippy])) {
        $conf = $tours['tours'][$clippy]['conf'];
    }

    if ($first_step_by_default) {
        if (empty($conf)) {
            // Get the first by default.
            $temp = reset($tours['tours']);
            $conf = $temp['conf'];
        }
    }

    if (!empty($steps)) {
        foreach ($steps as $iterator => $element) {
            $steps[$iterator]['intro'] = "<div id='clippy_head_title'>".__('%s assistant', get_product_name()).'</div>'.$steps[$iterator]['intro'];
        }

        if (!empty($conf['name_obj_js_tour'])) {
            $name_obj_js_tour = $conf['name_obj_js_tour'];
        }

        $autostart = true;
        if (isset($conf['autostart'])) {
            $autostart = $conf['autostart'];
        }

        $other_js = '';
        if (!empty($conf['other_js'])) {
            $other_js = $conf['other_js'];
        }

        $exit_js = '';
        if (!empty($conf['exit_js'])) {
            $exit_js = $conf['exit_js'];
        }

        $complete_js = '';
        if (!empty($conf['complete_js'])) {
            $complete_js = $conf['complete_js'];
        }

        $show_bullets = 0;
        if (!empty($conf['show_bullets'])) {
            $show_bullets = (int) $conf['show_bullets'];
        }

        $show_step_numbers = 0;
        if (!empty($conf['show_step_numbers'])) {
            $show_step_numbers = (int) $conf['show_step_numbers'];
        }

        $doneLabel = __('End wizard');
        if (!empty($conf['done_label'])) {
            $doneLabel = $conf['done_label'];
        }

        $skipLabel = __('End wizard');
        if (!empty($conf['skip_label'])) {
            $skipLabel = $conf['skip_label'];
        }

        $help_context = false;
        ?>
        <script type="text/javascript">
            var <?php echo $name_obj_js_tour; ?> = null;
            
            $(document).ready(function() {
                <?php echo $name_obj_js_tour; ?> = introJs();
                
                <?php echo $name_obj_js_tour; ?>.setOptions({
                    steps: <?php echo json_encode($steps); ?>,
                    showBullets: 
        <?php
        if ($show_bullets) {
            echo 'true';
        } else {
            echo 'false';
        }
        ?>
     ,
                    showStepNumbers: 
        <?php
        if ($show_step_numbers) {
            echo 'true';
        } else {
            echo 'false';
        }
        ?>
     ,
                    nextLabel: "<?php echo __('Next &rarr;'); ?>",
                    prevLabel: "<?php echo __('&larr; Back'); ?>",
                    skipLabel: "<?php echo $skipLabel; ?>",
                    doneLabel: "<?php echo $doneLabel; ?>",
                    exitOnOverlayClick: false,
                    exitOnEsc: true, //false,
                })
                .oncomplete(function(value) {
                    <?php echo $complete_js; ?>;
                })
                .onexit(function(value) {
                    <?php echo $exit_js; ?>;
                    
                    exit = confirm("<?php echo __('Do you want to exit the help tour?'); ?>");
                    return exit;
                });
                
                <?php
                if (!empty($conf['next_help'])) {
                    ?>
                    clippy_set_help('<?php echo $conf['next_help']; ?>');
                    <?php
                }
                ?>
                
                <?php
                if ($autostart) {
                    echo $name_obj_js_tour;
                    ?>
                    .start();
                    <?php
                }
                ?>
            });
            
            <?php echo $other_js; ?>
        </script>
        <?php
    }
}


/**
 * Undocumented function
 *
 * @param string $help Help.
 *
 * @return void
 */
function clippy_context_help($help=null)
{
    global $config;

    if ($config['tutorial_mode'] == 'expert') {
        return;
    }

    $id = uniqid('id_');

    $return = '';

    include_once $config['homedir'].'/include/help/clippy/'.$help.'.php';

    ob_start();
    $function = 'clippy_'.$help;
    $tours = $function();
    clippy_write_javascript_helps_steps($tours);
    $code = ob_get_clean();

    $code = str_replace('{clippy}', '#'.$id, $code);
    $code = str_replace('{clippy_obj}', 'intro_'.$id, $code);

    if ($help === 'module_unknow') {
        $title = __('You have unknown modules in this agent.');
        $intro = __('Unknown modules are modules which receive data normally at least in one occassion, but at this time are not receving data. Please check our troubleshoot help page to help you determine why you have unknown modules.');
        $img = html_print_image(
            'images/info-warning.svg',
            true,
            [
                'class' => 'main_menu_icon invert_filter',
                'style' => 'margin-left: -25px;',
            ]
        );
    } else if ($help === 'interval_agent_min') {
        $clippy_interval_agent_min = clippy_interval_agent_min();
        $title = $clippy_interval_agent_min['tours']['interval_agent_min']['steps'][0]['title'];
        $intro = $clippy_interval_agent_min['tours']['interval_agent_min']['steps'][0]['intro'];
        $img   = $clippy_interval_agent_min['tours']['interval_agent_min']['steps'][0]['img'];
    } else if ($help === 'data_configuration_module') {
        $clippy_data_configuration_module = clippy_data_configuration_module();
        $title = $clippy_data_configuration_module['tours']['data_configuration_module']['steps'][0]['title'];
        $intro = $clippy_data_configuration_module['tours']['data_configuration_module']['steps'][0]['intro'];
        $img   = $clippy_data_configuration_module['tours']['data_configuration_module']['steps'][0]['img'];
    } else if ($help === 'modules_not_learning_mode') {
        $clippy_modules_not_learning_mode = clippy_modules_not_learning_mode();
        $title = $clippy_modules_not_learning_mode['tours']['modules_not_learning_mode']['steps'][0]['title'];
        $intro = $clippy_modules_not_learning_mode['tours']['modules_not_learning_mode']['steps'][0]['intro'];
        $img   = $clippy_modules_not_learning_mode['tours']['modules_not_learning_mode']['steps'][0]['img'];
    } else if ($help === 'agent_module_interval') {
        $clippy_agent_module_interval = clippy_agent_module_interval();
        $title = $clippy_agent_module_interval['tours']['agent_module_interval']['steps'][0]['title'];
        $intro = $clippy_agent_module_interval['tours']['agent_module_interval']['steps'][0]['intro'];
        $img   = $clippy_agent_module_interval['tours']['agent_module_interval']['steps'][0]['img'];
    } else {
        $img = html_print_image(
            'images/info-warning.svg',
            true,
            ['class' => 'main_menu_icon invert_filter']
        );
    }

    $return = $code.'<div id="'.$id.'" class="inline div-'.$help.'"><a onclick="show_'.$id.'();" href="javascript: void(0);" >'.$img.'</a></div>
        <script type="text/javascript">
        
        function show_'.$id.'() {
            confirmDialog({
                title: "'.$title.'",
                message: "'.$intro.'",
                strOKButton: "'.__('Close').'",
                hideCancelButton: true,
                size: 675,
            });
        }
        
        $(document).ready(function() {
            (function pulse_'.$id.'() {
                $("#'.$id.' img")
                    .delay(100)
                    .animate({\'opacity\': 1})
                    .delay(400)
                    .animate({\'opacity\': 0}, pulse_'.$id.');
            })();
            
            //$("#'.$id.' img").pulsate ();
        });
        </script>
        ';

    return $return;
}
