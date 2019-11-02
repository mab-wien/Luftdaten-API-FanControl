<?php

class config
{
    protected $configSettings = null;

    public function __construct(string $defaultIniFile)
    {
        $this->loadConfig($defaultIniFile);
    }

    public function get(string $section = null, string $varName = null, $defaultValue = null)
    {
        if (!is_array($this->configSettings)) {
            return [];
        }
        if (empty($section)) {
            return $this->configSettings;
        }
        if (!isset($this->configSettings[$section])) {
            return [];
        }
        if (empty($varName)) {
            return $this->configSettings[$section];
        }
        if (!isset($this->configSettings[$section][$varName])) {
            return $defaultValue;
        }
        return $this->configSettings[$section][$varName];
    }

    private function loadConfig(string $defaultIniFile): bool
    {
        if (!file_exists(($defaultIniFile))) {
            return false;
        }
        $this->configSettings = parse_ini_file($defaultIniFile, true, INI_SCANNER_TYPED);
        if (empty($this->configSettings)) {
            return false;
        }
        return true;
    }

    public function getOverrides(string $overrideConfigFile): array
    {
        if (!file_exists(($overrideConfigFile))) {
            return $this->configSettings;
        }
        $configSettings = $this->configSettings;
        if (!is_array($configSettings)) {
            $configSettings = [];
        }
        $overrideConfig = parse_ini_file($overrideConfigFile, true, INI_SCANNER_TYPED);
        foreach ($overrideConfig as $key => $value) {
            foreach ($value as $vKey => $vValue) {
                if (!isset($overrideConfig[$key])) {
                    $configSettings[$key] = [];
                }
                $configSettings[$key][$vKey] = $vValue;
            }
        }
        return $configSettings;
    }

    public function setOverride(string $overrideConfigFile): bool
    {
        $overrideConfig = $this->getOverrides($overrideConfigFile);
        if (empty($overrideConfig)) {
            return false;
        }
        $this->configSettings = $overrideConfig;
        return true;
    }

    public function getFanProvider(string $defaultProvider = 'dummy'): string
    {
        return $this->get('fan', 'provider', 'dummy');
    }

}