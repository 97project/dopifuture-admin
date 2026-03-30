<?php

namespace App\Models;

use App\Models\MissionWay\MwPlayer as BaseMwPlayer;

/**
 * Legacy alias — yeni App\Models\MissionWay\MwPlayer modeline yönlendirir.
 * HarvestAppData tarafından kullanılır.
 *
 * @deprecated Doğrudan App\Models\MissionWay\MwPlayer kullanın.
 */
class MwPlayer extends BaseMwPlayer
{
}
