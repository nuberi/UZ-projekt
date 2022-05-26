<?php
function registrationHandler()
{
    $errors = validate(registrationSema(), $_POST);
    /* echo "<pre>";
var_dump($errors); */

    if (isError($errors)) {
        $encodedErrors = base64_encode(json_encode($errors));
        header("Location: /registration?errors=" . $encodedErrors . "&values=" . base64_encode(json_encode($_POST)));
        return;
    }

    $admin = "false";
    
    $pdo = getConnection();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$_POST['email']]);
    $user = $stmt->fetch();
    if ($user) {
        header('Location: /registration?info=Hiba! Próbálja meg mégegyszer!');
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
        header('Location: /admin?info=Hiba! Próbálja meg mégegyszer!');
        return;
    }
}

function registrationPageHandler()
{
    $errors = json_decode(base64_decode($_GET['errors'] ?? ""), true);

    $errorMessages = getErrorMessages(registrationSema(), $errors ?? []);
    if (!isLoggedIn()) {
        echo render('wrapper.phtml', [
            'content' => render('subsriptionForm.phtml', [
                'info'=> $_GET['info']?? "",
                'isSuccess' => $_GET['isSuccess'] ?? false,
                "errorMessages" => $errorMessages,
                'values' => json_decode(base64_decode($_GET['values'] ?? ''), true),
            ])
        ]);

        return;
    }
}