<?php
require_once(dirname(__FILE__) . '/myjablotron/myjablotron.class.php');

class jablotron extends basic implements fanController
{
    protected $username = null;
    protected $password = null;
    protected $pin = null;
    protected $authenticated = false;
    protected $myJA = null;
    protected $currentState = null;
    protected $targetState = null;
    protected $currentPGM = null;
    protected $runPGMId = null;
    protected $maxPGMId = null;
    protected $loggedIn = null;
    protected $cookieFile = null;

    public function __construct(array $options = null)
    {
        parent::__construct($options);
        $this->currentState = new fanState();
        $this->targetState = new fanState();
    }

    public function __destruct()
    {
        if ($this->myJA !== NULL) {
            $this->_debug("jablotron-target: " . print_r($this->targetState, true));
        }
    }

    private function init(): bool
    {
        if ($this->myJA !== NULL) {
            return true;
        }
        define('MY_COOKIE_FILE', $this->cookieFile);
        $this->myJA = new MyJablotron(
            $this->username,
            $this->password
        );
        $this->myJA->debug($this->debug);
        if ($this->login()) {
            if ($this->getCurrentPGM()) {
                $this->currentState->run = $this->getSensorValueById($this->runPGMId);
                $this->currentState->max = $this->getSensorValueById($this->maxPGMId);
                $this->currentState->classificationLevel = null;
                $this->_debug("jablotron-current: " . print_r($this->currentState, true));
                return true;
            }
        }
        return false;
    }

    private function login(): bool
    {
        if ($this->myJA->login()) {
            $this->authenticated = true;
            $this->_debug('authenticated=true');
            $ret = true;
        } else {
            $this->_debug('authenticated=false');
            $this->_debug($this->myJA->getErrors()[0]);
            $ret = false;
        }
        $this->loggedIn = $ret;
        return $ret;
    }

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

    public function _setRun(bool $state): bool
    {
        if (!$this->init()) {
            return false;
        }
        $this->targetState->run = $state;
        if ($this->currentState->run == $state) {
            return true;
        }
        $this->_debug('_setRun(' . ($state ? 'true' : 'false') . ');');
        if ($state) {
            return $this->myJA->lock('PGM_' . $this->runPGMId, $this->pin);
        } else {
            return $this->myJA->unlock('PGM_' . $this->runPGMId, $this->pin);
        }
    }

    public function _setMax(bool $state): bool
    {
        if (!$this->init()) {
            return false;
        }
        $this->targetState->max = $state;
        if ($this->currentState->max == $state) {
            return true;
        }
        $this->_debug('_setMax(' . ($state ? 'true' : 'false') . ');');
        if ($state) {
            return $this->myJA->lock('PGM_' . $this->maxPGMId, $this->pin);
        } else {
            return $this->myJA->unlock('PGM_' . $this->maxPGMId, $this->pin);
        }
    }

    public function _error(String $msg): bool
    {
        // Implement _error() method.
        $this->_debug('_error("' . $msg . '")');
        return true;
    }
}
