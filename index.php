<?php
if($_SERVER['DEPLOYMENT_MODE']==="DEV"){

    ini_set('display_errors','1');
    ini_set('display_startup_errors','1');
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
require './my-personal-data.php';



$method = $_SERVER["REQUEST_METHOD"];
$parsed = parse_url($_SERVER['REQUEST_URI']);
$path = $parsed['path'];

// Útvonalak regisztrálása
$routes = [
    // [method, útvonal, handlerFunction],
    ['GET','/logout','logoutHandler'],
    ['GET', '/', 'homeHandler'],
    ['GET', '/admin/myPesonaldata', 'CreateMyPresonalPageHandler'],
    ['GET', '/admin/etel-szerkesztese/{keresoBaratNev}', 'dishEditHandler'],
    ['GET', '/admin/productType-szerkesztese/{productTypeId}', 'productTypeEditHandler'],
    ['GET', '/admin/buildingType-szerkesztese/{buildingTypeId}', 'buildingTypeEditHandler'],
    ['GET','/admin/allDishList','allDishHandler'],
    ['GET','/admin/etel-tipusok','adminDishTypeHandler'],
    ['GET','/admin/product-types','adminProductTypeHandler'],
    ['GET','/admin/building-types','adminBuildingTypeHandler'],
    ['GET', '/admin/uj-etel-letrehozasa','dishCreatePageHandler'],
    ['GET', '/admin/adresseslist','adressesListPageHandler'],
    ['GET', '/admin/personallist','personalListPageHandler'],
    ['GET', '/admin/new-personal-page','personalCreatePageHandler'],
    ['GET','/admin/new-adress-page','adressCreatePageHandler'],
    ['GET','/admin/myAdressdata','myDataCreatePageHandler'],

   
    ['POST', '/update-dish/ {dishId}','updateDishHandler'],
    ['POST', '/update-productType/ {productTypeId}','updateProductTypeHandler'],
    ['POST', '/update-buildingType/ {buildingTypeId}','updateBuildingTypeHandler'],
    
   
    ['POST','/delete-dish/{dishId}','deleteDishHandler'],
    ['POST','/delete-productType/{productTypeId}','deleteProductTypeHandler'],
    ['POST','/delete-buildingType/{buildingTypeId}','deleteBuildingTypeHandler'],

    ['POST','/create-dish','createDishHandler'],
    ['POST','/create-dish-type','createDishTypeHandler'],
    ['POST','/create-product-type','createProductTypeHandler'],
    ['POST','/create-building-type','createBuildingTypeHandler'],
    ['POST','/create-adress','createAdressHandler'],
    ['POST','/create-personal','createPersonalHandler'],
    
    
   
    ['POST', '/login', 'loginhandler'],
    ['POST', '/register','registrationHandler'],
    ['POST','/logout','logoutHandler'],
    ['POST','/new-employee','updatepersonalDataHandler'],

];

// Útvonalválasztó inicializálása
$dispatch = registerRoutes($routes);
$matchedRoute = $dispatch($method, $path);
$handlerFunction = $matchedRoute['handler'];
$handlerFunction($matchedRoute['vars']);

// Handler függvények deklarálása
function registrationHandler(){
    $admin="false";
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
    $userId=$user['id'];
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
    }
    else{
        header('Location: /admin?info=invalidCredentials');
        return;
    }
   
  
   
}


function adminDasboardHandler(){
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
  function allDishHandler(){
    adminDasboardHandler();
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM dishes ORDER BY id desc ");
    $stmt->execute();
    $dishes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo render('admin-wrapper.phtml', [
        'content' => render('dish-list.phtml', [
            'dishes' =>$dishes
        ])
        ]);
}




function homeHandler()
{
    if (!isLoggedIn()) {
        echo render('wrapper.phtml', [
            'content'=>render('subsriptionForm.phtml', [
    
            ])
            ]);
    
        return;
    }
  
    $pdo = getConnection();
   
    $dishTypes=getAllDishTypes($pdo);
    foreach ($dishTypes as $index =>$dishType) {
        $stmt = $pdo->prepare("SELECT * FROM dishes WHERE isActive =1 AND dishTypeId =?");
        $stmt ->execute([$dishType['id']]);
        $dishes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $dishTypes[$index]['dishes'] = $dishes;
    }

    // echo"<pre>";
    // var_dump($dishTypes);
    if (isAdmin()) {

    echo render("admin-wrapper.phtml", [
    'content'=> render('public-menu.phtml', [
        'dishTypesWithDishes'=>$dishTypes
    ])
]);

}
else{
    echo render("wrapper.phtml", [
        'content'=> render('public-menu.phtml', [
            'dishTypesWithDishes'=>$dishTypes
        ])
    ]);
}
}




function notFoundHandler()
{
    http_response_code(404);
    echo render('wrapper.phtml',[
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
function updatepersonalDataHandler(){
    
        redirectToLoginPageNotLoggedIn();
   
   
      
        $pdo = getConnection();
        $stmt = $pdo->prepare(
            "UPDATE personalsData SET
            titleId=?,
            lastName=?,
            firstNameId=?,
            dateOfBirth=?,
            postId=?,
            isVerifed=?,
            otherInfo=?
           
            WHERE userId=?"
        );
        $stmt->execute([
       $_POST['titleId'],
        $_POST['lastName'],
       $_POST['firstNameId'],
        $_POST['dateOfBirth'],
       $_POST['postId'],
        $_POST['isVerifed'],
        $_POST['otherInfo'],
      $_SESSION['userId']
      
        ]);
     /*    echo "<pre>";
        var_dump($_POST,$_SESSION['userId']);

        exit; */
        header('Location: /admin/myPesonaldata');
    }


// home handler helyett
function employeeFormHandler(){
    CreateMyPresonalPageHandler();
}

