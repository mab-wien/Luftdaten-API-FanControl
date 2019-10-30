<?php

class dummy implements fanController
{
    protected $debug = false;

    public function __construct(array $options = null)
    {
        $this->setOptions($options);
    }

    private function setOptions($options): bool
    {
        if (empty($options)) {
            return false;
        }
        foreach ($options as $key => $value) {
            $this->$key = $value;
        }
        return true;
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

    private function _debug(string $msg): bool
    {
        if ($this->debug) {
            echo $msg . "\n";
            return true;
        }
        return false;
    }
}