<?php

namespace App\Models;

use App\Models\MissionWay\MwSimulationSession as BaseMwSession;

/**
 * Legacy alias — yeni App\Models\MissionWay\MwSimulationSession modeline yönlendirir.
 * HarvestAppData tarafından kullanılır.
 *
 * @deprecated Doğrudan App\Models\MissionWay\MwSimulationSession kullanın.
 */
class MwSession extends BaseMwSession
{
}
