<?php require 'config.php';$u=need('landlord','caretaker');$L=$u['landlord_id'];$m='';
if($_POST){$f=array_map('trim',$_POST);
 try{if(!preg_match('/^(\+?254|0)[17]\d{8}$/',$f['phone']))throw new Exception('Invalid phone');
  if($f['rent']<=0||$f['deposit']<0||$f['end']<=$f['start'])throw new Exception('Check rent, deposit and dates');
  $pdo->beginTransaction();
  $un=q("SELECT un.unit_id FROM units un JOIN properties p USING(property_id) WHERE un.unit_id=? AND p.landlord_id=? AND un.status='vacant' FOR UPDATE",[$f['unit'],$L])->fetch();
  if(!$un)throw new Exception('Unit is not vacant');
  $x=q('SELECT * FROM users WHERE national_id=? OR phone=?',[$f['nid'],$f['phone']])->fetch();$tmp='';
  if($x&&$x['role']!='tenant')throw new Exception('Account belongs to staff');
  if(!$x){$tmp=bin2hex(random_bytes(4));q("INSERT INTO users(full_name,national_id,phone,email,password_hash,role,landlord_id) VALUES(?,?,?,?,?,'tenant',?)",[$f['name'],$f['nid'],$f['phone'],$f['email'],password_hash($tmp,PASSWORD_DEFAULT),$L]);$uid=$pdo->lastInsertId();}else $uid=$x['user_id'];
  $t=q('SELECT tenant_id FROM tenants WHERE user_id=?',[$uid])->fetch();
  if($t)$tid=$t['tenant_id'];else{q('INSERT INTO tenants(user_id,emergency_contact,move_in_date) VALUES(?,?,?)',[$uid,$f['emerg'],$f['start']]);$tid=$pdo->lastInsertId();}
  if(q("SELECT 1 FROM leases WHERE unit_id=? AND status='active'",[$f['unit']])->fetch())throw new Exception('Unit already has an active lease');
  q('INSERT INTO leases(tenant_id,unit_id,rent_amount,deposit_amount,start_date,end_date,notice_period_days) VALUES(?,?,?,?,?,?,?)',[$tid,$f['unit'],$f['rent'],$f['deposit'],$f['start'],$f['end'],$f['notice']]);
  q("UPDATE units SET status='occupied' WHERE unit_id=?",[$f['unit']]);$pdo->commit();
  $m=$tmp?"<span class=ok>Tenant saved. Temporary password (shown once): <b>$tmp</b></span>":"<span class=ok>Lease created for existing tenant.</span>";
 }catch(Exception $ex){if($pdo->inTransaction())$pdo->rollBack();$m='<span class=err>'.e($ex->getMessage()).'</span>';}}
head('Register tenant & lease');echo "<p><a href=tenantlist.php>Search tenants</a></p>";echo "<p>$m</p>";?>
<form method=post><?=csrf()?>Full name<input name=name required>National ID<input name=nid required>Phone<input name=phone required>Email<input name=email type=email>Emergency contact<input name=emerg>
Vacant unit<select name=unit><?php foreach(q("SELECT un.unit_id,un.unit_number,un.rent_amount,p.property_name FROM units un JOIN properties p USING(property_id) WHERE p.landlord_id=? AND un.status='vacant'",[$L]) as $r)echo "<option value={$r['unit_id']}>".e($r['property_name'].' '.$r['unit_number'])." (KES {$r['rent_amount']})";?></select>
Rent<input name=rent type=number required>Deposit<input name=deposit type=number value=0>Start<input name=start type=date required>End<input name=end type=date required>Notice days<input name=notice type=number value=30><button>Save tenant & lease</button></form>
