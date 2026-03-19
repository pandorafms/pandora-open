<?php

namespace PandoraFMS\Modules\Users\Actions;

use PandoraFMS\Modules\Users\Entities\User;
use PandoraFMS\Modules\Users\Services\GetUserLoginService;

final class GetUserLoginAction
{
    public function __construct(
        private GetUserLoginService $getUserLoginService
    ) {
    }

    public function __invoke(string $idUser, string $pass): User
    {
        return $this->getUserLoginService->__invoke($idUser, $pass);
    }
}
