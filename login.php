<?php 
require('./connect.php');
session_start();

if(isset($_POST['submit'])){
    $email = $_POST['email'];
    $email = filter_var($email, FILTER_SANITIZE_STRING);
    $pass = md5($_POST['pass']);
    $pass = filter_var($pass, FILTER_SANITIZE_STRING);
   

    $select_form = $conn->prepare("SELECT * FROM `user_form` WHERE email= ?
    AND password= ?");
    $select_form->execute(array($email, $pass));
    $fetch_form = $select_form->fetch(PDO::FETCH_ASSOC);

    if($select_form->rowCount() > 0){
        $_SESSION['user_id'] =  $fetch_form['id'];
        header('Location:./index.php');
        exit();
    }else{
        $message[] = 'incorrect password or email!';
        $_SESSION['message'] = $message;
        header('Location:./login.php');
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <!-- custom css -->
    <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>

<?php 
    if(isset($_SESSION['message'])){
        foreach($_SESSION['message'] as $message){
            echo '<p class="message" onclick="this.remove();">'.$message.'</p>';
        }
        unset($_SESSION['message']);
    }
?>

    <!-- form-container section -->
    <div class="form-container">
        <form action="" method="post">
            <h3>login now</h3>
            <input type="email" name="email" class="box" required 
            placeholder="enter your email">
            <input type="password" name="pass" class="box" required 
            placeholder="enter your password">
            <input type="submit" name="submit" class="btn" value="login now">
            <p>don't have an account? <a href="./register.php">register now</a></p>
        </form>
    </div>
    
</body>
</html>