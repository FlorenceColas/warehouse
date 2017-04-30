<?php
/**
 * User: FlorenceColas
 * Date: 08/02/16
 * Version: 1.00
 * EnumAvailability: Enumeration of article availability
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Enum;

abstract class EnumAvailability
{
    const All = 0;
    const OnStock = 1;
    const NotOnStock = 2;
    const UnderInfoThreshold = 3;
    const UnderCriticalThreshold = 4;
    const OnStockRequireManualCheck = 5;
}

?>