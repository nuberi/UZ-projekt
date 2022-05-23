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
    $errors = validate(loginSema(), $_POST);
    /* echo "<pre>";
var_dump($errors); */

    if (isError($errors)) {
        $encodedErrors = base64_encode(json_encode($errors));
        header("Location: /login?errors=" . $encodedErrors . "&values=" . base64_encode(json_encode($_POST)));
        return;
    }

   // header("Location: /employee?isSuccess=1");
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
    $admin = "true";
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

function registrationPageHandlereHandler()
{
    $errors = json_decode(base64_decode($_GET['errors'] ?? ""), true);

    $errorMessages = getErrorMessages(registrationSema(), $errors ?? []);
    if (!isLoggedIn()) {
        echo render('wrapper.phtml', [
            'content' => render('subscriptionForm.phtml', [
                'isSuccess' => $_GET['isSuccess'] ?? false,
                "errorMessages" => $errorMessages,
                'values' => json_decode(base64_decode($_GET['values'] ?? ''), true),
            ])
        ]);

        return;
    }
}
function loginPageHandler()
{
    $errors = json_decode(base64_decode($_GET['errors'] ?? ""), true);

    $errorMessages = getErrorMessages(loginSema(), $errors ?? []);
    if (!isLoggedIn()) {
        echo render('wrapper.phtml', [
            'content' => render('login.phtml', [
                'isSuccess' => $_GET['isSuccess'] ?? false,
                "errorMessages" => $errorMessages,
                'values' => json_decode(base64_decode($_GET['values'] ?? ''), true),
            ])
        ]);

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



