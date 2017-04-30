<?php
/**
 * User: FlorenceColas
 * Date: 31/01/2017
 * Version: 1.00
 * AuthServiceInterface: Authentication service interface
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */
namespace Warehouse\Service;

interface AuthServiceInterface
{
    /**
     * @param string $logon
     * @param string $password
     * @param integer $rememberMe
     * @return boolean
     */
    public function authenticateUser($logon, $password, $rememberMe);

    public function sessionIsValid();
}