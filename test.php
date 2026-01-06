<?php

$hash = '$2y$12$T55Vn7x232Z.ZumPUawl2u7lvsPgzguCrvSK40DoMFy...'; // cola o hash completo
var_dump(password_verify('admin123', $hash));