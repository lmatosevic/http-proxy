## Usage
### Configure
Proxy configuration file is located in conf/proxy-config.php.
```php
<?php
define('PROXY_HOST', 'localhost');

define('PROXY_PORT', '8080');

define('REDIRECT_HOST', 'localhost');

define('REDIRECT_SCHEMA', 'http');

define('REDIRECT_PORT', '80');

define('LOG_INFO_PATH', 'log/server.log');

define('LOG_DEBUG_PATH', 'php://stdout');

define('LOG_ERROR_PATH', 'php://stderr');
```

### Run
You can run this proxy server using any HTTP server, like Apache. 
Or you can start it with PHP built-in server by running proxy server from /public folder with following command: 

```sh
"php -S 0.0.0.0:8080 route.php"
```

Server user must have read/write premissions to /public folder on deployed server.
