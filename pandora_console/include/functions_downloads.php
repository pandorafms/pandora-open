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
  * Draw message downloads.
  *
  * @param string $type Type.
  *
  * @return string
  */
function draw_msg_download(string $type='agent'): string
{
    ui_require_css_file('downloads');

    $msg = '';
    $title = '';
    $buttons_links = '';
    $footer = '';
    $extra_class = '';
    if ($type === 'satellite') {
        $extra_class = 'container-downloads-satellite';
        $title .= __(
            '%s satellite server',
            get_product_name()
        );

        $msg .= __(
            'The satellite is used to perform discovery, and to be able to perform remote monitoring of network equipment, windows and linux machines in those environments that are far from the %s server and do not have full connectivity (only from the Satellite to the %s server). This is especially useful in client environments or remote networks where we can\'t install agents, and we don\'t have visibility from our main network. By installing a satellite server we can monitor the systems in those networks from the %s console.',
            get_product_name(),
            get_product_name(),
            get_product_name()
        );

        $msg .= '<br><br>';

        $msg .= __(
            'The satellite server can be installed in Windows64 bit and Linux, it doesn\'t need many resources (you can install it in a virtual machine) and you only need to be able to access your %s server using the Tentacle port (41121/tcp).',
            get_product_name()
        );

        $buttons_links .= '<div class="satellite-buttons-links">';
        $buttons_links .= '<a href="https://pfms.me/windows-x64-satellite">';

        $buttons_links .= html_print_button(
            __('Windows 64 bit'),
            'satellite_windows_download',
            false,
            '',
            ['icon' => 'windows'],
            true,
            false
        );

        $buttons_links .= '</a>';
        $buttons_links .= '<a href="http://pfms.me/linux-x64-satellite-tarball">';

        $buttons_links .= html_print_button(
            __('Linux 64 bit (Tarball)'),
            'satellite_windows_download',
            false,
            '',
            ['icon' => 'linux'],
            true,
            false
        );

        $buttons_links .= '</a></div>';

        $footer .= '<i>';
        $footer .= __(
            'More downloads are available in the "File releases" section of the %s support portal.',
            get_product_name()
        );

        $footer .= '</i>';
    } else {
        $title .= __(
            '%s agents',
            get_product_name()
        );

        $msg .= __(
            'The %s agent is necessary to obtain detailed information of the system you want to monitor, it allows you to obtain more information than remote monitoring (without agent). In addition, if you want to use the RMM functions of the %s agent, it is essential to install the %s agent and the Pandora RC agent. The agent will need to access your %s server using the Tentacle port (41121/tcp).',
            get_product_name(),
            get_product_name(),
            get_product_name(),
            get_product_name()
        );

        $msg .= '<br><br>';

        $link = '<a target="_blank" href="index.php?sec=gagente&sec2=godmode/agentes/modificar_agente">';
        $link .= __('Management -> Resources -> Manage agents section.');
        $link .= '</a>';

        $msg .= __(
            'You can install agents, one by one, using the Agent Deployment Wizard from the %s If you want to download the agent to make a massive deployment, with our agent deployment tool or with another tool, you can download it from these links:',
            $link,
        );

        $buttons_links .= '<div class="links-dowloads-agents">';
        $buttons_links .= '<div class="links-dowloads-agents-title">';
        $buttons_links .= '<div>';
        $buttons_links .= html_print_image(
            'images/downloads_links/logo.svg',
            true,
            ['title' => __('%s ONE agent', get_product_name())]
        );
        $buttons_links .= '</div>';

        $buttons_links .= '<div>';
        $buttons_links .= __('%s ONE agent', get_product_name());
        $buttons_links .= '</div>';

        $buttons_links .= '</div>';
        $buttons_links .= '<ul><li>';
        $buttons_links .= '<a href="https://pfms.me/windosx64-agent">';
        $buttons_links .= html_print_image(
            'images/downloads_links/windows.svg',
            true,
            ['title' => 'Windows']
        );

        $buttons_links .= '<div>';
        $buttons_links .= 'Windows 64 bit ->';
        $buttons_links .= '</div>';

        $buttons_links .= '</a></li><li>';
        $buttons_links .= '<a href="https://pfms.me/linux-x64-agent-tarball">';
        $buttons_links .= html_print_image(
            'images/downloads_links/linux.svg',
            true,
            ['title' => 'Linux']
        );
        $buttons_links .= '<div>';
        $buttons_links .= 'Linux 64 bit (Tarball) ->';
        $buttons_links .= '</div>';
        $buttons_links .= '</a></li><li>';
        $buttons_links .= '<a href="https://pfms.me/linux-x64-agent-rpm">';
        $buttons_links .= html_print_image(
            'images/downloads_links/linux.svg',
            true,
            ['title' => 'Linux']
        );
        $buttons_links .= '<div>';
        $buttons_links .= 'Linux 64 bit el7 (RPM) ->';
        $buttons_links .= '</div>';
        $buttons_links .= '</a></li><li>';
        $buttons_links .= '<a href="https://pfms.me/linux-x64-agent-el8-rpm">';
        $buttons_links .= html_print_image(
            'images/downloads_links/linux.svg',
            true,
            ['title' => 'Linux']
        );
        $buttons_links .= '<div>';
        $buttons_links .= 'Linux 64 bit el8 (RPM) ->';
        $buttons_links .= '</div>';
        $buttons_links .= '</a></li><li>';
        $buttons_links .= '<a href="https://pfms.me/linux-x64-agent-el9-rpm">';
        $buttons_links .= html_print_image(
            'images/downloads_links/linux.svg',
            true,
            ['title' => 'Linux']
        );
        $buttons_links .= '<div>';
        $buttons_links .= 'Linux 64 bit el9 (RPM) ->';
        $buttons_links .= '</div>';
        $buttons_links .= '</a></li><li>';
        $buttons_links .= '<a href="https://pfms.me/macos-x64-agent-dmg">';
        $buttons_links .= html_print_image(
            'images/downloads_links/mac.svg',
            true,
            ['title' => 'Mac']
        );
        $buttons_links .= '<div>';
        $buttons_links .= 'MacOS 64 bit ->';
        $buttons_links .= '</div>';
        $buttons_links .= '</a></li></ul>';
        $buttons_links .= '</div>';

        $buttons_links .= '<div class="links-dowloads-agents">';
        $buttons_links .= '<div class="links-dowloads-agents-title">';
        $buttons_links .= '<div>';
        $buttons_links .= html_print_image(
            'images/downloads_links/rc.svg',
            true,
            ['title' => __('Pandora RC agent')]
        );
        $buttons_links .= '</div>';

        $buttons_links .= '<div>';
        $buttons_links .= __('Pandora RC agent');
        $buttons_links .= '</div>';

        $buttons_links .= '</div>';
        $buttons_links .= '<ul><li>';
        $buttons_links .= '<a href="https://pfms.me/windows-x64-rc">';
        $buttons_links .= html_print_image(
            'images/downloads_links/windows.svg',
            true,
            ['title' => 'Windows']
        );

        $buttons_links .= '<div>';
        $buttons_links .= 'Windows 64 bit ->';
        $buttons_links .= '</div>';

        $buttons_links .= '</a></li><li>';
        $buttons_links .= '<a href="https://pfms.me/linux-x64-rc-rpm">';
        $buttons_links .= html_print_image(
            'images/downloads_links/linux.svg',
            true,
            ['title' => 'Linux']
        );
        $buttons_links .= '<div>';
        $buttons_links .= 'Linux 64 bit (RPM) ->';
        $buttons_links .= '</div>';
        $buttons_links .= '</a></li><li>';
        $buttons_links .= '<a href="https://pfms.me/macos-x64-rc-dmg">';
        $buttons_links .= html_print_image(
            'images/downloads_links/mac.svg',
            true,
            ['title' => 'Mac']
        );
        $buttons_links .= '<div>';
        $buttons_links .= 'MacOS 64 bit ->';
        $buttons_links .= '</div>';
        $buttons_links .= '</a></li></ul>';
        $buttons_links .= '</div>';

        $footer .= '<i>';
        $footer .= __(
            'More downloads are available in the "File releases" section of the %s support portal.',
            get_product_name()
        );
        $footer .= '</i>';
    }

    $output = '<div class="container-downloads '.$extra_class.' ">';
    $output .= '<div class="card-downloads">';
    $output .= '<div class="card-downloads-title">';
    $output .= $title;
    $output .= '</div>';

    $output .= '<div class="card-downloads-msg">';
    $output .= $msg;
    $output .= '</div>';

    $output .= '<div class="card-downloads-links">';
    $output .= $buttons_links;
    $output .= '</div>';

    $output .= '<div class="card-downloads-footer">';
    $output .= $footer;
    $output .= '</div>';

    $output .= '</div>';
    $output .= '</div>';

    return $output;
}
