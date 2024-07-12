<?php
declare(strict_types=1);

require_once "../vendor/autoload.php";
require_once "../src/App.php";

$settings = require_once "../settings/settings.php";

$app = new App\App($settings);

$app->start($settings);