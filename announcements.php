<?php require 'config.php';$u=need();$L=$u['landlord_id'];$staff=$u['role']!='tenant';$m='';
if($_POST&&$staff){$t=trim($_POST['msg']);
 if(strlen($t)<5||strlen($t)>500)$m='<span class=err>Write between 5 and 500 characters</span>';
 else{q('INSERT INTO announcements(landlord_id,message,created_by) VALUES(?,?,?)',[$L,$t,$u['id']]);
  q("INSERT INTO notifications(user_id,message,type,date_sent) SELECT DISTINCT t.user_id,?,'announcement',NOW() FROM leases l JOIN tenants t ON t.tenant_id=l.tenant_id JOIN units un ON un.unit_id=l.unit_id JOIN properties p ON p.property_id=un.property_id WHERE l.status='active' AND p.landlord_id=?",['Announcement: '.$t,$L]);
  $m='<span class=ok>Announcement sent to all current tenants</span>';}}
head('Announcements');echo "<p>$m</p>";
if($staff)echo "<form method=post>".csrf()."<textarea name=msg rows=3 maxlength=500 placeholder='Write a notice for all tenants' required></textarea><button>Send announcement</button></form>";
foreach(q("SELECT a.message,a.created_at,us.full_name FROM announcements a JOIN users us ON us.user_id=a.created_by WHERE a.landlord_id=? ORDER BY a.announcement_id DESC LIMIT 50",[$L]) as $r)echo "<div class=card><span>".e($r['created_at'])." &middot; ".e($r['full_name'])."</span><p>".nl2br(e($r['message']))."</p></div>";
