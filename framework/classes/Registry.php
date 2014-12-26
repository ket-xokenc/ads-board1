<?php
namespace application\classes;
class Registry
{

    /**
     * Singleton registry instance
     * @var Singleton registry instance
     */
    static private $instance = null;
    /**
     * Hash table
     * @var array
     */
    private $registry = array();

    /**
     * Get Registry instanse
     * 
     * @return Singleton registry instance
     */
    static public function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Save an object by key into registry
     * 
     * @param integer|string $key
     * @param object $object
     * @return void
     */
    static public function set($key, $data)
    {
        self::getInstance()->registry[$key] = $data;
    }

    /**
     * Get an object by key from registry
     * 
     * @param integer|string $key
     * @return object
     */
    static public function get($key = null, $subkey = null)
    {
//        return self::getInstance()->registry[$key];
        if (is_null($key) && is_null($subkey))
            return self::getInstance()->registry;
        if (array_key_exists($key, self::getInstance()->registry) && is_null($subkey))
            return self::getInstance()->registry[$key];
        if (array_key_exists($subkey, self::getInstance()->registry[$key]) && !is_null($key) && !is_null($subkey))
            return self::getInstance()->registry[$key][$subkey];
        return false;
    }

    private function __construct()
    {
    }

    private function __clone()
    {
        
    }

}

