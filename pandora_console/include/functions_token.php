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

use PandoraFMS\Modules\Authentication\Actions\CreateTokenAction;
use PandoraFMS\Modules\Authentication\Actions\DeleteTokenAction;
use PandoraFMS\Modules\Authentication\Actions\GetTokenAction;
use PandoraFMS\Modules\Authentication\Actions\ListTokenAction;
use PandoraFMS\Modules\Authentication\Actions\UpdateTokenAction;
use PandoraFMS\Modules\Authentication\Entities\Token;
use PandoraFMS\Modules\Authentication\Entities\TokenFilter;


/**
 * Get token.
 *
 * @param integer $idToken Token ID.
 *
 * @return array
 */
function get_user_token(int $idToken): array
{
    global $container;
    $token = $container->get(GetTokenAction::class)->__invoke($idToken)->toArray();

    return $token;
}


/**
 * Get info tokens for user.
 *
 * @param integer     $page          Page.
 * @param integer     $pageSize      Size page.
 * @param string|null $sortField     Sort field.
 * @param string|null $sortDirection Sort direction.
 * @param array       $filters       Filters.
 *
 * @return array
 */
function list_user_tokens(
    int $page=0,
    int $pageSize=0,
    ?string $sortField=null,
    ?string $sortDirection=null,
    array $filters=[]
): array {
    global $config;
    global $container;

    $tokenFilter = new TokenFilter;
    $tokenFilter->setPage($page);
    $tokenFilter->setSizePage($pageSize);
    $tokenFilter->setSortField($sortField);
    $tokenFilter->setSortDirection($sortDirection);

    if (empty($filters['freeSearch']) === false) {
        $tokenFilter->setFreeSearch($filters['freeSearch']);
    }

    // phpcs:ignore
    /** @var Token $entityFilter */
    $entityFilter = $tokenFilter->getEntityFilter();

    if (empty($filters['idUser']) === false) {
        $entityFilter->setIdUser($filters['idUser']);
    }

    $result = $container->get(ListTokenAction::class)->__invoke($tokenFilter);

    return $result;
}


/**
 * Create token.
 *
 * @param array $params Params.
 *
 * @return array
 */
function create_user_token(array $params): array
{
    global $container;

    $token = new Token;
    $token->setIdUser($params['idUser']);
    $token->setLabel(io_safe_output($params['label']));
    $token->setValidity((empty($params['validity']) === false) ? io_safe_output($params['validity']) : null);
    $result = $container->get(CreateTokenAction::class)->__invoke($token)->toArray();

    return $result;
}


/**
 * Update token.
 *
 * @param integer $idToken Token ID.
 * @param array   $params  Params.
 *
 * @return array
 */
function update_user_token(int $idToken, array $params): array
{
    global $container;

    $token = $container->get(GetTokenAction::class)->__invoke($idToken);
    $oldToken = clone $token;

    $token->setIdUser($params['idUser']);
    $token->setLabel(io_safe_output($params['label']));
    $token->setValidity((empty($params['validity']) === false) ? io_safe_output($params['validity']) : null);

    $result = $container->get(UpdateTokenAction::class)->__invoke($token, $oldToken)->toArray();

    return $result;
}


/**
 * Delete token.
 *
 * @param integer $idToken Token ID.
 *
 * @return boolean
 */
function delete_user_token(int $idToken): bool
{
    global $container;

    $token = $container->get(GetTokenAction::class)->__invoke($idToken);
    $container->get(DeleteTokenAction::class)->__invoke($token);
    $result = true;

    return $result;
}


/**
 * Generate token for use ONLY in pandora.
 *
 * @param string $serverUniqueIdentifier Value server_unique_identifier from tconfig.
 * @param string $apiPassword            Value api_password from tconfig.
 *
 * @return string
 */
function generate_token_for_system(string $serverUniqueIdentifier='', string $apiPassword=''):string
{
    if (empty($serverUniqueIdentifier) === true
        || empty($apiPassword) === true
    ) {
        return '';
    }

    return md5($serverUniqueIdentifier).md5($apiPassword);
}
