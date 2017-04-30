<?php
/**
 * Created by PhpStorm.
 * User: FlorenceColas
 * Date: 16/02/16
 * Time: 20:56
 */

namespace Warehouse\Model;
use Zend\Form\Annotation;

/**
 * @Annotation\Hydrator("Zend\Stdlib\Hydrator\ObjectProperty")
 * @Annotation\Name("Logoff")
 */
class Logoff
{
    /**
     * @Annotation\Type("Zend\Form\Element\Submit")
     * @Annotation\Attributes({"value":"Log-off", "id":"logoff", "name":"logoff"})
     */
    public $logoff;

}