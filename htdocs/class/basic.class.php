<?php

/**
 * Class basic
 */
class basic
{
    /**
     * @var bool
     */
    public $debug = false;

    /**
     * basic constructor.
     * @param array|null $options
     */
    public function __construct(array $options = null)
    {
        $this->_setOptions($options);
    }

    /**
     * @param $options
     * @return bool
     */
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

    /**
     * @param string $msg
     * @return bool
     */
    protected function _debug(string $msg): bool
    {
        if ($this->debug) {
            try {
                $date = new DateTime();
                $date = $date->format("Y-m-d h:i:s");
            } catch (Exception $exception) {
                $date = '#';
                echo 'Exception: ', $exception->getMessage(), "\n";
            };
            $bt = debug_backtrace();
            $caller = array_shift($bt);
            echo $date . ': ' . get_class($this) . ':' . $caller['line'] . '# ' . $msg . "\n";
            return true;

        }
        return false;
    }

}
