<?php
function adressesListPageHandler(){
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
    on adresses.streetId = streets.streeId
    ;");
    $stmt->execute();
    $adresses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    

    echo render('admin-wrapper.phtml', [
        'content' => render('adress-list.phtml',[
            'adresses' =>$adresses
        ])
        ]);
}







