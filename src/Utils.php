<?php

/**
 * Utils for XLS importer
 *
 * @link  **
 * @copyright Copyright (c) 2017 MainSim
 * @author Sven Macolic 
 */

#namespace Slayer\models;

class Utils extends PDOProcedureHelper {
	/** 
	* @var array multiple inserts error collector 
	*/
	public $collectMultipleInsertErrors = [];
	/** 
	* @var array error collector 
	*/
	private $errorStructure;
	/** 
	* @var array import directives 
	*/
	private $typeStructure;
	/**
    * Error variable setter
    *
    * @param string $param
    * @return void
    */
	private function setErrorStructure($param) {
		array_push($this->errorStructure, $param);
	}
	/**
    * Error variable getter
    *
    * @return array
    */
	public function getErrorStructure() {
		return $this->errorStructure;
	} 
	/**
    * Import directives variable setter
    *
    * @param string $param
    * @return void
    */
	private function setTypeStructure($param) {
		$this->typeStructure = $param;
	}
	/**
    * Import directives variable getter
    *
    * @return array
    */
	public function getTypeStructure() {
		return $this->typeStructure;
	}
	/**
    * Class constructor
    *
    * @return void
    */
	function __construct() {
		set_exception_handler([$this, 'exceptionHandler']);
		Factory::connect();
    	$this->pdo = Factory::$pdo;
	}
	/**
    * Validate data types
    *
    * @param array $params
    * @return array
    */
	public function validateDataTypes($params) {
		$this->errorStructure = [];
		$count = 0;
		while($value = each($params)):
			if(empty($value[1]['type_rule'])):
				$type = $this->isInDb($value[1]['table'], trim($value[1]['field']), '', true);
				switch(strtoupper(trim($type))):
					case 'INT':
					case 'TINYINT':
					case 'MEDIUMINT':
					case 'BIGINT':
						$params[$count]['type_rule'] = 'INT';
					break;
					case 'FLOAT':
					case 'DOUBLE':
						$params[$count]['type_rule'] = 'FLOAT';
					break;
					case 'CHAR':
					case 'VARCHAR':
					case 'NVARCHAR':
					case 'TEXT':
					case 'LONGTEXT':
						$params[$count]['type_rule'] = 'STRING';
					break;
				endswitch;
				++$count;
				continue;
			endif;
			$coords = $value[1]['table'].' '.$value[1]['field'].' '.$value[1]['type_rule'];
			preg_match('/default/', $value[1]['field_rule']) && !preg_match('/\:/', $value[1]['field_rule']) && $this->setErrorStructure(self::errorTypes(0, $coords));
			if(preg_match('/default/', $value[1]['field_rule']) && preg_match('/\:/', $value[1]['field_rule'])):
				$crumbs = explode(':', $value[1]['field_rule']);
				strlen(trim($crumbs[1])) == 0 && $this->setErrorStructure(self::errorTypes(7, $coords));	
			endif;
			if(preg_match('/map/', $value[1]['field_rule'])):
				(!isset($value[1]['map_data']) || sizeof($value[1]['map_data']) == 0) && $this->setErrorStructure(self::errorTypes(8, $coords));	
			endif;
			if(!preg_match('/\:/', $value[1]['type_rule'])):
				if($this->isInDb($value[1]['table'], $value[1]['field'], $value[1]['type_rule'])):
					switch(strtoupper(trim($value[1]['type_rule']))):					
						case 'INT':
						case 'FLOAT':
						case 'STRING':
						break;
						default:
							$this->setErrorStructure(self::errorTypes(3, $coords));
						break;	
					endswitch;
				else:
					$this->setErrorStructure(self::errorTypes(1, $coords));
				endif;
			else:
				$type = explode(':', $value[1]['type_rule'], 2);
				if($this->isInDb($value[1]['table'], $value[1]['field'], $type[0])):
					switch(strtoupper(trim($type[0]))):
						case 'INT':
							if(preg_match('/\,/', $type[1])):
								$crumbs = explode(',', trim($type[1]));
								(sizeof($crumbs) > 2 || (int)$crumbs[0] == 0 || (int)$crumbs[1] == 0) && $this->setErrorStructure(self::errorTypes(2, $coords));
							else:
								(int)$type[1] == 0 && $this->setErrorStructure(self::errorTypes(2, $coords));
							endif;
						break;
						case 'STRING':
						case 'FLOAT':
							(int)$type[1] == 0 && $this->setErrorStructure(self::errorTypes(2, $coords));
						break;
						case 'REGEXP':
							if(!preg_match('/\:/', $type[1])):
								$this->setErrorStructure(self::errorTypes(2, $coords));
							else:
								$crumb = explode(':', $type[1], 2);
								(empty($crumb[0]) || empty($crumb[1])) && $this->setErrorStructure(self::errorTypes(2, $coords));
								!preg_match('/default/', $value[1]['field_rule']) && strtolower($crumb[0]) == 'r' && $this->setErrorStructure(self::errorTypes(2, $coords));
								preg_match('/default/', $value[1]['field_rule']) && !preg_match('/\:/', $value[1]['field_rule']) && $this->setErrorStructure(self::errorTypes(2, $coords));	
								if(preg_match('/default/', $value[1]['field_rule']) && preg_match('/\:/', $value[1]['field_rule'])):
									$crumbs = explode(':', $value[1]['field_rule']);
									strlen(trim($crumbs[1])) == 0 && $this->setErrorStructure(self::errorTypes(7, $coords));
								endif;
							endif;
						break;
						case 'DATE': 
							if(!preg_match('/\//', $type[1])):
								$this->setErrorStructure($this->errorTypes(2, $coords));
							else:
								!preg_match('/^(dd\/mm\/yyyy|mm\/dd\/yyyy|yyyy\/dd\/mm|yyyy\/mm\/dd)$/', strtolower($type[1])) && $this->setErrorStructure(self::errorTypes(2, $coords));	
							endif;
						break;
					endswitch;
				else:
					$this->setErrorStructure(self::errorTypes(1, $coords));
				endif;
			endif;
			++$count;
		endwhile;
		$this->setTypeStructure($params);
		return $this->getErrorStructure();
	}
	/**
    * Validate data values
    *
    * @param array $params
    * @return string
    */
	public function validateDataValue($params) {
		$types = $this->getTypeStructure();
		while($value = each($types)):
			$params = array_merge($params, ['field_rule' => $value[1]['field_rule']]);
			$params = array_merge($params, ['type_rule' => $value[1]['type_rule']]);
			$coords = json_encode($params);
			if($value[1]['table'] == $params['table'] && $value[1]['field'] == $params['field']):
				if(empty($params['value']) && preg_match('/default/', $value[1]['field_rule'])):
					$crumbs = explode(':', $value[1]['field_rule']);
					$params['value'] = $crumbs[1];
				endif;
				if(empty($params['value']) && strtolower($value[1]['field_rule']) == 'mandatory'):
					return self::errorTypes(6, $coords);	
				endif;
				if((is_null($value[1]['field_rule']) || $value[1]['field_rule'] == 'null') && empty($params['value'])) {
					return null;
				}
				if(strtolower($value[1]['field_rule']) == 'map'):
					$kval = (string)$params['value'];
					if(isset($value[1]['map_data'][$kval]) && strlen($value[1]['map_data'][$kval]) > 0):
						return $value[1]['map_data'][$kval];
					endif;
					return self::errorTypes(9, $coords);	
				endif;
				if(!preg_match('/\:/', $value[1]['type_rule'])):
					switch(strtoupper(trim($value[1]['type_rule']))):
						case 'INT': 
							if(!is_numeric($params['value']) && !is_null($params['value'])):
								return self::errorTypes(3, $coords);
							else:
								return (int)$params['value'];
							endif;
						case 'FLOAT': 
							if(!is_numeric($params['value']) && !is_null($params['value'])):
								return self::errorTypes(3, $coords);
							else:
								return (float)$params['value'];
							endif;
						case 'STRING': 
							return (string)$this->encode($params['value']);
						default:							
							return (string)$this->encode($params['value']);
					endswitch;
				else:
					$type = explode(':', $value[1]['type_rule'], 2);
					switch(strtoupper(trim($type[0]))):
						case 'INT':
							if(is_null($params['value']) || strtolower($params['value']) == 'null'):
								(int)$params['value'];	
							endif;
							if(!is_numeric($params['value']) && !is_null($params['value'])):
								return self::errorTypes(3, $coords);	
							endif;
							if(preg_match('/\,/', $type[1])):
									$crumbs = explode(',', trim($type[1]));
									if(strlen($params['value']) < $crumbs[0] || strlen($params['value']) > $crumbs[1]):
										return self::errorTypes(4, $coords);
									endif;
							else:
								if(strlen($params['value']) < $type[1] || strlen($params['value']) > $type[1]):
									return self::errorTypes(4, $coords);
								endif;
							endif;	
							return (int)$params['value'];
						break;
						case 'FLOAT':
							if(is_null($params['value']) || strtolower($params['value']) == 'null'):
								(float)$params['value'];	
							endif;
							if(!is_numeric($params['value']) && !is_null($params['value'])):
								return self::errorTypes(3, $coords);	
							else:
								return number_format((float)$params['value'], $type[1], '.', '');
							endif;
						break;
						case 'STRING':
                            if(strlen($params['value']) > $type[1]):
                                    return self::errorTypes(4, $coords);
                            endif;
							if(is_null($params['value']) || strtolower($params['value']) == 'null'):
								(string)$params['value'];	
							endif;
							return (string)$this->encode(substr($params['value'], 0, $type[1]));
						break;
						case 'REGEXP':
							$crumb = explode(':', $type[1], 2);
							if(strtolower($crumb[0]) == 'f'): #find
								if(!preg_match('/'.$crumb[1].'/', $params['value'])):
									return self::errorTypes(5, $coords);	
								endif;
							endif;
							if(strtolower($crumb[0]) == 'r'): #replace
								$crumbs = explode(':', $value[1]['field_rule']); 
								$params['value'] = preg_replace('/'.$crumb[1].'/', $crumbs[1], $params['value']);
							endif;
							return (string)$this->encode($params['value']);
						break;
						case 'DATE':
							$crumbs = explode('/', $params['value']);
							$date_format = strtolower($type[1]);
							if($date_format == 'dd/mm/yyyy' || $date_format == 'mm/dd/yyyy'):
								if(strlen($crumbs[0]) != 2 || strlen($crumbs[1]) != 2 || strlen($crumbs[2]) != 4):
									return self::errorTypes(5, $coords);
								endif;
							endif;
							if($date_format == 'yyyy/dd/mm' || $date_format == 'yyyy/mm/dd'):
								if(strlen($crumbs[0]) != 4 || strlen($crumbs[1]) != 2 || strlen($crumbs[2]) != 2):
									return self::errorTypes(5, $coords);
								endif;	
							endif;
							switch($date_format):
								case 'dd/mm/yyyy':
									$timestamp = strtotime(date('Js F, Y', strtotime(str_replace('/', '-', $params['value']))));
								break;
								case 'mm/dd/yyyy':
									$timestamp = strtotime(date('Js F, Y', strtotime($params['value'])));
								break;
								case 'yyyy/dd/mm':
									$timestamp = strtotime(date('Y Js F,', str_replace('/', '-', strtotime($params['value']))));
								break;
								case 'yyyy/mm/dd':
									$timestamp = strtotime(date('Y F, Js', strtotime($params['value'])));
								break;
							endswitch;
							return (int)$timestamp;
						break;
						default:
							return $this->encode($params['value']);
						break;
				    endswitch;
				endif;
			endif;
		endwhile;
	}
	/**
    * Divide multiple inserts and collect inserted ids and/or errors
    *
    * @param array $insert
    * @param boolean $error
    * @return void 
    */
	public function detectErrorByDivision($insert, $errorDescription = null, $error = false) {
        if(sizeof($insert['data']) == 1 && $error == true):
    		array_push($this->collectMultipleInsertErrors, array($errorDescription, $insert));
    		return;
    	endif; 
        if($error):            
            $first['table'] = $insert['table'];
            $first['fields'] = $insert['fields'];
            $first['data'] = array_slice($insert['data'], 0, sizeof($insert['data']) / 2);
            $first['increment'] = $insert['increment'];
            $this->detectErrorByDivision($first);
            $second['table'] = $insert['table'];
            $second['fields'] = $insert['fields'];
            $second['data'] = array_slice($insert['data'], sizeof($insert['data']) / 2);
            $second['increment'] = $insert['increment'];
            $this->detectErrorByDivision($second);
        elseif(!$error):
            $insertVar = $insert;
            $res = $this->insertMultiple($insertVar); 
            if(array_key_exists('mysqlerror', $res[0]) || array_key_exists('mssqlerror', $res[0])):
                $this->detectErrorByDivision($insert, $res[0], true);
            elseif(array_key_exists('lastIDS', $res[0])):
                return $res[0]['lastIDS']; 
            endif;
        endif;
    }
	/**
    * Encode special characters
    *
    * @param string $data
    * @return string
    */
	protected function encode($data) {
		if(preg_match('/\'/', $data) && strtolower(Factory::$engine) == 'mysql'):
			$output = preg_replace('/\'/', "\'", $data);
		elseif(preg_match('/\'/', $data) && strtolower(Factory::$engine) == 'sqlsrv'):
			$output = preg_replace('/\'/', "''", $data);
		else:
			$output = $data;
		endif;
		return $output;
	}
	/**
    * List of error types to use
    *
    * @param int $type
    * @param string $coords
    * @param boolean $toString
    * @return mixed (array/string)
    */
	private static function errorTypes($type, $coords) {
		$e = '#Error:'; #preg_match hash tag
		$err = [];
		$err[0] = $e.' Missed colon. *';
		$err[1] = $e.' Field type does not match with database table field data type. *';
		$err[2] = $e.' Incorrect data type value format. *';
		$err[3] = $e.' Incorrect data value type. *';
		$err[4] = $e.' Incorrect data value length. *';
		$err[5] = $e.' Incorrect data value format. *';
		$err[6] = $e.' Field is mandatory. *';
		$err[7] = $e.' Field default value is missing. *';
		$err[8] = $e.' Mapped data is missing. *';
		$err[9] = $e.' Mapped data does not match. *';
		return str_replace('*', '('.$coords.')', $err[$type]);	
	}
	/**
    * Dump database
    *
    * @param string $mysqldump absolute path to mysqldump
    * @param string $fpath relative path for mysql, absolute for mssql
    * @return string
    */
    public function dumpDB($mysqldump, $fpath) {
    	switch(strtolower(Factory::$engine)):
    		case 'mysql':
				$command = $mysqldump
					." -h ".Factory::$object['database']::$conn->host
					." -u ".Factory::$object['database']::$conn->user
					." -p".Factory::$object['database']::$conn->password
					." ".Factory::$object['database']::$conn->database;   
				exec($command, $out, $err);
				if($err):
					$fp = fopen($fpath.'\mysql_error_log_'.date('Y_m_d_H_i_s').'.txt', 'w');
					fwrite($fp, $err);
					fclose($fp);
					return false;
				else:
					$fp = fopen($fpath.'\\mysql_'.Factory::$object['database']::$conn->database.'_'.date('Y_m_d_H_i_s').'.sql', 'w');
					fwrite($fp, implode(PHP_EOL, $out));
					fclose($fp);
				endif;
    		break;
    		case 'sqlsrv':
    			$ret = $this->pdo->query("BACKUP DATABASE ".Factory::$object['database']::$conn->database
    				." TO DISK = N'".$fpath."\mssql_".Factory::$object['database']::$conn->database."_".date('Y_m_d_H_i_s').".bak'"); 
    			if(!$ret):
    				$fp = fopen($fpath.'\mssql_error_log_'.date('Y_m_d_H_i_s').'.txt', 'w');
					fwrite($fp, implode(PHP_EOL, $this->pdo->errorInfo()));
					fclose($fp);
					return false;
    			endif;
    			try {
                  $ret->execute();
                } catch(Exception $e) {}
    		break;
    	endswitch;
    	return true;
    }
    /**
    * Restore database
    *
    * @param string $mysql absolute path to mysql
    * @param string $fname relative path for mysql, absolute for mssql
    * @param string $fpath error log relative path
    * @return string
    */
    public function restoreDB($mysql, $fname, $fpath) {
    	switch(strtolower(Factory::$engine)):
    		case 'mysql':
    			$command = $mysql
					." -h ".Factory::$object['database']::$conn->host
					." -u ".Factory::$object['database']::$conn->user
					." -p".Factory::$object['database']::$conn->password
					." ".Factory::$object['database']::$conn->database
					." < ".$fname;   
              	passthru($command, $err);
                if($err):
                	$fp = fopen($fpath.'\mysql_error_log_'.date('Y_m_d_H_i_s').'.txt', 'w');
					fwrite($fp, $err);
					fclose($fp);
					return false;
                endif;				
    		break;
    		case 'sqlsrv':
    			$r = $this->pdo->query("USE master");
    			$r->execute();
    			$ret = $this->pdo->query("RESTORE DATABASE ".Factory::$object['database']::$conn->database
    				." FROM DISK = N'".$fname."' WITH REPLACE"); 
    			if(!$ret):
    				$fp = fopen($fpath.'\mssql_error_log_'.date('Y_m_d_H_i_s').'.txt', 'w');
					fwrite($fp, implode(PHP_EOL, $this->pdo->errorInfo()));
					fclose($fp);
					return false;
    			endif;
    			try {
                  $ret->execute();
                } catch(Exception $e) {}
    		break;
    	endswitch;
    	return true;
    }
}

?>