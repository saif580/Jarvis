<?php
class mysql {
	function __construct() {
		$this->errors = "";
	}
	function Connect($hostname,$port,$database,$username,$password) { // DATABASE CONNECTION */
		$result = mysql_connect($hostname.":".$port,$username,$password) or die ('Connection to database failed.'); // DATABASE CONNECTION
		if (!$result) {
			$this->Error('Connection to database server at: '.$this->hostname.' failed.');
		 	return false;
		}
		else {
			$sel_db=mysql_select_db($database); // SELECT THE DATABASE
			if (!$sel_db) die("unable to select db, does the database <b>".$database."</b> exist?") ;
			mysql_query("set autocommit=1"); // RUN IN AUTOCOMMIT MODE FOR INNODB TABLES
			return $result;
		}
	}
	function pconnect() { // PERSISTENT CONNECTION
		$result = mysql_pconnect($this->hostname, $this->username, $this->password);

		if (!$result) {
			echo 'Connection to database server at: '.$this->hostname.' failed.';
			return false;
		}
		return $result;
	}
	function Query($query,$query_no="") { // THE METHOD TO EXECUTE QUERIES
		//echo "Sql QUERY: ".$query."<br>";
  	//$result = mysql_query($query) or die("Query failed:	$query<br><br>".mysql_error());
  	$result = mysql_query($query) or die(ShowSQLError($query_no,$query));
  	return $result;
  }
  function FetchArray($result) { // A METHOD TO RETURN THE RESULT AS AN ARRAY
  	return mysql_fetch_array($result);
  }
  function FetchAssoc($result) { // AN ALTERNATIVE METHOD TO RETURN AS AN ASSOCIATIVE ARRAY
  	return mysql_fetch_assoc($result);
  }
  function FetchRow($result) { // AN ALTERNATIVE METHOD TO RETURN ROWS
    $query = mysql_fetch_row($result);
    return $result;
  }
  function ReturnQueryNum() { // A METHOD TO RETURN THE QUERY NUMBER
    return $this->query_num;
  }
  function NumRows($result) { // A METHOD TO RETURN THE NUMBER OF ROWS IN A RESULT
  	return mysql_num_rows($result);
  }
  function AffectedRows() { // A METHOD TO DETERMINE HOW MANY ROWS WERE AFFECTED BY THE QUERY
  	return mysql_affected_rows();
  }
  function GetColumns($result) {
  	//return mysql_fetch_field($result, $i);
  	$i = 0;
  	//echo mysql_num_fields($result);
  	$fields_arr[]="";
		for ($i=0;$i<mysql_num_fields($result);$i++) {
    	$meta= mysql_fetch_field($result, $i);
    	array_push($fields_arr,$meta->name);
    }
    return $fields_arr;
  }
  function LastInsertId() { // A METHOD TO OBTAIN THE LAST INSERTED AUTOINCREMENT ID
  	return mysql_insert_id();
  }
  function Begin() { // A METHOD TO START A TRANSACTION
  	mysql_query("set autocommit=0");
  }
  function commit() { // COMMIT
  	mysql_query("commit");
  }
  function Rollback() { // ROLLBACK
  	mysql_query("rollback");
  }
  function Error($err) {
  	$this->errors.=$err."<br />";
  }
  function ShowErrors() {
  	return $this->errors;
  }
}

function ShowSQLError($sql_id,$query="") {
	echo "An error has occured. Report being generated now...<br>";

	echo "Error: ".$sql_id."<br>";
	echo "This is the SQL error:<p>";
	echo mysql_error()."<p>";
	echo "This is the SQL:<p>";
	echo $query."<br>";

	//echo $data."<br>";
	$db=$GLOBALS['db'];
	//$sql="INSERT INTO error_sql_data (sql_id, output) VALUES ('".EscapeData($sql_id)."','".EscapeData(mysql_error())."')";
	//echo $sql."<br>";
	//$db->query($sql);
	echo "Report generated. Please go back and continue. The problem will be resolved soon.<br>";
	die();
}
?>