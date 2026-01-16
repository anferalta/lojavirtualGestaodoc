<?php

$router->get('/403', 'ErrorController@forbidden');
$router->get('/404', 'ErrorController@notFound');