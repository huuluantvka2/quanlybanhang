<?php
ob_start();
session_start();
ini_set('error_reporting', E_ALL);

//http://localhost/eCommerceSite-PHP/vnpay_php/vnpay_return.php?vnp_Amount=5198100&vnp_BankCode=NCB&vnp_BankTranNo=VNP13861556&vnp_CardType=ATM&vnp_OrderInfo=thanh+toan+vnpay&vnp_PayDate=20221023211454&vnp_ResponseCode=00&vnp_TmnCode=LOFV3JKL&vnp_TransactionNo=13861556&vnp_TransactionStatus=00&vnp_TxnRef=583714862&vnp_SecureHash=d39e4c1a6ccf7c4715cf8379d162d16371bba1aba36c7f5432b092bd7cab0e09ec3e15a1f028e57665f9565a169e8f427cb4362cc9e1ef57736c0aa00f4044a7

// Setting up the time zone
date_default_timezone_set('America/Los_Angeles');

// Host Name
$dbhost = 'localhost';

// Database Name
$dbname = 'ecommerceweb';

// Database Username
$dbuser = 'root';

// Database Password
$dbpass = '';

// Defining base url
define("BASE_URL", "");

// Getting Admin url
define("ADMIN_URL", BASE_URL . "admin" . "/");

try {
	$pdo = new PDO("mysql:host={$dbhost};dbname={$dbname}", $dbuser, $dbpass);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch( PDOException $exception ) {
	echo "Connection error :" . $exception->getMessage();
}


$item_number = time();
$item_name = 'Product Item(s)';
$vnp_Amount = ($_GET['vnp_Amount']/100);

$payment_date = date('Y-m-d H:i:s');
$statement = $pdo->prepare("INSERT INTO tbl_payment (
    customer_id,
    customer_name,
    customer_email,
    payment_date,
    txnid, 
    paid_amount,
    card_number,
    card_cvv,
    card_month,
    card_year,
    bank_transaction_info,
    payment_method,
    payment_status,
    shipping_status,
    payment_id
    ) 
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
$sql = $statement->execute(array(
    $_SESSION['customer']['cust_id'],
    $_SESSION['customer']['cust_name'],
    $_SESSION['customer']['cust_email'],
    $payment_date,
    '',
    $vnp_Amount,
    '',
    '',
    '',
    '',
    '',
    'VNPAY',
    '',
    '',
    $item_number
));

$i=0;
foreach($_SESSION['cart_p_id'] as $key => $value) 
{
$i++;
$arr_cart_p_id[$i] = $value;
}

$i=0;
foreach($_SESSION['cart_p_name'] as $key => $value) 
{
$i++;
$arr_cart_p_name[$i] = $value;
}

$i=0;
foreach($_SESSION['cart_size_name'] as $key => $value) 
{
$i++;
$arr_cart_size_name[$i] = $value;
}

$i=0;
foreach($_SESSION['cart_color_name'] as $key => $value) 
{
$i++;
$arr_cart_color_name[$i] = $value;
}

$i=0;
foreach($_SESSION['cart_p_qty'] as $key => $value) 
{
$i++;
$arr_cart_p_qty[$i] = $value;
}

$i=0;
foreach($_SESSION['cart_p_current_price'] as $key => $value) 
{
$i++;
$arr_cart_p_current_price[$i] = $value;
}


$i=0;
$statement = $pdo->prepare("SELECT * FROM tbl_product");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
foreach ($result as $row) {
$i++;
$arr_p_id[$i] = $row['p_id'];
$arr_p_qty[$i] = $row['p_qty'];
}


for($i=1;$i<=count($arr_cart_p_name);$i++) {
$statement = $pdo->prepare("INSERT INTO tbl_order (
    product_id,
    product_name,
    size, 
    color,
    quantity, 
    unit_price, 
    payment_id
    ) 
    VALUES (?,?,?,?,?,?,?)");
$sql = $statement->execute(array(
    $arr_cart_p_id[$i],
    $arr_cart_p_name[$i],
    $arr_cart_size_name[$i],
    $arr_cart_color_name[$i],
    $arr_cart_p_qty[$i],
    $arr_cart_p_current_price[$i],
    $item_number
));

// Update the stock
for($j=1;$j<=count($arr_p_id);$j++)
{
if($arr_p_id[$j] == $arr_cart_p_id[$i]) 
{
$current_qty = $arr_p_qty[$j];
break;
}
}
$final_quantity = $current_qty - $arr_cart_p_qty[$i];
$statement = $pdo->prepare("UPDATE tbl_product SET p_qty=? WHERE p_id=?");
$statement->execute(array($final_quantity,$arr_cart_p_id[$i]));

}




unset($_SESSION['cart_p_id']);
unset($_SESSION['cart_size_id']);
unset($_SESSION['cart_size_name']);
unset($_SESSION['cart_color_id']);
unset($_SESSION['cart_color_name']);
unset($_SESSION['cart_p_qty']);
unset($_SESSION['cart_p_current_price']);
unset($_SESSION['cart_p_name']);
unset($_SESSION['cart_p_featured_photo']);

?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <meta name="description" content="">
        <meta name="author" content="">
        <title>VNPAY RESPONSE</title>
        <!-- Bootstrap core CSS -->
        <link href="/vnpay_php/assets/bootstrap.min.css" rel="stylesheet"/>
        <!-- Custom styles for this template -->
        <link href="/vnpay_php/assets/jumbotron-narrow.css" rel="stylesheet">         
        <script src="/vnpay_php/assets/jquery-1.11.3.min.js"></script>
    </head>
    <body>
        <?php
        require_once("./config.php");
        $vnp_SecureHash = $_GET['vnp_SecureHash'];
        $inputData = array();
        foreach ($_GET as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }
        
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        ?>
        <!--Begin display -->
        <div class="container">
            <div class="header clearfix">
                <h3 class="text-muted">VNPAY RESPONSE</h3>
            </div>
            <div class="table-responsive">
                <div class="form-group">
                    <label >Mã đơn hàng:</label>

                    <label><?php echo $_GET['vnp_TxnRef'] ?></label>
                </div>    
                <div class="form-group">

                    <label >Số tiền:</label>
                    <label><?=number_format($_GET['vnp_Amount']/100) ?> VNĐ</label>
                </div>  
                <div class="form-group">
                    <label >Nội dung thanh toán:</label>
                    <label><?php echo $_GET['vnp_OrderInfo'] ?></label>
                </div> 
                <div class="form-group">
                    <label >Mã phản hồi (vnp_ResponseCode):</label>
                    <label><?php echo $_GET['vnp_ResponseCode'] ?></label>
                </div> 
                <div class="form-group">
                    <label >Mã GD Tại VNPAY:</label>
                    <label><?php echo $_GET['vnp_TransactionNo'] ?></label>
                </div> 
                <div class="form-group">
                    <label >Mã Ngân hàng:</label>
                    <label><?php echo $_GET['vnp_BankCode'] ?></label>
                </div> 
                <div class="form-group">
                    <label >Thời gian thanh toán:</label>
                    <label><?php echo $_GET['vnp_PayDate'] ?></label>
                </div> 
                <div class="form-group">
                    <label >Kết quả:</label>
                    <label>
                        <?php
                        if ($secureHash == $vnp_SecureHash) {
                            if ($_GET['vnp_ResponseCode'] == '00') {
                                echo "<span style='color:blue'>GD Thanh cong</span>";
                            } else {
                                echo "<span style='color:red'>GD Khong thanh cong</span>";
                            }
                        } else {
                            echo "<span style='color:red'>Chu ky khong hop le</span>";
                        }
                        ?>

                    </label>
                    <a href="../index.php">
                        <button>Quay lại</button>
                    </a>
                </div> 
            </div>
            <p>
                &nbsp;
            </p>
            <footer class="footer">
                   <p>&copy; VNPAY <?php echo date('Y')?></p>
            </footer>
        </div>  
    </body>
</html>
