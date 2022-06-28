<?php

namespace core\lib;
if ( ! defined('PPP')) exit('非法入口');
class Filter {
    //sql xss 注入防護
    private function filter($str)
    {
        $farr = array(
            "/\\s+/",
            "/<(\\/?)(script|i?frame|style|html|body|title|link|meta|object|\\?|\\%)([^>]*?)>/isU",
            "/(<[^>]*)on[a-zA-Z]+\s*=([^>]*>)/isU",
            "/select|insert|update|delete|\'|\/\*|\*|\.\.\/|\.\/|union|into|load_file|outfile|dump/is",
        );
        $str = preg_replace($farr,"",$str);
        return addslashes($str);
    }
    
    //sql xss 注入防護
    public function SQL_filter($array)
    {
        if (is_array($array))
        {
            foreach($array AS $index => $value)
            {
                $array[$index] = $this->filter($value);
            }
        }
        else
        {
            $array = $this->filter($array);
        }
        return $array;
    }
}