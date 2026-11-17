<?php require 'config.php';$u=need();$L=$u['landlord_id'];$m='';
if($_POST&&$u['role']=='tenant'){$d=trim($_POST['d']);$l=q("SELECT l.unit_id FROM leases l JOIN tenants t USING(tenant_id) WHERE t.user_id=? AND l.status='active'",[$u['id']])->fetch();
 if(strlen($d)<10||strlen($d)>500||!$l)$m='Description must be 10-500 characters and you need an active lease';
 else{q('INSERT INTO maintenance_requests(unit_id,tenant_id,description,date_submitted) SELECT ?,tenant_id,?,NOW() FROM tenants WHERE user_id=?',[$l['unit_id'],$d,$u['id']]);q("INSERT INTO notifications(user_id,message,type,date_sent) VALUES(?,?,'maintenance',NOW())",[$L,'New repair request: '.substr($d,0,60)]);$m='Request sent';}}
elseif($_POST){$nx=['pending'=>'in progress','in progress'=>'resolved'];$r=q('SELECT r.* FROM maintenance_requests r JOIN units un USING(unit_id) JOIN properties p USING(property_id) WHERE r.request_id=? AND p.landlord_id=?',[$_POST['id'],$L])->fetch();
 if($r&&isset($nx[$r['status']])){q('UPDATE maintenance_requests SET status=?,assigned_to=?,date_resolved=IF(?="resolved",NOW(),NULL) WHERE request_id=?',[$nx[$r['status']],$u['id'],$nx[$r['status']],$r['request_id']]);q("INSERT INTO notifications(user_id,message,type,date_sent) SELECT user_id,?,'maintenance',NOW() FROM tenants WHERE tenant_id=?",['Your repair request is now '.$nx[$r['status']],$r['tenant_id']]);}}
head('Maintenance');echo "<p>".e($m)."</p>";if($u['role']!='tenant')echo "<p><a href=unit_history.php>Maintenance history by unit</a></p>";
if($u['role']=='tenant')echo "<form method=post>".csrf()."<textarea name=d rows=3 placeholder='Describe the problem'></textarea><button>Submit request</button></form>";
$w=$u['role']=='tenant'?'t.user_id='.(int)$u['id']:'p.landlord_id='.(int)$L;
echo "<table><tr><th>Unit<th>Problem<th>Date<th>Status<th>";
foreach(q("SELECT r.*,un.unit_number FROM maintenance_requests r JOIN tenants t USING(tenant_id) JOIN units un ON un.unit_id=r.unit_id JOIN properties p USING(property_id) WHERE $w ORDER BY r.date_submitted DESC") as $r){
 echo "<tr><td>".e($r['unit_number'])."<td>".e($r['description'])."<td>{$r['date_submitted']}<td>{$r['status']}<td>";
 if($u['role']!='tenant'&&$r['status']!='resolved')echo "<form method=post>".csrf()."<input type=hidden name=id value={$r['request_id']}><button>Advance</button></form>";}
echo "</table>";
