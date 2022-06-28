<?php
if ( ! defined('PPP')) exit('非法入口');
return array(
	'METHOD' => 'AES-256-CBC',
    'KEY' => 'RANDOM_KEY_With_API',
    'IV' => 'Initialization_Vector_With_API',
    'SALT' => 'sw$'
);