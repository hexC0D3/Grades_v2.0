<?php

global $db;
$db=new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

if($db->mysqli->connect_errno){
	die("Not able to connect to db!");
}

class DB{
	public $mysqli;
	
	/** Init Database Object **/
	public function DB($DBhost, $username, $password, $DBname){
		$this->mysqli = new mysqli($DBhost, $username, $password, $DBname);
	}
	/** Get arrays from stmt **/
	private function getArrayFromSTMT($stmt){
		$meta = $stmt->result_metadata();
		if(empty($meta)){return true;}
		while ($field = $meta->fetch_field()) {
			$parameters[] = &$row[$field->name];
		}
		call_user_func_array(array($stmt, 'bind_result'), $parameters);
		while ($stmt->fetch()) {
			foreach($row as $key => $val) {
				$x[$key] = $val;
			}
			$results[] = $x;
		}
		if(isset($results)&&!empty($results)){
			return $results;
		}else{
			return array();
		}
	}
	/** Generate types string **/
	private function generateTypesString($arrayOfValues){
		$types="";
		foreach($arrayOfValues as $key => $value){
			if(is_string($value)){
				$types.="s";
			}else if(is_int($value)){
				$types.="i";
			}else if(is_double($value)){
				$types.="d";
			}else if(is_bool($value)){
				$arrayOfValues[$key]=(int)$value;
				$types.="i";
			}else{
				throw new Exception("Unimplemented data type: " . gettype($value));
				return false;
			}
		}
		return $types;
	}
	/** Binds parameters with types and values **/
	private function bindParameters($stmt, $types, $values){
		call_user_func_array('mysqli_stmt_bind_param', array_merge(array($stmt, $types), $values));
		return true;
	}
	/** Executes the passed query with the mysqli bind_param.
	 * The query must not contain any values, instead just insert an x.
	 * In the same order as the x are in the query, pass an array with the values **/
	function doQueryWithArgs($query, $arrayOfValues=array(), $types=false){
		$types = $types==false?$this->generateTypesString($arrayOfValues):$types;
		$values = array();
		// Create an array full of references, needed for prepared statements
		for($i = 0;$i<count($arrayOfValues);$i++){
			$values[$i] = &$arrayOfValues[$i];
		}
		
		
		if($stmt = $this->mysqli->prepare($query)){
			$this->bindParameters($stmt, $types, $values);
			$stmt->execute();
			$data=$this->getArrayFromSTMT($stmt);
			$stmt->close();
			return $data;
		}
		
		#var_dump($this->mysqli->error);
		
		return false;
	}
	/** This function executes the query wihtout any arguments **/
	function doQueryWithoutArgs($query){
		
		$data = $this->mysqli->query($query);
		if(is_bool($data)){
			return $data;
		}else{
			 for ($result = array (); $result = $result->fetch_assoc(); $result[] = $row);
		}
		
		return $result;
	}
}