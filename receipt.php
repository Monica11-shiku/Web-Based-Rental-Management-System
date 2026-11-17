<?php require 'config.php';$u=need();$L=$u['landlord_id'];
$r=q("SELECT pm.*,t.user_id tuid,us.full_name,un.unit_number,p.property_name,p.landlord_id ll,ld.full_name landlord FROM payments pm JOIN tenants t ON t.tenant_id=pm.tenant_id JOIN users us ON us.user_id=t.user_id JOIN leases l ON l.lease_id=pm.lease_id JOIN units un ON un.unit_id=l.unit_id JOIN properties p ON p.property_id=un.property_id JOIN users ld ON ld.user_id=p.landlord_id WHERE pm.payment_id=? AND pm.status='successful'",[(int)($_GET['id']??0)])->fetch();
if(!$r||($u['role']=='tenant'?$r['tuid']!=$u['id']:$r['ll']!=$L)){http_response_code(404);exit('Receipt not found');}
?><!doctype html><meta name=viewport content="width=device-width,initial-scale=1"><title>Receipt <?=e($r['receipt_number'])?></title>
<style>body{font:15px system-ui;max-width:420px;margin:20px auto;padding:16px;border:1px solid #999}h2{text-align:center;margin:0}td{padding:5px 0}td+td{text-align:right}@media print{button{display:none}body{border:0}}</style>
<h2>RENT RECEIPT</h2><p style=text-align:center><?=e($r['landlord'])?></p>
<table width=100%>
<tr><td>Receipt no.<td><?=e($r['receipt_number'])?>
<tr><td>Date<td><?=e($r['payment_date'])?>
<tr><td>Received from<td><?=e($r['full_name'])?>
<tr><td>Property / unit<td><?=e($r['property_name'].' '.$r['unit_number'])?>
<tr><td>Method<td><?=e($r['method'])?>
<tr><td><b>Amount paid</b><td><b>KES <?=number_format($r['amount'],2)?></b>
</table><p style=text-align:center>Thank you.</p><button onclick="print()">Print / Save as PDF</button>
