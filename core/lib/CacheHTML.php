<?php
namespace core\lib;
if ( ! defined('PPP')) exit('非法入口');
class CacheHTML {
    public $folder = PUBLICS;

    //確認是否有DIR
    public function check_folder($folder_name = "") {
        if($folder_name != "") {
            if(is_dir($this->folder . $folder_name)) {
                return true;
            }
        }
        return false;
    }

    private function get_html($folder_name = "") {
        if($folder_name != "") {
            if(is_dir($this->folder . $folder_name)) {
                return scandirFolderWithoutFolder($this->folder . $folder_name);
            }
        }
        return false;
    }
    
    public function pre_create_html($folder_name = "") {
        if($folder_name != "") {
            if(!is_dir($this->folder . $folder_name)) {
                //建DIR
                mkdir($this->folder . $folder_name, 0755, true);
            }
            // Cache the contents to a cache file
            ob_start();
            return true;
        }
        return false;
    }

    public function create_html($folder_name = "") {
        if($folder_name != "") {
            // Cache the contents to a cache file
            $cachefile = $this->folder . $folder_name . '/' . time() . '.html';
            $cached = fopen($cachefile, 'w');
            fwrite($cached, ob_get_contents());
            fclose($cached);
            ob_end_flush();
            return true;
        }
        return false;
    }

    private function delete_html($folder_name = "") {
        if($folder_name != "") {
            delTree($this->folder . $folder_name);
            return true;
        }
        return false;
    }

    public function include_html($folder_name = ""){
        if($folder_name != "") {
            $arr = $this->get_html($folder_name);
            if($arr != false && count($arr) == 1) {
                blob_etag($this->folder . $folder_name . $arr[0]);
                include($this->folder . $folder_name . $arr[0]);
                exit();
            }
            $this->delete_html($folder_name);
        }
        return false;
    }
}