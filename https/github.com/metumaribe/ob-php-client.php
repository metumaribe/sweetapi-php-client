<?php

/**
 * Class obClient
 * @package Sclient
 * @author Obinna Merenu <http://github.com/unerem>
 * @link  https://github.com/Metumaribe/sweetapi-php-client
 */

class obClient {
  public $handle;
  public $http_options;
  public $response_object;
  public $response_info;

   /**
   * Constructor.
   *
   */
  function __construct() {
    $this->http_options = [];

// @todo
// curl_setopt($ch, CURLOPT_TIMEOUT, 5);
// curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    $this->http_options[CURLOPT_RETURNTRANSFER] = true;
    $this->http_options[CURLOPT_FOLLOWLOCATION] = false;
  }

  /**
   * Perform a GET call to server
   *
   * Additionaly in $response_object and $response_info are the
   * response from server and the response info as it is returned
   * by curl_exec() and curl_getinfo() respectively.
   *
   * @param string $url The url to make the call to.
   * @param array $http_options Extra option to pass to curl handle.
   * @return string The response from curl if any
   */
  public function get($url, $http_options = array()) {
    $http_options = $http_options + $this->http_options;
    $this->handle = curl_init($url);
    if(! curl_setopt_array($this->handle, $http_options)){
      throw new RestClientException("Error setting cURL request options");
    }
    $this->response_object = curl_exec($this->handle);
    $this->_http_parse_message($this->response_object);
    curl_close($this->handle);
    
    // decode json to array
    return json_decode($this->response_object , TRUE);
  }

  /**
   * Response handler.
   *
   *
   * @param string $res the response object from the API.
   * @return string exception
   */
  private function _http_parse_message($res) {
    if(! $res){
      throw new HttpServerException(curl_error($this->handle), -1);
    }
    $this->response_info = curl_getinfo($this->handle);
    $code = $this->response_info['http_code'];
    if($code == 404) {
      throw new HttpServerException404(curl_error($this->handle));
    }
    if($code >= 400 && $code <=600) {
      throw new HttpServerException('Server response status was: ' . $code .
        ' with response: [' . $res . ']', $code);
    }
    if(!in_array($code, range(200,207))) {
      throw new HttpServerException('Server response status was: ' . $code .
        ' with response: [' . $res . ']', $code);
    }
  }
}

/**
 *
 * Class HttpServerException, HttpServerException404,
 * and RestClientException
 */
class HttpServerException extends Exception {
}
class HttpServerException404 extends Exception {
  function __construct($message = 'Not Found') {
    parent::__construct($message, 404);
  }
}
class RestClientException extends Exception {
}

// @todo remove below calls
// $call = new obClient();
// $call->get(url);