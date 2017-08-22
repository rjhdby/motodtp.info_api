<?php

namespace methods;


use core\MethodInterface;

abstract class MethodWithAuth implements MethodInterface
{
    public function __construct($data) {
        $auth = new Auth($data);
        $auth();
    }
}