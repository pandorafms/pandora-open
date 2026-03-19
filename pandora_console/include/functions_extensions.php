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
 * @package    Include
 * @subpackage Extensions
 */

$extension_file = '';


/**
 * Callback function for extensions in the console
 *
 * @param string $filename with contents of the extension
 */
function extensions_call_main_function($filename)
{
    global $config;

    $extension = &$config['extensions'][$filename];
    if ($extension['main_function'] != '') {
        $params = [];
        call_user_func_array(
            $extension['main_function'],
            array_values(($params ?? []))
        );
    }
}


/**
 * Callback function for godmode extensions
 *
 * @param string $filename File with extension contents
 */
function extensions_call_godmode_function($filename)
{
    global $config;

    $extension = &$config['extensions'][$filename];
    if ($extension['godmode_function'] != '') {
        $params = [];
        call_user_func_array(
            $extension['godmode_function'],
            array_values(($params ?? []))
        );
    }
}


/**
 * Callback login function for extensions
 */
function extensions_call_login_function()
{
    global $config;

    $params = [];
    foreach ($config['extensions'] as $extension) {
        if ($extension['login_function'] == '') {
            continue;
        }

        call_user_func_array(
            $extension['login_function'],
            array_values(($params ?? []))
        );
    }
}


/**
 * Checks if the current page is an extension
 *
 * @param string $page To check
 */
function extensions_is_extension($page)
{
    global $config;

    $return = false;

    $filename = basename($page);

    $return = isset($config['extensions'][$filename]);

    return $return;
}


/**
 * Scan the EXTENSIONS_DIR for search
 * the files extensions.
 *
 */
function extensions_get_extensions($rel_path='')
{
    $dir = $rel_path.EXTENSIONS_DIR;
    $handle = false;

    if (file_exists($dir)) {
        $handle = @opendir($dir);
    }

    if (empty($handle)) {
        return;
    }

    $file = readdir($handle);
    $extensions = [];
    $ignores = [
        '.',
        '..',
    ];
    while ($file !== false) {
        if (in_array($file, $ignores)) {
            $file = readdir($handle);
            continue;
        }

        $filepath = realpath($dir.'/'.$file);
        if (! is_readable($filepath) || is_dir($filepath) || ! preg_match('/.*\.php$/', $filepath)) {
            $file = readdir($handle);
            continue;
        }

        if ($file == 'update_manager.php') {
            continue;
        }

        $extension['file'] = $file;
        $extension['operation_menu'] = '';
        $extension['godmode_menu'] = '';
        $extension['main_function'] = '';
        $extension['godmode_function'] = '';
        $extension['login_function'] = '';
        $extension['dir'] = $dir;
        $extensions[$file] = $extension;
        $file = readdir($handle);
    }

    if (isset($extensions['ipam.php']) === true) {
        unset($extensions['ipam.php']);
    }

    if (isset($extensions['translate_string.php']) === true) {
        unset($extensions['translate_string.php']);
    }

    if (isset($extensions['files_repo.php']) === true) {
        unset($extensions['files_repo.php']);
    }

    return $extensions;
}


/**
 * @brief Check if an extension is enabled
 *
 * @param  string Extension name (ended with .php)
 * @return True if enabled
 */
function extensions_is_enabled_extension($name)
{
    global $config;
    return isset($config['extensions'][$name])
        || isset($config['extensions'][$name.'.php']);
}


/**
 * Get disabled extensions
 */
function extensions_get_disabled_extensions()
{
    global $config;

    $extensions = [];

    $dirs = [
        'open'       => EXTENSIONS_DIR.'/disabled',
    ];

    foreach ($dirs as $type => $dir) {
        $handle = false;

        if (file_exists($dir)) {
            $handle = @opendir($dir);
        }

        if (empty($handle)) {
            continue;
        }

        $ignores = [
            '.',
            '..',
        ];

        $file = readdir($handle);
        while ($file !== false) {
            if (in_array($file, $ignores)) {
                $file = readdir($handle);
                continue;
            }

            $filepath = realpath($dir.'/'.$file);
            if (! is_readable($filepath) || is_dir($filepath) || ! preg_match('/.*\.php$/', $filepath)) {
                $file = readdir($handle);
                continue;
            }

            // $content = file_get_contents($filepath);
            $content = '';

            $data = [];

            $data['operation_menu'] = false;
            if (preg_match("/<?php(\n|.)*extensions_add_operation_menu_option(\n|.)*?>/", $content)) {
                $data['operation_menu'] = true;
            }

            $data['godmode_menu'] = false;
            if (preg_match('/<\?php(\n|.)*extensions_add_godmode_menu_option(\n|.)*\?>/', $content)) {
                $data['godmode_menu'] = true;
            }

            $data['operation_function'] = false;
            if (preg_match('/<\?php(\n|.)*extensions_add_main_function(\n|.)*\?>/', $content)) {
                $data['operation_function'] = true;
            }

            $data['login_function'] = false;
            if (preg_match('/<\?php(\n|.)*extensions_add_login_function(\n|.)*\?>/', $content)) {
                $data['login_function'] = true;
            }

            $data['extension_ope_tab'] = false;
            if (preg_match('/<\?php(\n|.)*extensions_add_opemode_tab_agent(\n|.)*\?>/', $content)) {
                $data['extension_ope_tab'] = true;
            }

            $data['extension_god_tab'] = false;
            if (preg_match('/<\?php(\n|.)*extensions_add_godmode_tab_agent(\n|.)*\?>/', $content)) {
                $data['extension_god_tab'] = true;
            }

            $data['godmode_function'] = false;
            if (preg_match('/<\?php(\n|.)*extensions_add_godmode_function(\n|.)*\?>/', $content)) {
                $data['godmode_function'] = true;
            }

            $data['enabled'] = false;

            $extensions[$file] = $data;

            $file = readdir($handle);
        }
    }

    return $extensions;
}


/**
 * Get info of all extensions (enabled/disabled)
 */
function extensions_get_extension_info()
{
    global $config;

    $return = [];

    foreach ($config['extensions'] as $extension) {
        $data = [];
        $data['godmode_function'] = false;
        if (!empty($extension['godmode_function'])) {
            $data['godmode_function'] = true;
        }

        $data['godmode_menu'] = false;
        if (!empty($extension['godmode_menu'])) {
            $data['godmode_menu'] = true;
        }

        $data['operation_function'] = false;
        if (!empty($extension['main_function'])) {
            $data['operation_function'] = true;
        }

        $data['operation_menu'] = false;
        if (!empty($extension['operation_menu'])) {
            $data['operation_menu'] = true;
        }

        $data['login_function'] = false;
        if (!empty($extension['login_function'])) {
            $data['login_function'] = true;
        }

        $data['extension_ope_tab'] = false;
        if (!empty($extension['extension_ope_tab'])) {
            $data['extension_ope_tab'] = true;
        }

        $data['extension_god_tab'] = false;
        if (!empty($extension['extension_god_tab'])) {
            $data['extension_god_tab'] = true;
        }

        $data['enabled'] = true;

        $return[$extension['file']] = $data;
    }

    $return = ($return + extensions_get_disabled_extensions());

    return $return;
}


/**
 * Load all extensions
 *
 * @param array $extensions
 */
function extensions_load_extensions($process_login)
{
    global $config;
    global $extension_file;

    foreach ($config['extensions'] as $extension) {
        $extension_file = $extension['file'];
        $path_extension = realpath($extension['dir'].'/'.$extension_file);

        // --------------------------------------------------------------
        //
        // PHP BUG
        //
        // #66518     need some exceptions for php's include or require
        //
        // https://bugs.php.net/bug.php?id=66518
        // --------------------------------------------------------------
        // ~ if ($process_login) {
            // ~ //Check the syntax for avoid PHP errors
            // ~ $output = null;
            // ~ $return_var = null;
            // ~ exec('php -l ' . $path_extension, $output, $return_code);
            // ~ if ($return_code !== 0) {
                // ~ // There is a error.
                // ~
                // ~ set_pandora_error_for_header(
                    // ~ __('There are some errors in the PHP file of extension %s .', $extension_file));
            // ~ }
            // ~ else {
                // ~ require_once($path_extension);
            // ~ }
        // ~ }
        // ~ else {
        try {
            include_once $path_extension;
        }

        // PHP 7
        catch (Throwable $e) {
        }

        // PHP 5
        catch (Exception $e) {
        }

        // ~ }
    }
}


/**
 * This function adds a link to the extension with the given name in Operation menu.
 *
 * @param string name Name of the extension in the Operation menu
 * @param string fatherId Id of the parent menu item for the current extension
 * @param string subfatherId Id of the parent submenu item for the current extension
 * @param string icon Path to the icon image (18x18 px). If this parameter is blank then predefined icon will be used
 */
function extensions_add_operation_menu_option($name, $fatherId=null, $icon=null, $version='N/A', $subfatherId=null, $acl='AR')
{
    global $config;
    global $extension_file;

    /*
        $config['extension_file'] is set in extensions_load_extensions(),
        since that function must be called before any function the extension
        call, we are sure it will be set.
    */
    $option_menu['name'] = $name;
    $option_menu['acl'] = $acl;
    $extension = &$config['extensions'][$extension_file];

    $option_menu['sec2'] = $extension['dir'].'/'.mb_substr(($extension_file ?? ''), 0, -4);
    $option_menu['fatherId'] = $fatherId;
    $option_menu['subfatherId'] = $subfatherId;
    $option_menu['icon'] = $icon;
    $option_menu['version'] = $version;

    $extension['operation_menu'] = $option_menu;
}


/**
 * This function adds a link to the extension with the given name in Godmode menu.
 *
 * @param string name Name of the extension in the Godmode menu
 * @param string acl User ACL level required to see this extension in the godmode menu
 * @param string fatherId Id of the parent menu item for the current extension
 * @param string subfatherId Id of the parent submenu item for the current extension
 * @param string icon Path to the icon image (18x18 px). If this parameter is blank then predefined icon will be used
 */
function extensions_add_godmode_menu_option($name, $acl, $fatherId=null, $icon=null, $version='N/A', $subfatherId=null)
{
    global $config;
    global $extension_file;

    /*
        $config['extension_file'] is set in extensions_load_extensions(),
        since that function must be called before any function the extension
    call, we are sure it will be set. */
    $option_menu['acl'] = $acl;
    $option_menu['name'] = $name;
    $extension = &$config['extensions'][$extension_file];
    $option_menu['sec2'] = $extension['dir'].'/'.mb_substr($extension_file, 0, -4);
    $option_menu['fatherId'] = $fatherId;
    $option_menu['subfatherId'] = $subfatherId;
    $option_menu['icon'] = $icon;
    $option_menu['version'] = $version;
    $extension['godmode_menu'] = $option_menu;
}


/**
 * Add in the header tabs in Godmode agent menu the extension tab.
 *
 * @param tabId Id of the extension tab
 * @param tabName Name of the extension tab
 * @param tabIcon Path to the image icon
 * @param tabFunction Name of the function to execute when this extension is called
 */
function extensions_add_godmode_tab_agent($tabId, $tabName, $tabIcon, $tabFunction, $version='N/A', $acl='AW')
{
    global $config;
    global $extension_file;

    $extension = &$config['extensions'][$extension_file];
    $extension['extension_god_tab'] = [];
    $extension['extension_god_tab']['id'] = $tabId;
    $extension['extension_god_tab']['name'] = $tabName;
    $extension['extension_god_tab']['icon'] = $tabIcon;
    $extension['extension_god_tab']['function'] = $tabFunction;
    $extension['extension_god_tab']['version'] = $version;
    $extension['extension_ope_tab']['acl'] = $acl;
}


/**
 * Add in the header tabs in Operation agent menu the extension tab.
 *
 * @param tabId Id of the extension tab
 * @param tabName Name of the extension tab
 * @param tabIcon Path to the image icon
 * @param tabFunction Name of the function to execute when this extension is called
 */
function extensions_add_opemode_tab_agent($tabId, $tabName, $tabIcon, $tabFunction, $version='N/A', $acl='AR')
{
    global $config;
    global $extension_file;

    $extension = &$config['extensions'][$extension_file];
    $extension['extension_ope_tab'] = [];
    $extension['extension_ope_tab']['id'] = $tabId;
    $extension['extension_ope_tab']['name'] = $tabName;
    $extension['extension_ope_tab']['icon'] = $tabIcon;
    $extension['extension_ope_tab']['function'] = $tabFunction;
    $extension['extension_ope_tab']['version'] = $version;
    $extension['extension_ope_tab']['acl'] = $acl;
}


/**
 * Add the function to call when user clicks on the Operation menu link
 *
 * @param string $function_name Callback function name
 */
function extensions_add_main_function($function_name)
{
    global $config;
    global $extension_file;

    $extension = &$config['extensions'][$extension_file];
    $extension['main_function'] = $function_name;
}


/**
 * Add the function to call when user clicks on the Godmode menu link
 *
 * @param string $function_name Callback function name
 */
function extensions_add_godmode_function($function_name)
{
    global $config;
    global $extension_file;

    $extension = &$config['extensions'][$extension_file];
    $extension['godmode_function'] = $function_name;
}


/**
 * Adds extension function when user login on Pandora console
 *
 * @param string $function_name Callback function name
 */
function extensions_add_login_function($function_name)
{
    global $config;
    global $extension_file;

    $extension = &$config['extensions'][$extension_file];
    $extension['login_function'] = $function_name;
}


/**
 * Adds extension function when translation string
 *
 * @param string $function_name Callback function name
 */
function extensions_add_translation_string_function($translate_function_name)
{
    global $config;
    global $extension_file;

    $extension = &$config['extensions'][$extension_file];
    $extension['translate_function'] = $translate_function_name;
}
