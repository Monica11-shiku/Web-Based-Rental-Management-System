<?php require 'config.php';$u=need();$L=$u['landlord_id'];
$r=q("SELECT l.*,t.user_id tuid,us.full_name,us.national_id,us.phone,un.unit_number,un.unit_type,p.property_name,p.location,p.landlord_id ll,ld.full_name landlord FROM leases l JOIN tenants t ON t.tenant_id=l.tenant_id JOIN users us ON us.user_id=t.user_id JOIN units un ON un.unit_id=l.unit_id JOIN properties p ON p.property_id=un.property_id JOIN users ld ON ld.user_id=p.landlord_id WHERE l.lease_id=?",[(int)($_GET['id']??0)])->fetch();
if(!$r||($u['role']=='tenant'?$r['tuid']!=$u['id']:$r['ll']!=$L)){http_response_code(404);exit('Lease not found');}
$rn=q('SELECT * FROM lease_renewals WHERE lease_id=? ORDER BY renewal_id',[$r['lease_id']])->fetchAll();
?><!doctype html><meta name=viewport content="width=device-width,initial-scale=1"><title>Lease <?=(int)$r['lease_id']?></title>
<style>body{font:15px/1.6 system-ui,Segoe UI,sans-serif;max-width:680px;margin:20px auto;padding:20px;border:1px solid #999}h2{text-align:center;margin:0 0 14px}td{padding:5px 0;vertical-align:top}td+td{text-align:right}.sig{display:flex;justify-content:space-between;margin-top:56px}.sig div{width:44%;border-top:1px solid #000;padding-top:4px}@media print{button{display:none}body{border:0}}</style>
<h2>LEASE AGREEMENT</h2><table width=100%>
<tr><td>Landlord<td><?=e($r['landlord'])?>
<tr><td>Tenant<td><?=e($r['full_name'])?> (ID <?=e($r['national_id'])?>, phone <?=e($r['phone'])?>)
<tr><td>Premises<td><?=e($r['property_name'].' '.$r['unit_number'].' ('.$r['unit_type'].'), '.$r['location'])?>
<tr><td>Lease period<td><?=e($r['start_date'])?> to <?=e($r['end_date'])?>
<tr><td>Monthly rent<td>KES <?=number_format($r['rent_amount'],2)?>, due on the 5th of each month
<tr><td>Deposit<td>KES <?=number_format($r['deposit_amount'],2)?>
<tr><td>Notice period<td><?=(int)$r['notice_period_days']?> days
<tr><td>Status<td><?=e($r['status'])?></table>
<?php if($rn){echo "<h3>Renewals</h3><table width=100%>";foreach($rn as $x)echo "<tr><td>".e($x['created_at'])."<td>Ended ".e($x['old_end'])." to ".e($x['new_end']).", rent KES ".number_format($x['old_rent'])." to ".number_format($x['new_rent']);echo "</table>";}?>
<p>This document summarises the lease terms recorded in the Rental Management System.</p>
<div class=sig><div>Landlord signature and date</div><div>Tenant signature and date</div></div><p><button onclick="print()">Print / Save as PDF</button></p>
