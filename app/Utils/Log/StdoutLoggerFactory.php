<?php
declare(strict_types = 1);

namespace App\Utils\Log;

use Psr\Container\ContainerInterface;

class StdoutLoggerFactory
{
    public function __invoke(ContainerInterface $container) : \Psr\Log\LoggerInterface
    {
        return Log::get('sys');
    }
}