<?php
function deleteProductTypeHandler($urlParameterek){
    //echo "<pre>";
 // var_dump($urlParameterek);
 // exit;
 redirectToLoginPageNotLoggedIn();
 $pdo=getConnection();
 $stmt= $pdo->prepare("DELETE FROM producttypes WHERE productTypeId =?");
 $stmt->execute([
     $urlParameterek['productTypeId']
 ]);
 header('Location: /admin/product-tipusok');
}
function createProductTypeHandler(){
    redirectToLoginPageNotLoggedIn();
    $pdo= getConnection();
    $stmt= $pdo->prepare(
        "INSERT INTO producttypes 
        (productTypeName, productTypeDesc)
        VALUES
        (?,?);"
    );
    $stmt->execute([
        $_POST['name'],
        // slugify($_POST['name']),
         $_POST['productTypeDesc'],
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
   
    $stmt =$pdo->prepare("SELECT * FROM producttypes");
    $stmt ->execute();
    $productTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $productTypes;
}
function productTypeEditHandler($vars)
{
    redirectToLoginPageNotLoggedIn();
    // echo "<pre>";
    // var_dump($vars);
    // echo 'Étel szerkesztése: ' . $vars['keresoBaratNev'];
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM producttypes WHERE productTypeId=?");
    $stmt->execute([$vars['productTypeId']]);
    $productype = $stmt->fetch(PDO::FETCH_ASSOC);

  
    echo render('admin-wrapper.phtml', [
        'content' =>render('edit-productTypes.phtml',[
            'productType' => $productype,
          
        ])
        ]);

}
function updateProductTypeHandler($urlParams)
{
    redirectToLoginPageNotLoggedIn();
  
    $pdo = getConnection();
    $stmt = $pdo->prepare(
        "UPDATE producttypes SET
        productTypeName=?,
      
        productTypeDesc=?
       
        WHERE productTypeId= ?"
    );
    $stmt->execute([
    $_POST['name'],
  
    $_POST['description'],
   
    (int)$urlParams['productTypeId']
    ]);
    header('Location: /admin/product-tipusok');
}