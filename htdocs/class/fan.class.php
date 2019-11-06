<?php

/**
 * Class fan
 */
class fan extends basic
{
    protected $stateFile = null;
    /**
     * @var fanState|null
     */
    protected $currentState = null;
    /**
     * @var fanState|null
     */
    protected $targetState = null;
    protected $runClassificationLevel = null;
    protected $maxClassificationLevel = null;
    protected $runWaitCnt = 0;
    protected $runIf = null;
    protected $maxIf = null;
    protected $classificationLevelIf = null;
    protected $maxWaitCnt = 0;

    /**
     * fan constructor.
     * @param array|null $options
     */
    public function __construct(array $options = null)
    {
        parent::__construct($options);

        $this->currentState = new fanState();
        $this->targetState = new fanState();
        $this->setCurrentState();

    }

    /**
     * @return bool
     */
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

    /**
     * @return int
     */
    private function getRunClassificationLevel(): int
    {
        return constant(sprintf('%s::%s', sensorClassification::class, $this->runClassificationLevel));
    }

    /**
     * @return int
     */
    private function getMaxClassificationLevel(): int
    {
        return constant(sprintf('%s::%s', sensorClassification::class, $this->maxClassificationLevel));
    }

    /**
     * @param Int $classificationLevel
     * @return bool
     */
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

    /**
     * @param fanController $controller
     * @param string $action
     * @param $defaultReturn
     * @return bool
     */
    private function _commit(fanController $controller, string $action, $defaultReturn): bool
    {
        if ($this->targetState->$action === NULL) {
            $this->_debug('targetState->' . $action . ' is null');
            return $defaultReturn;
        }
        $ifMethod = $action . 'If';
        if ($this->targetState->$action && is_array($this->$ifMethod)) {
            foreach ($this->$ifMethod as $ifMethod) {
                if (!is_callable($ifMethod)) {
                    continue;
                }
                if (!$this->targetState->$action) {
                    break;
                }
                $this->targetState->$action = call_user_func($ifMethod);
                $this->_debug($action . ' - ' . $ifMethod . '=> ' . $this->targetState->$action);
            }
        }
        $waitCnt = $action . 'WaitCnt';
        if ($this->targetState->$action === $this->currentState->$action) {
            $this->_debug('targetState->' . $action . ' is currentState => ' . $this->currentState->$action);
            if ($this->currentState->$waitCnt != 0) {
                $this->currentState->$waitCnt = 0;
                return true;
            }
            return $defaultReturn;
        }
        if ($this->targetState->$action && isset($this->$waitCnt) && !empty($this->$waitCnt)) {
            if ($this->$waitCnt > $this->currentState->$waitCnt) {
                $this->currentState->$waitCnt++;
                $this->_debug($action . ' - waitCnt (' . $this->currentState->$waitCnt . '/' . $this->$waitCnt . ')');
                return true;
            }
        }
        $actionMethod = '_set' . ucfirst($action);
        if ($controller->$actionMethod($this->targetState->$action)) {
            $this->currentState->$action = $this->targetState->$action;
            $this->currentState->$waitCnt = 0;
            return true;
        } else {
            $this->_debug('error: ' . $actionMethod . '(' . $this->targetState->$action . ');');
            return $defaultReturn;
        }
    }

    /**
     * @param fanController $controller
     * @return bool
     */
    public function commit(fanController $controller): bool
    {
        $changes = false;
        $actions = [
            'run',
            'max',
            'classificationLevel',
        ];
        foreach ($actions as $action) {
            $changes = $this->_commit(
                $controller,
                $action,
                $changes
            );
        }
        if ($changes) {
            $this->_saveState();
            if ($this->debug) {
                $this->_debug("target: " . print_r($this->targetState, true));
            }
        }
        return $changes;
    }

    /**
     * @return bool
     */
    private function _saveState(): bool
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
