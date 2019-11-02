<?php

interface fanController
{
    public function _setRun(bool $state): bool;

    public function _setMax(bool $state): bool;

    public function _error(String $msg): bool;
}

class fanState
{
    public $run = null;
    public $max = null;
    public $classificationLevel = null;
}

class fan extends basic
{
    protected $stateFile = null;
    protected $currentState = null;
    protected $targetState = null;
    protected $runClassificationLevel = null;
    protected $maxClassificationLevel = null;

    public function __construct(array $options = null)
    {
        parent::__construct($options);

        $this->currentState = new fanState();
        $this->targetState = new fanState();
        $this->setCurrentState();

    }

    private function setCurrentState(): bool
    {
        if (file_exists($this->stateFile)) {
            if ($fanState = file_get_contents($this->stateFile)) {
                $this->currentState = unserialize($fanState);
                return true;
            }
        }
        $this->currentState = new fanState();
        return false;
    }

    private function getRunClassificationLevel(): int
    {
        return constant(sprintf('%s::%s', sensorClassification::class, $this->runClassificationLevel));
    }

    private function getMaxClassificationLevel(): int
    {
        return constant(sprintf('%s::%s', sensorClassification::class, $this->maxClassificationLevel));
    }

    public function setTargetState(Int $classificationLevel)
    {
        if (empty($classificationLevel)) {
            return false;
        }
        $this->targetState = new fanState();
        $this->targetState->classificationLevel = $classificationLevel;
        if ($classificationLevel <= $this->getRunClassificationLevel()) {
            $this->targetState->run = true;
        } elseif ($classificationLevel > $this->getRunClassificationLevel()) {
            $this->targetState->run = false;
        }
        if ($classificationLevel <= $this->getMaxClassificationLevel()) {
            $this->targetState->max = true;
        } elseif ($classificationLevel > $this->getMaxClassificationLevel()) {
            $this->targetState->max = false;
        }
        return true;
    }

    public function commit(fanController $controller)
    {
        $changes = false;
        $this->_debug("current: " . print_r($this->currentState, true));
        if ($this->targetState->run !== NULL) {
            if ($this->currentState->run !== $this->targetState->run) {
                if ($controller->_setRun($this->targetState->run)) {
                    $this->currentState->run = $this->targetState->run;
                    $changes = true;
                } else {
                    $this->_debug('error: _setRun(' . $this->targetState->run . ');');
                    return false;
                }
            }
        }
        if ($this->targetState->max !== NULL) {
            if ($this->currentState->max !== $this->targetState->max) {
                if ($controller->_setMax($this->targetState->max)) {
                    $this->currentState->max = $this->targetState->max;
                    $changes = true;
                } else {
                    $this->_debug('error: _setMax(' . $this->targetState->max . ');');
                    return false;
                }
            }
        }
        if ($this->currentState->classificationLevel !== $this->targetState->classificationLevel) {
            $this->currentState->classificationLevel = $this->targetState->classificationLevel;
            $changes = true;
        }
        if ($changes) {
            $this->_saveSate();
            if ($this->debug) {
                $this->_debug("target: " . print_r($this->targetState, true));
            }
        }
        return true;
    }

    private function _saveSate(): bool
    {
        if (empty($this->stateFile)) {
            return false;
        }
        if (!file_put_contents($this->stateFile, serialize($this->currentState))) {
            return false;
        }
        return true;
    }

}
