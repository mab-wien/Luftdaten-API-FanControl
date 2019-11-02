<?php

class auth extends basic
{
    protected $allowedIPs = null;
    protected $httpAuthUser = null;
    protected $httpAuthPass = null;

    public function __construct(array $options = null)
    {
        parent::__construct($options);
    }

    public function isIpAllowed(): bool
    {
        if (empty($this->allowedIPs)) {
            $this->_debug('allowedIPs not set');
            return true;
        }
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        if (!in_array($ip, $this->allowedIPs)) {
            $this->_debug($ip . ' not allowed');
            return false;
        }
        $this->_debug($ip . ' allowed');
        return true;
    }

    public function isAuthenticated(): bool
    {
        if (empty($this->httpAuthUser) || empty($this->httpAuthPass)) {
            $this->_debug('configHttpAuth not Set');
            return true;
        }
        if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
            $this->_debug('UserHttpAuth not Set');
            return false;
        }
        if ($_SERVER['PHP_AUTH_USER'] != $this->httpAuthUser || $_SERVER['PHP_AUTH_PW'] != $this->httpAuthPass) {
            $this->_debug('httpAuth not match');
            return false;
        }
        $this->_debug($this->httpAuthUser . ' authenticated');
        return true;
    }
}
