<?php

declare(strict_types=1);

use App\Kernel;

require dirname(__DIR__).'/vendor/autoload.php';

$kernel = new Kernel('test', false);
$kernel->boot();

$registry = $kernel->getContainer()->get('doctrine');

return $registry->getManager();
