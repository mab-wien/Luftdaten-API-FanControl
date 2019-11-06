<?php

/**
 * Interface fanController
 */
interface fanController
{
    /**
     * @param bool $state
     * @return bool
     */
    public function _setRun(bool $state): bool;

    /**
     * @param bool $state
     * @return bool
     */
    public function _setMax(bool $state): bool;

    /**
     * @param int $level
     * @return bool
     */
    public function _setClassificationLevel(int $level): bool;

    /**
     * @param String $msg
     * @return bool
     */
    public function _error(String $msg): bool;

}
