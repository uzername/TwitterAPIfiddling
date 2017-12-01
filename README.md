# Twitter Homeline || Twitter user Timeline viewer

This PHP code shows how to work with Twitter API (obtain user's activity). It is packed with jQuery ajax callbacks.

### Prerequisites

This app uses a project j7mbo/twitter-api-php ( http://github.com/J7mbo/twitter-api-php ) for accessing Twitter data through OAuth. It uses 'composer.phar' util to get the required library files.

### Installing

Just grab the files from this repo. Check your access credentials for a code which communicates with Twitter on https://apps.twitter.com/app/ and https://dev.twitter.com/ . Create the appsettings.json file with the following content:

```
{
    "TwitterConfig":{
        "oauth_access_token" : "YOUR OAUTH TOKEN",
        "oauth_access_token_secret": "YOUR OAUTH TOKEN SECRET",
        "consumer_key" : "YOUR CONSUMER KEY",
        "consumer_secret" : "YOUR CONSUMER SECRET"
    }
}
```

Probably you would need to change values of 'twitterconfig' and 'updatehandler' fields of $globalAppConfig in GlobalStaticConfigStorage.php

Upload these files to your webhosting...