<?php
// phpcs:disable Squiz.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
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
require_once '../include/functions_visual_map.php';
use Models\VisualConsole\Container as VisualConsole;

/**
 * Visual console view handler class.
 */
class Visualmap
{

    /**
     * Undocumented variable
     *
     * @var boolean
     */
    private $validAcl = false;

    /**
     * Undocumented variable
     *
     * @var boolean
     */
    private $acl = 'VR';

    /**
     * Undocumented variable
     *
     * @var boolean
     */
    private $id = 0;

    /**
     * Undocumented variable
     *
     * @var boolean
     */
    private $visualmap = null;

    /**
     * View widh.
     *
     * @var integer
     */
    private $width;

    /**
     * View height.
     *
     * @var integer
     */
    private $height;

    /**
     * Rotate view.
     *
     * @var boolean
     */
    private $rotate = false;


    /**
     * Constructor.
     */
    public function __construct()
    {
    }


    /**
     * Verifies ACL access.
     *
     * @param integer $groupID Target group id.
     *
     * @return void
     */
    private function checkVisualmapACL(int $groupID=0)
    {
        $system = System::getInstance();

        if ($system->checkACL($this->acl)) {
            $this->validAcl = true;
        } else {
            $this->validAcl = false;
        }
    }


    /**
     * Retrieve filters.
     *
     * @return void
     */
    private function getFilters()
    {
        $system = System::getInstance();

        $this->id = (int) $system->getRequest('id', 0);
        $this->width = (int) $system->getRequest('width', 0);
        $this->height = (int) $system->getRequest('height', 0);
    }


    /**
     * Renders the view.
     *
     * @return void
     */
    public function show()
    {
        $this->getFilters();

        if (empty($this->width) === true
            && empty($this->height) === true
        ) {
            // Reload forcing user to send width and height.
            $ui = Ui::getInstance();
            $ui->retrieveViewPort();
        }

        $this->height -= 45;

        $this->visualmap = db_get_row(
            'tlayout',
            'id',
            $this->id
        );

        if (empty($this->visualmap)) {
            $this->show_fail_acl();
        }

        $this->checkVisualmapACL($this->visualmap['id_group']);
        if (!$this->validAcl) {
            $this->show_fail_acl();
        }

        $this->show_visualmap();
    }


    /**
     * Shows an error if ACL fails.
     *
     * @param string $msg Optional message.
     *
     * @return void
     */
    private function show_fail_acl(string $msg='')
    {
        $error['type'] = 'onStart';
        if (empty($msg) === false) {
            $error['title_text'] = __('Error');
            $error['content_text'] = $msg;
        } else {
            $error['title_text'] = __('You don\'t have access to this page');
            $error['content_text'] = System::getDefaultACLFailText();
        }

        $home = new Home();

        $home->show($error);
    }


    /**
     * Ajax call manager.
     *
     * @param string $parameter2 Not sure why is doing this stuff.
     *
     * @return void
     */
    public function ajax(string $parameter2='')
    {
        return;
    }


    /**
     * Generates HTML code to view target Visual console.
     *
     * @return void
     */
    private function show_visualmap()
    {
        global $config;
        $ui = Ui::getInstance();
        $system = System::getInstance();

        include_once $system->getConfig('homedir').'/vendor/autoload.php';

        // Query parameters.
        $visualConsoleId = (int) $system->getRequest('id');

        // Refresh interval in seconds.
        $refr = (int) get_parameter('refr', $system->getConfig('vc_refr'));

        // Check groups can access user.
        $aclUserGroups = [];
        if (!users_can_manage_group_all('AR')) {
            $aclUserGroups = array_keys(users_get_groups(false, 'AR'));
        }

        // Load Visual Console.
        $visualConsole = null;
        try {
            $visualConsole = VisualConsole::fromDB(['id' => $visualConsoleId]);
        } catch (Throwable $e) {
            $this->show_fail_acl($e->getMessage());
            exit;
        }

        $ui->createPage();
        $ui->createDefaultHeader(
            sprintf(
                '%s',
                $this->visualmap['name']
            ),
            $ui->createHeaderButton(
                [
                    'icon'  => 'ui-icon-back',
                    'pos'   => 'left',
                    'text'  => __('Back'),
                    'href'  => 'index.php?page=visualmaps',
                    'class' => 'header-button-left',
                ]
            )
        );

        $ui->require_css('visual_maps');
        $ui->require_css('register');
        $ui->require_css('dashboards');
        $ui->require_javascript('pandora_visual_console');
        $ui->require_javascript('pandora_dashboards');
        $ui->require_javascript('jquery.cookie');
        $ui->require_css('modal');
        $ui->require_css('form');

        $ui->showFooter(false);
        $ui->beginContent();
        $ui->contentAddHtml(
            include_javascript_d3(true)
        );

        $size = [
            'width'  => $this->width,
            'height' => $this->height,
        ];

        if ((bool) $config['mobile_view_orientation_vc'] === true) {
            $size = [
                'width'  => $this->height,
                'height' => $this->width,
            ];
        }

        $ratio_t = $visualConsole->adjustToViewport($size);

        $visualConsoleData = $visualConsole->toArray();

        $uniq = uniqid();

        $output = '<div class="container-center" style="position:relative;">';
        // Style.
        $style = 'width:'.$visualConsoleData['width'].'px;';
        $style .= 'height:'.$visualConsoleData['height'].'px;';

        // Class.
        $class = 'visual-console-container-dashboard c-'.$uniq;
        // Id.
        $id = 'visual-console-container-'.$uniq;
        $output .= '<div style="'.$style.'" class="'.$class.'" id="'.$id.'">';
        $output .= '</div>';
        $output .= '</div>';

        // Check groups can access user.
        $aclUserGroups = [];
        if (users_can_manage_group_all('AR') === true) {
            $aclUserGroups = array_keys(
                users_get_groups(false, 'AR')
            );
        }

        $ignored_params['refr'] = '';
        \ui_require_javascript_file(
            'tiny_mce',
            'include/javascript/tiny_mce/'
        );
        \ui_require_javascript_file(
            'pandora_visual_console',
            'include/javascript/',
            true
        );
        \include_javascript_d3();
        \visual_map_load_client_resources();

        // Load Visual Console Items.
        $visualConsoleItems = VisualConsole::getItemsFromDB(
            $visualConsoleId,
            $aclUserGroups,
            $ratio_t
        );

        $visualConsoleItems = array_reduce(
            $visualConsoleItems,
            function ($carry, $item) {
                $carry[] = $item->toArray();
                return $carry;
            },
            []
        );

        $settings = \json_encode(
            [
                'props'                      => $visualConsoleData,
                'items'                      => $visualConsoleItems,
                'baseUrl'                    => ui_get_full_url('/', false, false, false),
                'page'                       => 'include/ajax/visual_console.ajax',
                'ratio'                      => $ratio_t,
                'size'                       => $size,
                'cellId'                     => $uniq,
                'uniq'                       => $uniq,
                'mobile'                     => true,
                'vcId'                       => $visualConsoleId,
                'id_user'                    => $config['id_user'],
                'mobile_view_orientation_vc' => (bool) !$config['mobile_view_orientation_vc'],
            ]
        );

        $ui->contentAddHtml($output);
        $ui->loadVc($settings, $visualConsoleId);

        $javascript = ob_get_clean();
        $ui->contentAddHtml($javascript);

        $ui->endContent();
        $ui->showPage();
    }


}
