<?php
include __DIR__.'/vendor/autoload.php';
use YandexStation\YandexDialog;

$dialog = new YandexDialog();

require "transliteral.php";

if ( $dialog->get_request() ) {
	function _new_session($dialog) {
		$dialog->add_message('Здравствуйте! Хотите написать свой код?');
        $dialog -> add_button('Да', null, null, true);
        $dialog -> add_button('Нет', null, null, true);

		$dialog->set_user_data("code", "");
		$dialog->set_user_data("vars", "[]");
	}

	$dialog->bind_new_action('_new_session');

    function _yes($tokens, $dialog) {
        $dialog -> add_message(
            'Вот и молодец, пора создать свой проект!'
        );
        $dialog -> add_button('Создать проект name', null, null, true);
    }
    $dialog -> bind_words_action([
        'я', 
        'буду', 
        'хочу',
        'да', 
        'давай'
    ], '_yes');

    function _newProgram($tokens, $dialog) {
        $dialog->add_message('Создан проект: ' . implode($tokens));
        $projects = $dialog->get_user_data('projects');
        $p_arr = json_decode($projects);
        $p_arr[] = array(
            "title" => $tokens['title'],
            "code"  => ""
        );

        $dialog->set_user_data('projects', json_encode($p_arr, JSON_UNESCAPED_UNICODE) );
        $dialog -> add_button('Покажи мои проекты', null, null, true);
    }
    $dialog->bind_template_action([
        'Название проекта {title:word}', 
        'Проект называется {title:word}', 
        'Создать проект {title:word}', 
        'Создай проект {title:word}'
    ], '_newProgram');

    function _programList($tokens, $dialog) {
        $projects = $dialog->get_user_data('projects');
        $p_arr = json_decode($projects);
        $out = "";

        foreach ($p_arr as $key=>$value) {
           $out .= "$key. " . $value->title . "\n";
        }

        $dialog -> add_message($out);
        $dialog -> add_button('Открыть проект name', null, null, true);
    }
    $dialog -> bind_words_action([
        'покажи', 
        'все', 
        'мои', 
        'проекты', 
        'список', 
        'проектов'
    ], '_programList');

    function _openProject($tokens, $dialog) {
        $projects = $dialog->get_user_data('projects');
        $p_arr = json_decode($projects);
        $out = "";

        foreach ($p_arr as $key=>$value) {
           $out = $value->title;
            if ($tokens['project'] == $out) {
                $activeProject = $tokens['project'];
                $dialog->add_message('Открыт проект: ' . $activeProject);
                $dialog -> add_button('Создай переменную Миша', null, null, true);
                return false;
            } else {
                $dialog->add_message('Такого проекта нет');
            }
        }

        $dialog -> add_message($out);
    }
    $dialog -> bind_template_action([
        'Открой проект {project:word}',
        'Открыть проект {project:word}',
        'Запустить проект {project:word}',  
        'Запусти проект {project:word}',
    ], '_openProject');

	function _create_empty_var($tokens, $dialog) {
		$var_name = convert($tokens["var"]);
		$cyr = $tokens["var"];

		$code = $dialog->get_user_data("code");
		$code .= "var $var_name;\n";
		$dialog->set_user_data("code", $code);

		$vars = json_decode( $dialog->get_user_data("vars") );
		$vars[] = array(
			"name"   => $var_name,
			"origin" => $tokens["var"],
			"value"  => null
		);
		$dialog->set_user_data("vars", json_encode($vars, JSON_UNESCAPED_UNICODE) );

    	$dialog->add_message("Переменная $var_name создана");
    	$dialog -> add_button("Переменная $cyr равна 15", null, null, true);
    	$dialog -> add_button("Переменная $cyr равна 15+14", null, null, true);
    }

    $dialog -> bind_template_action([
        'Создай переменную {var:word}',
        'Создать переменную {var:word}'
    ], '_create_empty_var');

    function _output_program($tokens, $dialog) {
    	$code = $dialog->get_user_data("code");
    	$dialog->add_message($code);

    	$dialog -> add_button('Открой мой код', null, null, true);
    }
    $dialog->bind_template_action([
        'Покажи мой код',
        'Открой мой код'
    ], '_output_program');


    function _output_var($tokens, $dialog) {
    	$pre_name = $tokens["var"];
    	$vars = json_decode( $dialog->get_user_data("vars") );

    	foreach ($vars as $var) {
    		if ($var->origin === $pre_name) {
    			$var_name = $var->name;
    		}
    	}

		if ( isset($var_name) ) {
			$code = $dialog->get_user_data("code");
			$code .= "console.log($var_name);\n";
			$dialog->set_user_data("code", $code);

			$dialog->add_message("Переменная $var_name выведена");
		}
		else {
			$dialog->add_message("Такой переменной нет");
		}
		
		$dialog -> add_button('Покажи код', null, null, true);
    }
    $dialog -> bind_template_action([
        'Выведи переменную {var:word}'
    ], '_output_var');


    function _assign_var($tokens, $dialog) {
    	$pre_name = $tokens["var"];
    	$value = $tokens["val"];
    	$vars = json_decode( $dialog->get_user_data("vars") );

    	foreach ($vars as $var) {
    		if ($var->origin === $pre_name) { $var_name = $var->name; }
    	}

    	if ( isset($var_name) ) {
    		$code = $dialog->get_user_data("code");
			$code .= "$var_name = $value;\n";
			$dialog->set_user_data("code", $code);

			$dialog->add_message("Переменной $var_name присвоено значение $value");
    	}
    	else {
    		$dialog->add_message("Такой переменной нет");
    	}

		$dialog->add_button('Выведи переменную Миша', null, null, true);
    }
    $dialog->bind_template_action([
        'Переменная {var:word} равна {val:word}'
    ], '_assign_var');

    function _no($tokens, $dialog) {
        $dialog -> add_message('Очень жаль, ещё увидимся!');
        $dialog -> end_session();
    }
    $dialog -> bind_words_action(['не', 'нет', 'не хочу'], '_no');
    
    $dialog->finish();
    exit;
}
?>