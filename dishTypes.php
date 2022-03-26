<?php
function createDishTypeHandler(){
    redirectToLoginPageNotLoggedIn();
adminDasboardHandler();

    $pdo= getConnection();
    $stmt= $pdo->prepare(
        "INSERT INTO dishtypes 
        (name,slug,description)
        VALUES
        (?,?,?);"
    );
    $stmt->execute([
        $_POST['name'],
        slugify($_POST['name']),
        $_POST['description'],
    ]);
    header('Location: /admin/etel-tipusok');
}


function adminDishTypeHandler(){
    redirectToLoginPageNotLoggedIn();
    adminDasboardHandler();
    $pdo= getConnection();
    $dishTypes=getAllDishTypes($pdo);
    echo render('admin-wrapper.phtml',[
        'content'=>render("dish-type-list.phtml",[
            'dishTypes'=>$dishTypes,
        ])
        ]);
}
function getAllDishTypes($pdo){
 
    $stmt =$pdo->prepare("SELECT * FROM dishtypes");
    $stmt ->execute();
    $dishTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $dishTypes;
}