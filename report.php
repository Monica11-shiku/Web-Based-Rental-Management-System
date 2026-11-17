<?php require 'config.php';$u=need('landlord','caretaker');$L=$u['landlord_id'];
$mo=preg_match('/^\d{4}-\d{2}$/',$_GET['m']??'')?$_GET['m']:date('Y-m');
$props=q('SELECT property_id,property_name FROM properties WHERE landlord_id=? ORDER BY property_name',[$L])->fetchAll();
$pid=(int)($_GET['p']??0);if($pid&&!in_array($pid,array_column($props,'property_id')))$pid=0;$pf=$pid?" AND p.property_id=$pid":'';
$J="FROM payments pm JOIN leases l ON l.lease_id=pm.lease_id JOIN units un ON un.unit_id=l.unit_id JOIN properties p ON p.property_id=un.property_id JOIN tenants t ON t.tenant_id=pm.tenant_id JOIN users us ON us.user_id=t.user_id WHERE p.landlord_id=? AND pm.status='successful' AND DATE_FORMAT(pm.payment_date,'%Y-%m')=?$pf";
if(isset($_GET['csv'])){header('Content-Type: text/csv');header("Content-Disposition: attachment; filename=collections-$mo.csv");$o=fopen('php://output','w');
 fputcsv($o,['Date','Receipt','Property','Unit','Tenant','Method','Amount']);
 foreach(q("SELECT pm.payment_date,pm.receipt_number,p.property_name,un.unit_number,us.full_name,pm.method,pm.amount $J ORDER BY pm.payment_date",[$L,$mo]) as $r)fputcsv($o,array_values($r));exit;}
head('Monthly report');
$exp=(float)q("SELECT COALESCE(SUM(c.amount),0) s FROM rent_charges c JOIN leases l ON l.lease_id=c.lease_id JOIN units un ON un.unit_id=l.unit_id JOIN properties p ON p.property_id=un.property_id WHERE p.landlord_id=? AND DATE_FORMAT(c.period,'%Y-%m')=?$pf",[$L,$mo])->fetch()['s'];
$rows=q("SELECT p.property_name,SUM(CASE WHEN pm.method='cash' THEN pm.amount ELSE 0 END) cash,SUM(CASE WHEN pm.method='mpesa' THEN pm.amount ELSE 0 END) mp $J GROUP BY p.property_id",[$L,$mo])->fetchAll();
$tot=array_sum(array_map(fn($r)=>$r['cash']+$r['mp'],$rows));
$ex=q("SELECT category,SUM(amount) s FROM expenses WHERE landlord_id=? AND DATE_FORMAT(expense_date,'%Y-%m')=?".($pid?" AND property_id=$pid":'')." GROUP BY category",[$L,$mo])->fetchAll();$xt=array_sum(array_column($ex,'s'));
$opt='<option value=0>All properties';foreach($props as $p)$opt.="<option value={$p['property_id']}".($p['property_id']==$pid?' selected':'').">".e($p['property_name']);
$q=http_build_query(['m'=>$mo,'p'=>$pid,'csv'=>1]);
echo "<form method=get>Month<input type=month name=m value='".e($mo)."'>Property<select name=p>$opt</select><button>Show</button></form><p><a href='?$q'>Download CSV</a> <button onclick=\"print()\">Print report</button></p>
<div class=grid><div class=card><span>Expected rent</span><b>KES ".number_format($exp)."</b></div><div class=card><span>Collected</span><b>KES ".number_format($tot)."</b><small>".($exp?round($tot/$exp*100)."% of expected":"")."</small></div><div class=card><span>Expenses</span><b>KES ".number_format($xt)."</b></div><div class=card><span>Net income</span><b>KES ".number_format($tot-$xt)."</b></div></div>
<h3>Collections by property</h3><table><tr><th>Property<th>Cash<th>M-Pesa<th>Total";
foreach($rows as $r)echo "<tr><td>".e($r['property_name'])."<td>".number_format($r['cash'])."<td>".number_format($r['mp'])."<td>".number_format($r['cash']+$r['mp']);
echo "</table><h3>Income and expenses</h3><table><tr><th>Expense category<th>Amount";
foreach($ex as $r)echo "<tr><td>".e($r['category'])."<td>".number_format($r['s']);
echo "<tr><th>Total expenses<th>".number_format($xt)."</table><h3>Occupancy</h3><table><tr><th>Property<th>Occupied<th>Vacant<th>Rate";
foreach(q("SELECT p.property_name,COALESCE(SUM(un.status='occupied'),0) o,COALESCE(SUM(un.status='vacant'),0) v FROM properties p LEFT JOIN units un ON un.property_id=p.property_id WHERE p.landlord_id=?$pf GROUP BY p.property_id",[$L]) as $r)echo "<tr><td>".e($r['property_name'])."<td>{$r['o']}<td>{$r['v']}<td>".(($r['o']+$r['v'])?round($r['o']/($r['o']+$r['v'])*100):0)."%";
echo "</table><h3>Maintenance summary</h3><table><tr><th>Status<th>Requests this month";
foreach(q("SELECT r.status,COUNT(*) n FROM maintenance_requests r JOIN units un ON un.unit_id=r.unit_id JOIN properties p ON p.property_id=un.property_id WHERE p.landlord_id=? AND DATE_FORMAT(r.date_submitted,'%Y-%m')=?$pf GROUP BY r.status",[$L,$mo]) as $r)echo "<tr><td>{$r['status']}<td>{$r['n']}";
echo "</table><h3>Arrears (as of today)</h3><table><tr><th>Unit<th>Tenant<th>Owed<th>Days overdue<th>1-30 days<th>31-60 days<th>61+ days";
$pm=array_column(q('SELECT l.lease_id,un.property_id FROM leases l JOIN units un ON un.unit_id=l.unit_id')->fetchAll(),'property_id','lease_id');
foreach(arrears($L) as $a){if($a['balance']<=0||($pid&&($pm[$a['lease_id']]??0)!=$pid))continue;
 echo "<tr><td>".e($a['unit_number'])."<td>".e($a['full_name'])."<td>".number_format($a['balance'])."<td>{$a['days']}<td>".number_format($a['b1'])."<td>".number_format($a['b2'])."<td>".number_format($a['b3']);}
echo "</table>";
