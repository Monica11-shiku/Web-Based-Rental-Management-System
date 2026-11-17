<?php require 'config.php'; // Called by Safaricom, no login. Only acts on a matching PENDING payment.
$d=json_decode(file_get_contents('php://input'),true)['Body']['stkCallback']??null;
if($d){$p=q("SELECT * FROM payments WHERE checkout_id=? AND status='pending'",[$d['CheckoutRequestID']??''])->fetch();
 if($p){
  if(($d['ResultCode']??1)==0){$it=[];foreach($d['CallbackMetadata']['Item']??[] as $i)$it[$i['Name']]=$i['Value']??null;
   $code=$it['MpesaReceiptNumber']??null;
   if($code&&abs(($it['Amount']??0)-$p['amount'])<0.01){
    try{q("UPDATE payments SET status='successful',mpesa_code=?,receipt_number=?,payment_date=NOW() WHERE payment_id=? AND status='pending'",[$code,$code,$p['payment_id']]);
     q("INSERT INTO notifications(user_id,message,type,date_sent) SELECT user_id,?,'payment',NOW() FROM tenants WHERE tenant_id=?",["M-Pesa payment of KES {$p['amount']} received. Code $code",$p['tenant_id']]);
    }catch(PDOException $x){error_log('callback: '.$x->getMessage());}
   }else error_log('callback mismatch: '.json_encode($d));
  }else q("UPDATE payments SET status='failed' WHERE payment_id=?",[$p['payment_id']]);
 }}
header('Content-Type: application/json');echo '{"ResultCode":0,"ResultDesc":"Accepted"}';
