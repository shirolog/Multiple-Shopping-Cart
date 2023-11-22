<?php 
require('./connect.php');
session_start();

if(isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];
}else{
    $user_id = '';
    header('Location:./login.php');
    exit();
}

if(isset($_GET['logout'])){
    unset($user_id);
    session_destroy();
    header('Location:./login.php');
    exit();
}

if(isset($_POST['add_to_cart'])){
    $product_image = $_POST['product_image'];
    $product_image = filter_var($product_image, FILTER_SANITIZE_STRING);
    $product_name = $_POST['product_name'];
    $product_name = filter_var($product_name, FILTER_SANITIZE_STRING);
    $product_price = $_POST['product_price'];
    $product_price = filter_var($product_price, FILTER_SANITIZE_STRING);
    $product_quantity = $_POST['product_quantity'];
    $product_quantity = filter_var($product_quantity, FILTER_SANITIZE_STRING);

    $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id= ? AND name= ?");
    $select_cart->execute(array($user_id, $product_name));
    if($select_cart->rowCount() > 0){
        $message[] = 'product already added to cart!';
    }else{
        $insert_cart = $conn->prepare("INSERT INTO `cart` (user_id, name, price, image, quantity) VALUES
        (?, ?, ?, ?, ?)");
        $insert_cart->execute(array($user_id, $product_name, $product_price, $product_image, $product_quantity));
        $message[] = 'product  added to cart!';
    }
    $_SESSION['message'] = $message;
    header('Location:./index.php');
    exit();
}

if(isset($_POST['update_cart'])){
    $cart_id = $_POST['cart_id'];
    $cart_id = filter_var($cart_id, FILTER_SANITIZE_STRING);
    $cart_qty = $_POST['cart_qty'];
    $cart_qty = filter_var($cart_qty, FILTER_SANITIZE_STRING);

    $update_cart = $conn->prepare("UPDATE `cart` SET quantity= ? WHERE id= ?");
    $update_cart->execute(array($cart_qty, $cart_id));
    $message[] = 'cart quantity updated successfully!';
    $_SESSION['message']= $message;
    header('Location:./index.php');
    exit();
}

if(isset($_GET['remove'])){
    $delete_id = $_GET['remove'];
    $delete_cart = $conn->prepare("DELETE FROM  `cart` WHERE id= ?");
    $delete_cart->execute(array($delete_id));
    $message[] = 'product deleted from cart!';
    $_SESSION['message']= $message;
    header('Location:./index.php');
    exit();
}else{
    $delete_id = '';
}

if(isset($_GET['delete_all'])){
    $delete_cart = $conn->prepare("DELETE FROM  `cart` WHERE user_id= ?");
    $delete_cart->execute(array($user_id));
    $message[] = 'product all deleted from cart!';
    $_SESSION['message']= $message;
    header('Location:./index.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>

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

<!-- container section -->
<div class="container">

    <div class="user-profile">
        <?php 
        $select_form = $conn->prepare("SELECT * FROM `user_form` WHERE id= ?");
        $select_form->execute(array($user_id));
        if($select_form->rowCount() > 0){
            $fetch_form = $select_form->fetch(PDO::FETCH_ASSOC);
        ?>
            <p>username : <span><?= $fetch_form['name']; ?></span></p>
            <p>email : <span><?= $fetch_form['email']; ?></span></p>
            <div class="flex">
                <a href="./login.php" class="btn">login</a>
                <a href="./register.php" class="option-btn">register</a>
                <a href="./index.php?logout=<?= $user_id; ?>" class="delete-btn"
                onclick="return confirm('are your sure you want to logout?');">logout</a>
            </div>
        <?php 
        }
        ?>
    </div>

    <div class="products">

        <h1 class="heading">latest products</h1>

        <div class="box-container">
            <?php 
                $select_products = $conn->prepare("SELECT * FROM `products`");
                $select_products->execute();
                if($select_products->rowCount() > 0){
                    while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){
            ?>
               <form action="" method="post" class="box">
                    <input type="hidden" name="product_image" value="<?= $fetch_products['image']; ?>">
                    <input type="hidden" name="product_name" value="<?= $fetch_products['name']; ?>">
                    <input type="hidden" name="product_price" value="<?= $fetch_products['price']; ?>">
                    <img src="./assets/images/<?= $fetch_products['image']; ?>" alt="">
                    <p class="name"><?= $fetch_products['name']; ?></p>
                    <p class="price">$<?= $fetch_products['price']; ?>/-</p>
                    <input type="number" name="product_quantity" min="1" value="1">
                    <input type="submit" name="add_to_cart" value="add to cart" class="btn">
               </form> 
            <?php 
            }
            }
            ?>
        </div>
    </div>

    <div class="shopping-cart">

        <h1 class="heading">shopping cart</h1>

        <table>
            <thead>
                <th>image</th>
                <th>name</th>
                <th>price</th>
                <th>quantity</th>
                <th>total price</th>
                <th>action</th>
            </thead>

            <tbody>
                    
                <?php 
                    $grand_total = 0;
                    $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id= ?");
                    $select_cart->execute(array($user_id));
                    if($select_cart->rowCount() > 0){
                        while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
                        $sub_total= $fetch_cart['price']  * $fetch_cart['quantity']; 
                ?>

                <tr>
                    <td><img src="./assets/images/<?= $fetch_cart['image']; ?>" height="100" alt=""></td>
                    <td><?= $fetch_cart['name']; ?></td>
                    <td>$<?= $fetch_cart['price'];?>/-</td>
                    <td>
                        <form action="" method="post">
                            <input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">
                            <input type="number" name="cart_qty" min="1" value="<?= $fetch_cart['quantity']; ?>">
                            <input type="submit" name="update_cart" class="option-btn" value="update">
                        </form>
                    </td>
                    <td>$<?= number_format($sub_total);?>/-</td>
                    <td><a href="./index.php?remove=<?= $fetch_cart['id']; ?>" class="delete-btn"
                    onclick="return confirm('remove item from cart?');">remove</a></td>
                </tr>

                <?php 
                 $grand_total += $sub_total;
                }
                }else{
                    echo '<td style="padding: 20px; text-transform:capitalize;" colspan=6>no item added</td>';
                }
                ?>
                <tr class="table-bottom">
                    <td colspan="4">grand total: </td>
                    <td>$<?= number_format($grand_total);?>/-</td>
                    <td><a href="./index.php?delete_all" class="delete-btn <?php echo ($grand_total > 1)?'':'disabled'; ?>" 
                    onclick="return confirm('delete all from cart?');">delete all</a></td>
                </tr>
            </tbody>
        </table>

        <div class="cart-btn">
            <a href="#" class="btn <?php echo ($grand_total > 1)?'':'disabled'; ?>">proceed to checkout</a>
        </div>
    </div>

</div>
    
</body>
</html>