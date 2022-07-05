<?php
namespace core\lib;
if ( ! defined('PPP')) exit('非法入口');

class resModel {
    public $code;
	public $data;
    public $message;
    
    public function __construct($status, $data, $message = null) {
        $httpStatus = array(
            200 => 'OK',
            201 => 'Created',
            204 => 'No Content',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => '...',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => '...',
            406 => '...',
            407 => '...',
        );

        // http_response_code($status);
        $this->init($data, $message ? $message : $httpStatus[$status]);
        $this->code = $status;
        return $this;
        // return $this->code.$this->data.$this->message;
    }

	private function init($data, $message) {
		if(gettype($data) === 'string') {
			$this->message = $data;
			$data = null;
			$message = null;
		}
		if($data) {
			$this->data = $data;
		}
		if($message) {
			$this->message = $message;
		}
	}
}
