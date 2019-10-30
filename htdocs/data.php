<?php
/* show errors */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/* includes */
require_once(dirname(__FILE__) . '/class/sensor.class.php');
require_once(dirname(__FILE__) . '/class/fan.class.php');

/* config */
function loadConfig(string $defaultIniFile, string $customIniFile): array
{
    $config = parse_ini_file($defaultIniFile, true, INI_SCANNER_TYPED);
    if (!file_exists($customIniFile)) {
        return $config;
    }
    $customConfig = parse_ini_file($customIniFile, true, INI_SCANNER_TYPED);
    foreach ($customConfig as $key => $value) {
        foreach ($value as $vKey => $vValue) {
            $config[$key][$vKey] = $vValue;
        }
    }
    return $config;
}

$config = loadConfig('default.ini', 'config.ini');
$driver = isset($config['fan']['driver']) ? $config['fan']['driver'] : 'dummy';
require_once(dirname(__FILE__) . '/class/provider/' . $driver . '.class.php');

/* main */
$apiSensors = new sensor($config['sensor']);
if ($apiSensors->initByInput()) {
    $fan = new fan($config['fan']);
    if ($fan->setTargetState($apiSensors->getClassificationLevel())) {
        $fan->commit(new $driver($config[$driver]));
    }
}
