<?php

/**
 * GlobalStaticConfigStorage. Used to store global settings. Link it to any file in project and enjoy config
 *
 * @author Ivan
 */
class GlobalStaticConfigStorage {
    public static $globalAppConfig = array(
        //a json file where the API credentials are stored
    'twitterconfig' => 'appsettings.json',
        //how often should we update the page
    'updatefrequency' => 1.5,
        //define separate handler for ajax queries, to keep all headers clear
    'updatehandler' => 'ajax_handler.php',
    );
    public static function obtainTwitterConfig() {
        $str = file_get_contents(__DIR__."/".GlobalStaticConfigStorage::$globalAppConfig['twitterconfig']);
        $json = json_decode($str, true);
        return $json;
    }
}
