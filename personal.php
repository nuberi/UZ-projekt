<?php
function personalListPageHandler(){
   
    redirectToLoginPageNotLoggedIn();
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT  lastNames.lastName,firstnames.firstName,posts.post,titles.title
    FROM personalsData
    LEFT JOIN lastNames
    on personalsData.lastNameId=lastNames.lastNameId
    LEFT JOIN  firstnames
    on personalsData.firstNameId = firstnames.firstNameId
    LEFT JOIN  posts
    on personalsData.postId = posts.postId
    LEFT JOIN  titles
    on personalsData.titleId = titles.titleId
    ;");
    $stmt->execute();
    $personals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    

    echo render('admin-wrapper.phtml', [
        'content' => render('personal-list.phtml',[
            'personals' =>$personals
        ])
        ]);
}
function createPersonalHandler(){
    redirectToLoginPageNotLoggedIn();
  /*    echo "<pre>";
    var_dump($_POST);
    exit; */

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
function personalCreatePageHandler(){
    redirectToLoginPageNotLoggedIn();
    $pdo=getConnection();
    $firstNames=getAllFirstNames($pdo);
    $lastNames=getAllLastNames($pdo);
    $posts=getAllPosts($pdo);
    $titles=getAllTitles($pdo);

    echo render('admin-wrapper.phtml',[
        'content'=> render('create-personals.phtml',[
            'firstNames' =>$firstNames,
            'lastNames'=>$lastNames,
            'posts'=>$posts,
            'titles'=>$titles,
        ])
        ]);

}
function getAllFirstNames($pdo){
 
    $stmt =$pdo->prepare("SELECT * FROM firstnames");
    $stmt ->execute();
    $firstNames = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $firstNames;
}
function getAllLastnames($pdo){
    $stmt=$pdo->prepare("SELECT * FROM lastNames");
    $stmt->execute();
    $lastNames = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $lastNames;
}
function getAllPosts($pdo){
    $stmt=$pdo->prepare("SELECT * FROM posts");
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $posts;
}
function getAllTitles($pdo){
    $stmt=$pdo->prepare("SELECT * FROM titles");
    $stmt->execute();
    $titles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $titles;
}