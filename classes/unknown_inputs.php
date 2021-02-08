<?php
class unknown_inputs {

	function __construct() {

	}

	public function SetParameters($id) {

		/* SET CHECKING TO FALSE */
		$this->parameter_check=False;

		/* CHECKS */
		if (!IS_NUMERIC($id)) { $this->Errors("Invalid ID"); return False; }

		/* SET SOME COMMON VARIABLES */
		$this->id=$id;

		/* CALL THE INFO METHOD */
		$this->Info();

		/* PARAMETER CHECK SUCCESSFUL */
		$this->parameter_check=True;

		return True;
	}

	private function Info() {

		$db=$GLOBALS['database'];

		$sql="SELECT *
					FROM unknown_inputs
					WHERE id = '".$this->id."'
					";
		//echo $sql."<br>";
		$result = $db->Query($sql);
		if ($db->NumRows($result) > 0) {
			while($row = $db->FetchArray($result)) {
				/* HERE WE CALL THE FIELDS AND SET THEM INTO DYNAMIC VARIABLES */
				$arr_cols=$db->GetColumns($result);
				for ($i=1;$i<count($arr_cols);$i++) {
					$col_name=$arr_cols[$i];
					$this->$col_name=$row[$col_name];
				}
			}
		}
	}

	/* GET A COLUMN NAME FROM THE ARRAY */
	public function GetInfo($p) {
		if (ISSET($this->$p)) {
			return $this->$p;
		}
		else {
			return false;
		}
	}

	private function Errors($err) {
		$this->errors.=$err."<br>";
	}

	public function ShowErrors() {
		return $this->errors;
	}
}
?>