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

class User
{

    private static $instance;

    private $user;

    private $logged = false;

    private $errorLogin = false;

    private $loginTime = false;

    private $logout_action = false;

    private $needDoubleAuth = false;

    private $errorDoubleAuth = false;


    public static function getInstance()
    {
        if (!(self::$instance instanceof self)) {
            // Check if in the session
            $system = System::getInstance();
            $user = $system->getSession('user', null);

            if (is_object($user) === false) {
                $user = json_decode($user, true);
            }

            if (!empty($user)) {
                self::$instance = new self();
                foreach ($user as $k => $v) {
                    self::$instance->{$k} = $v;
                }
            } else {
                self::$instance = new self();
            }
        }

        return self::$instance;
    }


    public function jsonSerialize()
    {
        return get_object_vars($this);
    }


    public function saveLogin()
    {
        if ($this->logged) {
            $system = System::getInstance();

            // hack to compatibility with pandora
            global $config;
            $config['id_user'] = $this->user;

            $system->setSessionBase('id_usuario', $this->user);
            $system->setSession('user', json_encode($this->jsonSerialize()));

            config_user_set_custom_config();
        }
    }


    public function isLogged()
    {
        $system = System::getInstance();

        $autologin = $system->getRequest('autologin', false);
        $auth_token = $_POST['auth_token'];
        if ($autologin !== false) {
            $user = $system->getRequest('user', null);
            $password = $system->getRequest('password', null);
            $this->login($user, $password);
        } else if (empty($auth_token) === false) {
            $this->login(null, null, $auth_token);
        }

        return $this->logged;
    }


    public function login($user=null, $password=null, $auth_token='')
    {
        global $config;
        $system = System::getInstance();
        if (empty($auth_token) === false && (bool) $config['JWT_signature'] !== false) {
            $jwt = new JWTRepository($config['JWT_signature']);
            if ($jwt->setToken($auth_token)
                && $jwt->validate()
            ) {
                $this->logged = true;
                $this->user = $jwt->payload()->get('id_user');
                $this->loginTime = time();
                $this->errorLogin = false;
                $this->saveLogin();
            } else {
                $this->logged = false;
                $this->errorLogin = __('Token invalid');
            }

            return $this->logged;
        }

        if (($user == null) && ($password == null)) {
            $user = $system->getRequest('user', null);
            $password = $system->getRequest('password', null);
        }

        if (empty($user) === false
            && empty($password) === false
        ) {
            $user_in_db = db_get_row_filter(
                'tusuario',
                ['id_user' => $user],
                '*'
            );

            $this->logged = false;
                $this->loginTime = false;
                $this->errorLogin = true;
                $this->needDoubleAuth = false;
                $this->errorDoubleAuth = false;

            if ($user_in_db !== false) {
                if (((bool) $user_in_db['is_admin'] === false)
                    && ((bool) $user_in_db['not_login'] === true)
                ) {
                    $this->logged = false;
                    $this->loginTime = false;
                    $this->errorLogin = true;
                    $this->needDoubleAuth = false;
                    $this->errorDoubleAuth = false;
                } else {
                    $user_proccess_login = process_user_login($user, $password);
                    if ($user_proccess_login !== false) {
                        $this->logged = true;
                        $this->user = $user_proccess_login;
                        $this->loginTime = time();
                        $this->errorLogin = false;
                        // The user login was successful, but the second step is not completed.
                        if ($this->isDobleAuthRequired()) {
                            $this->needDoubleAuth = true;
                        }
                    }
                }
            }
        }

        $this->saveLogin();

        return $this->logged;
    }


    public function getLoginTime()
    {
        return $this->loginTime;
    }


    public function isWaitingDoubleAuth()
    {
        return $this->needDoubleAuth;
    }


    public function isDobleAuthRequired($user=false)
    {
        if (empty($user) && !empty($this->user)) {
            $user = $this->user;
        }

        if (!empty($user)) {
            return (bool) db_get_value('id', 'tuser_double_auth', 'id_user', $user);
        } else {
            return false;
        }
    }


    public function validateDoubleAuthCode($user=null, $code=null)
    {
        if (!$this->needDoubleAuth) {
            return true;
        }

        $system = System::getInstance();
        include_once $system->getConfig('homedir').'/include/auth/GAuth/Auth.php';

        $result = false;

        if (empty($user)) {
            $user = $this->user;
        }

        if (empty($code)) {
            $code = $system->getRequest('auth_code', null);
            $code = $system->safeOutput($code);
        }

        if (!empty($user) && !empty($code)) {
            $secret = db_get_value('secret', 'tuser_double_auth', 'id_user', $user);

            if ($secret === false) {
                $result = false;
                $this->errorDoubleAuth = [
                    'title_text'   => __('Double authentication failed'),
                    'content_text' => __('Secret code not found').'. '.__('Please contact the administrator to reset your double authentication'),
                ];
            } else if (!empty($secret)) {
                try {
                    $gAuth = new \GAuth\Auth($secret);
                    $result = $gAuth->validateCode($code);

                    // Double auth success
                    if ($result) {
                        $this->needDoubleAuth = false;
                        $this->saveLogin();
                    } else {
                        $result = false;
                        $this->errorDoubleAuth = [
                            'title_text'   => __('Double authentication failed'),
                            'content_text' => __('Invalid code'),
                        ];
                    }
                } catch (Exception $e) {
                    $result = false;
                    $this->errorDoubleAuth = [
                        'title_text'   => __('Double authentication failed'),
                        'content_text' => __('There was an error checking the code'),
                    ];
                }
            }
        }

        return $result;
    }


    public function logout()
    {
        $system = System::getInstance();

        $this->user = null;
        $this->logged = false;
        $this->loginTime = false;
        $this->errorLogin = false;
        $this->logout_action = true;
        $this->needDoubleAuth = false;
        $this->errorDoubleAuth = false;

        $system->setSession('user', null);
        $system->sessionDestroy();
    }


    public function showLoginPage()
    {
        global $pandora_version;

        $ui = Ui::getInstance();
        $system = System::getInstance();

        $ui->createPage();
        if ($this->errorLogin) {
            $options['type'] = 'onStart';
            $options['title_text'] = __('Login Failed');
            $options['content_text'] = __('User not found in database or incorrect password.');

            $ui->addDialog($options);
        }

        if ($this->logout_action) {
            $options['dialog_id'] = 'logout_dialog';
            $options['type'] = 'onStart';
            $options['title_text'] = __('Login out');
            $options['content_text'] = __('Your session has ended. Please close your browser window to close this %s session.', get_product_name());
            $ui->addDialog($options);
        }

        $ui->createHeader();
        $ui->showFooter(false);
        $ui->beginContent();

        $ui->contentAddHtml(
            '
            <style>
                div.ui-content {
                    animation: container_login 2s ease;
                }
                
                @keyframes container_login {
                    0% {
                        transform: scale(.93);
                        opacity: 0.1;
                    }
                    
                    100% {
                        transform: scale(1);
                        opacity: 1;
                    }
                }

                .ui-page-active {
                    padding-top: 0px !important;
                }

                .ui-page-theme-a {
                    background-color: transparent !important;
                }

                .ui-mobile {
                    height: 100% !important;
                }
            </style>
            <script>
            $(document).ready(function () {
                $(".ui-header.ui-bar-inherit.ui-header-fixed.slidedown").remove();
                $("div#main_page").css({
                    "display": "flex",
                    "flex-direction": "column",
                    "justify-content": "center"
                });
                $(".ui-overlay-a").addClass("login-background");
                $(".ui-overlay-a").removeClass("ui-overlay-a");
                $(".ui-page-theme-a").css({"background-color":"transparent !important"});

                $("#text-login_btn").click(function (e) {
                    $("#user-container").hide();
                    $("#password-container").hide();
                    $("#text-login_btn").hide();
                    $("#spinner-login").show();
                  });
            });
            </script>
        '
        );

        $logo_image = html_print_image(
            ui_get_mobile_login_icon(),
            true,
            [
                'alt'    => 'logo',
                'border' => 0,
            ],
            false,
            false,
            false,
            true
        );

        $ui->contentAddHtml(
            '<div class="login_logo center">'.$logo_image.'</div>'
        );
        $ui->contentAddHtml('<div id="login_container">');
        $ui->beginForm('');
        $ui->formAddHtml(html_print_input_hidden('action', 'login', true));
        $options = [
            'name'        => 'user',
            'value'       => $this->user,
            'placeholder' => __('User'),
            // 'autofocus'   => 'autofocus',
            // 'label'       => __('User'),
        ];
        $ui->formAddInputText($options);
        $options = [
            'name'        => 'password',
            'value'       => '',
            'placeholder' => __('Password'),
            // 'label'       => __('Password'),
            'required'    => 'required',
        ];
        $ui->formAddInputPassword($options);

        $spinner = '
        <div class="spinner invisible" id="spinner-login">
            <span></span>
            <span></span>
            <span></span>
            <span></span>
        </div>
        ';
        $ui->formAddHtml($spinner);

        $options = [
            'value'    => __('Login'),
            'icon'     => 'arrow-r',
            'icon_pos' => 'right',
            'name'     => 'login_btn',
        ];
        $ui->formAddSubmitButton($options);

        $ui->endForm();

        if ($system->getConfig('auth', 'mysql') === 'saml') {
            // Add SAML login button.
            $ui->beginForm('');
            $ui->formAddHtml(
                html_print_input_hidden('action', 'login', true)
            );
            $ui->formAddHtml(
                html_print_input_hidden('saml', '1', true)
            );
            $ui->formAddSubmitButton(
                [
                    'value'    => __('Login with SAML'),
                    'icon'     => 'arrow-r',
                    'icon_pos' => 'right',
                    'name'     => 'login_button_saml',
                ]
            );
            $ui->endForm('');
        }

        $ui->contentAddHtml('</div>');
        $ui->contentAddHtml('<div class="center" id="ver_num">'.$pandora_version.'</div>');
        $ui->endContent();
        $ui->showPage();

        $this->errorLogin = false;
        $this->logout_action = false;
    }


    public function showDoubleAuthPage()
    {
        global $pandora_version;

        $ui = Ui::getInstance();

        $ui->createPage();
        if (!empty($this->errorDoubleAuth)) {
            $options['type'] = 'onStart';
            $options['title_text'] = $this->errorDoubleAuth['title_text'];
            $options['content_text'] = $this->errorDoubleAuth['content_text'].'<br>';
            $ui->addDialog($options);
        }

        $left_button = $ui->createHeaderButton(
            [
                'icon'  => 'ui-icon-back',
                'pos'   => 'left',
                'text'  => __('Logout'),
                'href'  => 'index.php?action=logout',
                'class' => 'header-button-left ui-icon-back',
            ]
        );
        $ui->createHeader('', $left_button);
        $ui->showFooter(false);
        $ui->beginContent();

        $ui->contentAddHtml(
            '
            <style>
                .ui-page-active {
                    padding-top: 0px !important;
                }

                .ui-page-theme-a {
                    background-color: transparent !important;
                }

                .ui-mobile {
                    height: 100% !important;
                }
            </style>
            <script>
            $(document).ready(function () {
                // $(".ui-header.ui-bar-inherit.ui-header-fixed.slidedown").remove();
                $(".ui-header.ui-bar-inherit.ui-header-fixed.slidedown").css({"background-color":"transparent"});
                $("div#main_page").css({
                    "display": "flex",
                    "flex-direction": "column",
                    "justify-content": "center"
                });
                $(".ui-overlay-a").addClass("login-background");
                $(".ui-overlay-a").removeClass("ui-overlay-a");
                $(".ui-page-theme-a").css({"background-color":"transparent !important"});
                $("div.ui-page.ui-page-theme-a.ui-page-active#main_page").css({"background-color":"transparent !important"});
            });
            </script>
        '
        );

        $ui->contentAddHtml(
            '<div class="login_logo center">'.html_print_image(
                ui_get_mobile_login_icon(),
                true,
                [
                    'alt'    => 'logo',
                    'border' => 0,
                ],
                false,
                false,
                false,
                true
            ).'</div>'
        );
        $ui->contentAddHtml('<div id="login_container">');
        $ui->beginForm();
        $ui->formAddHtml(html_print_input_hidden('action', 'double_auth', true));
        $options = [
            'name'        => 'auth_code',
            'value'       => '',
            'placeholder' => __('Authenticator code'),
            'autofocus'   => 'autofocus',
            // 'label'       => __('Authenticator code'),
        ];
        $ui->formAddInputPassword($options);
        $options = [
            'value'    => __('Check code'),
            'icon'     => 'arrow-r',
            'icon_pos' => 'right',
            'name'     => 'auth_code_btn',
        ];
        $ui->formAddSubmitButton($options);
        $ui->endForm();
        $ui->contentAddHtml('</div>');
        $ui->contentAddHtml('<div class="center" id="ver_num">'.$pandora_version.'</div>');
        $ui->endContent();
        $ui->showPage();

        $this->errorDoubleAuth = false;
    }


    public function getIdUser()
    {
        return $this->user;
        // Oldies methods
    }


    public function isInGroup($access='AR', $id_group=0, $name_group=false)
    {
        return (bool) check_acl($this->user, $id_group, $access);
    }


    public function getIdGroups($access='AR', $all=false)
    {
        return array_keys(users_get_groups($this->user, $access, $all));
    }


    public function getInfo()
    {
        return users_get_user_by_id($this->user);
    }


}
