<?php
/**
 * User: FlorenceColas
 * Date: 16/02/16
 * Version: 1.00
 * EnumAccess: Enumeration of user access
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Enum;

abstract class EnumAccess
{
    const VISITOR = 0;
    const MEMBER = 1;
    const ADMINISTRATOR = 2;
}

?>