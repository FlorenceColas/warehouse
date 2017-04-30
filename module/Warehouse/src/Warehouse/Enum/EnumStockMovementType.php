<?php
/**
 * User: FlorenceColas
 * Date: 10/01/2017
 * Version: 1.00
 * EnumAccess: Enumeration of stock movement types
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Enum;

class EnumStockMovementType
{
    const MOVEMENT_SHOP_ADD = 1;
    const MOVEMENT_SHOP_REMOVE = -1;
    const MOVEMENT_REGULARISATION_ADD = 2;
    const MOVEMENT_REGULARISATION_REMOVE = -2;
}