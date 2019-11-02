<?php

class dummy extends basic implements fanController
{
    public function __construct(array $options = null)
    {
        parent::__construct($options);
    }

    public function _setRun(bool $state): bool
    {
        // Implement _setRun() method.
        $this->_debug('_setRun(' . ($state ? 'true' : 'false') . ');');
        return true;
    }

    public function _setMax(bool $state): bool
    {
        // Implement _setMax() method.
        $this->_debug('_setMax(' . ($state ? 'true' : 'false') . ');');
        return true;
    }

    public function _error(String $msg): bool
    {
        // Implement _error() method.
        $this->_debug('_error("' . $msg . '")');
        return true;
    }

}