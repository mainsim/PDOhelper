<?php

/**
 * Simple data layer
 *
 * @link  **
 * @copyright Copyright (c) 2017 Sven Macolic
 * @license GNU General Public License.
 */

namespace mainsim\pdohelper;

use PDO;

class PDOProcedureHelper {
         /** 
         * @var object PDO 
         */
         public $pdo;
         /** 
         * @var string engine 
         */
         public $engine;
         /** 
         * @var string engine 
         */
         public $db;
         /**
         * Class constructor
         *
         * @return void
         */
         function __construct($db) {
             set_exception_handler([$this, 'exceptionHandler']);
             $conn = new Factory($db);
             $this->pdo = $conn->pdo;
             $this->engine = $conn->engine;
             $this->db = $conn->db;
         }
         /**
         * Get database records
         *
         * @param array $dataObject
         * @param boolean $type
         * @return array / Exception
         */
         public function select($dataObject, $type = false) {
                $do = (object)$dataObject;  

                $tables = property_exists($do, 'tables') ? self::parseSelectTables($do->tables) : '';
                $fields = $this->parseSelectFields($do->fields);
                $joins = property_exists($do, 'join') ? self::parseSelectJoins($do->join) : '';
                $condition = property_exists($do, 'condition') ? self::parseCondition($do->condition, (property_exists($do, 'operator') ? $do->operator : NULL)) : '';
                $group = property_exists($do, 'group') ? 'GROUP BY '.$do->group : '';
                $order = property_exists($do, 'order') ? self::parseSelectOrder($do->order) : '';
                $limit = property_exists($do, 'limit') ? self::parseSelectLimit($do->limit) : '';

                switch(strtolower($this->engine)):
                        case 'mysql': $query = $this->pdo->prepare('CALL selectData(?, ?, ?, ?, ?, ?, ?)'); break;
                        case 'sqlsrv': $query = $this->pdo->prepare('EXEC selectData ?, ?, ?, ?, ?, ?, ?'); break;
                endswitch;

                $query->bindParam(1, $tables, PDO::PARAM_STR);
                $query->bindParam(2, $fields, PDO::PARAM_STR);
                $query->bindParam(3, $joins, PDO::PARAM_STR);
                $query->bindParam(4, $condition, PDO::PARAM_STR);
                $query->bindParam(5, $group, PDO::PARAM_STR);
                $query->bindParam(6, $order, PDO::PARAM_STR);
                $query->bindParam(7, $limit, PDO::PARAM_STR);

                try {
                  $query->execute();
                  return self::getArray($query->fetchAll(), $type);
                } catch(Exception $e) {}
         }
         /**
         * Insert record
         *
         * @param array $dataObject
         * @param boolean $type
         * @return array / Exception
         */
         public function insert($dataObject, $type = false) {
                $do = (object)$dataObject;
                $table = $do->table;
                $fields = self::parseInsertFields($do->fields);
                $values = self::parseInsertValues($do->fields);
                $increment = $do->increment;

                switch(strtolower($this->engine)):
                        case 'mysql': $query = $this->pdo->prepare('CALL insertData(?, ?, ?, ?)'); break;
                        case 'sqlsrv': $query = $this->pdo->prepare('EXEC insertData ?, ?, ?, ?'); break;
                endswitch;

                $query->bindParam(1, $table, PDO::PARAM_STR);
                $query->bindParam(2, $fields, PDO::PARAM_STR);
                $query->bindParam(3, $values, PDO::PARAM_STR);
                $query->bindParam(4, $increment, PDO::PARAM_STR);
    
                try {
                    $query->execute();
                    return self::getArray($query->fetchAll(), $type);
                } catch(Exception $e) {}
         }
         /**
         * Insert multiple records
         *
         * @param array $dataObject
         * @param boolean $type
         * @return array / Exception
         */
         public function insertMultiple($dataObject, $type = false) {
                $do = (object)$dataObject;

                $table = $do->table;
                $fields = self::parseInsertMultipleFields($do->fields);
                $data = self::parseInsertMultipleValues($do->data);
                $increment = $do->increment;

                switch(strtolower($this->engine)):
                        case 'mysql': $query = $this->pdo->prepare('CALL insertMultipleData(?, ?, ?, ?)'); break;
                        case 'sqlsrv': $query = $this->pdo->prepare('EXEC insertMultipleData ?, ?, ?, ?'); break;
                endswitch;

                $query->bindParam(1, $table, PDO::PARAM_STR);
                $query->bindParam(2, $fields, PDO::PARAM_STR);
                $query->bindParam(3, $data, PDO::PARAM_STR);
                $query->bindParam(4, $increment, PDO::PARAM_STR);

                try {
                    $query->execute();
                    return self::getArray($query->fetchAll(), $type);
                } catch(Exception $e) {}
         }
         /**
         * Update record
         *
         * @param array $dataObject
         * @param boolean $type
         * @return array / Exception
         */
         public function update($dataObject, $type = false) {
                $do = (object)$dataObject;

                $table = $do->table;
                $fields = self::parseUpdateFields($do->fields);
                $condition = self::parseCondition($do->condition, (property_exists($do, 'operator') ? $do->operator : NULL));

                switch(strtolower($this->engine)):
                        case 'mysql': $query = $this->pdo->prepare('CALL updateData(?, ?, ?)'); break;
                        case 'sqlsrv': $query = $this->pdo->prepare('EXEC updateData ?, ?, ?'); break;
                endswitch;
                
                $query->bindParam(1, $table, PDO::PARAM_STR);
                $query->bindParam(2, $fields, PDO::PARAM_STR);
                $query->bindParam(3, $condition, PDO::PARAM_STR);

                try {
                    $query->execute();
                    return self::getArray($query->fetchAll(), $type);
                } catch(Exception $e) {}
         }
         /**
         * Delete record
         *
         * @param array $dataObject
         * @param boolean $type
         * @return array / Exception
         */
         public function delete($dataObject, $type = false) {
                $do = (object)$dataObject;

                $table = $do->table;
                $condition = self::parseCondition($do->condition, (property_exists($do, 'operator') ? $do->operator : NULL));
                $increment = $do->increment;

                switch(strtolower($this->engine)):
                        case 'mysql': $query = $this->pdo->prepare('CALL deleteData(?, ?, ?)'); break;
                        case 'sqlsrv': $query = $this->pdo->prepare('EXEC deleteData ?, ?, ?'); break;
                endswitch;

                $query->bindParam(1, $table, PDO::PARAM_STR);
                $query->bindParam(2, $condition, PDO::PARAM_STR);
                $query->bindParam(3, $increment, PDO::PARAM_STR);

                try {
                    $query->execute();
                    return self::getArray($query->fetchAll(), $type);
                } catch(Exception $e) {}
         }
         /**
         *Query database
         *
         * @param string $q
         * @param boolean $type
         * @return array / Exception
         */
         public function query($q, $type = false) {
                print_r($this->db);
                $query = $this->pdo->prepare($q);
                try {
                    $query->execute();
                    print_r($query->fetchAll());
                    return self::getArray($query->fetchAll(), $type);
                } catch(Exception $e) {}
         }
         /**
         *Add columns to database table
         *
         * @param array $dataObject
         * @return boolean
         */
         public function addColumns($dataObject) {
                $do = (object)$dataObject;
                $sql = 'ALTER TABLE '.$do->table.' '; 

                while($col = each($do->cols)):
                    $sql .= 'ADD COLUMN '.$col['key'].' '.$col['value'].',';
                endwhile;

                $sql = substr($sql, 0, -1).';';
                $query = $this->pdo->prepare($sql);

                if(!$query):
                    return false;
                endif;

                try {
                    $query->execute();
                    return true;
                } catch(Exception $e) {}
         }
         /**
         * Get database tables and columns
         *
         * @param array $dataObject
         * @param string $table
         * @param string $field
         * @param string $search
         * @param boolean $type
         * @return boolean
         */
         public function isInDb($table = '', $field = '', $search = '', $type_return = false, $type = false) {
                switch(strtolower($this->engine)):
                        case 'mysql': $query = $this->pdo->prepare('CALL tablesColumnsData(?)'); break;
                        case 'sqlsrv': $query = $this->pdo->prepare('EXEC tablesColumnsData ?'); break;
                endswitch;

                $query->bindParam(1, $this->db, PDO::PARAM_STR);

                try {
                    $query->execute();
                    $result = self::getArray($query->fetchAll(), $type);
                } catch(Exception $e) {}

                if(empty($table) && empty($field)):
                    return $result;
                endif;

                while(list($key, $val) = each($result)):
                    if(!empty($table) && empty($field)):
                        if($val['tname'] == $table):
                            return true;
                        endif;
                    endif;
                    if(!empty($table) && !empty($field)):
                        if($val['tname'] == $table):
                            $fcol = explode(',', $val['fname']);
                            $ftcol = explode(',', $val['ftype']);
                            while(list($k, $v) = each($fcol)):
                                if($v == $field):
                                    if(empty($search)):
                                        if($type_return):
                                            return $ftcol[$k];
                                        else:
                                            return true;
                                        endif;
                                    else:
                                        return $this->checkTypes(strtolower($search), $ftcol[$k]);
                                    endif;
                                endif;   
                            endwhile;
                        endif;
                    endif;
                endwhile;
                return false;
         }

         /**
         * Get all table and columns from selected database
         *
         * @param string $type
         * @return array
         */
         public function getTableColumns($type = false) {
                switch(strtolower($this->engine)):
                        case 'mysql': $query = $this->pdo->prepare('CALL tablesColumnsData(?)'); break;
                        case 'sqlsrv': $query = $this->pdo->prepare('EXEC tablesColumnsData ?'); break;
                endswitch;

                $query->bindParam(1, $this->db, PDO::PARAM_STR);

                try {
                    $query->execute();
                    return self::getArray($query->fetchAll(), $type);
                } catch(Exception $e) {}
         }
         /**
         * Check if type is present in field type list
         *
         * @param string $search
         * @param string $type
         * @return boolean
         */
         public function checkTypes($search, $type) {
                $types = [];
                $types['string'] = 'varchar,nvarchar,text,longtext';
                $types['regexp'] = 'varchar,nvarchar,text,longtext,int,bigint,float,double,date,datetime';
                $types['int'] = 'int,bigint';
                $types['float'] = 'float,double';
                $types['date'] = 'date,datetime,int';

                if(isset($types[$search]) && strlen($types[$search]) > 0):
                    if(preg_match('/'.$type.'/', $types[$search])):
                        return true;
                    endif;
                endif;

                return false;
         }
         /**
         * Set select tables
         *
         * @param array $tables
         * @return string
         */
         private static function parseSelectTables($tables) {
                        $crumbs = '';
                        while($table = each($tables)):
                          $crumbs .= $table['value'].',';
                        endwhile;
                        return substr($crumbs, 0, -1);
         }
         /**
         * Set select fields
         *
         * @param array $fields
         * @return string
         */
         private function parseSelectFields($fields) {
                  $crumbs = '';
                  while($field = each($fields)):
                    if(preg_match('/\*/', $field['value'])):
                        $crumbs .= $field['value'].',';
                    elseif(preg_match('/\./', $field['value'])):
                        $dot = explode('.', $field['value']);
                        $crumbs .= '`'.$dot[0].'`.`'.$dot[1].'`,';
                    else:
                         $crumbs .= '`'.$field['value'].'`,';
                    endif;
                  endwhile;
                  return substr($crumbs, 0, -1);
         }
         /**
         * Set select joins
         *
         * @param object $joins
         * @return string
         */
         private static function parseSelectJoins($joins) {
                        $collect = [];
                        while($join = each($joins)):
                          array_push($collect, $join['value']['type'].' JOIN '.$join['value']['table'].' ON '.self::parseCondition($join['value']['condition'], (isset($join['value']['operator']) ? $join['value']['operator'] : NULL), true));
                        endwhile;
                        return implode(' ', $collect);
         }
         /**
         * Set select order
         *
         * @param array $order
         * @return string
         */
         private static function parseSelectOrder($order) {
                        return 'ORDER BY '.$order[0].' '.$order[1];
         }
         /**
         * Set select limit
         *
         * @param array $limit
         * @return string
         */
         private static function parseSelectLimit($limit) {
                        if(sizeof($limit) == 2):
                          return 'LIMIT '.$limit[0].', '.$order[1];
                        else:
                          return 'LIMIT '.$limit[0];
                        endif;
         }
         /**
         * Set insert fields
         *
         * @param object $fields
         * @return string
         */
         private static function parseInsertFields($fields) {
                 $crumbs = '';
                 while($r = each($fields)):
                    $crumbs .= $r['key'].',';
                 endwhile;
                 return substr($crumbs, 0, -1);
         }
         /**
         * Set multiple insert fields
         *
         * @param object $fields
         * @return string
         */
         private static function parseInsertMultipleFields($fields) {
                 $crumbs = '';
                 while($r = each($fields)):
                    $crumbs .= $r['value'].',';
                 endwhile;
                 return substr($crumbs, 0, -1);
         }
         /**
         * Set insert values
         *
         * @param object $values
         * @return string
         */
         private static function parseInsertValues($values) {
                 $crumbs = '';
                 while(list($key, $val) = each($values)):
                    $crumbs .= is_numeric($val) ? $val.',' : '\''.$val.'\',';
                 endwhile;
                 return substr($crumbs, 0, - 1);
         }
         /**
         * Set multiple insert values
         *
         * @param object $values
         * @return string
         */
         private static function parseInsertMultipleValues($values) {
                 $crumbs = '';
                 while(list($key, $val) = each($values)):
                    $crumbs .= '(';
                    while($v = each($val)):
                        $crumbs .= is_numeric($v['value']) ? $v['value'].',' : '\''.$v['value'].'\',';
                    endwhile;
                    $crumbs = substr($crumbs, 0, -1).'),';
                 endwhile;
                 return substr($crumbs, 0, -1);
         }
         /**
         * Set update fields and values
         *
         * @param object $fields
         * @return string
         */
         private static function parseUpdateFields($fields) {
                 $crumbs = '';
                 while(list($key, $val) = each($fields)):
                    $crumbs .= $key.' = '.(is_numeric($val) ? $val.',' : '\''.$val.'\',');
                 endwhile;
                 return substr($crumbs, 0, -1);
         }
         /**
         * Set query conditions
         *
         * @param array $condition
         * @param array $operator
         * @return string
         */
         private static function parseCondition($condition, $operator, $fromJoin = false) {
                 $fields = '';
                 $collectQuery = [];
                 $i = 0;
                 while($crumbs = each($condition)):
                   $collect = '';
                   while(list($k, $val) = each($crumbs['value'])):
                    $collect .= $k == 2 ? (is_numeric($val) || (preg_match('/\./', $val) && !preg_match('/\@/', $val)) ? $val.' ' : '\''.$val.'\' ') : $val.' ';
                   endwhile;
                   array_push($collectQuery, $collect);
                   if($operator != NULL):
                       strlen(current($operator)) > 0 && array_push($collectQuery, strtoupper(current($operator)));
                       next($operator);
                   endif;
                   ++$i;
                 endwhile;
                 $i = 0;
                 $collectField = [];
                 $replacedOperators = [];
                 while(list($key, $crumbs) = each($collectQuery)):
                   if(preg_match('/OR|AND/', $crumbs)):
                       switch($crumbs):
                           case substr($crumbs, 0, 2) == '((':
                                $preg = preg_replace('/^\(\(/', '', $crumbs);
                                if(substr($preg, -1) == ')'):
                                                 array_push($collectField, [0 => $i, 1 => '((', 2 => ')']);
                                                 array_push($replacedOperators, preg_replace('/\)/', '', $preg));
                                                 break;
                                endif;
                                array_push($collectField, [0 => $i, 1 => '((', 2 => NULL]);
                                array_push($replacedOperators, preg_replace('/\)/', '', $preg));
                           break;
                           case substr($crumbs, -2) == '))':
                                $preg = preg_replace('/\)\)/', '', $crumbs);
                                if(substr($preg, 0, 1) == '('):
                                                 array_push($collectField, [0 => $i, 1 => '(', 2 => '))']);
                                                 array_push($replacedOperators, preg_replace('/\(/', '', $preg));
                                                 break;
                                endif;
                                array_push($collectField, [0 => $i, 1 => '))', 2 => NULL]);
                                array_push($replacedOperators, preg_replace('/\(/', '', $preg));
                           break;
                           case (substr($crumbs, 0, 1) == '(' && substr($crumbs, -1) == ')'):
                                if(substr($crumbs, 0, 1) == '('):
                                                   array_push($replacedOperators, preg_replace('/\(|\)/', '', $crumbs));
                                                   array_push($collectField, [0 => $i, 1 => '(', 2 => ')']);
                                endif;
                           break;
                           case substr($crumbs, 0, 1) == '(':
                                array_push($replacedOperators, preg_replace('/\(/', '', $crumbs));
                                array_push($collectField, [0 => $i, 1 => '(', 2 => NULL]);
                           break;
                           case (substr($crumbs, -1) == ')' && substr($crumbs, -2) != '))'):
                                array_push($replacedOperators, preg_replace('/\)/', '', $crumbs));
                                array_push($collectField, [0 => $i, 1 => ')', 2 => NULL]);
                           break;
                           case (substr($crumbs, 0, 1) != '(' || substr($crumbs, -1) != ')'):
                                array_push($replacedOperators, $crumbs);
                           break;
                       endswitch;
                   else:
                        array_push($replacedOperators, $crumbs);
                   endif;
                   ++$i;
                 endwhile;
                 for($i = sizeof($collectField) - 1; $i >= 0; --$i):
                   foreach($replacedOperators as $key => $val):
                      if($key == $collectField[$i][0]):
                          switch($collectField[$i][1]):
                                case '((':
                                    array_splice($replacedOperators, $key - 1, 0, [$collectField[$i][1]]);
                                break;
                                case '(':
                                    array_splice($replacedOperators, $key - 1, 0, [$collectField[$i][1]]);
                                break;
                                case ')':
                                    array_splice($replacedOperators, $key + 2, 0, [$collectField[$i][1]]);
                                break;
                                case '))':
                                    array_splice($replacedOperators, $key + 2, 0, [$collectField[$i][1]]);
                                break;
                          endswitch;
                          switch($collectField[$i][2]):
                                case '))':
                                    array_splice($replacedOperators, $key + 3, 0, [$collectField[$i][2]]);
                                break;
                                case ')':
                                    array_splice($replacedOperators, $key + 3, 0, [$collectField[$i][2]]);
                                break;
                          endswitch;
                      endif;
                   endforeach;
                 endfor;
                 while($collect = each($replacedOperators)):
                    $fields .= $collect['value'].' ';
                 endwhile;
                 return (!$fromJoin ? ' WHERE ' : '').substr($fields, 0, -1);
         }
         /**
         * Set join conditions
         *
         * @param array $condition
         * @return string
         */
         private static function parseJoinCondition($condition) {
                 $fields = '';
                 while($crumbs = current($condition)):
                   $fields .= $crumbs.' ';
                   next($condition);
                 endwhile;
                 return substr($fields, 0, -1);
         }
         /**
         * Get associative array
         *
         * @param array $result
         * @param boolean $type (0=>key, 1=>values)
         * @return string
         */
         private static function getArray($result, $type) {
                 $collect = [];
                 $crumbs = [];
                 while($array = each($result)):
                   while(list($key, $val) = each($array['value'])):
                      if(!$type):
                        !is_numeric($key) && $crumbs[$key] = $val;
                      else:
                        is_numeric($key) && $crumbs[$key] = $val;
                      endif;
                   endwhile;
                   array_push($collect, $crumbs);
                 endwhile;
                 return $collect;
         }
         /**
         * Print arrays
         *
         * @param array $result
         * @return string
         */
         public function pre($result) {
            print '<pre>'; print_r($result); print '</pre>';
         }
         /**
         * Exception handler
         *
         * @param object Exception
         * @return string
         */
         public static function exceptionHandler($e) {
                print 'Exception Caught: '.$e->getMessage();
         }
}



?>
