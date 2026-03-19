<?php

namespace PandoraFMS\Modules\Users\Services;

use PandoraFMS\Modules\Shared\Exceptions\NotFoundException;
use PandoraFMS\Modules\Users\Entities\User;
use PandoraFMS\Modules\Users\Repositories\UserRepository;

final class GetUserLoginService
{
    public function __construct(
        private UserRepository $userRepository,
        private GetUserService $getUserService
    ) {
    }

    public function __invoke(string $idUser, string $pass): User
    {
        $result = \process_user_login($idUser, $pass);

        if ($result === false) {
            throw new NotFoundException(__('Not found User'));
        }

        return $this->getUserService->__invoke($idUser);
    }
}
