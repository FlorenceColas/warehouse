<?php
/**
 * User: FlorenceColas
 * Date: 24/01/2017
 * Version: 1.00
 * Login: model for the login page fields content
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */

namespace Warehouse\Model;

use Zend\Form\Annotation;

/**
 * @Annotation\Hydrator("Zend\Stdlib\Hydrator\ObjectProperty")
 * @Annotation\Name("User")
 */
class Login
{
    /**
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true" })
     * @Annotation\Filter({"name":"StripTags"})
     * @Annotation\Options({"label":"Id: "})
     * @Annotation\Attributes({"placeholder":"Enter your logon name","autofocus":"true","size":"30"})
     */
    public $logonname;

    /**
     * @Annotation\Type("Zend\Form\Element\Password")
     * @Annotation\Required({"required":"true" })
     * @Annotation\Filter({"name":"StripTags"})
     * @Annotation\Options({"label":"Password: "})
     * @Annotation\Attributes({"placeholder":"Enter your password","size":"30"})
     */
    public $password;

    /**
     * @Annotation\Type("Zend\Form\Element\Checkbox")
     * @Annotation\AllowEmpty(true)
     * @Annotation\Options({"label":"Remember Me "})
     */
    public $rememberme;

    /**
     * @Annotation\Type("Zend\Form\Element\Submit")
     * @Annotation\Attributes({"value":"Login","class":"btn-default-size"})
     */
    public $submit;
}