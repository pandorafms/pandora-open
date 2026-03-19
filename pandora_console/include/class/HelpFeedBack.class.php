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

require_once $config['homedir'].'/godmode/wizards/Wizard.main.php';
ui_require_css_file('pandora');
/**
 * Class HelpFeedBack.
 */
class HelpFeedBack extends Wizard
{

    /**
     * Allowed methods to be called using AJAX request.
     *
     * @var array
     */
    public $AJAXMethods = [
        'loadFeedbackForm',
        'sendMailMethod',
    ];

    /**
     * Url of controller.
     *
     * @var string
     */
    public $ajaxController;


    /**
     * Checks if target method is available to be called using AJAX.
     *
     * @param string $method Target method.
     *
     * @return boolean True allowed, false not.
     */
    public function ajaxMethod($method)
    {
        return in_array($method, $this->AJAXMethods);
    }


    /**
     * Constructor.
     *
     * @param string $ajax_controller Controller.
     *
     * @return object
     */
    public function __construct($ajax_controller)
    {
        $this->ajaxController = $ajax_controller;

        return $this;
    }


    /**
     * Main method.
     *
     * @return void
     */
    public function run()
    {
        ui_require_css_File('discovery');
        ui_require_css_file('help_feedback');

        $help_url = get_parameter('b', null);
        $help_url = io_safe_input(base64_decode($help_url));

        if ($help_url === null) {
            echo __('Page not found');
        } else {
            ?>
        <iframe width="100%" height="100%" frameBorder="0" id="h_Viewer"
            src="<?php echo $help_url; ?>">
            <?php echo __('Browser not compatible.'); ?>
        </iframe>
            <?php
        }

        $hidden = '<input type="hidden" value="'.$help_url.'" ';
        $hidden .= ' form="feedback_form" name="help_url" />';

        echo $hidden;

        echo '<div class="help_feedback">';
        // Load feedback form.
        echo $this->loadFeedbackForm();
        echo '</div><div id="back" class="invisible"></div>';
    }


    /**
     * Loads a feedback form
     *
     * @return​ ​string HTML code for form.
     *
     * @return Function loadFeedbackForm.
     */
    public function loadFeedbackForm()
    {
        global $config;

        $form = [
            'action'   => '#',
            'id'       => 'feedback_form',
            'onsubmit' => 'return false;',
        ];

        $inputs = [
            [
                'wrapper'       => 'div',
                'block_id'      => 'flex-row-baseline w100p',
                'class'         => 'flex-row-baseline w100p',
                'direct'        => 1,
                'block_content' => [
                    [
                        'arguments' => [
                            'label'      => __('Suggestion'),
                            'type'       => 'radio_button',
                            'attributes' => 'class="btn"',
                            'name'       => 'suggestion',
                            'id'         => 'suggestion',
                            'script'     => 'disableRadio(\'report\')',
                            'return'     => true,
                        ],
                    ],
                    [
                        'arguments' => [
                            'label'      => __('Something is wrong'),
                            'type'       => 'radio_button',
                            'attributes' => 'class="btn"',
                            'name'       => 'report',
                            'id'         => 'report',
                            'script'     => 'disableRadio(\'suggestion\')',
                            'return'     => true,
                        ],
                    ],
                ],
            ],
            [

                'label'     => __('What happened?'),
                'class'     => 'explain',
                'arguments' => [
                    'class' => 'textarea_feedback',
                    'id'    => 'feedback_text',
                    'type'  => 'textarea',
                    'name'  => 'feedback_text',
                ],
            ],
            [
                'label'     => __('Your Email'),
                'arguments' => [
                    'id'          => 'feedback_email',
                    'name'        => 'feedback_email',
                    'input_class' => 'email_feedback',
                    'class'       => 'email_feedback',
                    'type'        => 'email',
                    'required'    => true,
                ],
            ],
            [
                'arguments' => [
                    'button_class' => 'btn_submit',
                    'class'        => 'btn_submit',
                    'attributes'   => 'class="sub next btn_submit_feed_back"',
                    'type'         => 'submit',
                    'id'           => 'submit_feedback',
                    'label'        => __('Submit'),
                ],
            ],
        ];

        $output = ui_print_toggle(
            [
                'id'      => 'toggle_help_feedback',
                'content' => $this->printForm(
                    [
                        'form'   => $form,
                        'inputs' => $inputs,
                    ],
                    true
                ),
                'name'    => __('Feedback'),
                'return'  => true,
                'class'   => 'no-border',
                'img_a'   => 'images/arrow_down_white.png',
                'img_b'   => 'images/arrow_up_white.png',

            ]
        );

        $output .= $this->loadJS();
        return $output;
    }


    /**
     * Function send_mail_method,we use send_email_attachment method
     * from functions_cron.php.
     *
     * @param​ ​string​ $feedback_option type fo mail.
     * @param​ ​string​ $feedback_text text mail.
     * @param​ ​string​ $feedback_mail costumer mail.
     *
     * @return void.
     */
    public function sendMailMethod()
    {
        global $config;
        $suggestion = get_parameter('type', 'false');
        $feedback_text = get_parameter('feedback_text', null);
        $feedback_mail = get_parameter('feedback_email', null);
        $help_url = get_parameter('help_url', 'unknown');

        $section = explode('title=', $help_url, 2);

        $subject = '';
        if (is_array($section) === true && isset($section[1]) === true) {
            $subject = '['.$section[1].']';
        }

        if ($suggestion !== 'false') {
            $subject .= __('[pandorafms wiki] New suggestion');
        } else {
            $subject .= __('[pandorafms wiki] New report');
        }

        if (empty($feedback_mail) === true) {
            $error = [
                'error' => __(
                    'Please provide your email address, we promise not to bother you'
                ),
            ];
        }

        if (empty($feedback_text) === true) {
            if ($suggestion !== 'false') {
                $msg = 'Please provide some feedback. Write something awesome!';
            } else {
                $msg = 'Please provide some feedback. We\'ll appreciate it!';
            }

            $error = [
                'error' => __($msg),
            ];
        }

        if ($error !== null) {
            echo json_encode($error);
            exit;
        }
        $uid = $config['pandora_uid'];
        if (empty($uid) === true) {
            $uid = 'not registered';
        }

        $body = '<ul><li><b>User mail</b> '.$feedback_mail.'</li>';
        $body .= '<li><b>Console</b> <i>'.$uid.'</i></li>';
        $body .= '<li><b>URL</b> '.$help_url.'</li></ul>';
        $body .= '<h2>Message</h2>';
        $body .= '<p>'.$feedback_text.'</p>';

        $r = ['error' => __('Something went wrong while sending the report.')];

        echo json_encode($r);

        exit;
    }


    /**
     * Load extra JS.
     *
     * @return string JS content.
     */
    public function loadJS()
    {
        ob_start();
        ?>
    <script type="text/javascript">
        function disableRadio(id) {
            $('#'+id).prop('checked', false)
        }

        // Set values to data.
        $("#feedback_form").on('submit', function() {
            // Make the AJAX call to send mails.
            $.ajax({
                type: "POST",
                url: "ajax.php",
                dataType: "json",
                data: {
                    page: "<?php echo $this->ajaxController; ?>",
                    method: 'sendMailMethod',
                    type: $('#suggestion').prop('checked'),
                    feedback_text: $("textarea[name=feedback_text]").val(),
                    feedback_email: $("input[name=feedback_email]").val(),
                    help_url: $("input[name=help_url]").val(),
                },
                success: function (data) {
                    var title;
                    var content;
                    var failed = 0;
                    var className='submit-next';

                    if (data.error != "") {
                        title = '<?php echo __('Failed'); ?>';
                        content = data.error;
                        failed = 1;
                        className='submit-cancel';
                    } else {
                        title = '<?php echo __('Success'); ?>';
                        content = '<?php echo __('Your report had been successfully sent to Artica.').'<br>'.__('Thank you!'); ?>';
                    }
                    $('#back').html(content);
                    $('#back').dialog({
                        title: title,
                        buttons: [
                            {
                                class:
                                "ui-widget ui-state-default ui-corner-all ui-button-text-only sub upd " + className,
                                text: '<?php echo __('OK'); ?>',
                                click: function() {
                                    $(this).dialog("close");
                                    if (failed == 0) {
                                        $('#toggle_help_feedback').empty();
                                    }
                                }
                            },
                        ]
                    })
                },
                error: function (data) {
                    console.error("Fatal error in AJAX call to send help feedback mail")
                }
            });
        });

    </script>
        <?php
        return ob_get_clean();
    }


}
