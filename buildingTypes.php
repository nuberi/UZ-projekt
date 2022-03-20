<?php
function createBuildingTypeHandler(){
    redirectToLoginPageNotLoggedIn();
    $pdo= getConnection();
    $stmt= $pdo->prepare(
        "INSERT INTO ingatlantipusok
        (ingatlanTipus)
        VALUES
        (?);"
    );
    $stmt->execute([
        $_POST['buildingType'],
        // slugify($_POST['name']),
        // $_POST['description'],
    ]);
    header('Location: /admin/building-tipusok');
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
   
    $stmt =$pdo->prepare("SELECT * FROM ingatlantipusok");
    $stmt ->execute();
    $buildingTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $buildingTypes;
}