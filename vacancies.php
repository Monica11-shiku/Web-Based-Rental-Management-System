<?php require 'config.php';$m='';
if($_POST){$n=trim($_POST['n']);$p=trim($_POST['p']);
 if(!$n||!preg_match('/^(\+?254|0)[17]\d{8}$/',$p)||!q("SELECT 1 FROM units WHERE unit_id=? AND status='vacant'",[$_POST['u']])->fetch())$m='Check your details';
 elseif(q("SELECT 1 FROM vacancy_applications WHERE unit_id=? AND applicant_phone=? AND status='new'",[$_POST['u'],$p])->fetch())$m='You already applied for this unit';
 else{q('INSERT INTO vacancy_applications(unit_id,applicant_name,applicant_phone,message,date_applied) VALUES(?,?,?,?,NOW())',[$_POST['u'],$n,$p,substr($_POST['msg'],0,500)]);q("INSERT INTO notifications(user_id,message,type,date_sent) SELECT p.landlord_id,?,'application',NOW() FROM units un JOIN properties p ON p.property_id=un.property_id WHERE un.unit_id=?",['New vacancy application from '.$n,$_POST['u']]);$m='Application sent';}}
head('Vacant units');echo "<p>".e($m)."</p>";
foreach(q("SELECT un.*,p.property_name,p.location FROM units un JOIN properties p USING(property_id) WHERE un.status='vacant'") as $r)
 echo "<hr><div style='max-width:340px'>".unitimg($r)."</div><b>".e($r['property_name'])." ".e($r['unit_number'])."</b> ({$r['unit_type']}) KES ".number_format($r['rent_amount'])." - ".e($r['location'])."<form method=post><input type=hidden name=u value={$r['unit_id']}><input name=n placeholder=Name><input name=p placeholder=Phone><input name=msg placeholder=Message><button>Apply</button></form>";
