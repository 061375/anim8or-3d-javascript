<?php
/*****************************************************************
 * version loader
 * @description : loads the latest version of the project unless otherwize specified
 * @copyright Copyright (c) 2010-Present, 061375
 * @author Jeremy Heminger <j.heminger@061375.com>
 * @bindings
 * @deprecated = false
 *
 * */
$version = isset($_GET['version']) ? $_GET['version'] : false;
if (false == $version) {
    $versions = array();
    if ($handle = opendir(getcwd().'/versions/')) {
        while (false !== ($entry = readdir($handle))) {
            $versions[] = $entry;
        }
        closedir($handle);
        $version = $versions[count($versions) -1 ];
    }
}
$file = getcwd().'/versions/'.$version;
if (true == file_exists($file)) {
    include($file);
}
?>