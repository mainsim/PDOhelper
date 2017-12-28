<?php
/**
 * Simple data layer
 *
 * @link  **
 * @copyright Copyright (c) 2017 Sven Macolic
 * @license GNU General Public License.
 */

namespace mainsim\pdohelper;

class Factory {
      /**
      * @var string Database engine
      */
      public $engine = [];
      /**
      * @var string Connection object
      */
      public $db = [];
      /** 
      * @var object Connection object
      */
      public $pdo = []; 
      /**
      * @var array Connection object
      */
      private $object = [];
      /**
      * Constructor
      *
      * @return object
      */
      function __construct($db) {
          $this->connect($db);
      }
      /**
      * Database connection
      *
      * @return object
      */
      private function connect($db) {
             if(!isset($this->object['database'])):
                      $this->object['database'] = new Connect($db);
                      $this->pdo = $this->object['database']->PDOConnect(); // TODO switch for other drivers (pg, mysql, mysqli)
                      $this->db = $this->object['database']->db;
                      $this->engine = $this->object['database']::$conn->engine;
             endif;
      }
}

?>
