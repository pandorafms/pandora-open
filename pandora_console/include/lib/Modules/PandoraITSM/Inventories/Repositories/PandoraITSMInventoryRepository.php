<?php

namespace PandoraFMS\Modules\PandoraITSM\Inventories\Repositories;

use PandoraFMS\Modules\PandoraITSM\Inventories\Entities\PandoraITSMInventory;
use PandoraFMS\Modules\PandoraITSM\Inventories\Entities\PandoraITSMInventoryFilter;

interface PandoraITSMInventoryRepository
{


    /**
     * @return PandoraITSMInventory[],
     */
    public function list(PandoraITSMInventoryFilter $pandoraITSMInventoryFilter): array;


    public function count(PandoraITSMInventoryFilter $pandoraITSMInventoryFilter): int;


    public function getOne(PandoraITSMInventoryFilter $pandoraITSMInventoryFilter): array;


    public function create(PandoraITSMInventory $pandoraITSMInventory): PandoraITSMInventory;


    public function update(PandoraITSMInventory $pandoraITSMInventory): PandoraITSMInventory;


    public function delete(int $id): void;


}
