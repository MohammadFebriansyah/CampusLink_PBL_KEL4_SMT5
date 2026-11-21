<?php

namespace App\Logging;

use Illuminate\Log\Logger;
use Monolog\Handler\FormattableHandlerInterface;

/** Dipasang lewat 'tap' di config/logging.php: semua handler channel memakai format JSON terstruktur. */
class StrukturLog
{
    public function __invoke(Logger $logger): void
    {
        foreach ($logger->getHandlers() as $handler) {
            if ($handler instanceof FormattableHandlerInterface) {
                $handler->setFormatter(new FormatJsonLog);
            }
        }
    }
}
