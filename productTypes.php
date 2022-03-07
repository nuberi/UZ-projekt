<?php
function createProductTypeHandler(){
    redirectToLoginPageNotLoggedIn();
    $pdo= getConnection();
    $stmt= $pdo->prepare(
        "INSERT INTO productTypes 
        (nev)
        VALUES
        (?);"
    );
    $stmt->execute([
        $_POST['name'],
        // slugify($_POST['name']),
        // $_POST['description'],
    ]);
    header('Location: /admin/product-tipusok');
}


function adminProductTypeHandler(){
    redirectToLoginPageNotLoggedIn();
    $pdo= getConnection();
    $productTypes=getAllProductTypes($pdo);
    echo render('admin-wrapper.phtml',[
        'content'=>render("product-type-list.phtml",[
            'productTypes'=>$productTypes,
        ])
        ]);
}
function getAllProductTypes($pdo){
   
    $stmt =$pdo->prepare("SELECT * FROM productTypes");
    $stmt ->execute();
    $productTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $productTypes;
}