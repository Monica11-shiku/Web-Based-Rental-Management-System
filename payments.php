<?php require 'config.php';$u=need();$L=$u['landlord_id'];$m='';
if($_POST&&$u['role']!='tenant'){$a=(float)$_POST['amount'];
 $l=q("SELECT l.lease_id,l.tenant_id FROM leases l JOIN units un USING(unit_id) JOIN properties p USING(property_id) WHERE l.lease_id=? AND p.landlord_id=? AND l.status='active'",[$_POST['lease'],$L])->fetch();
 if(!$l||$a<=0)$m='Invalid payment';
 elseif(q('SELECT 1 FROM payments WHERE lease_id=? AND amount=? AND payment_date>DATE_SUB(NOW(),INTERVAL 1 MINUTE)',[$l['lease_id'],$a])->fetch())$m='Same amount was just recorded - possible duplicate';
 else{$rc='RCT'.date('ymd').strtoupper(bin2hex(random_bytes(3)));
  q("INSERT INTO payments(lease_id,tenant_id,amount,payment_date,method,receipt_number,status,recorded_by) VALUES(?,?,?,NOW(),'cash',?,'successful',?)",[$l['lease_id'],$l['tenant_id'],$a,$rc,$u['id']]);
  q("INSERT INTO notifications(user_id,message,type,date_sent) SELECT user_id,?,'payment',NOW() FROM tenants WHERE tenant_id=?",["Payment of KES $a received. Receipt $rc",$l['tenant_id']]);$m="Recorded. Receipt $rc";}}
head('Payments');echo "<p>".e($m)."</p>";
if($u['role']!='tenant'){?><form method=post><?=csrf()?>Lease<select name=lease><?php foreach(arrears($L) as $a)echo "<option value={$a['lease_id']}>".e($a['unit_number'].' - '.$a['full_name'])." (owes ".number_format($a['balance']).")";?></select>Cash amount<input name=amount type=number min=1 step=0.01 required><button>Record cash payment</button></form><?php }
$w=$u['role']=='tenant'?'t.user_id='.(int)$u['id']:'p.landlord_id='.(int)$L;
echo "<table><tr><th>Date<th>Receipt<th>Method<th>Amount<th>Status<th>";
foreach(q("SELECT pm.* FROM payments pm JOIN tenants t USING(tenant_id) JOIN leases l ON l.lease_id=pm.lease_id JOIN units un USING(unit_id) JOIN properties p USING(property_id) WHERE $w ORDER BY payment_date DESC LIMIT 50") as $r)echo "<tr><td>{$r['payment_date']}<td>".e($r['receipt_number'])."<td>{$r['method']}<td>".number_format($r['amount'])."<td>{$r['status']}<td>".($r['status']=='successful'?"<a href='receipt.php?id={$r['payment_id']}'>Receipt</a>":'');
echo "</table>";
