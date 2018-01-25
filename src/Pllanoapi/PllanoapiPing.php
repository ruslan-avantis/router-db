<?php
/**
 * This file is part of the RouterDb
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/pllano/router-db
 * @version 1.0.1
 * @package pllano/router-db
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
namespace RouterDb\Pllanoapi;
 
use RouterDb\Utility;
use RouterDb\Ex;
use GuzzleHttp\Client as Guzzle;
 
class PllanoapiPing
{
    private $db = null;
    private $config;
 
    public function __construct(array $config = array())
    {
        if (count($config) >= 1){
            $this->config = $config;
        }
    }

    public function ping($resource = null)
    {
        if ($resource != null) {
            try {
                $url = $this->config["db"]["pllanoapi"]["url"];
                $query = "?limit=1&offset=0";
                if ($this->config["db"]["pllanoapi"]["auth"] == "QueryKeyAuth" && $this->config["db"]["pllanoapi"]["public_key"] != null) {
                    $query = "?public_key=".$this->config["db"]["pllanoapi"]["public_key"]."&limit=1&offset=0";
                }
                $guzzle = new Guzzle();
                $response = $guzzle->request("GET", $url."".$resource."".$query);
                $output = $response->getBody();
                $output = (new Utility())->clean_json($output);
                $records = json_decode($output, true);
                if (isset($records["headers"]["code"])) {
                    $this->db = "pllanoapi";
                    return $this->db;
                }
            } catch (Ex $ex) {
            return null;
            }
        } else {
            return null;
        }
    }
 
}
 