<?php

namespace PandoraFMS\Modules\PandoraITSM\Inventories\Actions;

use PandoraFMS\Modules\PandoraITSM\Inventories\Entities\PandoraITSMInventoryFilter;
use PandoraFMS\Modules\PandoraITSM\Inventories\Services\CountPandoraITSMInventoryService;
use PandoraFMS\Modules\PandoraITSM\Inventories\Services\ListPandoraITSMInventoryService;

use PandoraFMS\Modules\Shared\Entities\PaginationData;

final class ListPandoraITSMInventoryAction
{


    public function __construct(
        private ListPandoraITSMInventoryService $listPandoraITSMInventoryService,
        private CountPandoraITSMInventoryService $countPandoraITSMInventoryService
    ) {
    }


    public function __invoke(PandoraITSMInventoryFilter $pandoraITSMInventoryFilter): array
    {
        return (new PaginationData(
            $pandoraITSMInventoryFilter->getPage(),
            $pandoraITSMInventoryFilter->getSizePage(),
            $this->countPandoraITSMInventoryService->__invoke($pandoraITSMInventoryFilter),
            $this->listPandoraITSMInventoryService->__invoke($pandoraITSMInventoryFilter)
        ))->toArray();
    }


}
