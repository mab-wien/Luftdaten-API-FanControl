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
$provider = isset($config['fan']['provider']) ? $config['fan']['provider'] : 'dummy';
require_once(dirname(__FILE__) . '/class/provider/' . $provider . '.class.php');

/* check auth */
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
}
$allowedIps = $config['general']['allowedIPs'];
if (is_array($allowedIps) && !in_array($ip,$allowedIps)) {
    exit;
}

/* main */
$apiSensors = new sensor($config['sensor']);
if ($apiSensors->initByInput()) {
    $fan = new fan($config['fan']);
    if ($fan->setTargetState($apiSensors->getClassificationLevel())) {
        $pid = 0;
        if ($config['general']['backgroundProcess']) {
            $pid = pcntl_fork();
        }
        if ($pid == 0) {
            $fan->commit(new $provider($config[$provider]));
        }
    }
}
