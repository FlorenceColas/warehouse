<?php
/**
 * User: FlorenceColas
 * Date: 24/01/2017
 * Version: 1.00
 * MyAuthStorage: custom session storage
 *------------------------------------------------------------------------------------------------------------------
 * Updates:
 *
 */
namespace Warehouse\Service;

use Zend\Authentication\Storage;

class MyAuthStorage extends Storage\Session
{
    public function setRememberMe($rememberMe = 0, $time = 1209600) {
         if ($rememberMe == 1) {
             $this->session->getManager()->rememberMe($time);
         }
    }

    public function forgetMe() {
        $this->session->getManager()->forgetMe();
    }

    public function setLogonName($logon){
        $this->session->logonName = $logon;
    }

    public function getLogonName() {
        return $this->session->logonName;
    }

    public function setAccess($access) {
        $this->session->access = $access;
    }

    public function getAccess() {
        return $this->session->access;
    }

    public function setName($name) {
        $this->session->name = $name;
    }

    public function getName() {
        return $this->session->name;
    }

    public function setId($id) {
        $this->session->id = $id;
    }

    public function getId() {
        return $this->session->id;
    }

    public function setAuthenticationExpirationTime() {
        $expirationTime = time() + $this->session->allowedIdleTimeInSeconds;
        $this->session->expirationTime = $expirationTime;
    }

    public function getAuthenticationExpirationTime() {
        return $this->session->expirationTime;
    }

    public function isExpiredAuthenticationTime() {
        return $this->session->expirationTime < time();
    }

    public function clearAuthenticationExpirationTime() {
        $this->session->expirationTime = 0;
    }

    /**
     * @param int $allowedIdleTimeInSeconds
     */
    public function setAllowedIdleTimeInSeconds($allowedIdleTimeInSeconds) {
        $this->session->allowedIdleTimeInSeconds = $allowedIdleTimeInSeconds;
    }
}