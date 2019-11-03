<?php

/**
 * Class dummy
 */
class dummy extends basic implements fanController
{
    /**
     * dummy constructor.
     * @param array|null $options
     */
    public function __construct(array $options = null)
    {
        parent::__construct($options);
    }

    /**
     * @param bool $state
     * @return bool
     */
    public function _setRun(bool $state): bool
    {
        // Implement _setRun() method.
        $this->_debug('_setRun(' . ($state ? 'true' : 'false') . ');');
        return true;
    }

    /**
     * @param bool $state
     * @return bool
     */
    public function _setMax(bool $state): bool
    {
        // Implement _setMax() method.
        $this->_debug('_setMax(' . ($state ? 'true' : 'false') . ');');
        return true;
    }

    /**
     * @param String $msg
     * @return bool
     */
    public function _error(String $msg): bool
    {
        // Implement _error() method.
        $this->_debug('_error("' . $msg . '")');
        return true;
    }

}