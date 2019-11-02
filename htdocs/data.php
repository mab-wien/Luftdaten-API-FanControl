<?php
/* show errors */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/* includes */
require_once(dirname(__FILE__) . '/class/config.class.php');
require_once(dirname(__FILE__) . '/class/basic.class.php');
require_once(dirname(__FILE__) . '/class/auth.class.php');
require_once(dirname(__FILE__) . '/class/sensor.class.php');
require_once(dirname(__FILE__) . '/class/fan.class.php');

/* config */
$config = new config('default.ini');
$config->setOverride('config.ini');
$provider = $config->getFanProvider();
require_once(dirname(__FILE__) . '/class/provider/' . $provider . '.class.php');

/* check auth */
$auth = new auth($config->get('auth'));
if (!$auth->isIpAllowed() || !$auth->isAuthenticated()) {
    exit;
}

/* main */
$apiSensors = new sensor($config->get('sensor'));
if ($apiSensors->initByInput()) {
    $fan = new fan($config->get('fan'));
    if ($fan->setTargetState($apiSensors->getClassificationLevel())) {
        $pid = 0;
        if ($config->get('general', 'backgroundProcess')) {
            $pid = pcntl_fork();
        }
        if ($pid == 0) {
            $fan->commit(new $provider($config->get($provider)));
        }
    }
}
