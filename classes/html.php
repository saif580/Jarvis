<?php
class html {
	public function DrawChatBox($input="") {
		$c  = "<html>
				<head>
					<link rel='stylesheet' type='text/css' href='bootstrap.min.css'>
					<link rel='stylesheet' type='text/css' href='style.css'>
				</head>
				<body class='bg'>
				<div class='wrapper'>
					<h1 style='text-align:center'><b>COLLEGE ENQUIRY CHATBOT</b></h1>
					<br><br>
					
						<div class='row'>
							<div class='col-md-6 col-md-offset-3'>
								<form method='post' action='index.php' class='form-group'>
								<input id='myGlower'  type='text' name='message' class='form-control' placeholder='CHAT'>
							</div>
							<div class='col-md-1'>
								<input class='btn btn-primary btn-block' type='submit' value='Talk' size=10>
							</div>
							</form>
						</div>
					
				</div>
				<script src='main.js'></script>
				</body>
				";
						
				
		return $c;
	}
	public function DrawResponse($input="") {
		$c ="<div class='row'>
				<div class='col-md-7 col-md-offset-3 text-center'>
					<div class='alert alert-success'>
					".$input."
					</div>
				</div>
			</div>
		"
		
		;
		return $c;
	}
	public function ShowUnknownInputs() {
		$db = $GLOBALS['database'];
		$c = "";
		$sql = "SELECT id, input
						FROM unknown_inputs
						WHERE input <> ''
						LIMIT 10
						";
		$result = $db->Query($sql);
		if ($db->NumRows($result) == 0) {
			$this->Errors("No inputs");
			return false;
		}
		else {
			$c .= "<table border=1>\n";

			while ($row = $db->FetchArray($result)) {
				$c .= "<form method='post' action='unknown.php'>\n";
				$c .= "<tr>\n";
					$c .= "<td valign=top>\n";
					if (EMPTY($row['input'])) {
						$c .= "EMPTY";
					}
					else {
						$c .= $row['input'];
					}
					$c .= "</td>\n";
					$c .= "<td valign=top>\n";
					for ($i=0;$i<10;$i++) {
						$c .= "<input type=text name=template[]><br />\n";
					}
					$c .= "<input type='submit' value='Save'>\n";
					$c .= "</td>\n";
					$c .= "<td valign=top>\n";
					$c .= "<a href=unknown.php?delete=y&id=".$row['id'].">Delete</a>\n";
					$c .= "</td>\n";
				$c .= "</tr>\n";
				$c .= "<input type=hidden name=id value='".$row['id']."'>\n";
				$c .= "</form>\n";
			}

			$c .= "</table>\n";
		}
		return $c;
	}
	private function Errors($err) {
		$this->errors.=$err."<br>";
	}

	public function ShowErrors() {
		return $this->errors;
	}
}
?>