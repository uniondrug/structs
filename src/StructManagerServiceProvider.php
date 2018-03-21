<?php

namespace Uniondrug\Structs;

use Phalcon\Di\ServiceProviderInterface;

class StructManagerServiceProvider implements ServiceProviderInterface
{
    public function register(\Phalcon\DiInterface $di)
    {
        $di->set(
            'structManager',
            function () {
                return new StructManager();
            }
        );
    }
}
