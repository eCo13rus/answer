<?php

namespace NamePlugin;

class NameApi
{
    public $api_url;

    public function listVacancies($post, $vid = 0)
    {
        if (!is_object($post)) {
            throw new \InvalidArgumentException("Parameter 'post' must be an object.");
        }

        $page = 0;
        $found = null;
        $ret = array();

        do {
            $params = http_build_query([
                'status' => 'all',
                'id_user' => $this->selfGetOption('superjob_user_id'),
                'with_new_response' => 0,
                'order_field' => 'date',
                'order_direction' => 'desc',
                'page' => $page,
                'count' => 100
            ]);

            $res = $this->apiSend($this->api_url . '/hr/vacancies/?' . $params);
            $res_o = json_decode($res);

            if ($res === false || !is_object($res_o) || !isset($res_o->objects)) {
                throw new \RuntimeException("API request failed or returned invalid data.");
            }

            foreach ($res_o->objects as $value) {
                if ($vid > 0 && $value->id == $vid) {
                    $found = $value;
                    break 2;
                }

                $ret[] = $value;
            }

            $page++;
        } while (!$found && $res_o->more);

        return $vid > 0 ? $found : $ret;
    }

    public function apiSend($url)
    {
        return file_get_contents($url);
    }

    public function selfGetOption($optionName)
    {
        return get_option($optionName);
    }
}
