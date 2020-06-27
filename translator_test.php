<meta charset="utf-8">
<?php
	$pseudo = file_get_contents("pseudo.txt");
	$programs = explode("programm", $pseudo);

	$out = "<form method='GET'>";
	foreach ($programs as $key => $value) {
		$out .= "<input type='submit' name='prog_num' value='$key'>";
	}
	$out .= "</form>";
	echo $out;

	require "translator.php";

	if ( isset($_GET["prog_num"]) ) {
		$pn = (int)$_GET["prog_num"];
	}

	if ( isset($pn) ) {
		$p = new Program($programs[$pn]);
		$table = "<table>
					<tr>
						<th>Code</th>
						<th>Output</th>
					</tr><tr>";

		$table .= "<td>" . preg_replace("/\n/", "<br>", $p->origin) . "</td>";
		$table .= "<td>";

		foreach ($p->out_stream as $value) {
			$table .= "<p><i><b>$value</b></i></p>";
		}

		$table .= "</td></tr></table>";
		
		echo $table;
	}
?>