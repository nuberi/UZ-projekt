<?php
function newEmployeeHandler()
{
    $errors = validate(alkalmazottSema(), $_POST);
 
    if (isError($errors)) {
        $encodedErrors = base64_encode(json_encode($errors));
        header("Location: /employee?errors=" . $encodedErrors . "&values=" . base64_encode(json_encode($_POST)));
        return;
    }
    header("Location: /employee?isSuccess=1");
}

function employeeHandler()
{
    $errors = json_decode(base64_decode($_GET['errors'] ?? ""), true);
    $errorMessages = getErrorMessages(alkalmazottSema(), $errors ?? []);
    if (isLoggedIn()) {
        echo render('wrapper.phtml', [
            'content' => render('newEmployee.phtml', [
                'isSuccess' => $_GET['isSuccess'] ?? false,
                "errorMessages" => $errorMessages,
                'values' => json_decode(base64_decode($_GET['values'] ?? ''), true),
            ])
        ]);
        return;
    }
}