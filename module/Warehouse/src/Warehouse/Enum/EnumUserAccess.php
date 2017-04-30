<?php
/**
 * User: FlorenceColas
 * Date: 05/01/2017
 * Version: 1.00
 * EnumUserAccess: Enumeration of authorization user access
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Enum;

class EnumUserAccess
{
    const UER_ACCESS_ADMIN = 1;
    const UER_ACCESS_USER = 2;
    const UER_ACCESS_VISITOR = 3;

    const UER_ACCESS_ADMIN_LABEL = 'Admin';
    const UER_ACCESS_USER_LABEL = 'User';
    const UER_ACCESS_VISITOR_LABEL = 'Visitor';

}