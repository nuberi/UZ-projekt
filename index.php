<?php
if ($_SERVER['DEPLOYMENT_MODE'] === "DEV") {

    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

require './router.php';
require './slugifier.php';
require './auth.php';
require './dishes.php';
require './dishTypes.php';
require './product.php';
require './productTypes.php';
require './buildingTypes.php';
require './adresses.php';
require './personal.php';
require './my-adress-data.php';
require './my-personal-data.php';
require './Schema.php';




$method = $_SERVER["REQUEST_METHOD"];
$parsed = parse_url($_SERVER['REQUEST_URI']);
$path = $parsed['path'];

// Útvonalak regisztrálása
$routes = [
    // [method, útvonal, handlerFunction],
    ['GET', '/logout', 'logoutHandler'],
    ['GET', '/login', 'loginPageHandler'],
    ['GET', '/employee', 'employeeHandler'],
    ['GET', '/', 'homeHandler'],
    ['GET', '/registration', 'registrationPageHandler'],
    ['GET', '/admin','adminPageHandler'],

    ['GET', '/admin/etel-szerkesztese/{keresoBaratNev}', 'dishEditHandler'],
    ['GET', '/admin/productType-szerkesztese/{productTypeId}', 'productTypeEditHandler'],
    ['GET', '/admin/buildingType-szerkesztese/{buildingTypeId}', 'buildingTypeEditHandler'],
    ['GET', '/admin/allDishList', 'allDishHandler'],
    ['GET', '/admin/etel-tipusok', 'adminDishTypeHandler'],
    ['GET', '/admin/product-types', 'adminProductTypeHandler'],
    ['GET', '/admin/building-types', 'adminBuildingTypeHandler'],
    ['GET', '/admin/uj-etel-letrehozasa', 'dishCreatePageHandler'],
    ['GET', '/admin/adresseslist', 'adressesListPageHandler'],
    ['GET', '/admin/personallist', 'personalListPageHandler'],
    ['GET', '/admin/new-personal-page', 'personalCreatePageHandler'],
    ['GET', '/admin/new-adress-page', 'adressCreatePageHandler'],
    ['GET', '/admin/myAdressdata', 'myDataCreatePageHandler'],
    ['GET', '/admin/myPesonaldata', 'modifyMyPersonalHandler'],

    ['POST', '/update-dish/ {dishId}', 'updateDishHandler'],
    ['POST', '/update-productType/ {productTypeId}', 'updateProductTypeHandler'],
    ['POST', '/update-buildingType/ {buildingTypeId}', 'updateBuildingTypeHandler'],


    ['POST', '/delete-dish/{dishId}', 'deleteDishHandler'],
    ['POST', '/delete-productType/{productTypeId}', 'deleteProductTypeHandler'],
    ['POST', '/delete-buildingType/{buildingTypeId}', 'deleteBuildingTypeHandler'],

    ['POST', '/create-dish', 'createDishHandler'],
    ['POST', '/create-dish-type', 'createDishTypeHandler'],
    ['POST', '/create-product-type', 'createProductTypeHandler'],
    ['POST', '/create-building-type', 'createBuildingTypeHandler'],
    ['POST', '/create-adress', 'createAdressHandler'],
    ['POST', '/create-personal', 'modifyMyPresonalPageHandler'],



    ['POST', '/login', 'loginhandler'],
    ['POST', '/register', 'registrationHandler'],
    ['POST', '/logout', 'logoutHandler'],
    ['POST', '/new-employee', 'newEmployeeHandler'],

];

// Útvonalválasztó inicializálása
$dispatch = registerRoutes($routes);
$matchedRoute = $dispatch($method, $path);
$handlerFunction = $matchedRoute['handler'];
$handlerFunction($matchedRoute['vars']);

// Handler függvények deklarálása


function newEmployeeHandler()
{
    $errors = validate(alkalmazottSema(), $_POST);
    /* echo "<pre>";
var_dump($errors); */

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

function homeHandler()
{
    $errors = json_decode(base64_decode($_GET['errors'] ?? ""), true);

    $errorMessages = getErrorMessages(alkalmazottSema(), $errors ?? []);
    if (!isLoggedIn()) {

        echo render('wrapper.phtml', [
            'content' => render('home.phtml', [
                'isSuccess' => $_GET['isSuccess'] ?? false,
                "errorMessages" => $errorMessages,
                'values' => json_decode(base64_decode($_GET['values'] ?? ''), true),
            ])
        ]);

        return;
    }

    $pdo = getConnection();

    $dishTypes = getAllDishTypes($pdo);
    foreach ($dishTypes as $index => $dishType) {
        $stmt = $pdo->prepare("SELECT * FROM dishes WHERE isActive =1 AND dishTypeId =?");
        $stmt->execute([$dishType['id']]);
        $dishes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $dishTypes[$index]['dishes'] = $dishes;
    }

    // echo"<pre>";
    // var_dump($dishTypes);
    if (isAdmin()) {

        echo render("admin-wrapper.phtml", [
            'content' => render('public-menu.phtml', [
                'dishTypesWithDishes' => $dishTypes
            ])
        ]);
    } else {
        echo render("wrapper.phtml", [
            'content' => render('public-menu.phtml', [
                'dishTypesWithDishes' => $dishTypes
            ])
        ]);
    }
}




function notFoundHandler()
{
    http_response_code(404);
    echo render('wrapper.phtml', [
        'content' => render('404.phtml')
    ]);
}

function render($path, $params = [])
{
    ob_start();
    require __DIR__ . '/views/' . $path;
    return ob_get_clean();
}

function getConnection()
{
    return new PDO(
        'mysql:host=' . $_SERVER['DB_HOST'] . ';dbname=' . $_SERVER['DB_NAME'],
        $_SERVER['DB_USER'],
        $_SERVER['DB_PASSWORD']
    );
}
