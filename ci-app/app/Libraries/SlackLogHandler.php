<?php

namespace App\Libraries;

use CodeIgniter\Log\Handlers\BaseHandler;
use CodeIgniter\Log\Handlers\HandlerInterface;

class SlackLogHandler extends BaseHandler implements HandlerInterface
{

    public function __construct(array $config = [])
    {
        parent::__construct($config);

    }

    public function handle($level, $message): bool
    {
        helper('jsi_helper');

        //slackIt($level, $message, '', false);

        return true;
    }
}