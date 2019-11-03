<?php

/**
 * Class dummy
 */
class dummy extends basic implements fanController
{
    /**
     * @var fanState|null
     */
    protected $currentState = null;
    /**
     * @var fanState|null
     */
    protected $targetState = null;

    /**
     * dummy constructor.
     * @param array|null $options
     */
    public function __construct(array $options = null)
    {
        parent::__construct($options);
        $this->currentState = new fanState();
        $this->targetState = new fanState();
    }

    /**
     * @param bool $state
     * @return bool
     */
    public function _setRun(bool $state): bool
    {
        // Implement _setRun() method.
        $this->targetState->run = $state;
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
        $this->targetState->max = $state;
        $this->_debug('_setMax(' . ($state ? 'true' : 'false') . ');');
        return true;
    }

    /**
     * @param int $level
     * @return bool
     */
    public function _setClassificationLevel(int $level): bool
    {
        // Implement _setClassificationLevel() method.
        $this->targetState->classificationLevel = $level;
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