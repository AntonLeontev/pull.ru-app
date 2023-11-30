<?php

if (! function_exists('objectize')) {
    function objectize(array $input): object
    {
        return json_decode(json_encode($input));
    }
}
