<?php
class Program {
	public $origin;
	public $in_stream;
	public $out_stream = array();

	private $vars = array();

	function __construct($source_string) {
		$origin = $source_string;
		$this->origin = $source_string;
		$this->translator($origin);
	}

	private function translator($str) {
		$codenames   = ["число"];
		$types       = ["int"];
		$default_val = [0];
		$rows = explode("\n", $str);

		$on_if    = false;
		$if_state = false;
		$on_else  = false;

		foreach ($rows as $row) {
			if (strlen($row) == 0) continue;

			$on_else = preg_match('/иначе/', $row);

			if ( $on_if && $if_state  &&  $on_else ) continue;
			if ( $on_if && !$if_state && !$on_else ) continue;
			
			// search output command
			if ( preg_match('/выведи/', $row) ) {
				$set_row = preg_replace('/выведи\s/', "", $row);

				$out = $this->is_expression($set_row)
					? $this->expression($set_row)
					: $set_row;

				$this->out_stream[] = $this->contain_var($out);

				continue;
			}

			// set varialables
			$set_var = false;
			foreach ($codenames as $key=>$cn) {
				if ( preg_match('/'.$cn.'/', $row) && !preg_match('/случайное_число/', $row)) {
					$row_expl = explode(" ", $row);
					$this->vars[] = array(
						"name"  => $row_expl[1],
						"type"  => $types[$key],
						"value" => $default_val[$key]
					);

					$set_var = true;
					break;
				}
			}
			
			if ( $set_var ) continue;

			// search assign char
			if ( preg_match('/\=/', $row) ) {
				$assign_nodes = explode("=", $row);
				
				if ( $this->is_random($assign_nodes[1]) ) {
					$assign_val = $this->randint($assign_nodes[1]);
				}
				else if ( $this->is_expression($assign_nodes[1]) ) {
					$assign_val = $this->expression($assign_nodes[1]);
				} else {
					$assign_val = $assign_nodes[1];
				}

				$this->set_val($assign_nodes[0], $assign_val);

				continue;
			}

			// search IF expression
			echo $row . "<br>";
			if ( preg_match('/если/', $row) ) {
				if ( preg_match('/конец_если/', $row) ) { 
					$on_if = false; 
				}
				else {
					$set_row = preg_replace('/если\s/', "", $row);

					$on_if = true;
					$if_state = $this->logic_state($set_row);
					$on_else   = $if_state ? false : true;
				}

				continue;
			}
		}

		return $this->vars;
	}

// #################################################
// ######## Simple logic expressions solver ########
// #################################################

	private function is_logic($value) {
		if ( preg_match('/(больше)|(меньше)|(равно)/', $value) ) 
			{ return true; } 
		else 
			{ return false; }
	}

	private function logic_state($expression_origin) {
		$del_double_spaces = preg_replace('/\s/', " ", $expression_origin);
		$exp_nodes = preg_split('/(больше)|(меньше)|(равно)/', $del_double_spaces);

		preg_match_all('/(больше)|(меньше)|(равно)/', $del_double_spaces, $exp);

		$vals = array();

		foreach ($exp_nodes as $val) {
			$vals[] = is_numeric($val)
				? (int)$val
				: (int)$this->get_val($val);
		}

		$e = preg_replace("/\s/", "", $exp[0][0]);
		switch ($e) {
			case 'больше':
				return (int)$vals[0] > (int)$vals[1]
					? true
					: false;
				break;
			case 'меньше':
				return (int)$vals[0] < (int)$vals[1]
					? true
					: false;
				break;
			case 'равно':
				return (int)$vals[0] == (int)$vals[1]
					? true
					: false;
				break;
			default:
				return "Error: undefined sign"; break;
		}
	}

// ##################################################
// ######### Simple math expressions solver #########
// ##################################################

	private function is_expression($value) {
		if ( preg_match('/([\+\-\/\*])/', $value) ) 
			{ return true; } 
		else 
			{ return false; }
	}

	private function expression($expression_origin) {
		$del_spaces = preg_replace('/\s/', "", $expression_origin);
		$exp_nodes = preg_split('/([\+\-\:\*])/', $del_spaces);

		preg_match_all('/([\+\-\/\*])/', $del_spaces, $exp);

		$vals = array();

		foreach ($exp_nodes as $val) {
			$vals[] = is_numeric($val)
				? (int)$val
				: (int)$this->get_val($val);
		}

		$actions_order = array("*", "/", "+", "-");
		$aoi = 0;

		// math expression order
		while (count($exp[0]) > 0) {
			$n1 = array_search($actions_order[$aoi], $exp[0]);

			if ($n1 === false) { $aoi++; continue; }

			$n2 = $n1 + 1;
			$act = $actions_order[$aoi];

			switch ($act) {
				case '+':
					$vals[$n1] += $vals[$n2]; break;
				case '-':
					$vals[$n1] -= $vals[$n2]; break;
				case '/':
					$vals[$n1] /= $vals[$n2]; break;
				case '*':
					$vals[$n1] *= $vals[$n2]; break;
				default:
					return "Error: undefined sign"; break;
			}

			array_splice($exp[0], $n1, 1);
			array_splice($vals,   $n2, 1);
		}

		return $vals[0];
	}
// #################################################
// ######### Programm varialable functions #########
// #################################################

	private function get_val($varialable_name) {
		$var_name = preg_replace('/\s/', "", $varialable_name);
		foreach ($this->vars as $key=>$var) {
			if ( preg_match('/'.$var_name.'/', $var["name"]) ) {
				return $this->vars[$key]["value"];
			}
		}

		return "Error: undefined varialable name";
	}

	private function set_val($varialable_name, $value) {
		$var_name = preg_replace('/\s/', "", $varialable_name);
		foreach ($this->vars as $key=>$var) {
			if ($var["name"] == $var_name) {
				$this->vars[$key]["value"] = $value;
				return true;
			}
		}

		return "Error: undefined varialable name";
	}

// ##################################################
// ############# Random function assets #############
// ##################################################
	private function is_random($expression) {
		if ( preg_match("/случайное_число/", $expression) ) {
			return true;
		}
		else {
			return false;
		}
	}

	private function randint($expression_origin) {
		$expr = preg_replace('/случайное_число\sот/', "", $expression_origin);
		$rm_spaces = preg_replace('/\s/', "", $expr);
		$vals = explode("до", $rm_spaces);

		return rand((int)$vals[0], (int)$vals[1]);
	}

	private function contain_var($outstr) {
		$out = $outstr;
		foreach ($this->vars as $key=>$var) {
			$out = preg_replace('/'.$var["name"].'/', $var["value"], $out);
		}

		return $out;
	}
}
?>