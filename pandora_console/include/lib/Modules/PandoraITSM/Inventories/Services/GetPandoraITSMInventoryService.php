<?php

namespace PandoraFMS\Modules\PandoraITSM\Inventories\Services;

use PandoraFMS\Modules\PandoraITSM\Inventories\Entities\PandoraITSMInventory;
use PandoraFMS\Modules\PandoraITSM\Inventories\Entities\PandoraITSMInventoryFilter;
use PandoraFMS\Modules\PandoraITSM\Inventories\Repositories\PandoraITSMInventoryRepository;

final class GetPandoraITSMInventoryService
{


    public function __construct(
        private PandoraITSMInventoryRepository $pandoraITSMInventoryRepository,
    ) {
    }


    public function __invoke(int $idPandoraITSMInventory): array
    {
        $pandoraITSMInventoryFilter = new PandoraITSMInventoryFilter();
        /*
            @var PandoraITSMInventory $entityFilter
        */
        $entityFilter = $pandoraITSMInventoryFilter->getEntityFilter();
        $entityFilter->setIdPandoraITSMInventory($idPandoraITSMInventory);

        return $this->pandoraITSMInventoryRepository->getOne($pandoraITSMInventoryFilter);
    }


}
