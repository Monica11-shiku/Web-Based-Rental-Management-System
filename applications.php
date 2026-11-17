<?php require 'config.php';$u=need('landlord','caretaker');$L=$u['landlord_id'];
if($_POST&&in_array($_POST['s']??'',['approved','rejected']))
 q("UPDATE vacancy_applications va JOIN units un ON un.unit_id=va.unit_id JOIN properties p ON p.property_id=un.property_id SET va.status=? WHERE va.application_id=? AND va.status='new' AND p.landlord_id=?",[$_POST['s'],(int)$_POST['id'],$L]);
head('Vacancy applications');
echo "<p>Approving does not create a tenant. Register approved applicants on the Tenants page.</p><table><tr><th>Date<th>Unit<th>Applicant<th>Phone<th>Message<th>Status";
foreach(q("SELECT va.*,un.unit_number,p.property_name FROM vacancy_applications va JOIN units un ON un.unit_id=va.unit_id JOIN properties p ON p.property_id=un.property_id WHERE p.landlord_id=? ORDER BY va.application_id DESC",[$L]) as $r){
 echo "<tr><td>{$r['date_applied']}<td>".e($r['property_name'].' '.$r['unit_number'])."<td>".e($r['applicant_name'])."<td>".e($r['applicant_phone'])."<td>".e($r['message'])."<td>";
 if($r['status']=='new')foreach(['approved'=>'Approve','rejected'=>'Reject'] as $s=>$t)echo "<form method=post style=display:inline>".csrf()."<input type=hidden name=id value={$r['application_id']}><input type=hidden name=s value=$s><button>$t</button></form> ";
 else echo e($r['status']);}
echo "</table>";
