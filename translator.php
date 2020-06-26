<meta charset="utf-8">
<?php
$in_stream  = array();
$out_stream = array();

$pseudo = file_get_contents("pseudo.txt");
$programs = explode("programm", $pseudo);

foreach ($programs as $pg) {
	echo $pg . "<hr>";
}

class Program {
	public $origin;
	public $in_stream;
	public $out_stream;

	private $vars = array();

	function __construct($source_string) {
		$origin = $source_string;

		$v = $this->translator($origin);

		print_r($this->out_stream);
	}

	private function translator($str) {
		$codenames   = ["число"];
		$types       = ["int"];
		$default_val = [0];
		$rows = explode("\n", $str);

		foreach ($rows as $row) {
			if (strlen($row) == 0) continue;
			// set varialables
			foreach ($codenames as $key=>$cn) {
				if (preg_match('/'.$cn.'/', $row)) {
					$row_expl = explode(" ", $row);
					$this->vars[] = array(
						"name"  => $row_expl[1],
						"type"  => $types[$key],
						"value" => $default_val[$key]
					);

					break;
				}
			}
			
			// search assign char
			if ( preg_match('/\=/', $row) ) {
				$del_spaces = preg_replace('/\s/', "", $row);
				$assign_nodes = explode("=", $del_spaces);

				if ( $this->is_expression($assign_nodes[1]) ) {
					$assign_val = $this->expression($assign_nodes[1]);
				}
				else {
					$assign_val = $assign_nodes[1];
				}

				$this->set_val($assign_nodes[0], $assign_val);
			}

			// search output command
			if ( preg_match('/выведи/', $row) ) {
				$set_row = preg_replace('/выведи\s/', "", $row);
				if ( $this->is_expression($set_row) ) {
					$out = $this->expression($set_row);
				}
				else {
					$out = $set_row;
				}

				$this->out_stream = $out;
			}
		}

		return $this->vars;
	}

	private function is_expression($value) {
		if ( preg_match('/([\+\-\/\*])/', $value) ) {
			return true;
		}
		else {
			return false;
		}
	}

	private function expression($expression_origin) {
		$exp_nodes = preg_split('/([\+\-\:\*])/', $expression_origin);
		preg_match('/([\+\-\/\*])/', $expression_origin, $exp, PREG_OFFSET_CAPTURE);

		$vals = array();

		foreach ($exp_nodes as $val) {
			if (is_numeric($val)) {
				$vals[] = (int)$val;
			}
			else {
				$vals[] = (int)$this->get_val($val);
			}
		}

		$out = 0;

		switch ($exp[0][0]) {
			case '+':
				$out = $vals[0] + $vals[1];
				break;
			case '-':
				$out = $vals[0] - $vals[1];
				break;
			case '/':
				$out = $vals[0] / $vals[1];
				break;
			case '*':
				$out = $vals[0] * $vals[1];
				break;
			default:
				return "Error";
				break;
		}

		return $out;
	}

	private function get_val($varialable_name) {
		$var_name = preg_replace('/\s/', "", $varialable_name);
		foreach ($this->vars as $key=>$var) {
			if ( preg_match('/'.$var_name.'/', $var["name"]) ) {
				return $this->vars[$key]["value"];
			}
		}

		return "Error";
	}

	private function set_val($varialable_name, $value) {
		$var_name = preg_replace('/\s/', "", $varialable_name);
		foreach ($this->vars as $key=>$var) {
			if ($var["name"] == $var_name) {
				$this->vars[$key]["value"] = $value;
			}
		}
	}
}

$t = new Program($programs[0]);
?>