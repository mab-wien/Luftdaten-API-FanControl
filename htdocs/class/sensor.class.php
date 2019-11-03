<?php

/**
 * Class sensorClassification
 */
abstract class sensorClassification
{
    const best = 1;
    const better = 2;
    const good = 3;
    const moderate = 4;
    const unhealthy = 5;
    const unhealthier = 6;
    const unhealthiest = 7;
    const dangerous = 8;
    const dangerousest = 9;
}

/**
 * Class sensor
 */
class sensor extends basic
{
    protected $json = null;
    protected $data = null;
    protected $pm2 = null;
    protected $pm10 = null;
    protected $classificationLevel = null;
    protected $idPM2 = null;
    protected $idPM10 = null;
    protected $columnName = null;

    /**
     * sensor constructor.
     * @param array|null $options
     */
    public function __construct(array $options = null)
    {
        parent::__construct($options);
    }

    /**
     * @param $value
     * @param String $msg
     * @return bool
     */
    private function isEmpty($value, String $msg): bool
    {
        if (empty($value)) {
            $this->_debug($msg . ' is empty');
            return true;
        }
        return false;
    }

    /**
     * @param string $id
     * @param array $array
     * @return bool|mixed
     */
    private function getSensorValueById(string $id, array $array)
    {
        if (!isset($array["sensordatavalues"])) {
            $this->_debug('sensordatavalues not found');
            return false;
        }
        $sensorDataValues = $array["sensordatavalues"];
        $column = @array_column($sensorDataValues, $this->columnName);
        if (empty($column)) {
            $this->_debug('array_column not found:' . $this->columnName);
            return false;
        }
        $idx = array_search($id, $column);
        if ($idx < 0) {
            $this->_debug('id not found: ' . $id);
            return false;
        }
        if (!isset($sensorDataValues[$idx]['value'])) {
            $this->_debug('value not found');
            return false;
        }
        return $sensorDataValues[$idx]['value'];
    }

    /**
     * @return string
     */
    public function initByInput(): string
    {
        return $this->init(file_get_contents("php://input"));
    }

    /**
     * @param string $data
     * @return bool
     */
    public function init(string $data): bool
    {
        if (!$this->setJsonData($data)) {
            $this->_debug('setJsonData');
            return false;
        }
        if (!$this->setSensorData($this->json)) {
            $this->_debug('setSensorData');
            return false;
        }
        if (!$this->setPMData($this->json)) {
            $this->_debug('setPMData');
            return false;
        }
        if (!$this->setClassificationLevel()) {
            $this->_debug('setClassificationLevel');
            return false;
        }
        return true;
    }

    /**
     * @param string $data
     * @return bool
     */
    private function setJsonData(string $data): bool
    {
        $this->json = json_decode($data, true);
        if ($this->isEmpty($this->json, 'input-data')) {
            return false;
        }
        return true;
    }

    /**
     * @param array $jsonData
     * @return bool
     */
    private function setSensorData(array $jsonData): bool
    {
        $sensorData = $jsonData["sensordatavalues"];
        if ($this->isEmpty($sensorData, 'input-json-sensor-data')) {
            return false;
        }
        $this->data = $sensorData;
        return true;
    }

    /**
     * @param array $jsonData
     * @return bool
     */
    private function setPMData(array $jsonData): bool
    {
        $pm2 = $this->getSensorValueById($this->idPM2, $jsonData);
        if ($this->isEmpty($pm2, 'input-json-sensor-data-' . $this->idPM2)) {
            return false;
        }
        $this->pm2 = $pm2;
        $this->_debug('pm2: ' . $this->pm2);
        $pm10 = $this->getSensorValueById($this->idPM10, $jsonData);
        if ($this->isEmpty($pm10, 'input-json-sensor-data-' . $this->idPM10)) {
            return false;
        }
        $this->pm10 = $pm10;
        $this->_debug('pm10: ' . $this->pm10);
        return true;
    }


    /**
     * @return bool
     */
    private function setClassificationLevel(): bool
    {
        if ($this->pm10 <= 2 && $this->pm2 <= 2) {
            $this->classificationLevel = sensorClassification::best;
        } else if ($this->pm10 <= 27 && $this->pm2 <= 6) {
            $this->classificationLevel = sensorClassification::better;
        } else if ($this->pm10 <= 54 && $this->pm2 <= 12) {
            $this->classificationLevel = sensorClassification::good;
        } else if ($this->pm10 <= 154 && $this->pm2 <= 35) {
            $this->classificationLevel = sensorClassification::moderate;
        } else if ($this->pm10 <= 254 && $this->pm2 <= 55) {
            $this->classificationLevel = sensorClassification::unhealthy;
        } else if ($this->pm10 <= 354 && $this->pm2 <= 150) {
            $this->classificationLevel = sensorClassification::unhealthier;
        } else if ($this->pm10 <= 424 && $this->pm2 <= 250) {
            $this->classificationLevel = sensorClassification::unhealthiest;
        } else if ($this->pm10 <= 604 && $this->pm2 <= 500) {
            $this->classificationLevel = sensorClassification::dangerous;
        } else {
            $this->classificationLevel = sensorClassification::dangerousest;
        }
        if ($this->isEmpty($this->classificationLevel, 'setClassificationLevel')) {
            return false;
        }
        $this->_debug('setClassificationLevel(' . $this->classificationLevel . ')');
        return true;
    }

    /**
     * @return int
     */
    public function getClassificationLevel(): int
    {
        return $this->classificationLevel;
    }
}
