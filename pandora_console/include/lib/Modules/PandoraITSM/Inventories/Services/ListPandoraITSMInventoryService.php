<?php

namespace PandoraFMS\Modules\PandoraITSM\Inventories\Services;

use PandoraFMS\Modules\PandoraITSM\Inventories\Entities\PandoraITSMInventoryFilter;
use PandoraFMS\Modules\PandoraITSM\Inventories\Repositories\PandoraITSMInventoryRepository;

final class ListPandoraITSMInventoryService
{


    public function __construct(
        private PandoraITSMInventoryRepository $pandoraITSMInventoryRepository,
    ) {
    }


    public function __invoke(PandoraITSMInventoryFilter $pandoraITSMInventoryFilter): array
    {
        return $this->pandoraITSMInventoryRepository->list($pandoraITSMInventoryFilter);
    }


}
