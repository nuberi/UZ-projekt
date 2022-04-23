<?php
function logoutHandler()
{
    session_start();
    $params = session_get_cookie_params();
    setcookie(session_name(), '', 0, $params['path'], $params['domain'], $params['secure'], isset($params['httponly']));
    session_destroy();
    header('Location: /');
}
function loginHandler()
{
    $pdo = getConnection();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$_POST['email']]);
    $user = $stmt->fetch();
    if (!$user) {
        header('Location: /admin?info=invalidCredentials');
        return;
    }

    if (!password_verify($_POST['password'], $user['password'])) {
        header('Location: /admin?info=invalidCredentials');
        return;
    }

    session_start();
    $_SESSION['admin'] = $user['admin'];
    $_SESSION['userId'] = $user['id'];
    
    if ($user['admin']) {

        header('Location:/');
    } else {

        header('Location:/');
    }

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
function registrationHandler()
{
    $admin = "false";
    // me
    $pdo = getConnection();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$_POST['email']]);
    $user = $stmt->fetch();
    if ($user) {
        header('Location: /admin?info=invalidCredentials');
        return;
    }

    /* if (password_verify($_POST['password'], $user['password'])) {
        header('Location: /admin?info=invalidCredentials');
        return;
    } */

    $pdo = getConnection();
    $statment = $pdo->prepare(
        "INSERT INTO `users` (`email`, `password`, `createdAt`, admin) 
        VALUES (?, ?, ?, ?);"
    );
    $statment->execute([
        $_POST["email"],
        password_hash($_POST["password"], PASSWORD_DEFAULT),
        time(),
        $admin
    ]);
    $pdo = getConnection();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$_POST['email']]);
    $user = $stmt->fetch();
    $userId = $user['id'];
    if ($user) {
        $pdo = getConnection();
        $statment = $pdo->prepare(
            "INSERT INTO `personalsData` (`userId`,`entryRecorded`) 
        VALUES (?,?);"
        );
        $statment->execute([
            $userId,
            time(),

        ]);
        LoginHandler();
        // header('Location: /');
    } else {
        header('Location: /admin?info=invalidCredentials');
        return;
    }
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



