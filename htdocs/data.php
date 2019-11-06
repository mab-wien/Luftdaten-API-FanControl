<?php
/* show errors */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/* includes */
spl_autoload_register(function ($class_name) {
    require_once(dirname(__FILE__) . '/class/' . $class_name . '.class.php');
});

/* config */
$config = new config('default.ini');
$config->setOverride('config.ini');

/* check auth */
$auth = new auth($config->get('auth'));
if (!$auth->isIpAllowed() || !$auth->isAuthenticated()) {
    exit;
}

/* main */
$provider = $config->getFanProvider('dummy');
sun::getInstance($config->get('sun'));
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
