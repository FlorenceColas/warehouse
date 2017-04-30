<?php
/**
 * User: FlorenceColas
 * Date: 16/02/16
 * Version: 1.00
 * EnumComparaison: Enumeration of comparaison
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Enum;

abstract class EnumComparaison
{
    const COMPARE_DEFAULT = 0; //compare quantities
    const COMPARE_ON_STOCK = 1; //verify article on stock
    const COMPARE_MANUAL_CHECK = 2; //on stock but require manual check for complete availability

    const COMPARE_DEFAULT_LABEL = 'Check on quantity';
    const COMPARE_ON_STOCK_LABEL = 'Check on stock';
    const COMPARE_MANUAL_CHECK_LABEL = 'Check on stock + Manual check';
}

?>