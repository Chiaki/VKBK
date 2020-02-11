<?php

class db {
	
	function __construct(){
		$conn = null;
		
		return true;
	}
	
	function connect($host,$user,$pass,$base){
		$db = mysqli_connect($host,$user,$pass);
		if(!$db){
			die("Ошибка подключения к БД: ".mysqli_error());
		}
		
		if(!mysqli_select_db($db,$base)){
			die("Ошибка выбора БД: ".mysqli_error());
		}
		mysqli_set_charset($db,'utf8');
		mysqli_query($db,"SET NAMES 'utf8mb4'");
		$this->conn = $db;
		return $db;
	}
	
	function free_query($res){
		return @mysqli_free_result($res);
	}

	function query($query){
		$responce = mysqli_query($this->conn,$query);
		if(!$responce){
			die("<font style=\"color:red;font-weight:bold;\">Плохой запрос: {$query}\r\n".mysqli_error($this->conn)."</font>\r\n");
		} else {
			return $responce;
		}
	}
	
	function return_row($query){
		return mysqli_fetch_assoc($query);
	}
	
	function query_row($query){
		if($query!=''){
			$q = $this->query($query);
			return $this->return_row($q);
		} else {
			return false;
		}
	}
	
	function affected_rows(){
		return mysqli_affected_rows($this->conn);
	}
	
	function affected_rows_sel(){
		return mysqli_num_rows();
	}
	
	function real_escape($data){
		if(!empty($data)){ return mysqli_real_escape_string($this->conn,$data); } else { return false; }
	}
	
	function close($res) {
		mysqli_close($res);
	}
	
}

?>