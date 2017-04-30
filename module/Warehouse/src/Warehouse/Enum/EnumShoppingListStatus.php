<?php
/**
 * User: FlorenceColas
 * Date: 10/12/16
 * Version: 1.00
 * EnumShoppingListStatus: Enumeration of shopping list article status
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Enum;

abstract class EnumShoppingListStatus
{
    const SHOPPING_LIST_STATUS_TO_BUY = 0;
    const SHOPPING_LIST_STATUS_NEW_TO_BUY = 1;
    const SHOPPING_LIST_STATUS_BOUGHT = 2;
}