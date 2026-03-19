<?php

namespace PandoraFMS\Modules\Shared\Middlewares;

use PandoraFMS\Modules\Authentication\Services\GetUserTokenService;
use PandoraFMS\Modules\Authentication\Services\UpdateTokenService;
use PandoraFMS\Modules\Authentication\Services\ValidateServerIdentifierTokenService;
use PandoraFMS\Modules\Authentication\Services\ValidateUserTokenService;
use PandoraFMS\Modules\Shared\Exceptions\NotFoundException;
use PandoraFMS\Modules\Shared\Services\Timestamp;
use Psr\Http\Message\ServerRequestInterface as Request;

final class UserTokenMiddleware
{


    public function __construct(
        private readonly ValidateServerIdentifierTokenService $validateServerIdentifierTokenService,
        private readonly ValidateUserTokenService $validateUserTokenService,
        private readonly GetUserTokenService $getUserTokenService,
        private readonly UpdateTokenService $updateTokenService,
        private readonly Timestamp $timestamp
    ) {
    }


    public function check(Request $request): bool
    {
        global $config;

        // DO NOT REMOVE THIS LINE.
        // In case a JSON error occurs outside of the API, it will be reset to handle
        // formatting errors in the parameters.
        json_encode([]);

        $authorization = ($request->getHeader('Authorization')[0] ?? '');

        $token = null;
        try {
            $authorization = str_replace('Bearer ', '', $authorization);
            $validTokenUiniqueServerIdentifier = $this->validateServerIdentifierTokenService->__invoke($authorization);
            if ($validTokenUiniqueServerIdentifier === false) {
                preg_match(
                    '/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/',
                    $authorization,
                    $matches
                );

                $uuid = ($matches[0] ?? '');
                if (empty($uuid) === true) {
                    return false;
                }

                $strToken = str_replace($uuid.'-', '', $authorization);
                $validToken = $this->validateUserTokenService->__invoke($uuid, $strToken);
                $token = $this->getUserTokenService->__invoke($uuid);
                if ($token !== null && $validToken) {
                    $config['id_user'] = $token->getIdUser();
                    $oldToken = clone $token;
                    $token->setLastUsage($this->timestamp->getMysqlCurrentTimestamp(0));
                    $this->updateTokenService->__invoke($token, $oldToken);
                }
            } else {
                $validToken = true;
                $token = false;
            }
        } catch (NotFoundException) {
            $token = null;
        }

        if ($token !== null && $validToken) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            if ($validTokenUiniqueServerIdentifier === true) {
                $_SESSION['id_usuario'] = 'admin';
                $config['id_user'] = 'admin';
            } else {
                $_SESSION['id_usuario'] = $token->getIdUser();
                $config['id_user'] = $token->getIdUser();
            }

            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }
        }

        return $token !== null && $validToken;
    }


}
