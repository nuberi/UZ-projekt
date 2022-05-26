<?php
function logoutHandler()
{
    session_start();
    $params = session_get_cookie_params();
    setcookie(session_name(), '', 0, $params['path'], $params['domain'], $params['secure'], isset($params['httponly']));
    session_destroy();
    header('Location: /');
}




//A felhaszáló be van-e jelentkezve?
function isLoggedIn()
{
    if (!isset($_COOKIE[session_name()])) {
        return false;
    }
    if (!isset($_SESSION)) {
        session_start();
       
    }
    if (!isset($_SESSION['userId'])) {
        return false;
    }

    return true;
}
function isAdmin()
{
    if (!isset($_SESSION['admin'])) {
        return false;
    }
    if ($_SESSION['admin'] === "false") {
        return false;
    }
    if ($_SESSION['admin'] === "true") {
        return true;
    }
    return false;
}
function redirectToLoginPageNotLoggedIn()
{
    if (isLoggedIn()) {
        return;
    }
    notFoundHandler();
     header('Location: /login');
    
}


function adminDasboardHandler()
{
    if (!isLoggedIn()) {
        echo render('wrapper.phtml', [
            'content' => render('login.phtml')
        ]);
        return;
    }
    if (!isAdmin()) {
        header('Location: /');
    }
}



