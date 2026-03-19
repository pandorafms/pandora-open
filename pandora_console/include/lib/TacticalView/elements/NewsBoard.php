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

use PandoraFMS\TacticalView\Element;

/**
 * NewsBoard, this class contain all logic for this section.
 */
class NewsBoard extends Element
{


    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        ui_require_css_file('news');
        include_once 'general/news_dialog.php';
        $this->title = __('News Board');
    }


    /**
     * Returns the html of the latest news.
     *
     * @return string
     */
    public function getNews():string
    {
        global $config;
        $options = [];
        $options['id_user'] = $config['id_user'];
        $options['modal'] = false;
        $options['limit'] = 7;
        $news = get_news($options);

        if (!empty($news)) {
            $output = '<div id="news-board" class="new">';
            foreach ($news as $article) {
                $default = false;
                if ($article['text'] == '&amp;lt;p&#x20;style=&quot;text-align:&#x20;center;&#x20;font-size:&#x20;13px;&quot;&amp;gt;Hello,&#x20;congratulations,&#x20;if&#x20;you&apos;ve&#x20;arrived&#x20;here&#x20;you&#x20;already&#x20;have&#x20;an&#x20;operational&#x20;monitoring&#x20;console.&#x20;Remember&#x20;that&#x20;our&#x20;forums&#x20;and&#x20;online&#x20;documentation&#x20;are&#x20;available&#x20;24x7&#x20;to&#x20;get&#x20;you&#x20;out&#x20;of&#x20;any&#x20;trouble.&#x20;You&#x20;can&#x20;replace&#x20;this&#x20;message&#x20;with&#x20;a&#x20;personalized&#x20;one&#x20;at&#x20;Admin&#x20;tools&#x20;-&amp;amp;gt;&#x20;Site&#x20;news.&amp;lt;/p&amp;gt;&#x20;') {
                    $article['subject'] = __('Welcome to Pandora FMS Console');
                    $default = true;
                }

                $text_bbdd = io_safe_output($article['text']);
                $text = html_entity_decode($text_bbdd);

                $output .= '<div class="new-board">';
                $output .= '<div class="new-board-header">';
                $output .= '<span class="new-board-title">'.$article['subject'].'</span>';
                $output .= '<span class="new-board-author">'.__('By').' '.$article['author'].' '.ui_print_timestamp($article['timestamp'], true).'</span>';
                $output .= '</div>';
                $output .= '<div class="new content">';

                if ($default) {
                    $output .= '<div class="default-new">';
                    $output .= '<div class="default-image-new">';
                    $output .= '<img src="./images/welcome_image.svg" alt="img colabora con nosotros - Support">';
                    $output .= '</div><div class="default-text-new">';

                    $output .= '
                        <p>'.__('Welcome to our monitoring tool so grand,').'
                        <br>'.__('Where data insights are at your command.').'
                        <br>'.__('Sales, marketing, operations too,').'
                        <br>'.__("Customer support, we've got you.").'
                        </p>
                        
                        <p>'.__('Our interface is user-friendly,').'
                        <br>'.__("Customize your dashboard, it's easy!").'
                        <br>'.__('Set up alerts and gain insights so keen,').'
                        <br>'.__("Optimize your data, like you've never seen.").'
                        </p>
                        
                        <p>'.__('Unleash its power now, and join the pro league,').'
                        <br>'.__('Unlock the potential of your data to intrigue.').'
                        <br>'.__('Monitoring made simple, efficient and fun,').'
                        <br>'.__('Discover a whole new way to get things done.').'
                        </p>
                        
                        <p>'.__('And take control of your IT once and for all.').'</p>
                        
                        <span>'.__('You can replace this message with a personalized one at Admin tools -> Site news.').'</span>
                    ';

                    $output .= '</div></div>';
                } else {
                    $text = str_replace('<script', '&lt;script', $text);
                    $text = str_replace('</script', '&lt;/script', $text);
                    $output .= nl2br($text);
                }

                $output .= '</div></div>';
            }

            $output .= '</div>';

            return $output;
        } else {
            return '';
        }
    }


}
