<?php
/**
 * Created by PhpStorm.
 * User: FlorenceColas
 * Date: 27/02/16
 * Time: 23:04
 */

namespace Warehouse\View\Helper;

use Zend\Form\View\Helper\FormCollection;

class FieldCollection extends FormCollection
{
    protected $defaultElementHelper = 'fieldRow';
}