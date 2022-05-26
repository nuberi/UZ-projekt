<?php
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
        header('Location: /login?info=Hiba! Próbálja meg mégegyszer!');
        return;
    }

    if (!password_verify($_POST['password'], $user['password'])) {
        header('Location: /login?info=Hiba! Próbálja meg mégegyszer!');
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
function loginPageHandler()
{
    $errors = json_decode(base64_decode($_GET['errors'] ?? ""), true);

    $errorMessages = getErrorMessages(loginSema(), $errors ?? []);
    if (!isLoggedIn()) {
        echo render('wrapper.phtml', [
            'content' => render('login.phtml', [
                'info'=> $_GET['info']?? null,
                'isSuccess' => $_GET['isSuccess'] ?? false,
                "errorMessages" => $errorMessages,
                'values' => json_decode(base64_decode($_GET['values'] ?? ''), true),
            ])
        ]);

        return;
    }
}