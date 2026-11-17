<?php require 'config.php';$u=need();$L=$u['landlord_id'];
$r=q("SELECT l.lease_id,t.user_id tuid,us.full_name,un.unit_number,p.property_name,p.landlord_id ll FROM leases l JOIN tenants t ON t.tenant_id=l.tenant_id JOIN users us ON us.user_id=t.user_id JOIN units un ON un.unit_id=l.unit_id JOIN properties p ON p.property_id=un.property_id WHERE l.lease_id=?",[(int)($_GET['id']??0)])->fetch();
if(!$r||($u['role']=='tenant'?$r['tuid']!=$u['id']:$r['ll']!=$L)){http_response_code(404);exit('Statement not found');}
$rows=q("SELECT d,ds,dr,cr FROM (SELECT due_date d,CONCAT('Rent for ',DATE_FORMAT(period,'%M %Y')) ds,amount dr,0 cr,1 o FROM rent_charges WHERE lease_id=? UNION ALL SELECT DATE(payment_date),CONCAT('Payment ',receipt_number,' (',method,')'),0,amount,2 FROM payments WHERE lease_id=? AND status='successful') x ORDER BY d,o",[$r['lease_id'],$r['lease_id']])->fetchAll();
?><!doctype html><meta name=viewport content="width=device-width,initial-scale=1"><title>Statement</title>
<style>body{font:15px/1.5 system-ui,Segoe UI,sans-serif;max-width:760px;margin:20px auto;padding:20px;border:1px solid #999}h2{text-align:center;margin:0}table{border-collapse:collapse;width:100%;margin-top:14px}td,th{padding:6px 8px;border-bottom:1px solid #ccc;text-align:left}td:nth-child(n+3),th:nth-child(n+3){text-align:right}@media print{button{display:none}body{border:0}}</style>
<h2>ACCOUNT STATEMENT</h2><p style=text-align:center><?=e($r['full_name'])?> &middot; <?=e($r['property_name'].' '.$r['unit_number'])?> &middot; printed <?=date('Y-m-d')?></p>
<table><tr><th>Date<th>Description<th>Charged<th>Paid<th>Balance</tr>
<?php $b=0;foreach($rows as $x){$b+=$x['dr']-$x['cr'];echo "<tr><td>".e($x['d'])."<td>".e($x['ds'])."<td>".($x['dr']>0?number_format($x['dr'],2):'')."<td>".($x['cr']>0?number_format($x['cr'],2):'')."<td>".number_format($b,2);}?>
<tr><th colspan=4>Closing balance (KES)<th><?=number_format($b,2)?></table><p><button onclick="print()">Print / Save as PDF</button></p>
