<?php
/**
 * File containing Client class
 *
 * @category  App
 * @package   Package
 * @author    Fredi Pevcin <fpevcin@astina.ch>
 * @copyright 2014 Astina AG (http://astina.ch)
 */

namespace Spark;

use InvalidArgumentException;

/**
 * Short description for class Client
 *
 * @category  App
 * @package   Package
 * @author    Fredi Pevcin <fpevcin@astina.ch>
 * @copyright 2014 Astina AG (http://astina.ch)
 */
class Client
{
    private $url;
    private $accessToken;

    public function __construct($url, $accessToken)
    {
        $this->url = $url;
        $this->accessToken = $accessToken;
    }

    /**
     * @param array $args
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function exec(array $args = array())
    {
        $fields = array(
            'access_token' => $this->accessToken,
        );

        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-type: application/x-www-form-urlencoded'
        ));

        $result = curl_exec($ch);
        $error = curl_error($ch);
        $errorNo = curl_errno($ch);
        curl_close($ch);

        if ($errorNo !== 0) {
            throw new InvalidArgumentException($error, $errorNo);
        }

        $data = json_decode($result, true);

        if (isset($data['error'])) {
            throw new InvalidArgumentException($data['error'], -1);
        }

        return $data;
    }
}
