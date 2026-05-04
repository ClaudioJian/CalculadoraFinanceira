<?php 
// aqui coloca algumas configurações
// normalmente, pega o valor no enviroment path(nesse caso, dotenv pode ser ótima) ou nos files mais seguros

define('DB_NAME','calculator_financeiro');
define('HOST','localhost');
define('SERVER_USER','root');

/*
define('ADM_PASSWORD','');
define('USER_NAME_X','');
define('USER_PASSWORD_X','');
*/

//user
define('USER_NOT_LOGGED',-10);
define('USER_WRONG_CREDIT',-11);
define('USER_ALREDY_EXIST',-12);
define('USER_NOT_FIND',-15);

//data
define('DATA_INVALID_FORMAT',-21);
define('DATA_NOT_FOUND',-25);

//front end request
define('REQUEST_INVALID',-40);
define('REQUEST_INVALID_INPUT',-41);

//database
define('DB_ERR_CONNECTION',-50);
define('DB_ERR_GRANT',-51);
define('DB_ERR_INSERT',-52);
define('DB_ERR_UPDATE',-53);
define('DB_ERR_DELETE',-54);
define('DB_ERR_SELECT',-55);

define('DB_INSERT',52);
define('DB_DELETE',54);
define('DB_SELECT',55);

?>