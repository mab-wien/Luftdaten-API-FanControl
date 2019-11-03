<?php
require_once(dirname(__FILE__) . '/myjablotron/myjablotron.class.php');

/**
 * Class jablotron
 */
class jablotron extends basic implements fanController
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
     * @var MyJablotron|null
     */
    protected $myJA = null;
    protected $username = null;
    protected $password = null;
    protected $pin = null;
    protected $authenticated = false;
    protected $currentPGM = null;
    protected $runPGMId = null;
    protected $maxPGMId = null;
    protected $loggedIn = null;
    protected $cookieFile = null;

    /**
     * jablotron constructor.
     * @param array|null $options
     */
    public function __construct(array $options = null)
    {
        parent::__construct($options);
        $this->currentState = new fanState();
        $this->targetState = new fanState();
    }

    /**
     */
    public function __destruct()
    {
        if ($this->myJA !== NULL) {
            $this->_debug("target: " . print_r($this->targetState, true));
            $this->debugMyJAErrors();
        }

    }

    /**
     */
    private function debugMyJAErrors()
    {
        if ($this->myJA !== NULL) {
            $errors = $this->myJA->getErrors();
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->_debug('myJAError: ' . $error);
                }
            }
        }
    }

    /**
     * @return bool
     */
    private function init(): bool
    {
        if ($this->myJA !== NULL) {
            return true;
        }
        $this->myJA = new MyJablotron(
            $this->username,
            $this->password,
            $this->cookieFile
        );
        $this->myJA->debug($this->debug);
        if ($this->login()) {
            if ($this->getCurrentPGM()) {
                $this->currentState->run = $this->getSensorValueById($this->runPGMId);
                $this->currentState->max = $this->getSensorValueById($this->maxPGMId);
                $this->currentState->classificationLevel = null;
                $this->_debug("current: " . print_r($this->currentState, true));
                return true;
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    private function login(): bool
    {
        if ($this->myJA->login()) {
            $this->authenticated = true;
            $this->_debug('authenticated=true');
            $ret = true;
        } else {
            $this->_debug('authenticated=false');
            $ret = false;
        }
        $this->loggedIn = $ret;
        return $ret;
    }

    /**
     * @return bool
     */
    private function getCurrentPGM(): bool
    {
        $currentPGM = $this->myJA->getPGM();
        if ($currentPGM === false && $this->loggedIn == false) {
            $this->loggedIn = $this->login();
            return $this->getCurrentPGM();
        }

        if (empty($currentPGM)) {
            return false;
        } else {
            $this->currentPGM = $currentPGM;
            return true;
        }
    }

    /**
     * @param string $id
     * @return bool|mixed
     */
    private function getSensorValueById(string $id)
    {
        $id = $id - 1;
        if (empty($this->currentPGM)) {
            $this->_debug('currentPGM is empty');
            return false;
        }
        if (empty($this->currentPGM[$id])) {
            $this->_debug('id is empty');
            return false;
        }
        if (!isset($this->currentPGM[$id]['stav'])) {
            $this->_debug('stav is not set');
            return false;
        }
        return $this->currentPGM[$id]['stav'];
    }

    /**
     * @param bool $state
     * @return bool
     */
    public function _setRun(bool $state): bool
    {
        if (!$this->init()) {
            return false;
        }
        $this->targetState->run = $state;
        if ($this->currentState->run == $state) {
            $this->_debug('_setRun(' . ($state ? 'true' : 'false') . '); is currentSate');
            return true;
        }
        $this->_debug('_setRun(' . ($state ? 'true' : 'false') . ');');
        if ($state) {
            return $this->myJA->lock('PGM_' . $this->runPGMId, $this->pin);
        } else {
            return $this->myJA->unlock('PGM_' . $this->runPGMId, $this->pin);
        }
    }

    /**
     * @param bool $state
     * @return bool
     */
    public function _setMax(bool $state): bool
    {
        if (!$this->init()) {
            return false;
        }
        $this->targetState->max = $state;
        if ($this->currentState->max == $state) {
            $this->_debug('_setMax(' . ($state ? 'true' : 'false') . '); is currentSate');
            return true;
        }
        $this->_debug('_setMax(' . ($state ? 'true' : 'false') . ');');
        if ($state) {
            return $this->myJA->lock('PGM_' . $this->maxPGMId, $this->pin);
        } else {
            return $this->myJA->unlock('PGM_' . $this->maxPGMId, $this->pin);
        }
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
