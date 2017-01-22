<?php

require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/../src/Application.php';

include __DIR__ . '/../conf/proxy-config.php';

(new lm\proxy\Application)->run();