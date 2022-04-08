<?php
function myDataListPageHandler(){
    redirectToLoginPageNotLoggedIn();
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT  buildingTypes.buildingType, countryes.country, cityes.postCode,cityes.cityName,streets.streetName,adresses.houseNumber,adresses.building,adresses.floor,adresses.door,adresses.adressDesc 
    FROM adresses 
    LEFT JOIN buildingTypes
    on adresses.buildingTypeId=buildingTypes.buildingTypeId
    LEFT JOIN  countryes
    on adresses.countryId = countryes.countryId
    LEFT JOIN  cityes
    on adresses.cityId = cityes.cityId
    LEFT JOIN  streets
    on adresses.streetId = streets.streetId
    WHERE adresses.adressId = ;");
    $stmt->execute();
    $adresses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    

    echo render('admin-wrapper.phtml', [
        'content' => render('adress-list.phtml',[
            'adresses' =>$adresses
        ])
        ]);
}

function createMyAdressHandler(){
    redirectToLoginPageNotLoggedIn();
  /*    echo "<pre>";
    var_dump($_POST);
    exit; */

    $pdo=getConnection();
    $stmt=$pdo->prepare(
        "INSERT INTO adresses(buildingTypeId,countryId,cityId,streetId,streetTypeId,houseNumber,building,floor,door,adressDesc)
        VALUES
        (?,?,?,?,?,?,?,?,?,?);"
    );
    $stmt->execute([
       
        $_POST['buildingTypeId'],
        $_POST['countryId'],
        $_POST['cityId'],
        $_POST['streetId'],
        $_POST['streetTypeId'],
        $_POST['houseNumber'],
        $_POST['building'],
        $_POST['floor'],
        $_POST['door'],
        $_POST['adressDesc']


        
    ]);
    header("Location:/admin/adresseslist");
}
function myDataCreatePageHandler(){
    redirectToLoginPageNotLoggedIn();
    $pdo=getConnection();
    $buildingTypes=getAllBuildingTypes($pdo);
    $countryes=getAllCountryes($pdo);
    $cityes=getAllCityes($pdo);
    $streets=getAllstreets($pdo);
    $streetTypes=getAllStreetTypes($pdo);
    $myPersonalDataId=getMyPersonalDataId($pdo);


    echo render('admin-wrapper.phtml',[
        'content'=> render('create-my-adress.phtml',[
            'buildingTypes'=> $buildingTypes,
            'countryes'=>$countryes,
            'cityes'=>$cityes,
            'streets'=>$streets,
            'streetTypes'=>$streetTypes,
           'myPersonalDataId'=> $myPersonalDataId
        ])
        ]);

}
function getMyCountryes($pdo){
 
    $stmt =$pdo->prepare("SELECT * FROM countryes ORDER BY country ASC");
    $stmt ->execute();
    $countryes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $countryes;
}
function getMyCityes($pdo){
    $stmt=$pdo->prepare("SELECT * FROM cityes ORDER BY cityName ASC");
    $stmt->execute();
    $cityes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $cityes;
}
function getMyStreets($pdo){
    $stmt=$pdo->prepare("SELECT * FROM streets ORDER BY streetName ASC");
    $stmt->execute();
    $streets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $streets;
}
function getMyStreetTypes($pdo){
    $stmt=$pdo->prepare("SELECT * FROM streetTypes ORDER BY streetType ASC");
    $stmt->execute();
    $streetTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $streetTypes;
}
function getMyPersonalDataId($pdo){
    $stmt=$pdo->prepare("SELECT personalDataId FROM users WHERE id =$_SESSION[userId]");
    $stmt->execute();
    $myPersonalDataId = $stmt->fetch(PDO::FETCH_ASSOC);
    return $myPersonalDataId;
}
