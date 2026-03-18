<?php
echo "<style>body, input, button{background:#000;color:#fff;font-family:monospace;padding:10px 10px;}</style>";
$test = true;
if(!$test){
    header("Location: ../index.php");
    exit();
}
session_start();
if (!isset($_SESSION['test_access'])){
    $correct_pass = "test123";

    if(isset($_POST['pass'])):
        
        if($_POST['pass'] === $correct_pass){
            $_SESSION['test_access'] = true;
            header("Location: database_test.php");
            exit;
        }else{
            echo "Wrong password";
        }

    else:
        ?>
        <form method="POST">
    <h2>Test System Login</h2>
    
    <input type="password" name="pass" placeholder="Enter Test Password">
    
    <button type="submit">Enter</button>

</form>
    <?php endif;
}else{
    header("Location: database_test.php");
}
?>