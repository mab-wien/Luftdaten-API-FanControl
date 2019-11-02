<?php

class basic
{
    public $debug = false;

    public function __construct(array $options = null)
    {
        $this->_setOptions($options);
    }

    private function _setOptions($options): bool
    {
        if (empty($options)) {
            return false;
        }
        foreach ($options as $key => $value) {
            $this->$key = $value;
        }
        $this->_debug('setDebugMode');
        return true;
    }

    protected function _debug(string $msg): bool
    {
        if ($this->debug) {
            $bt = debug_backtrace();
            $caller = array_shift($bt);
            $date = new DateTime();
            $date = $date->format("Y-m-d h:i:s");
            echo $date . ': ' . get_class($this) . ':' . $caller['line'] . '# ' . $msg . "\n";
            return true;
        }
        return false;
    }

}
