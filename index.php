<?php
//composer's wizardry. links twitter API wrapper
require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/GlobalStaticConfigStorage.php';
/**
 * represents entrypoint
 */
class MainProcessor {
    private $instMainRenderer;
    //$globalAppConfig moved to GlobalStaticConfigStorage
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
            //handle_error();  
            break;
        }
    }
    //private function obtainTwitterConfig() moved to GlobalStaticConfigStorage
    /**
     * 
     * @return type
     */
    private function obtainHomeTimelineContentInitial() {
        $twitterDeveloperAccessCredentials = GlobalStaticConfigStorage::obtainTwitterConfig();
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
    /**
     * Get messages sent by user (newsfeed?): https://developer.twitter.com/en/docs/tweets/timelines/api-reference/get-statuses-user_timeline.html
     * @param type $in_screenName
     * @param type $in_count
     * @return type
     */
    private function obtainUserTimelineContentInitial($in_screenName, $in_count) {
        $twitterDeveloperAccessCredentials = GlobalStaticConfigStorage::obtainTwitterConfig();
        /*
        echo "<pre>".json_encode($twitterDeveloperAccessCredentials)."</pre>";
         */
        $url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
        $requestMethod = 'GET';
        $getfield = "?screen_name=".$in_screenName."&count=".$in_count;
        $twitter = new TwitterAPIExchange($twitterDeveloperAccessCredentials["TwitterConfig"]);
        $result0 = $twitter->setGetfield($getfield);
        $result = $result0->buildOauth($url, $requestMethod);
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
                //twitter API library return a json string, decode it
                if (isset($_GET['query_variant'])&&($_GET['query_variant']=='usr_timeline')&&isset($_GET['screen_name'])) {
                    $found_screenname = $_GET['screen_name'];
                    //how many posts should we display in user's 
                    $found_postcount = 25;
                    if (isset($_GET['post_count'])) {
                        $found_postcount = $_GET['post_count'];
                    }
                    $contentRaw = json_decode($this->obtainUserTimelineContentInitial($found_screenname, $found_postcount),true);
                    $this->instMainRenderer->renderDispatchPageUserTimeLine($contentRaw, $found_screenname, $found_postcount);
                } else {
                $contentRaw = json_decode($this->obtainHomeTimelineContentInitial(),true);
                $this->instMainRenderer->renderDispatchPage($contentRaw);
                }
            } else { 
                if ($_GET['action']=='ajax_get_update') {
                    echo "WRONG REQUEST!";
                } else {
                //bad action value, show landing page
                $this->instMainRenderer->renderLandingPage();
                }
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
        //$recordLine = $this->addOrUpdateUrlParam("action", "dispatch");
        echo "<h1 style=\"font-size:40px; margin-top:30px; margin-bottom:30px; text-align:center\">Twitter Home Timeline</h1>";
        //echo "<p style=\"text-align:center; text-align:center; font-size:20px; margin-top:30px;\">"."<a href=\"".$recordLine."\">"."Let's check out the Home Line from Twitter (this is supposed to be in realtime and may slow down the browser)"."</a>"."</p>";
        echo "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"get\" target=\"_blank\">";
        echo "<div style=\"text-align:center;\"> <input type=\"submit\" style=\"border: none; text-decoration:underline; color: dkgray; font-size:20px; margin-top:30px; cursor: pointer; background-color:white;\" value=\"Let's check out the Home Line from Twitter (this is supposed to be in realtime and may slow down the browser)\"></div>";
        echo "<input type=\"hidden\" name=\"action\" value=\"dispatch\">";
        echo "<div style=\"width: 40%;margin-left: auto;margin-right: auto; margin-top: 30px; border: 1px solid #ccc; padding:10px;\"> "
           . "<input type=\"radio\" name=\"query_variant\" value=\"home_timeline\" checked> <label>Home Timeline</label> <br/>"
           . "<input type=\"radio\" name=\"query_variant\" value=\"usr_timeline\"> <label>timeline of other user (screen name, enter without &lt; @ &gt;)</label>&nbsp;<input type=\"text\" name=\"screen_name\">"
           . " </div>";
        echo "</form>";
    }
    /**
     * render Home TimeLine
     * @param type $in_contentRaw - an array which contains tweets from user's Home Timeline
     */
    public function renderDispatchPage($in_contentRaw) {
        //declare here jQuery part of page
        echo "<script src=\"https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js\"></script>";
        echo "<script type=\"text/javascript\">";
        //https://stackoverflow.com/questions/9642205/how-to-force-a-script-reload-and-re-execute
        //https://stackoverflow.com/questions/7718935/load-scripts-asynchronously
        echo  "function reloadJs(src1) {"
             ."src = $('script[src$=\"' + src1 + '\"]').attr(\"src\");"
             ."$('script[src$=\"' + src + '\"]').remove();";
        echo  "var head= document.getElementsByTagName('head')[0];";
        echo  "var script= document.createElement('script');"
             ."script.type= 'text/javascript';"
             ."script.src= src1; script.async=true;"
                . "head.appendChild(script); }";
        echo "reloadJs(\"https://platform.twitter.com/widgets.js\");";
        echo "</script>";
        echo "<script type=\"text/javascript\">";
        echo "  setInterval(update, Math.round(1000*60*".GlobalStaticConfigStorage::$globalAppConfig["updatefrequency"]."));";
        //perform update in cycle, over the defined amount of minutes
        echo "function update(){ ";
        //update timestamp
        echo "   var dt = new Date();";
        echo "   var utcDate = dt.toUTCString();";
        echo "   document.getElementById(\"timestamp\").innerHTML = utcDate; ";
        //update page content, configure AJAX query to server. Once again, query is done every updatefrequency minutes (minvalue = 1) not to abuse Twitter services
        //set URL
        $params = /*$_GET;*/array(); $params["action"] = "ajax_get_update";
        //$AJAX_path = $_SERVER['PHP_SELF']."?".http_build_query($params);
        $AJAX_path = GlobalStaticConfigStorage::$globalAppConfig["updatehandler"]."?".http_build_query($params);
        echo "   var jqxhr = $.get(\"".$AJAX_path."\", "
                //jquery handler - server answer. I suppose that client should not get busy generating a frontend part, but instead receive from server a pregenerated HTML-ready code
                // and then just paste it to a certain div. It is way better for productivity (backend operates more resources than frontend), but somewhat violates strict architectural practices
                . "function (data) {"
                . "document.getElementById(\"tweets-content-smnm\").innerHTML = data;"
                ." reloadJs(\"https://platform.twitter.com/widgets.js\");"
                . "}".");";
        echo "}";
        echo "</script>";
        
        echo "<h1 style=\"font-size:40px; margin-top:30px; margin-bottom:30px; text-align:center\">Twitter Home Timeline [Display]</h1>";
        //echo "<script async src=\"https://platform.twitter.com/widgets.js\" charset=\"utf-8\"></script>";
        echo "<div id=\"timestamp-common\" style=\"width:100%\"> Updated every <span id=\"timestamp-delay\">".GlobalStaticConfigStorage::$globalAppConfig["updatefrequency"]." min</span> || Last update: <span id=\"timestamp\"> on page loading </span> </div>";
        echo "<br/>";
        echo "<div id=\"tweets-content-smnm\" >";
        foreach ($in_contentRaw as $singleTweet) {
            //let's prepare main text of tweet for output by prerendering all the links
                $rawText = $singleTweet['text'];
                //https://stackoverflow.com/questions/206059/php-validation-regex-for-url
                $regex = "/(?i)\b((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\".,<>?«»“”‘’]))/";
                $result2 = preg_replace($regex,'<a href="$1">$1</a>',$rawText);
            
            echo '<blockquote class="twitter-tweet" data-lang="'.$singleTweet['user']['lang'].'">';
            echo '<p lang="'.$singleTweet['lang'].'" dir="'."ltr".'">';
            echo $result2;
            echo "</p>";
            echo '&mdash '.$singleTweet['user']['name'].'(@'.$singleTweet['user']['screen_name'].') '.'<a href="https://twitter.com/'.$singleTweet['user']['screen_name'].'/status/'.$singleTweet['id_str'].'?ref_src=twsrc%5Etfw">'.$singleTweet['created_at'].'</a>';
            echo "</blockquote>";
            
        }
        echo "</div>";
        //echo $in_contentRaw;
    }
    public function renderDispatchPageUserTimeLine($in_contentRaw, $in_screenname, $in_postcount) {
        
                //declare here jQuery part of page
        echo "<script src=\"https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js\"></script>";
        echo "<script type=\"text/javascript\">";
        //https://stackoverflow.com/questions/9642205/how-to-force-a-script-reload-and-re-execute
        //https://stackoverflow.com/questions/7718935/load-scripts-asynchronously
        echo  "function reloadJs(src1) {"
             ."src = $('script[src$=\"' + src1 + '\"]').attr(\"src\");"
             ."$('script[src$=\"' + src + '\"]').remove();";
        echo  "var head= document.getElementsByTagName('head')[0];";
        echo  "var script= document.createElement('script');"
             ."script.type= 'text/javascript';"
             ."script.src= src1; script.async=true;"
                . "head.appendChild(script); }";
        echo "reloadJs(\"https://platform.twitter.com/widgets.js\");";
        echo "</script>";
        echo "<script type=\"text/javascript\">";
        echo "  setInterval(update, Math.round(1000*60*".GlobalStaticConfigStorage::$globalAppConfig["updatefrequency_usertimeline"]."));";
        //perform update in cycle, over the defined amount of minutes
        echo "function update(){ ";
        //update timestamp
        echo "   var dt = new Date();";
        echo "   var utcDate = dt.toUTCString();";
        echo "   document.getElementById(\"timestamp\").innerHTML = utcDate; ";
        //update page content, configure AJAX query to server. Once again, query is done every updatefrequency minutes (minvalue = 1) not to abuse Twitter services
        //set URL
        $params = /*$_GET;*/array(); $params["action"] = "ajax_get_update_user"; $params["screen_name"] = $in_screenname; $params["post_count"] = $in_postcount;
        //$AJAX_path = $_SERVER['PHP_SELF']."?".http_build_query($params);
        $AJAX_path = GlobalStaticConfigStorage::$globalAppConfig["updatehandler"]."?".http_build_query($params);
        
        echo "   var jqxhr = $.get(\"".$AJAX_path."\", "
                //jquery handler - server answer. I suppose that client should not get busy generating a frontend part, but instead receive from server a pregenerated HTML-ready code
                // and then just paste it to a certain div. It is way better for productivity (backend operates more resources than frontend), but somewhat violates strict architectural practices
                . "function (data) {"
                . "document.getElementById(\"tweets-content-smnm\").innerHTML = data;"
                ." reloadJs(\"https://platform.twitter.com/widgets.js\");"
                . "}".");";
         
        
        echo "}";
        echo "</script>";
        
        echo "<h1 style=\"font-size:40px; margin-top:30px; margin-bottom:30px; text-align:center\">Twitter User Timeline [Display] [".$in_screenname."]</h1>";
        //echo "<script async src=\"https://platform.twitter.com/widgets.js\" charset=\"utf-8\"></script>";
        echo "<div id=\"timestamp-common\" style=\"width:100%\"> Updated every <span id=\"timestamp-delay\">".GlobalStaticConfigStorage::$globalAppConfig["updatefrequency_usertimeline"]." min</span> || Last update: <span id=\"timestamp\"> on page loading </span> </div>";
        echo "<br/>";
        echo "<div id=\"tweets-content-smnm\" >";
        foreach ($in_contentRaw as $singleTweet) {
            //let's prepare main text of tweet for output by prerendering all the links
                $rawText = $singleTweet['text'];
                //https://stackoverflow.com/questions/206059/php-validation-regex-for-url
                $regex = "/(?i)\b((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\".,<>?«»“”‘’]))/";
                $result2 = preg_replace($regex,'<a href="$1">$1</a>',$rawText);
            
            echo '<blockquote class="twitter-tweet" data-lang="'.$singleTweet['user']['lang'].'">';
            echo '<p lang="'.$singleTweet['lang'].'" dir="'."ltr".'">';
            echo $result2;
            echo "</p>";
            echo '&mdash '.$singleTweet['user']['name'].'(@'.$singleTweet['user']['screen_name'].') '.'<a href="https://twitter.com/'.$singleTweet['user']['screen_name'].'/status/'.$singleTweet['id_str'].'?ref_src=twsrc%5Etfw">'.$singleTweet['created_at'].'</a>';
            echo "</blockquote>";
            
        }
        echo "</div>";
        
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
        <title>Twitter Timeline / Feed</title>
    </head>
    <body>
        <?php
            $instMainProcessor = new MainProcessor();
            $instMainProcessor->resolveRequest();
        ?>
    </body>
</html>
