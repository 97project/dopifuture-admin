<?php

namespace App\Models;

use App\Models\MissionWay\RefSimulation as BaseRefSimulation;

/**
 * Legacy alias — yeni App\Models\MissionWay\RefSimulation modeline yönlendirir.
 * HarvestAppData MwSimulation olarak kullanır; artık ref_simulations tablosuna yazar.
 *
 * @deprecated Doğrudan App\Models\MissionWay\RefSimulation kullanın.
 */
class MwSimulation extends BaseRefSimulation
{
}
