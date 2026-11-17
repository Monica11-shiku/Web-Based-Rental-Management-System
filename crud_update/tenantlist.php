<?php require 'config.php';$u=need('landlord','caretaker');$L=$u['landlord_id'];$s=trim($_GET['s']??'');
head('Search tenants');
echo "<form method=get><input name=s value='".e($s)."' placeholder='Name, phone or unit'><button>Search</button></form>";
$w='';$p=[$L];if($s!==''){$w=" AND (us.full_name LIKE ? OR us.phone LIKE ? OR un.unit_number LIKE ?)";$k="%$s%";array_push($p,$k,$k,$k);}
$rows=q("SELECT us.full_name,us.phone,un.unit_number,pr.property_name,l.status,l.rent_amount,l.start_date,l.end_date FROM users us JOIN tenants t ON t.user_id=us.user_id LEFT JOIN leases l ON l.tenant_id=t.tenant_id LEFT JOIN units un ON un.unit_id=l.unit_id LEFT JOIN properties pr ON pr.property_id=un.property_id WHERE us.role='tenant' AND us.landlord_id=?$w ORDER BY us.full_name,l.lease_id DESC",$p)->fetchAll();
if(!$rows)exit('<p>No tenants found.</p>');
echo "<table><tr><th>Tenant<th>Phone<th>Property / unit<th>Lease<th>Rent<th>Period";
foreach($rows as $r)echo "<tr><td>".e($r['full_name'])."<td>".e($r['phone'])."<td>".e(trim($r['property_name'].' '.$r['unit_number']))."<td>".e($r['status']??'no lease')."<td>".($r['rent_amount']?number_format($r['rent_amount']):'')."<td>".e(trim($r['start_date'].' to '.$r['end_date'],' to'));
echo "</table>";
