<?php

namespace App\Tests\Mock;

use ReCaptcha\ReCaptcha;
use ReCaptcha\Response;

class ReCaptchaMockFactory
{
    public static function create(): ReCaptcha
    {
        return new class('secret') extends ReCaptcha {
            public function verify($response, $remoteIp = null): Response
            {
                return new Response(true);
            }
        };
    }
}
