<?php declare(strict_types = 1);

namespace Amp\Postgres;

use Amp\Struct;

class Notification {
    use Struct;
    
    /** @var string Channel name. */
    public $channel;
    
    /** @var int PID of message source. */
    public $pid;
    
    /** @var string Message paypload */
    public $payload;
}