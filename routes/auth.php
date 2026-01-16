<?php

$router->get('/', 'AuthController@showLogin');
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login', ['csrf']);
$router->get('/logout', 'AuthController@logout');

$router->get('/recuperar', 'AuthController@recuperar');
$router->post('/recuperar', 'AuthController@enviarRecuperacao');
$router->get('/redefinir', 'AuthController@formRedefinir');
$router->post('/redefinir', 'AuthController@redefinirSenha');