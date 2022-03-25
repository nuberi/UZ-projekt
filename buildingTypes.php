<?php
function deleteBuildingTypeHandler($urlParameterek){
    //echo "<pre>";
 // var_dump($urlParameterek);
 // exit;
 redirectToLoginPageNotLoggedIn();
 $pdo=getConnection();
 $stmt= $pdo->prepare("DELETE FROM buildingTypes WHERE buildingTypeId =?");
 $stmt->execute([
     $urlParameterek['buildingTypeId']
 ]);
 header('Location: /admin/building-types');
}

function buildingTypeEditHandler($vars)
{
    redirectToLoginPageNotLoggedIn();
    // echo "<pre>";
    // var_dump($vars);
    // echo 'Étel szerkesztése: ' . $vars['keresoBaratNev'];
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM buildingTypes WHERE buildingTypeId=?");
    $stmt->execute([$vars['buildingTypeId']]);
    $buildingType = $stmt->fetch(PDO::FETCH_ASSOC);

  
    echo render('admin-wrapper.phtml', [
        'content' =>render('edit-buildingType.phtml',[
            'buildingType' => $buildingType,
          
        ])
        ]);

}


function createBuildingTypeHandler(){
    redirectToLoginPageNotLoggedIn();
    $pdo= getConnection();
    $stmt= $pdo->prepare(
        "INSERT INTO buildingTypes
        (buildingType)
        VALUES
        (?);"
    );
    $stmt->execute([
        $_POST['buildingType'],
        // slugify($_POST['name']),
        // $_POST['description'],
    ]);
    header('Location: /admin/building-types');
}


function adminBuildingTypeHandler(){
    redirectToLoginPageNotLoggedIn();
    $pdo= getConnection();
    $buildingTypes=getAllBuildingTypes($pdo);
    echo render('admin-wrapper.phtml',[
        'content'=>render("building-type-list.phtml",[
            'buildingTypes'=>$buildingTypes,
        ])
        ]);
}
function getAllBuildingTypes($pdo){
   
    $stmt =$pdo->prepare("SELECT * FROM buildingTypes");
    $stmt ->execute();
    $buildingTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $buildingTypes;
}
function updateBuildingTypeHandler($urlParams)
{
    redirectToLoginPageNotLoggedIn();
  
    $pdo = getConnection();
    $stmt = $pdo->prepare(
        "UPDATE buildingTypes SET
        buildingType=?
      
        -- productTypeDesc=?
       
        WHERE buildingTypeId= ?"
    );
    $stmt->execute([
    $_POST['name'],
  
    // $_POST['description'],
   
    (int)$urlParams['buildingTypeId']
    ]);
    header('Location: /admin/building-types');
}