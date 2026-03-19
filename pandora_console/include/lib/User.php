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
namespace PandoraFMS;

/**
 * Object user.
 */
class User extends Entity implements PublicLogin
{

    /**
     * Current 'id_usuario'.
     *
     * @var string
     */
    public $idUser;

    /**
     * User main table.
     *
     * @var string
     */
    protected $table;


    /**
     * Initializes a user object.
     *
     * @param mixed $id_user User id.
     * - Username
     */
    public function __construct($id_user)
    {
        $this->table = 'tusuario';

        if (is_string($id_user) === true
            && empty($id_user) === false
        ) {
            $filter = ['id_user' => $id_user];
            parent::__construct(
                $this->table,
                $filter
            );
        } else {
            // Create empty skel.
            parent::__construct($this->table, null);
        }
    }


    /**
     * Saves current definition to database.
     *
     * @param boolean $alias_as_name Use alias as agent name.
     *
     * @return mixed Affected rows of false in case of error.
     * @throws \Exception On error.
     */
    public function save()
    {
        if (empty($this->idUser) === false) {
            if (is_user($this->idUser) === true) {
                // User update.
                $updates = $this->fields;

                $rs = \db_process_sql_update(
                    $this->table,
                    $updates,
                    ['id_user' => $this->fields['id_user']]
                );

                if ($rs === false) {
                    global $config;
                    throw new \Exception(
                        __METHOD__.' error: '.$config['dbconnection']->error
                    );
                }
            } else {
                // User creation.
                $userData = $this->fields;

                // Clean null fields.
                foreach ($userData as $k => $v) {
                    if ($v === null) {
                        unset($userData[$k]);
                    }
                }

                $rs = create_user($userData['id_user'], $userData['password'], $userData);

                if ($rs === false) {
                    global $config;
                    $error = $config['dbconnection']->error;

                    throw new \Exception(
                        __METHOD__.' error: '.$error
                    );
                }

                $this->fields['id_user'] = $rs;
            }
        }

        return true;
    }


    /**
     * Authentication.
     *
     * @param array|null $data User information.
     * - Username
     * - PHP session ID.
     *
     * @return static
     */
    public static function auth(?array $data)
    {
        global $config;

        // Unset user.
        unset($config['id_usuario']);
        unset($_SESSION['id_usuario']);

        if (is_array($data) === true) {
            if (isset($data['phpsessionid']) === true) {
                $info = \db_get_row_filter(
                    'tsessions_php',
                    ['id_session' => io_safe_input($data['phpsessionid'])]
                );

                if ($info !== false) {
                    // Process.
                    $session_data = session_decode($info['data']);
                    $user = new self($_SESSION['id_usuario']);

                    // Valid session.
                    return $user;
                }

                return null;
            }

            if (isset($data['id_usuario']) === true
                && isset($data['password']) === true
            ) {
                $user_in_db = process_user_login(
                    $data['id_usuario'],
                    $data['password'],
                    true
                );
                if ($user_in_db !== false) {
                    $config['id_usuario'] = $user_in_db;
                    $config['id_user'] = $user_in_db;

                    // Originally at api.php.
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }

                    $_SESSION['id_usuario'] = $data['id_usuario'];
                    session_write_close();

                    $user = new self($data['id_usuario']);
                    // Valid session.
                    return $user;
                }
            }
        }
    }


    /**
     * Process login
     *
     * @param array|null $data Data.
     *
     * @return boolean
     */
    public static function login(?array $data)
    {
        $user = self::auth($data);

        if ($user->idUser === null) {
            return false;
        }

        return true;
    }


    /**
     * Generates a hash to authenticate in public views.
     *
     * @param string|null $other_secret If you need to authenticate using a
     * varable string, use this 'other_secret' to customize the hash.
     *
     * @return string Returns a hash with the authenticaction.
     */
    public static function generatePublicHash(?string $other_secret=''): string
    {
        global $config;

        $str = $config['server_unique_identifier'];
        $str .= ($config['id_user'] ?? get_parameter('id_user'));
        $str .= $other_secret;
        return hash('sha256', $str);
    }


    /**
     * Generates a hash to authenticate in public views with user from url.
     *
     * @param string|null $other_secret If you need to authenticate using a
     * varable string, use this 'other_secret' to customize the hash.
     *
     * @return string Returns a hash with the authenticaction.
     */
    public static function generatePublicHashUser(?string $other_secret='', $id_user_url=''): string
    {
        global $config;

        $str = $config['dbpass'];
        $str .= ($id_user_url ?? $config['id_user']);
        $str .= $other_secret;
        return hash('sha256', $str);
    }


    /**
     * Validates a hash to authenticate in public view.
     *
     * @param string $hash         Hash to be checked.
     * @param string $other_secret Any custom string needed for you.
     *
     * @return boolean Returns true if hash is valid.
     */
    public static function validatePublicHash(
        string $hash,
        string $other_secret=''
    ): bool {
        global $config;

        if (isset($config['id_user']) === true) {
            // Already logged in.
            return true;
        }

        $userFromParams = false;
        // Try to get id_user from parameters if it is missing.
        if (isset($config['id_user']) === false) {
            $userFromParams = true;
            $config['id_user'] = get_parameter('id_user', false);
            // It is impossible to authenticate without an id user.
            if ($config['id_user'] === false) {
                unset($config['id_user']);
                return false;
            }
        } else {
            $config['public_access'] = false;
        }

        if (empty($other_secret) === true) {
            $auth_token_secret = db_get_value('auth_token_secret', 'tusuario', 'id_user', $config['id_user']);

            if (empty($auth_token_secret) === false) {
                $other_secret = $auth_token_secret;
            }
        }

        // Build a hash to check.
        $hashCheck = self::generatePublicHash($other_secret);
        if ($hashCheck === $hash) {
            // "Log" user in.
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }

            $_SESSION['id_usuario'] = $config['id_user'];
            session_write_close();

            $config['public_access'] = true;
            $config['force_instant_logout'] = true;
            return true;
        }

        // Remove id user from config array if authentication has failed.
        if ($userFromParams === true) {
            unset($config['id_user']);
        }

        return false;
    }


}
