<?php /**
 * This file is part of the RouterDb
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/pllano/router-db
 * @version 1.2.0
 * @package pllano/router-db
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
namespace Pllano\RouterDb\Apis;

use Pllano\RouterDb\Utility;
use GuzzleHttp\Client as Guzzle;
use Elasticsearch\ClientBuilder as Elastic;
 
/**
 * ElasticsearchDb
*/
class Elasticsearch
{

    private $client;
    private $resource = null;
    private $host = null;
    private $port = null;
    private $type = null;
    private $index = null;
    private $auth = null;
    private $user = null;
    private $password = null;
 
    public function __construct(array $config = [], array $options = [])
    {
        if (count($config) >= 1){
            if (isset($config["host"])) {
                $this->host = $config["host"];
            }
            if (isset($config["port"])) {
                $this->port = $config["port"];
            }
            if (isset($config["type"])) {
                $this->type = $config["type"];
            }
            if (isset($config["index"])) {
                $this->index = $config["index"];
            }
            if (isset($config["auth"])) {
                $this->auth = $config["auth"];
            }
            if (isset($config["user"])) {
                $this->user = $config["user"];
            }
            if (isset($config["password"])) {
                $this->password = $config["password"];
            }
        }

        $index = $config['db']['elasticsearch']['index'];
        $user = $config['db']['elasticsearch']['user'];
        $pass = $config['db']['elasticsearch']['pass'];
        $hosts = ['http://'.$elasticsearch_user.':'.$elasticsearch_pass.'@localhost:9200'];
        $this->client = Elastic::create()->setHosts($hosts)->build();

    }

    public function ping($resource = null)
    {
            return "elasticsearch";
    }

    // Загрузить
    public function get($resource = null, array $arr = [], $id = null)
    {
        if (isset($resource)) {

            if ($this->type === true) {
                $type = $resource;
                $index = $this->index;
            } else {
                $index = $this->index."_".$resource;
                $type = null;
            }
 
            // если $id определен то это обычный get
            if (isset($id)) {
 
                $params["index"] = $index;
                $params["type"] = $type;
                $params["id"] = $id;
                $params["client"] = ['ignore' => [400, 404, 500]];

                $get = $this->client->get($params);
 
            } elseif (count($arr) >= 1 && $id === null) {
                // Если мы получили массив $arr то это search
                
                $this->client->search($params);
 
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
 
    // Искать
    public function search($resource = null, array $query_arr = [], $keyword = null)
    {
        // Здесь будет много кода с маневрами :)
        $this->client->search($params);
    }
 
    // Создаем одну запись
    public function post($resource = null, array $arr = [])
    {

        $params["index"] = $this->index;
        $params["type"] = $this->type;
        if (isset($id)) {
            $params["id"] = $id;
        }
        $params["client"] = ['ignore' => [400, 404, 500]];
        
        if (count($arr) >= 1) {
            foreach($arr as $key => $value)
            {
                if (isset($key) && isset($unit)) {
                    $params["body"][$key] = $value;
                }
            }
        }
 
        $this->client->index($params);
 
    }
 
    // Обновляем
    public function put($resource = null, array $arr = [], $id = null)
    {
        if (isset($resource)) {

            if ($this->type === true) {
                $type = $resource;
                $index = $this->index;
            } else {
                $index = $this->index."_".$resource;
                $type = null;
            }
 
            if (isset($id)) {
                $params["index"] = $index;
                $params["type"] = $type;
                $params["id"] = $id;
                $params["client"] = ['ignore' => [400, 404, 500]];
        
                if (count($arr) >= 1) {
                    foreach($arr as $key => $value)
                    {
                        if (isset($key) && isset($unit)) {
                            $params["body"]["doc"][$key] = $value;
                        }
                    }
                }
 
                $this->client->update($params);
            }
        }
    }
    
    // Обновляем
    public function patch($resource = null, array $arr = [], $id = null)
    {
        if (isset($resource)) {

            if ($this->type === true) {
                $type = $resource;
                $index = $this->index;
            } else {
                $index = $this->index."_".$resource;
                $type = null;
            }
 
            if (isset($id)) {
                $params["index"] = $index;
                $params["type"] = $type;
                $params["id"] = $id;
                $params["client"] = ['ignore' => [400, 404, 500]];
        
                if (count($arr) >= 1) {
                    foreach($arr as $key => $value)
                    {
                        if (isset($key) && isset($unit)) {
                            $params["body"]["doc"][$key] = $value;
                        }
                    }
                }
 
                $this->client->update($params);
            }
        }
    }
 
    // Удаляем
    public function delete($resource = null, array $arr = [], $id = null)
    {
        if (isset($resource)) {

            if ($this->type === true) {
                $type = $resource;
                $index = $this->index;
            } else {
                $index = $this->index."_".$resource;
                $type = null;
            }
 
            if ($id >= 1) {
                $params["index"] = $index;
                if (isset($type)) {
                    $params["type"] = $type;
                }
                $params["id"] = $id;
                $params["client"] = ['ignore' => [400, 404, 500]];
 
                $this->client->delete($params);
 
            } elseif (count($arr) >= 1) {
                foreach($arr as $value)
                {
                    // ПЕРЕПИСАТЬ !!!!!!
                    if (isset($value["id"])) {
                        $params["index"] = $index;
                        if (isset($type)) {
                            $params["type"] = $type;
                        }
                        $params["id"] = $value["id"];
                        $params["client"] = ['ignore' => [400, 404, 500]];
 
                        $this->client->delete($params);
                    }
                }
            } else {
               return null;
            }
        } else {
            return null;
        }
    }
 
    // Получить последний идентификатор
    public function last_id($resource)
    {
        return null;
    }

}
 