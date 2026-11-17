<?php require 'config.php';$u=need('landlord','caretaker');$L=$u['landlord_id'];$m='';
if($_POST){$id=(int)$_POST['lease'];$d=(float)$_POST['ded'];$dt=$_POST['date'];
 $l=q("SELECT l.*,un.unit_id uid FROM leases l JOIN units un ON un.unit_id=l.unit_id JOIN properties p ON p.property_id=un.property_id WHERE l.lease_id=? AND l.status='active' AND p.landlord_id=?",[$id,$L])->fetch();
 if(!$l||$d<0||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$dt)||$dt<$l['start_date'])$m='<span class=err>Check the lease, date and deductions</span>';
 else{$bal=0;foreach(arrears($L) as $a)if($a['lease_id']==$id)$bal=$a['balance'];
  $ref=$l['deposit_amount']-$bal-$d;
  try{$pdo->beginTransaction();
   q('INSERT INTO moveouts(lease_id,moveout_date,deposit,balance,deductions,refund_due,notes,processed_by) VALUES(?,?,?,?,?,?,?,?)',[$id,$dt,$l['deposit_amount'],$bal,$d,$ref,substr($_POST['notes'],0,300),$u['id']]);
   q("UPDATE leases SET status='terminated',end_date=? WHERE lease_id=?",[$dt,$id]);
   q("UPDATE units SET status='vacant' WHERE unit_id=?",[$l['uid']]);$pdo->commit();
   $m="<span class=ok>Moved out. ".($ref>=0?"Refund due to tenant: KES ".number_format($ref,2):"Tenant still owes KES ".number_format(-$ref,2))."</span>";
  }catch(Exception $x){if($pdo->inTransaction())$pdo->rollBack();$m='<span class=err>Could not save. Try again.</span>';}}}
head('Move-out & deposit refund');echo "<p>$m</p>";
$dep=array_column(q("SELECT l.lease_id,l.deposit_amount FROM leases l JOIN units un ON un.unit_id=l.unit_id JOIN properties p ON p.property_id=un.property_id WHERE p.landlord_id=? AND l.status='active'",[$L])->fetchAll(),'deposit_amount','lease_id');
echo "<p>Refund = deposit - unpaid rent - damage charges. A negative balance (overpayment) is added back.</p><table><tr><th>Unit<th>Tenant<th>Balance<th>Deposit<th>Move out";
foreach(arrears($L) as $a)echo "<tr><td>".e($a['unit_number'])."<td>".e($a['full_name'])."<td>".number_format($a['balance'])."<td>".number_format($dep[$a['lease_id']]??0)."<td><form method=post onsubmit=\"return confirm('End this lease and free the unit? This cannot be undone.')\">".csrf()."<input type=hidden name=lease value={$a['lease_id']}>Date<input type=date name=date required>Damage charges<input type=number name=ded min=0 value=0 step=0.01>Notes<input name=notes maxlength=300><button>Move out</button></form>";
echo "</table><h3>Past move-outs</h3><table><tr><th>Date<th>Unit<th>Tenant<th>Deposit<th>Balance<th>Damages<th>Refund due";
foreach(q("SELECT m.*,un.unit_number,us.full_name FROM moveouts m JOIN leases l ON l.lease_id=m.lease_id JOIN units un ON un.unit_id=l.unit_id JOIN properties p ON p.property_id=un.property_id JOIN tenants t ON t.tenant_id=l.tenant_id JOIN users us ON us.user_id=t.user_id WHERE p.landlord_id=? ORDER BY m.moveout_id DESC",[$L]) as $r)echo "<tr><td>{$r['moveout_date']}<td>".e($r['unit_number'])."<td>".e($r['full_name'])."<td>".number_format($r['deposit'])."<td>".number_format($r['balance'])."<td>".number_format($r['deductions'])."<td>".number_format($r['refund_due'],2);
echo "</table>";
