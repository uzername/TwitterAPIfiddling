<?php
//composer's wizardry. links twitter API wrapper
require_once __DIR__.'/vendor/autoload.php';
/**
 * represents entrypoint
 */
class MainProcessor {
    private $instMainRenderer;
    private static $globalAppConfig = array(
    'twitterconfig' => 'appsettings.json',
    );
    public function __construct() {
        $this->instMainRenderer = new MainRenderer();
    }
    public function resolveRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        // http://stackoverflow.com/questions/359047/detecting-request-type-in-php-get-post-put-or-delete
        switch ($method) {
          case 'PUT':

            break;
          case 'POST':
            //do_something_with_post();  
            break;
          case 'GET':
            $this->resolveGetQuery();  
            break;
          case 'HEAD':

            break;
          case 'DELETE':

            break;
          case 'OPTIONS':

            break;
          default:
            handle_error();  
            break;
        }
    }
    private function obtainTwitterConfig() {
        $str = file_get_contents(__DIR__."/".MainProcessor::$globalAppConfig['twitterconfig']);
        $json = json_decode($str, true);
        return $json;
    }
    private function obtainHomeTimelineContentInitial() {
        $twitterDeveloperAccessCredentials = $this->obtainTwitterConfig();
        /*
        echo "<pre>".json_encode($twitterDeveloperAccessCredentials)."</pre>";
         */
        $url = 'https://api.twitter.com/1.1/statuses/home_timeline.json';
        $requestMethod = 'GET';
        $twitter = new TwitterAPIExchange($twitterDeveloperAccessCredentials["TwitterConfig"]);
        $result = $twitter->buildOauth($url, $requestMethod);
        /*
        echo "<pre>";
        var_dump($result);
        echo "</pre>";
         */
        $result2 = $result->performRequest();
        return $result2;
    }
    private function resolveGetQuery() {
        if (isset($_GET['action'])) {
            if ($_GET['action']=='dispatch') { 
                $contentRaw = json_decode($this->obtainHomeTimelineContentInitial(),true);
                $this->instMainRenderer->renderDispatchPage($contentRaw);
            } else { //bad action value, show landing page
                $this->instMainRenderer->renderLandingPage();
            }
        } else { 
            $this->instMainRenderer->renderLandingPage();
        }
    }
}
/**
 * used to perform rendering on page. do not instantiate it directly, but in MainProcessor
 */
class MainRenderer {
    /** showing all Tweets is a jquery-heavy operation. Show user a landing page that informs him about this.
     * 
     */
    public function renderLandingPage() {
        //build here a link that guides to render page
        //https://stackoverflow.com/questions/3162725/how-can-i-add-get-variables-to-end-of-the-current-url-in-php
        $recordLine = $this->addOrUpdateUrlParam("action", "dispatch");
        echo "<h1 style=\"font-size:40px; margin-top:30px; margin-bottom:30px; text-align:center\">Twitter Home Timeline</h1>";
        echo "<p style=\"text-align:center; text-align:center; font-size:20px; margin-top:30px;\">"."<a href=\"".$recordLine."\">"."Let's check out the Home Line from Twitter (this is supposed to be in realtime and may slow down the browser)"."</a>"."</p>";
    }
    /**
     * 
     * @param type $in_contentRaw - an array which contains tweets from user's Home Timeline
     */
    public function renderDispatchPage($in_contentRaw) {
        //declare here jQuery part of page
        echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>';
        echo "<script type=\"text/javascript\">";
        echo '  setInterval(update, 1000);';
        //perform update in cycle
        echo 'function update(){
                 
              }';
        echo "</script>";
        
        echo "<h1 style=\"font-size:40px; margin-top:30px; margin-bottom:30px; text-align:center\">Twitter Home Timeline [Display]</h1>";
        echo "<script async src=\"https://platform.twitter.com/widgets.js\" charset=\"utf-8\"></script>";
        
        foreach ($in_contentRaw as $singleTweet) {
            //let's prepare main text of tweet for output by prerendering all the links
                $rawText = $singleTweet['text'];
                //https://stackoverflow.com/questions/206059/php-validation-regex-for-url
                $regex = "/(?i)\b((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\".,<>?«»“”‘’]))/";
                $result2 = preg_replace($regex,'<a href="$1">$1</a>',$rawText);
            
            echo '<blockquote class="twitter-tweet" data-lang="'.$singleTweet['user']['lang'].'">';
            echo '<p lang="'.$singleTweet['lang'].'" dir="'."ltr".'">';
            echo $result2;
            echo '</p>';
            echo '&mdash '.$singleTweet['user']['name'].'(@'.$singleTweet['user']['screen_name'].') '.'<a href="https://twitter.com/'.$singleTweet['user']['screen_name'].'/status/'.$singleTweet['id_str'].'?ref_src=twsrc%5Etfw">'.$singleTweet['created_at'].'</a>';
            echo '</blockquote>';
            
        }
        
        //echo $in_contentRaw;
    }
    private function addOrUpdateUrlParam($name, $value) {
        $params = $_GET;
        unset($params[$name]);
        $params[$name] = $value;
        return basename($_SERVER['PHP_SELF']).'?'.http_build_query($params);
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Twitter Home Timeline</title>
    </head>
    <body>
        <?php
            $instMainProcessor = new MainProcessor();
            $instMainProcessor->resolveRequest();
        ?>
    </body>
</html>
