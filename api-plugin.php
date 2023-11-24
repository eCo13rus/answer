<?php

namespace NamePlugin;

class NameApi
{
    public $api_url;

    // Изменен метод listVacancies для улучшения читаемости и структуры
    public function listVacancies($post, $vid = 0)
    {
        // Добавлена проверка типа входного параметра и исключение при ошибке
        if (!is_object($post)) {
            throw new \InvalidArgumentException("Parameter 'post' must be an object.");
        }

        $page = 0;
        $found = null;
        $ret = array();

        // Используем цикл do-while вместо goto для улучшения читаемости
        do {
            // Параметры запроса сформированы с помощью http_build_query для безопасности и читаемости
            $params = http_build_query([
                'status' => 'all',
                'id_user' => $this->selfGetOption('superjob_user_id'),
                'with_new_response' => 0,
                'order_field' => 'date',
                'order_direction' => 'desc',
                'page' => $page,
                'count' => 100
            ]);

            // Вызов apiSend для получения данных
            $res = $this->apiSend($this->api_url . '/hr/vacancies/?' . $params);
            $res_o = json_decode($res);

            // Обработка ошибок запроса к API
            if ($res === false || !is_object($res_o) || !isset($res_o->objects)) {
                throw new \RuntimeException("API request failed or returned invalid data.");
            }

            // Перебор полученных объектов
            foreach ($res_o->objects as $value) {
                // Проверка на наличие конкретного ID вакансии
                if ($vid > 0 && $value->id == $vid) {
                    $found = $value;
                    break 2; // Выход из цикла и do-while сразу
                }

                // Сохранение всех объектов, если конкретный ID не указан
                $ret[] = $value;
            }

            $page++;
        } while (!$found && $res_o->more);

        // Возврат найденного объекта или всего списка
        return $vid > 0 ? $found : $ret;
    }

    // Изменен метод apiSend для выполнения HTTP-запроса
    public function apiSend($url)
    {
        // Получение ответа от API
        $response = file_get_contents($url);

        // Проверка успешности запроса
        if ($response === false) {
            throw new \RuntimeException("Failed to get API response.");
        }

        return $response;
    }

    public function selfGetOption($optionName)
    {
        // Метод для получения настройки (пример с использованием WordPress get_option)
        
        // return get_option($optionName);
    }
}
