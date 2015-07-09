<?php

define('DW_IDENTICON_AUTH_SALT', 'cd737699d017c76f3fae9f919821537d1856ef30b27e15ca2a3378fd69b2ccc491776327fac3a21a47f65be23eb87cfa9bccb4b5b2f0311ed2df3b4f4b112e8d');
function DWIdenticonAuthHash($data, $check)
{
    $hash = hash('sha256', DW_IDENTICON_AUTH_SALT . $check . $data);
    return $hash;
}