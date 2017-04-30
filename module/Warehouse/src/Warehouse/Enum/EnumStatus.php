<?php
/**
 * User: FlorenceColas
 * Date: 06/02/16
 * Version: 1.00
 * EnumStatus: Enumeration of article status in the inventory
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Enum;

abstract class EnumStatus
{
    const Disabled = 0;
    const Enabled = 1;
    const Blocked = 2;
}

?>