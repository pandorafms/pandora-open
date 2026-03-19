<?php

use PandoraFMS\Modules\PandoraITSM\Inventories\Controllers\GetPandoraITSMInventoryController;
use PandoraFMS\Modules\PandoraITSM\Inventories\Controllers\ListPandoraITSMInventoryController;
use Slim\App;

return function (App $app) {
    $app->map(['GET', 'POST'], '/pandoraITSM/inventory/list', ListPandoraITSMInventoryController::class);
    $app->get('/pandoraITSM/inventory/{idPandoraITSMInventory}', GetPandoraITSMInventoryController::class);
};
