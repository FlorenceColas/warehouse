<?php
/**
 * User: FlorenceColas
 * Date: 06/03/16
 * Version: 1.00
 * EnumPriority: Enumeration of existing session containers/values
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Enum;

abstract class EnumSession
{
    const INVENTORY_AREA = 'area';

    const INVENTORYSEARCH = 'InventorySearch';
    const INVENTORYSEARCH_AREA = 'area';
    const INVENTORYSEARCH_SECTION = 'section';
    const INVENTORYSEARCH_STATUS = 'status';
    const INVENTORYSEARCH_AVAILABILITY = 'availability';

    const USER = 'UserSession';
    const USER_LOGONNAME = 'logonName';
    const USER_ACCESS = 'access';

    const RECIPESEARCH = 'RecipeSearch';
    const RECIPESEARCH_CATEGORY = 'category';

}

?>