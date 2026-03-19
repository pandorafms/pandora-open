<?php

namespace PandoraFMS\Modules\PandoraITSM\Inventories\Controllers;

use PandoraFMS\Modules\PandoraITSM\Inventories\Actions\GetPandoraITSMInventoryAction;
use PandoraFMS\Modules\Shared\Controllers\Controller;
use PandoraFMS\Modules\Shared\Services\ValidateAclSystem;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class GetPandoraITSMInventoryController extends Controller
{


    public function __construct(
        private GetPandoraITSMInventoryAction $getPandoraITSMInventoryAction,
        private ValidateAclSystem $acl
    ) {
    }


    /**
     * @OA\Get(
     *   security={{ "bearerAuth": {}}},
     *   path="/pandoraITSM/inventory/{idPandoraITSMInventory}",
     *   tags={"PandoraITSM"},
     *   summary="Show pandoraITSMInventory",
     * @OA\Parameter(ref="#/components/parameters/parameterIdPandoraITSMInventory"),
     * @OA\Response(response=200,                                                    ref="#/components/responses/ResponsePandoraITSMInventory"),
     * @OA\Response(response=400,                                                    ref="#/components/responses/BadRequest"),
     * @OA\Response(response=401,                                                    ref="#/components/responses/Unauthorized"),
     * @OA\Response(response=403,                                                    ref="#/components/responses/Forbidden"),
     * @OA\Response(response=404,                                                    ref="#/components/responses/NotFound"),
     * @OA\Response(response=500,                                                    ref="#/components/responses/InternalServerError")
     *  )
     */
    public function __invoke(Request $request, Response $response): Response
    {
        $idPandoraITSMInventory = $this->getParam($request, 'idPandoraITSMInventory');

        $this->acl->validate(0, 'AR', ' tried to read agents for pandoraITSMInventories');

        $result = $this->getPandoraITSMInventoryAction->__invoke($idPandoraITSMInventory);
        return $this->getResponse($response, $result);
    }


}
