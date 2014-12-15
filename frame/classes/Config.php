<?php
define('ACONF', APP_PATH. 'config/');

/**
 * Class Config
 *
 * Collects all configs from files.
 * Generates one array of configs.
 */
class Config
{
    /**
     * @var array Array of configs
     */
    private static $conf;

    private static $path = ACONF;

    /**
     * Get config by key or key with subkey
     *
     * @param null $key
     * @param null $subkey
     * @return bool
     */
    public static function get($key = null, $subkey = null)
    {
        if (is_null($key) && is_null($subkey))
            return self::$conf;
        if (array_key_exists($key, self::$conf) && is_null($subkey))
            return self::$conf[$key];
        if (array_key_exists($subkey, self::$conf[$key]) && !is_null($key) && !is_null($subkey))
            return self::$conf[$key][$subkey];
        return false;
    }


    /**
     * Initialize config array
     *
     * @param null $dir
     * @return array
     */
    public static function init($dir = null)
    {
        $confDefault = Config::assembleConfig(self::$path . 'default/');
        self::$conf = $confDefault;

        if (isset($dir) && !is_null($dir)) {
            $pathConfig = self::$path . $dir . '/';
            $confUser = Config::assembleConfig($pathConfig);
            self::$conf = array_replace_recursive($confDefault, $confUser);
        }
        return self::$conf;
    }

    /**
     * Create one config array from all files in directory which is passed like parameter
     *
     * @param $path
     * @return array
     */
    public static function assembleConfig($path)
    {
        $filesList = glob($path . '*.php');
        $config = array();
        foreach ($filesList as $file) {
            $confTemp = require_once $file;
            $config = array_merge($config, $confTemp);
        }
        return $config;
    }

    /**
     * Sets path to user config directory.
     * '/' on the end of line is required.
     *
     * Attention! Do it carefully.
     * If you redefine config folder
     * it will have to contains folder 'default' for default configs.
     *
     * @param $path
     */
    public static function setPath($path)
    {
        if (is_dir($path))
            self::$path = $path;
    }

}