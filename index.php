<?php

include __DIR__.'/vendor/autoload.php';
use YandexStation\YandexDialog;

$alice = new YandexDialog();

$activeProject;
$cyr = [
    'а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п',
    'р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',
    'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П',
    'Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я'
];
$lat = [
    'a','b','v','g','d','e','io','zh','z','i','y','k','l','m','n','o','p',
    'r','s','t','u','f','h','ts','ch','sh','sht','a','i','y','e','yu','ya',
    'A','B','V','G','D','E','Io','Zh','Z','I','Y','K','L','M','N','O','P',
    'R','S','T','U','F','H','Ts','Ch','Sh','Sht','A','I','Y','e','Yu','Ya'
];

if($alice->get_request()) {

    function _new_session($alice) {
        $alice -> add_button('Да', null, null, true);
        $alice -> add_button('Нет', null, null, true);
        $alice -> add_button('Я буду создать программу!', null, null, true);
        $alice -> add_message('Здравствуйте! Хотите написать свой код?');

        $alice->set_user_data("projects", "[]");
        $alice->set_user_data("perems", "[]");
    }
    $alice -> bind_new_action('_new_session');
    
    function _yes($tokens, $alice) {
        $alice -> add_message(
            'Вот и молодец, пора создать свой проект!
            Введите его название'
        );
    }
    $alice -> bind_words_action([
        'я', 
        'буду', 
        'хочу', 
        'код',  
        'программу', 
        'да', 
        'давай'
    ], '_yes');

    function _newProgram($tokens, $alice) {
        $alice->add_message('Создан проект: ' . implode($tokens));
        $projects = $alice->get_user_data('projects');
        $p_arr = json_decode($projects);
        $p_arr[] = array(
            "title" => $tokens['title'],
            "code"  => ""
        );

        $alice->set_user_data('projects', json_encode($p_arr, JSON_UNESCAPED_UNICODE) );
    }
    $alice->bind_template_action([
        'Название проекта {title:word}', 
        'Проект называется {title:word}', 
        'Создать проект {title:word}', 
        'Создай проект {title:word}'
    ], '_newProgram');

    function _programList($tokens, $alice) {
        $projects = $alice->get_user_data('projects');
        $p_arr = json_decode($projects);
        $out = "";

        foreach ($p_arr as $key=>$value) {
           $out .= "$key. " . $value->title . "\n";
        }

        $alice -> add_message($out);
    }
    $alice -> bind_words_action([
        'покажи', 
        'все', 
        'мои', 
        'проекты', 
        'список', 
        'проектов'
    ], '_programList');

    function _openProject($tokens, $alice) {
        $projects = $alice->get_user_data('projects');
        $p_arr = json_decode($projects);
        $out = "";

        foreach ($p_arr as $key=>$value) {
           $out = $value->title;
            if ($tokens['project'] == $out) {
                $activeProject = $tokens['project'];
                $alice->add_message('Открыт проект: ' . $activeProject);
                return false;
            } else {
                $alice->add_message('Такого проекта нет');
            }
        }

        $alice -> add_message($out);
    }
    $alice -> bind_template_action([
        'Открой проект {project:word}',
        'Открыть проект {project:word}',
        'Запустить проект {project:word}',  
        'Запусти проект {project:word}',
    ], '_openProject');

    function _createPerem($tokens, $alice) {
        $perems = $alice->get_user_data('perems');
        $p_arr = json_decode($perems);
        $p_arr[] = array(
            "perem" => $tokens['perem'],
            "value"  => $tokens['value']
        );
        $alice->set_user_data('perems', json_encode($p_arr, JSON_UNESCAPED_UNICODE) );
        $alice -> add_message('Создана переменная ' . $tokens['perem'] . ' со значением ' . $tokens['value']);
    }
    $alice->bind_template_action([
        'Создать переменную {perem:word} со значением {value:word}', 
        'Создай переменную {perem:word} со значением {value:word}',
    ], '_createPerem');

    function _listPerem($tokens, $alice) {
        $perems = $alice->get_user_data('perems');
        $p_arr = json_decode($perems);
        $out = "";

        foreach ($p_arr as $key=>$value) {
           $out .= "$key. " . $value->perem . " = " . $value->value . "\n";
        }

        $alice -> add_message($out);
    }
    $alice -> bind_words_action([
        'покажи', 
        'все', 
        'мои', 
        'переменные', 
        'список', 
        'переменных'
    ], '_listPerem');

    function _no($tokens, $alice) {
        $alice -> add_message('Очень жаль, ещё увидимся!');
        $alice -> end_session();
    }
    $alice -> bind_words_action(['не', 'нет', 'не хочу'], '_no');

    function _default($alice) {
        $alice -> add_message('Я вас не понимаю!');
    }
    $alice -> bind_default_action('_default');

    $alice->finish();
    exit;
}

?>