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
global $config;
require_once $config['homedir'].'/include/class/TipsWindow.class.php';
$view = get_parameter('view', '');
$action = get_parameter('action', '');
try {
    $tipsWindow = new TipsWindow();
} catch (Exception $e) {
    echo '[TipsWindow]'.$e->getMessage();
    return;
}

if ($view === 'create' || $view === 'edit') {
    // IF exists actions
    if ($action === 'create' || $action === 'edit') {
        $files = $_FILES;
        $id_lang = get_parameter('id_lang', '');
        $id_profile = get_parameter('id_profile', '');
        $title = get_parameter('title', '');
        $text = get_parameter('text', '');
        $url = get_parameter('url', '');
        $enable = get_parameter_switch('enable', '');
        $errors = [];

        if (count($files) > 0) {
            $e = $tipsWindow->validateImages($files);
            if ($e !== false) {
                $errors = $e;
            }
        }

        if (empty($id_lang) === true) {
            $errors[] = __('Language is empty');
        }

        if (empty($title) === true) {
            $errors[] = __('Title is empty');
        }

        if (empty($text) === true) {
            $errors[] = __('Text is empty');
        }

        switch ($action) {
            case 'create':
                if (count($errors) === 0) {
                    if (count($files) > 0) {
                        $uploadImages = $tipsWindow->uploadImages($files);
                    } else {
                        $uploadImages = '';
                    }

                    $response = $tipsWindow->createTip($id_lang, $id_profile, $title, $text, $url, $enable, $uploadImages);

                    if ($response === 0) {
                        $errors[] = __('Error in insert tip');
                    }
                }

                $tipsWindow->viewCreate($errors);
            return;

            case 'edit':
                $idTip = get_parameter('idTip', '');
                $imagesToDelete = get_parameter('images_to_delete', '');
                if (empty($idTip) === false) {
                    if (count($errors) === 0) {
                        if (empty($imagesToDelete) === false) {
                            $imagesToDelete = json_decode(io_safe_output($imagesToDelete), true);
                            $tipsWindow->deleteImagesFromTip($idTip, $imagesToDelete);
                        }

                        if (count($files) > 0) {
                            $uploadImages = $tipsWindow->uploadImages($files);
                        }

                        $response = $tipsWindow->updateTip($idTip, $id_profile, $id_lang, $title, $text, $url, $enable, $uploadImages);

                        if ($response === 0) {
                            $errors[] = __('Error in update tip');
                        }
                    }

                    $tipsWindow->viewEdit($idTip, $errors);
                }
            return;

            default:
                $tipsWindow->draw();
            return;
        }


        return;
    }

    // If not exists actions
    switch ($view) {
        case 'create':
            $tipsWindow->viewCreate();
        return;

        case 'edit':
            $idTip = get_parameter('idTip', '');
            if (empty($idTip) === false) {
                $tipsWindow->viewEdit($idTip);
            }
        return;

        default:
            $tipsWindow->draw();
        return;
    }
}

if ($action === 'delete') {
    $idTip = get_parameter('idTip', '');
    $errors = [];
    if (empty($idTip) === true) {
        $errors[] = __('Tip required');
    }

    if (count($errors) === 0) {
        $response = $tipsWindow->deleteTip($idTip);

        if ($response === 0) {
            $errors[] = __('Error in delete tip');
        }
    }

    $tipsWindow->draw($errors);
    return;
}

$tipsWindow->draw();
