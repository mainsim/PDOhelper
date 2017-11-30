<?php
/**
 * Simple data layer
 *
 * @link  **
 * @copyright Copyright (c) 2013 Sven Macolic
 * @license GNU General Public License.
 */

namespace mainsim\pdohelper;

class Factory {
      /**
      * @var string Database engine
      */
      public static $engine;
      /**
      * @var string Connection object
      */
      public static $db;
      /** 
      * @var object Connection object
      */
      public static $pdo; 
      /**
      * @var array Connection object
      */
      public static $object = [];
      /**
      * Database connection
      *
      * @return object
      */
      public static function connect($db) {
             if(!isset(self::$object['database'])):
                                                    self::$object['database'] = new Connect($db);
                                                    self::$pdo = self::$object['database']->PDOConnect(); // TODO switch for other drivers (pg, mysql, mysqli)
                                                    self::$db = self::$object['database']->db;
                                                    self::$engine = self::$object['database']::$conn->engine;
             endif;
             return self::$object['database'];
      }
}

?>
