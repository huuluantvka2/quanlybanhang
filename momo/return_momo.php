<?php
ob_start();
session_start();
ini_set('error_reporting', E_ALL);

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



header('Content-type: text/html; charset=utf-8');


$secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa'; //Put your secret key in there

if (!empty($_GET)) {
    $partnerCode = $_GET["partnerCode"];
    $orderId = $_GET["orderId"];
    $message = $_GET["message"];
    $transId = $_GET["transId"];
    $orderInfo = utf8_encode($_GET["orderInfo"]);
    $amount = $_GET["amount"];
    $responseTime = $_GET["responseTime"];
    $requestId = $_GET["requestId"];
    $extraData = $_GET["extraData"];
    $payType = $_GET["payType"];
    $orderType = $_GET["orderType"];
    $extraData = $_GET["extraData"];
    $m2signature = $_GET["signature"]; //MoMo signature

}


$item_number = time();
$item_name = 'Product Item(s)';
$vnp_Amount = $amount;

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
    'MOMO CARD',
    'Pending',
    'Pending',
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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>MoMo Sandbox</title>
    <script type="text/javascript" src="./statics/jquery/dist/jquery.min.js"></script>
    <script type="text/javascript" src="./statics/moment/min/moment.min.js"></script>
    <script type="text/javascript" src="./statics/bootstrap/dist/js/bootstrap.min.js"></script>
    <script type="text/javascript"
            src="./statics/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.4.1/css/bootstrap.min.css"/>
    <link rel="stylesheet"
          href="./statics/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css"/>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h1 class="panel-title">Payment status/Kết quả thanh toán</h1>
                </div>
                <div class="panel-body">
                    <div class="row">
                       
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <label for="fxRate" class="col-form-label">PartnerCode</label>
                                <div class='input-group date' id='fxRate'>
                                    <input type='text' name="partnerCode" value="<?php echo $partnerCode; ?>"
                                           class="form-control"/>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <label for="fxRate" class="col-form-label">OrderId</label>
                                <div class='input-group date' id='fxRate'>
                                    <input type='text' name="orderId" value="<?php echo $orderId; ?>"
                                           class="form-control"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <label for="fxRate" class="col-form-label">transId</label>
                                <div class='input-group date' id='fxRate'>
                                    <input type='text' name="transId" value="<?php echo $transId; ?>"
                                           class="form-control"/>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <label for="fxRate" class="col-form-label">OrderInfo</label>
                                <div class='input-group date' id='fxRate'>
                                    <input type='text' name="orderInfo" value="<?php echo $orderInfo; ?>"
                                           class="form-control"/>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <label for="fxRate" class="col-form-label">orderType</label>
                                <div class='input-group date' id='fxRate'>
                                    <input type='text' name="orderType" value="<?php echo $orderType; ?>"
                                           class="form-control"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <label for="fxRate" class="col-form-label">Amount</label>
                                <div class='input-group date' id='fxRate'>
                                    <input type='text' name="amount" value="<?php echo number_format($amount); ?>VND"
                                           class="form-control"/>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <label for="fxRate" class="col-form-label">Message</label>
                                <div class='input-group date' id='fxRate'>
                                    <input style="color:green;"type='text' name="message" value="<?php echo $message; ?>"
                                           class="form-control"/>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <label for="fxRate" class="col-form-label">payType</label>
                                <div class='input-group date' id='fxRate'>
                                    <input type='text' name="payType" value="<?php echo $payType; ?>"
                                           class="form-control"/>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <label for="fxRate" class="col-form-label">ExtraData</label>
                                <div class='input-group date' id='fxRate'>
                                    <input type='text' type="text" name="extraData" value="<?php echo $extraData; ?>"
                                           class="form-control"/>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <label for="fxRate" class="col-form-label">signature</label>
                                <div class='input-group date' id='fxRate'>
                                    <input type='text' name="signature" value="<?php echo $m2signature; ?>"
                                           class="form-control"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">

                        
                        <div style="color:green; font-size: 26px; text-align:center;">
                            <?php echo ($message == "Successful.") ? 'MICHALE cảm ơn bạn đã thanh toán:': 'Thanh toán thất bại!';?> <?php echo number_format($amount); ?>VND
                            </div>
                            <br>
                            <div class="form-group">
                                <a href="../index.php" class="btn btn-primary">Trở lại mua hàng...</a>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</body>
</html>