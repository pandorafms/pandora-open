<?php

namespace PandoraFMS\Modules\Users\Controllers;

use PandoraFMS\Modules\Shared\Controllers\Controller;
use PandoraFMS\Modules\Shared\Services\ValidateAclSystem;
use PandoraFMS\Modules\Users\Actions\GetUserLoginAction;
use PandoraFMS\Modules\Users\Entities\UserFilter;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class GetUserLoginController extends Controller
{
    public function __construct(
        private GetUserLoginAction $getUserLoginAction,
        private ValidateAclSystem $acl
    ) {
    }

    /**
     * @OA\Get(
     *   security={{ "bearerAuth": {}}},
     *   path="/user/{idUser}/login",
     *   tags={"Users"},
     *   summary="show user when login process",
     *   @OA\Parameter(ref="#/components/parameters/parameterIdUser"),
     *   @OA\Parameter(ref="#/components/parameters/parameterIdUserPass"),
     *   @OA\Response(response=200, ref="#/components/responses/ResponseUser"),
     *   @OA\Response(response=400, ref="#/components/responses/BadRequest"),
     *   @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *   @OA\Response(response=403, ref="#/components/responses/Forbidden"),
     *   @OA\Response(response=404, ref="#/components/responses/NotFound"),
     *   @OA\Response(response=500, ref="#/components/responses/InternalServerError")
     *  ),
     *  @OA\Parameter(
     *    parameter="parameterIdUserPass",
     *    name="password",
     *    in="query",
     *    description="User password",
     *    required=true,
     *    @OA\Schema(
     *      type="string",
     *      default=null
     *    )
     *  )
     */
    public function __invoke(Request $request, Response $response): Response
    {
        $idUser = $this->getParam($request, 'idUser');
        $userFilter = $this->fromRequest($request, UserFilter::class);
        $pass = $userFilter->getEntityFilter()->getPassword();

        $this->acl->validate(0, 'UM', ' tried to manage user');
        
        $result = $this->getUserLoginAction->__invoke($idUser, $pass);

        return $this->getResponse($response, $result);
    }
}
