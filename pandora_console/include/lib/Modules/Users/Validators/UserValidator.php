<?php

namespace PandoraFMS\Modules\Users\Validators;

use PandoraFMS\Modules\Shared\Validators\Validator;
use PandoraFMS\Modules\Users\Enums\UserHomeScreenEnum;

class UserValidator extends Validator
{
    public const VALIDSECTION = 'ValidSection';

    protected function isValidSection($section): bool
    {
        $result = UserHomeScreenEnum::get(strtoupper($section));
        return empty($result) === true ? false : true;
    }


}
