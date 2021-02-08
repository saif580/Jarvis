<?php
class data {
	function __construct() {
		$this->userid = "";
	}
	public function startSession() {
		$db = $GLOBALS['database'];

		$sql = "INSERT INTO sessions (session_id)
						VALUES (
						'".session_id()."'
						)
						";
		$result = $db->Query($sql);
		if ($db->AffectedRows($result) == 0) {
			$this->Errors("Failed to start session");
			return false;
		}
		else {
			$_SESSION['userid'] = $db->LastInsertID();
		}
		return true;
	}

	function setParameters($input) {
		$input = $this->cleanInput($input);
		$this->input = $input;
	}

	public function ProcessInput() {
		$result = $this->BasicMatch();
		if ($result) { $this->CheckTemplateSyntax(); $this->StoreChat(); return true; }
		$result = $this->getWildCardResponses();
		if ($result) { $this->StoreChat(); return true; }

		// WE DO NOT KNOW HOW TO PROCESS THE INPUT
		$this->output = "I do not understand that command, but I will learn from it";
		$this->EnterUnknownInput();
	}

	
	//here searchin for the pattern in database
	public function BasicMatch() {

		$db = $GLOBALS['database'];

		$sql = "SELECT id,template
						FROM aiml
						WHERE pattern like '".$this->input."%'
						";
		$result = $db->Query($sql);
		if ($db->NumRows($result) == 0) {
			return false;
		}
		else {
			while ($row = $db->FetchArray($result)) {
				$this->output = $row['template'];
				return true;
			}
		}
	}
	private function getWildCardResponses() {
		$db = $GLOBALS['database'];

		$pieces = explode(" ",$this->input);

		for ($i=0;$i<count($pieces);$i++) {
			$search_criteria = "";
			for ($j=0;$j<count($pieces)-$i;$j++) {
				$search_criteria .= $pieces[$j] . " ";
			}
			//echo $search_criteria."<br />";
			$result = $this->BasicMatch();
			if ($result) {
				return true;
			}
		}
		return false;
	}
	/*
		THIS FUNCTION DOES THE AIML SYNTAX CHECKING
	*/
	private function CheckTemplateSyntax() {
		// CHECK IF THE TEMPLATE MATCHES A RANDOM RESPONSE
		//echo $this->output;
		if (preg_match("/^\<random\>/",$this->output)) {
			$pieces = explode("</li> <li>",$this->output);
			//print_r($pieces);
			$total_pieces = count($pieces) -1;
			$ret = rand(0,$total_pieces);
			$output = strip_tags($pieces[$ret]);
			$this->output = $output;
		}
		// CHECK FOR BOT PERSONALITIES
		if (preg_match("/\<bot/",$this->output)) {
			$pieces = explode("<bot ",$this->output);
			$needed_piece = $pieces[1];
			$bits = explode(Chr(34),$needed_piece);
			$final_piece = $bits[1];
			//echo $final_piece."<br />";
			$value=$this->GetBotPersonalityValue($final_piece);
			$this->output = str_replace("<bot name=\"$final_piece\"/>",$value,$this->output);
		}

	}

	private function StoreChat() {
		$db = $GLOBALS['database'];

		$sql = "INSERT INTO chat (userid,client_message,server_response,datetime_response)
						VALUES (
						'".$_SESSION['userid']."',
						'".mysql_real_escape_string($this->input)."',
						'".mysql_real_escape_string($this->output)."',
						sysdate()
						)
						";
		$result = $db->Query($sql);
		if ($db->AffectedRows($result) == 0) {
			$this->Errors("Failed to log chat");
			return false;
		}
		return true;
	}
	private function EnterUnknownInput() {
		$db = $GLOBALS['database'];
		if (EMPTY($this->input)) { return True; }
		$sql = "INSERT INTO unknown_inputs (input,userid,timestamp)
						VALUES (
						'".$this->input."',
						'".$_SESSION['userid']."',
						sysdate()
						)
						";
		$result = $db->Query($sql);
		if ($db->AffectedRows($result) == 0) {
			$this->Errors("Failed to update unknown inputs");
			return false;
		}
		return true;
	}
	private function GetBotPersonalityValue($name) {
		$db = $GLOBALS['database'];

		$sql = "SELECT value
						FROM botpersonality
						WHERE name = '".mysql_real_escape_string($name)."'
						";
		$result = $db->Query($sql);
		if ($db->NumRows($result) == 0) {
			$this->Errors("Missing Syntax for Bot Personality");
			return false;
		}
		else {
			while ($row = $db->FetchArray($result)) {
				return $row['value'];
			}
		}
	}
	public function AddToAIMLBasic($pattern,$template) {
		$db = $GLOBALS['database'];

		$aiml = "<category><pattern>$pattern</pattern><template>$template</template></category>";

		$sql = "INSERT INTO aiml (aiml,pattern,template)
						VALUES (
						'".$aiml."',
						'".$pattern."',
						'".$template."'
						)
						";
		$result = $db->Query($sql);
		if ($db->AffectedRows($result) == 0) {
			$this->Errors("Failed to update unknown inputs");
			return false;
		}
		return true;
	}
	public function AddToAIMLRandom($pattern,$template_array) {
		$db = $GLOBALS['database'];

		$tmp = "<random> ";
		foreach ($template_array as $template) {
			if (!EMPTY($template)) {
				$tmp .= "<li>$template</li> ";
			}
		}
		$tmp .= " </random>";

		$aiml = "<category><pattern>$pattern</pattern>$tmp</category>";
		$pattern = str_replace("?","",$pattern);
		$sql = "INSERT INTO aiml (aiml,pattern,template)
						VALUES (
						'".$aiml."',
						'".$pattern."',
						'".$tmp."'
						)
						";
		//echo $sql."<br />";
		$result = $db->Query($sql);
		if ($db->AffectedRows($result) == 0) {
			$this->Errors("Failed to update unknown inputs");
			return false;
		}
		return true;
	}
	public function DeleteUnknownInput($id) {
		$db = $GLOBALS['database'];

		$sql = "DELETE FROM unknown_inputs
						WHERE id = '".mysql_real_escape_string($id)."'
						";
		$result = $db->Query($sql);
		if ($db->AffectedRows($result) == 0) {
			$this->Errors("Missing Syntax for Bot Personality");
			return false;
		}
		else {
			return True;
		}
	}


	function cleanInput($tmp)	{

		//strip any html tags .. naughty naughty these shouldnt be here but just in case
		$tmp = strip_tags($tmp);

		//remove puncutation except full stops
		$tmp = preg_replace('/\.+/', '.', $tmp);
		$tmp = preg_replace('/\,+/', '', $tmp);
		$tmp = preg_replace('/\!+/', '', $tmp);
		$tmp = preg_replace('/\?+/', '', $tmp);
		$tmp = str_replace("'", " ", $tmp);
		$tmp = str_replace("\"", " ", $tmp);
		$tmp = preg_replace('/\s\s+/', ' ', $tmp);
		//replace more than 2 in a row occurances of the same char with two occurances of that char
		$tmp = preg_replace('/aa+/', 'oaa', $tmp);
		$tmp = preg_replace('/bb+/', 'bb', $tmp);
		$tmp = preg_replace('/cc+/', 'cc', $tmp);
		$tmp = preg_replace('/dd+/', 'dd', $tmp);
		$tmp = preg_replace('/ee+/', 'ee', $tmp);
		$tmp = preg_replace('/ff+/', 'ff', $tmp);
		$tmp = preg_replace('/gg+/', 'gg', $tmp);
		$tmp = preg_replace('/hh+/', 'hh', $tmp);
		$tmp = preg_replace('/ii+/', 'ii', $tmp);
		$tmp = preg_replace('/jj+/', 'jj', $tmp);
		$tmp = preg_replace('/kk+/', 'kk', $tmp);
		$tmp = preg_replace('/ll+/', 'll', $tmp);
		$tmp = preg_replace('/mm+/', 'mm', $tmp);
		$tmp = preg_replace('/nn+/', 'nn', $tmp);
		$tmp = preg_replace('/oo+/', 'oo', $tmp);
		$tmp = preg_replace('/pp+/', 'pp', $tmp);
		$tmp = preg_replace('/qq+/', 'qq', $tmp);
		$tmp = preg_replace('/rr+/', 'rr', $tmp);
		$tmp = preg_replace('/ss+/', 'ss', $tmp);
		$tmp = preg_replace('/tt+/', 'tt', $tmp);
		$tmp = preg_replace('/uu+/', 'uu', $tmp);
		$tmp = preg_replace('/vv+/', 'vv', $tmp);
		$tmp = preg_replace('/ww+/', 'ww', $tmp);
		$tmp = preg_replace('/xx+/', 'xx', $tmp);
		$tmp = preg_replace('/yy+/', 'yy', $tmp);
		$tmp = preg_replace('/zz+/', 'zz', $tmp);

		//trim to remove white space
		$tmp = trim($tmp);

		//debug
		//runDebug("",3,"cleanInput","<br>Array Name = Not in array<br>Cleaned input string<br>Was = $dtmp<br>Is = $tmp");

		//return the string
		return $tmp;
	}

	public function getResponse() {
		return $this->output;
	}

	private function Errors($err) {
		$this->errors.=$err."<br>";
	}

	public function ShowErrors() {
		return $this->errors;
	}
}
?>