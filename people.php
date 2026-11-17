<?php require 'config.php';$u=need('landlord','caretaker');$L=$u['landlord_id'];$m='';
if($_POST){$a=$_POST['a']??'';
 if($a=='reset'){$x=q("SELECT user_id,full_name FROM users WHERE user_id=? AND role='tenant' AND landlord_id=?",[$_POST['id'],$L])->fetch();
  if($x){$p=bin2hex(random_bytes(4));q('UPDATE users SET password_hash=?,failed_attempts=0,locked_until=NULL WHERE user_id=?',[password_hash($p,PASSWORD_DEFAULT),$x['user_id']]);$m="<span class=ok>New temporary password for ".e($x['full_name']).": <b>$p</b> (shown once - give it to them in person)</span>";}}
 elseif($a=='approve'){$x=q("SELECT r.reset_id,us.full_name FROM password_resets r JOIN users us ON us.user_id=r.user_id WHERE r.reset_id=? AND r.status='pending' AND us.landlord_id=? AND (us.role='tenant' OR (us.role='caretaker' AND ?='landlord'))",[(int)$_POST['id'],$L,$u['role']])->fetch();
  if($x){$c=sprintf('%06d',random_int(0,999999));q("UPDATE password_resets SET status='approved',code_hash=?,approved_by=?,expires_at=DATE_ADD(NOW(),INTERVAL 30 MINUTE),attempts=0 WHERE reset_id=?",[hash('sha256',$c),$u['id'],$x['reset_id']]);$m="<span class=ok>Reset code for ".e($x['full_name']).": <b>$c</b> (valid 30 minutes, shown once). Give it to them in person.</span>";}}
 elseif($u['role']=='landlord'&&$a=='toggle')q("UPDATE users SET active=1-active WHERE user_id=? AND role='caretaker' AND landlord_id=?",[$_POST['id'],$L]);
 elseif($u['role']=='landlord'&&$a=='add'){$f=array_map('trim',$_POST);$er=[];
  if(!$f['name'])$er[]='Name required';
  if(!preg_match('/^\d{6,10}$/',$f['nid']))$er[]='ID must be digits';
  if(!preg_match('/^(\+?254|0)[17]\d{8}$/',$f['phone']))$er[]='Invalid Kenyan phone';
  if(!filter_var($f['email'],FILTER_VALIDATE_EMAIL))$er[]='Invalid email';
  if(!$er&&q('SELECT 1 FROM users WHERE national_id=? OR phone=? OR email=?',[$f['nid'],$f['phone'],$f['email']])->fetch())$er[]='An account with these details already exists';
  if($er)$m='<span class=err>'.e(implode('. ',$er)).'</span>';
  else{$p=bin2hex(random_bytes(4));q("INSERT INTO users(full_name,national_id,phone,email,password_hash,role,landlord_id) VALUES(?,?,?,?,?,'caretaker',?)",[$f['name'],$f['nid'],$f['phone'],$f['email'],password_hash($p,PASSWORD_DEFAULT),$L]);$m="<span class=ok>Caretaker created. Temporary password (shown once): <b>$p</b></span>";}}}
head('People');echo "<p>$m</p>";
if($u['role']=='landlord'){echo "<h3>Add caretaker</h3><form method=post>".csrf()."<input type=hidden name=a value=add>Full name<input name=name>National ID<input name=nid>Phone<input name=phone>Email<input name=email><button>Create caretaker</button></form><h3>Caretakers</h3><table><tr><th>Name<th>Phone<th>Status<th>";
 foreach(q("SELECT * FROM users WHERE role='caretaker' AND landlord_id=?",[$L]) as $r)echo "<tr><td>".e($r['full_name'])."<td>".e($r['phone'])."<td>".($r['active']?'active':'disabled')."<td><form method=post>".csrf()."<input type=hidden name=a value=toggle><input type=hidden name=id value={$r['user_id']}><button>".($r['active']?'Disable':'Enable')."</button></form>";
 echo "</table>";}
$rq=q("SELECT r.reset_id,r.requested_at,us.full_name,us.role FROM password_resets r JOIN users us ON us.user_id=r.user_id WHERE r.status='pending' AND us.landlord_id=? AND (us.role='tenant' OR (us.role='caretaker' AND ?='landlord'))",[$L,$u['role']])->fetchAll();
if($rq){echo "<h3>Password reset requests</h3><table><tr><th>Name<th>Role<th>Requested<th>";foreach($rq as $r)echo "<tr><td>".e($r['full_name'])."<td>{$r['role']}<td>{$r['requested_at']}<td><form method=post>".csrf()."<input type=hidden name=a value=approve><input type=hidden name=id value={$r['reset_id']}><button>Approve and show code</button></form>";echo "</table>";}
echo "<h3>Tenants</h3><table><tr><th>Name<th>Phone<th>";
foreach(q("SELECT * FROM users WHERE role='tenant' AND landlord_id=? ORDER BY full_name",[$L]) as $r)echo "<tr><td>".e($r['full_name'])."<td>".e($r['phone'])."<td><form method=post>".csrf()."<input type=hidden name=a value=reset><input type=hidden name=id value={$r['user_id']}><button>Reset password</button></form>";
echo "</table>";
