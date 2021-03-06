<?php

namespace errors;


class Codes
{
    const OK = 0;

    const WRONG_METHOD        = 1;
    const NO_METHOD           = 2;
    const INVALID_ARGUMENTS   = 3;
    const INSUFFICIENT_RIGHTS = 4;

    const NO_USER           = 10;
    const WRONG_CREDENTIALS = 11;
    const READ_ONLY         = 12;

    const USER_IS_READ_ONLY = 20;
    const CREATE_TIMEOUT    = 21;
}