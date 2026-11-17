<?php require 'config.php';$u=need();$L=$u['landlord_id'];$staff=$u['role']!='tenant';$m='';
if($_POST&&$staff){$id=(int)$_POST['id'];$ne=$_POST['end']??'';$nr=(float)($_POST['rent']??0);
 $l=q("SELECT l.*,t.user_id tuid FROM leases l JOIN tenants t ON t.tenant_id=l.tenant_id JOIN units un ON un.unit_id=l.unit_id JOIN properties p ON p.property_id=un.property_id WHERE l.lease_id=? AND l.status='active' AND p.landlord_id=?",[$id,$L])->fetch();
 if(!$l||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$ne)||$ne<=$l['end_date']||$nr<=0)$m='<span class=err>Choose an end date later than the current one and a rent above 0</span>';
 else{try{$pdo->beginTransaction();
  q('INSERT INTO lease_renewals(lease_id,old_end,new_end,old_rent,new_rent,renewed_by) VALUES(?,?,?,?,?,?)',[$id,$l['end_date'],$ne,$l['rent_amount'],$nr,$u['id']]);
  q('UPDATE leases SET end_date=?,rent_amount=? WHERE lease_id=?',[$ne,$nr,$id]);
  q("INSERT INTO notifications(user_id,message,type,date_sent) VALUES(?,?,'lease',NOW())",[$l['tuid'],"Your lease has been renewed until $ne at KES ".number_format($nr)." per month"]);
  $pdo->commit();$m='<span class=ok>Lease renewed</span>';
 }catch(Exception $x){if($pdo->inTransaction())$pdo->rollBack();$m='<span class=err>Could not save. Try again.</span>';}}}
head('Leases');echo "<p>$m</p>";
$w=$staff?'p.landlord_id='.(int)$L:'t.user_id='.(int)$u['id'];
echo "<table><tr><th>Tenant<th>Unit<th>Rent<th>Period<th>Status<th>";
foreach(q("SELECT l.*,un.unit_number,p.property_name,us.full_name FROM leases l JOIN tenants t ON t.tenant_id=l.tenant_id JOIN users us ON us.user_id=t.user_id JOIN units un ON un.unit_id=l.unit_id JOIN properties p ON p.property_id=un.property_id WHERE $w ORDER BY (l.status='active') DESC,l.lease_id DESC") as $r){
 echo "<tr><td>".e($r['full_name'])."<td>".e($r['property_name'].' '.$r['unit_number'])."<td>".number_format($r['rent_amount'])."<td>{$r['start_date']} to {$r['end_date']}<td>{$r['status']}<td><a href='lease_print.php?id={$r['lease_id']}' target=_blank>Print lease</a> &middot; <a href='statement.php?id={$r['lease_id']}' target=_blank>Statement</a>";
 if($staff&&$r['status']=='active')echo "<form method=post>".csrf()."<input type=hidden name=id value={$r['lease_id']}>New end date<input type=date name=end required>New rent<input type=number name=rent min=1 step=0.01 value='{$r['rent_amount']}'><button>Renew</button></form>";}
echo "</table>";
