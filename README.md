## xhprof-client
use xhprof-client collect xhprof data

### install
```
# vim composer.json
{
    "minimum-stability": "dev",
    "require": {
        "ruansheng/xhprof-client":"master"
    }
}

# composer install
```

### use
```
use Xhprof\Client\Client;

$client = new Client();
$client->setCollectRate();
$client->setProjectId('huzhu');
$client->setRedisAddres();
$client->setRedisKeyInfo('xhprof-data');
$client->collection();
```