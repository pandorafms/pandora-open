<?php

namespace PandoraFMS\Modules\PandoraITSM\Inventories\Actions;

use PandoraFMS\Modules\PandoraITSM\Inventories\Entities\PandoraITSMInventory;
use PandoraFMS\Modules\PandoraITSM\Inventories\Services\GetPandoraITSMInventoryService;

final class GetPandoraITSMInventoryAction
{


    public function __construct(
        private GetPandoraITSMInventoryService $getPandoraITSMInventoryService
    ) {
    }


    public function __invoke(int $idPandoraITSMInventory): array
    {
        return $this->getPandoraITSMInventoryService->__invoke($idPandoraITSMInventory);
    }


}
