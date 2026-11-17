<?php require 'config.php';require 'mpesa.php';$u=need('tenant');$m='';
$t=q("SELECT t.tenant_id,l.lease_id,us.phone FROM tenants t JOIN leases l USING(tenant_id) JOIN users us ON us.user_id=t.user_id WHERE t.user_id=? AND l.status='active'",[$u['id']])->fetch();
if(!$t)exit('No active lease');
if($_POST){$a=(int)$_POST['amount'];$ph=preg_replace('/^(254|0)/','254',preg_replace('/\D/','',$_POST['phone']));
 if($a<1||!preg_match('/^254[17]\d{8}$/',$ph))$m='Enter a valid amount and Safaricom number';
 else{$r=mp_stk($ph,$a,'LEASE'.$t['lease_id']);
  if(($r['ResponseCode']??'')==='0'){q("INSERT INTO payments(lease_id,tenant_id,amount,payment_date,method,receipt_number,status,checkout_id) VALUES(?,?,?,NOW(),'mpesa','','pending',?)",[$t['lease_id'],$t['tenant_id'],$a,$r['CheckoutRequestID']]);$m='Check your phone and enter your M-Pesa PIN. Then refresh Payments.';}
  else{error_log('stk fail: '.json_encode($r));$m='Could not start the payment. Please try again.';}}}
head('Pay rent with M-Pesa');echo "<p>".e($m)."</p>";?>
<form method=post><?=csrf()?>M-Pesa phone<input name=phone value="<?=e($t['phone'])?>">Amount (KES)<input name=amount type=number min=1 required><button>Pay with M-Pesa</button></form>
