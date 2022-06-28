<?php

namespace core\lib;

if ( ! defined('PPP')) exit('非法入口');

use core\lib\config;
use Medoo\Medoo;
class model extends Medoo
{
	public function __construct()
	{
		$database = config::all('database');
		// p($database);exit;
		parent::__construct($database);
	}

}