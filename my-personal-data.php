<?php
function getUserPersonalData($userId){
   
    // redirectToLoginPageNotLoggedIn();
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT *
    FROM personalsData
  
   
    where personalsData.userId=$userId
    ;");
    $stmt->execute();
    $personal = $stmt->fetch(PDO::FETCH_ASSOC);
 
   return $personal;
}

function createMyPersonalHandler(){
    // redirectToLoginPageNotLoggedIn();
   

    $pdo=getConnection();
    $stmt=$pdo->prepare(
        "INSERT INTO personalsData(lastNameId,firstNameId,postId,titleId)
        VALUES
        (?,?,?,?);"
    );
    $stmt->execute([
       
        $_POST['lastNameId'],
        $_POST['firstNameId'],
        $_POST['postId'],
        $_POST['titleId'],

        
    ]);
    header("Location:/admin/personallist");
}
function modifyMyPersonalHandler(){

    
/* echo "<pre>";
    var_dump( $userId);
    exit;  */
    isLoggedIn();
    redirectToLoginPageNotLoggedIn();
    $pdo=getConnection();
    $firstNames=getAllFirstNames($pdo);
    // $lastNames=getAllLastNames($pdo);
    $posts=getAllPosts($pdo);
    $titles=getAllTitles($pdo);
   $personal=getUserPersonalData($_SESSION['userId']);
  
  
    echo render('admin-wrapper.phtml',[
        'content'=> render('modify-my-personal-data.phtml',[
            'firstNames' =>$firstNames,
            
            'posts'=>$posts,
            'titles'=>$titles,
            'personal'=>$personal
        ])
        ]);

}

function updatePersonalDataHandler()
{
    isLoggedIn();
    redirectToLoginPageNotLoggedIn();
    $pdo = getConnection();
    $stmt = $pdo->prepare(
        "UPDATE personalsData SET
            titleId=?,
            lastName=?,
            firstNameId=?,
            dateOfBirth=?,
            postId=?,
            otherInfo=?
           
            WHERE userId=?"
    );
    $stmt->execute([
        $_POST['titleId'],
        $_POST['lastName'],
        $_POST['firstNameId'],
        $_POST['dateOfBirth'],
        $_POST['postId'],
        $_POST['otherInfo'],
        $_SESSION['userId']

    ]);
  
    header('Location: /admin/myPesonaldata');
}