<?php

global $config;

ob_start();

$error = ob_get_clean();
if (empty($error) === false) {
    throw new Exception($error);
}
