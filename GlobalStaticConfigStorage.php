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
        //it is allowed max of 15 requests during 15 min window
    'updatefrequency' => 1.5,
        //it is allowed up to 600 queries per 15 minutes. It means that 40 queries may be done per minute. Let's reduce to 30 queries / minute, not to overabuse Twitter services
        //distance between queries is 30/60 = 0.5 query per second. But actually noone would post with a speed higher than 1 minute. 
        //Seems like that receiving ban from Twitter for overusing its services is not good... So, let's keep pace limited to 0.75 query/minute
    'updatefrequency_usertimeline' => 0.75,
        //define separate handler for ajax queries, to keep all headers clear
    'updatehandler' => 'ajax_handler.php',
    );
    public static function obtainTwitterConfig() {
        $str = file_get_contents(__DIR__."/".GlobalStaticConfigStorage::$globalAppConfig['twitterconfig']);
        $json = json_decode($str, true);
        return $json;
    }
}
