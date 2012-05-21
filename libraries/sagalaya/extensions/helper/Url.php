<?php

namespace sagalaya\extensions\helper;

use lithium\template\Helper;

/**
 * Description of Server
 *
 * @author Mukhamad Ikhsan
 */
class Url extends Helper {
    
    public function url() {
        return 'http://' . $_SERVER['SERVER_NAME'];
    }
    
    public function matchUrl($match, $url) {        
        return preg_match("|{$match}|", $url);
    }      
}

?>