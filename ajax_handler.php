<?php
//this file is used to handle ajax requests
//for Twitter API
require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/GlobalStaticConfigStorage.php';
class MainProcessorAjax {
    private $instAjaxRenderer;
    public function __construct() {
        $this->instAjaxRenderer = new AjaxRenderer();
    }
    public function resolveRequestAjax() {
        $method = $_SERVER['REQUEST_METHOD'];
        // http://stackoverflow.com/questions/359047/detecting-request-type-in-php-get-post-put-or-delete
        switch ($method) {
          case 'PUT':

            break;
          case 'POST':
            //do_something_with_post();  
            break;
          case 'GET':
            $getQueryResult = $this->resolveGetQueryAjax(); 
            
            echo $getQueryResult;
            break;
          case 'HEAD':

            break;
          case 'DELETE':

            break;
          case 'OPTIONS':

            break;
          default:
            //handle_error();  
            break;
        }
    }
    
    private function resolveGetQueryAjax() {
        $varToReturn = "EMPTY";
        if (isset($_GET['action'])) {
            if ($_GET['action']=='ajax_get_update') {
                //perform query to Twitter API, update everything
                $contentRaw = json_decode($this->obtainHomeTimelineContentInitial(),true);
                $varToReturn = $this->instAjaxRenderer->renderHomeTimeLineToString($contentRaw);
            } else {
                $varToReturn = "ERROR002:wrong_action_value";
            }
        } else {
            $varToReturn = "ERROR001:action_not_set";
        }
        return $varToReturn;
    }
    
    private function obtainHomeTimelineContentInitial() {
        $twitterDeveloperAccessCredentials = GlobalStaticConfigStorage::obtainTwitterConfig();
        $url = 'https://api.twitter.com/1.1/statuses/home_timeline.json';
        $requestMethod = 'GET';
        $twitter = new TwitterAPIExchange($twitterDeveloperAccessCredentials["TwitterConfig"]);
        $result = $twitter->buildOauth($url, $requestMethod);
        $result2 = $result->performRequest();
        return $result2;
    }
}
/**
 * this class is used to pre-render content of page
 */
class AjaxRenderer {
    public function renderHomeTimeLineToString($in_contentRaw) {
        $completeTweetCollection = "";
        
        foreach ($in_contentRaw as $singleTweet) {
            //let's prepare main text of tweet for output by prerendering all the links
                $rawText = $singleTweet['text'];
                //https://stackoverflow.com/questions/206059/php-validation-regex-for-url
                $regex = "/(?i)\b((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\".,<>?«»“”‘’]))/";
                $result2 = preg_replace($regex,'<a href="$1">$1</a>',$rawText);
            
            $completeTweetCollection= $completeTweetCollection.'<blockquote class="twitter-tweet" data-lang="'.$singleTweet['user']['lang'].'">';
            $completeTweetCollection= $completeTweetCollection.'<p lang="'.$singleTweet['lang'].'" dir="'."ltr".'">';
            $completeTweetCollection= $completeTweetCollection.$result2;
            $completeTweetCollection= $completeTweetCollection.'</p>';
            $completeTweetCollection= $completeTweetCollection.'&mdash '.$singleTweet['user']['name'].'(@'.$singleTweet['user']['screen_name'].') '.'<a href="https://twitter.com/'.$singleTweet['user']['screen_name'].'/status/'.$singleTweet['id_str'].'?ref_src=twsrc%5Etfw">'.$singleTweet['created_at'].'</a>';
            $completeTweetCollection= $completeTweetCollection.'</blockquote>';
            $completeTweetCollection= $completeTweetCollection.'<br/>';
        }
        return $completeTweetCollection;
    }
}

$instMainProcessorAjax = new MainProcessorAjax();
$instMainProcessorAjax->resolveRequestAjax();
?>
