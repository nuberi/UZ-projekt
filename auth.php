<?php
function logoutHandler(){
    session_start();
    $params=session_get_cookie_params();
    setcookie(session_name(),'',0,$params['path'],$params['domain'],$params['secure'],isset($params['httponly']));
    session_destroy();
    header('Location: /');
}
function loginHandler(){
    $pdo= getConnection();
    $stmt= $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$_POST['email']]);
    $user = $stmt->fetch();
    if(!$user){
        header('Location: /admin?info=invalidCredentials');
        return;
    }
   
    if (!password_verify($_POST['password'],$user['password'])){
        header('Location: /admin?info=invalidCredentials');
        return;
    }
    if ($user['admin']===1) {
        session_start();
        $_SESSION['admin']=$user['admin'];
        $_SESSION['userId']=$user['id'];
        header('Location:/admin');
    }
    else {
        session_start();
        $_SESSION['admin']=$user['admin'];
        $_SESSION['userId']=$user['id'];
        header('Location:/');
    }
}
//A felhaszáló be van-e jelentkezve?
function isLoggedIn(){
    if(!isset($_COOKIE[session_name()])){
        return false;
    }
    if(!isset($_SESSION)){
        session_start();
    }
    if(!isset($_SESSION['userId'])){
        return false;
    }
    
    return true;
}
function isAdmin(){
    if (!isset($_SESSION['admin'])) {
        return false;
    }
    if ($_SESSION['admin']===0) {
        return false;
    }
    if ($_SESSION['admin']===1) {
        return true;
    }
}
function redirectToLoginPageNotLoggedIn(){
    if(isLoggedIn()){
        return;
    }
    header('Location: /admin');
    exit;
}