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
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
/**
 * JWT Repository.
 */
final class JWTRepository
{

    /**
     * Allowed methods to be called using AJAX request.
     *
     * @var array
     */
    public $AJAXMethods = ['create'];

    /**
     * Signature
     *
     * @var string
     */
    private $signature;

    /**
     * Token
     *
     * @var Token
     */
    private $token;


    /**
     * Constructor
     *
     * @param string $_signature Signature of JWT.
     */
    public function __construct(string $_signature)
    {
        $this->signature = $_signature;
    }


    /**
     * Checks if target method is available to be called using AJAX.
     *
     * @param string $method Target method.
     *
     * @return boolean True allowed, false not.
     */
    public function ajaxMethod($method)
    {
        // Check access.
        check_login();

        return in_array($method, $this->AJAXMethods);
    }


    /**
     * Create token
     *
     * @return string
     */
    public function create(): string
    {
        global $config;
        try {
            $sha = new Sha256();
            $configJWT = Configuration::forSymmetricSigner(
                $sha,
                InMemory::plainText($this->signature)
            );

            $now = new DateTimeImmutable();
            $token = $configJWT->builder()->issuedAt($now)->canOnlyBeUsedAfter($now)->expiresAt($now->modify('+1 minute'))->withClaim('id_user', $config['id_user'])->getToken($configJWT->signer(), $configJWT->signingKey());

            return $token->toString();
        } catch (Exception $e) {
            return '';
        }
    }


    /**
     * Validate a JWT, USE FIRST setToken().
     *
     * @return boolean
     */
    public function validate():bool
    {
        try {
            $sha = new Sha256();
            $configJWT = Configuration::forSymmetricSigner(
                $sha,
                InMemory::plainText($this->signature)
            );
            $signed = new SignedWith($sha, InMemory::plainText($this->signature));
            $now = new DateTimeZone('UTC');
            $strictValid = new StrictValidAt(SystemClock::fromUTC());
            $constraints = [
                $signed,
                $strictValid,
            ];
            return $configJWT->validator()->validate($this->token, ...$constraints);
        } catch (Exception $e) {
            return false;
        }
    }


    /**
     * Get payload of token.
     *
     * @return object
     */
    public function payload():object
    {
        return $this->token->claims();
    }


    /**
     * Setting token.
     *
     * @param string $tokenString String token to setting.
     *
     * @return boolean
     */
    public function setToken(string $tokenString):bool
    {
        try {
            $encoder = new JoseEncoder();
            $parser = new Parser($encoder);
            $this->token = $parser->parse($tokenString);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }


    /**
     * Generate random signature.
     *
     * @return string
     */
    public static function generateSignature(): string
    {
        return bin2hex(random_bytes(32));
    }

}
