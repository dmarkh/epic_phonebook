<?php

/* 
 *    Singleton example for usage in classes :
 *    $db = singleton::getInstance('Database'); 
 *    $db will contain a shared single instance of the class 'Database'
 */

class singleton
{
   /**
    * Returns single instance of the class, Singleton Pattern at work.
    *
    * @param string $class name of the class to be singletonized
    * @return class $instance singleton instance of the required class
    */
    public static function &getInstance ($class) {
        static $singleton_instances = array();  // array of instance names
        if (!array_key_exists($class, $singleton_instances)) {
            $singleton_instances[$class] = new $class;
        }
        $instance =& $singleton_instances[$class];
        return $instance;
    }
    
}
