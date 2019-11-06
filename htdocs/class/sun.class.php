<?php

/**
 * Class sun
 */
class sun extends basic
{
    /**
     * @var sun
     */
    private static $instance;
    protected $latitude = null;
    protected $longitude = null;
    protected $offsetSunset = 0;
    protected $offsetSunrise = 0;
    protected $timestampSunset = null;
    protected $timestampSunrise = null;
    protected $timestampCurrent = null;
    protected $zenith = 90.583333;
    protected $gmtOffset = 0;
    protected $current = null;

    /**
     * auth constructor.
     * @param array|null $options
     */
    public function __construct(array $options = null)
    {
        parent::__construct($options);
        $this->current = $this->_init();
    }

    private function _init(): string
    {
        $this->timestampCurrent = time();

        $this->timestampSunrise = date_sunrise(
            $this->timestampCurrent,
            SUNFUNCS_RET_TIMESTAMP,
            $this->latitude,
            $this->longitude,
            $this->zenith,
            $this->gmtOffset
        );
        $this->timestampSunset = date_sunset(
            $this->timestampCurrent,
            SUNFUNCS_RET_TIMESTAMP,
            $this->latitude,
            $this->longitude,
            $this->zenith,
            $this->gmtOffset
        );

        if (
            $this->timestampCurrent > ($this->timestampSunrise + $this->offsetSunrise) &&
            $this->timestampCurrent < ($this->timestampSunset + $this->offsetSunset)
        ) {
            return 'day';
        } else {
            return 'night';
        }
    }

    /**
     * @return bool
     */
    static public function isDay(): bool
    {
        $sun = sun::getInstance();
        if (empty($sun->current)) {
            return null;
        }
        if ($sun->current == "day") {
            return true;
        }
        return false;
    }

    /**
     * @param array|null $options
     * @return basic
     */
    public static function getInstance(array $options = null)
    {
        if (is_null(self::$instance)) {
            self::$instance = new self($options);
        }
        return self::$instance;
    }

    /**
     * @return bool
     */
    static public function isNight(): bool
    {
        $sun = sun::getInstance();
        if (empty($sun->current)) {
            return null;
        }
        if ($sun->current == "night") {
            return true;
        }
        return false;
    }
}