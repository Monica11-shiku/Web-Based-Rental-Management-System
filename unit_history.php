<?php require 'config.php';$u=need('landlord','caretaker');$L=$u['landlord_id'];$id=(int)($_GET['unit']??0);
head('Maintenance history by unit');
echo "<form method=get><select name=unit>";
foreach(q("SELECT un.unit_id,un.unit_number,p.property_name FROM units un JOIN properties p ON p.property_id=un.property_id WHERE p.landlord_id=? ORDER BY p.property_name,un.unit_number",[$L]) as $x)echo "<option value={$x['unit_id']}".($x['unit_id']==$id?' selected':'').">".e($x['property_name'].' '.$x['unit_number']);
echo "</select><button>Show history</button></form>";
if($id){$rows=q("SELECT r.*,us.full_name tenant,st.full_name handler FROM maintenance_requests r JOIN units un ON un.unit_id=r.unit_id JOIN properties p ON p.property_id=un.property_id JOIN tenants t ON t.tenant_id=r.tenant_id JOIN users us ON us.user_id=t.user_id LEFT JOIN users st ON st.user_id=r.assigned_to WHERE r.unit_id=? AND p.landlord_id=? ORDER BY r.request_id DESC",[$id,$L])->fetchAll();
 if(!$rows)exit('<p>No repair requests for this unit.</p>');
 $d=[];foreach($rows as $r)if($r['date_resolved'])$d[]=(strtotime($r['date_resolved'])-strtotime($r['date_submitted']))/86400;
 echo "<p>".count($rows)." requests &middot; ".count($d)." resolved".($d?" &middot; average ".round(array_sum($d)/count($d),1)." days to resolve":"")."</p><table><tr><th>Submitted<th>Tenant<th>Problem<th>Status<th>Resolved<th>Handled by";
 foreach($rows as $r)echo "<tr><td>{$r['date_submitted']}<td>".e($r['tenant'])."<td>".e($r['description'])."<td>{$r['status']}<td>".e($r['date_resolved']??'')."<td>".e($r['handler']??'');
 echo "</table>";}
