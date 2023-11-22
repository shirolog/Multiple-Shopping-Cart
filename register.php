<?php 
require('./connect.php');
session_start();

if(isset($_POST['submit'])){
    $name = $_POST['name'];
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $email = $_POST['email'];
    $email = filter_var($email, FILTER_SANITIZE_STRING);
    $pass = md5($_POST['pass']);
    $pass = filter_var($pass, FILTER_SANITIZE_STRING);
    $cpass = md5($_POST['cpass']);
    $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

    $select_form = $conn->prepare("SELECT * FROM `user_form` WHERE email= ?
    AND password= ?");
    $select_form->execute(array($email, $cpass));

    if($select_form->rowCount() > 0){
        $message[] = 'user already exist!';
        $_SESSION['message'] = $message;
        header('Location:./register.php');
        exit();
    }else{
        if($pass != $cpass){
            $message[] = 'password not matched!';
            $_SESSION['message'] = $message;
            header('Location:./register.php');
            exit();
        }else{
            $insert_form = $conn->prepare("INSERT INTO `user_form` (name, email, password) VALUES
            (?, ?, ?)");
            $insert_form->execute(array($name, $email, $cpass));
            $message[] = 'registered successfully!';
            $_SESSION['message'] = $message;
            header('Location:./login.php');
            exit();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>

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
            <h3>register now</h3>
            <input type="text" name="name" class="box" required 
            placeholder="enter your name">
            <input type="email" name="email" class="box" required 
            placeholder="enter your email">
            <input type="password" name="pass" class="box" required 
            placeholder="enter your password">
            <input type="password" name="cpass" class="box" required 
            placeholder="confirm your password">
            <input type="submit" name="submit" class="btn" value="register now">
            <p>already have an account? <a href="./login.php">login now</a></p>
        </form>
    </div>
    
</body>
</html>