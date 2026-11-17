<?php require 'config.php';$u=need();$L=$u['landlord_id'];head('Dashboard');
function kes($n){return 'KES '.number_format($n);}
if($u['role']=='tenant'){
 $t=q("SELECT l.lease_id,l.rent_amount,un.unit_number,p.property_name FROM tenants t JOIN leases l ON l.tenant_id=t.tenant_id JOIN units un ON un.unit_id=l.unit_id JOIN properties p ON p.property_id=un.property_id WHERE t.user_id=? AND l.status='active'",[$u['id']])->fetch();
 if(!$t)exit('<p>No active lease.</p>');
 $b=0;foreach(arrears($L) as $a)if($a['lease_id']==$t['lease_id'])$b=$a['balance'];
 echo "<div class=grid><div class=card><span>Your unit</span><b>".e($t['unit_number'])."</b><small>".e($t['property_name'])."</small></div><div class=card><span>Monthly rent</span><b>".kes($t['rent_amount'])."</b></div><div class=card><span>".($b>0?'Balance due':'Balance')."</span><b>".kes($b)."</b><small><a href=pay.php>Pay rent</a></small></div></div><h3>Recent payments</h3><table><tr><th>Date<th>Receipt<th>Amount";
 foreach(q("SELECT pm.payment_date,pm.receipt_number,pm.amount FROM payments pm JOIN tenants t ON t.tenant_id=pm.tenant_id WHERE t.user_id=? AND pm.status='successful' ORDER BY pm.payment_id DESC LIMIT 5",[$u['id']]) as $r)echo "<tr><td>{$r['payment_date']}<td>".e($r['receipt_number'])."<td>".kes($r['amount']);
 exit('</table>');}
$J="FROM payments pm JOIN leases l ON l.lease_id=pm.lease_id JOIN units un ON un.unit_id=l.unit_id JOIN properties p ON p.property_id=un.property_id WHERE p.landlord_id=? AND pm.status='successful'";
$c=q("SELECT SUM(un.status='occupied') o,SUM(un.status='vacant') v FROM units un JOIN properties p ON p.property_id=un.property_id WHERE p.landlord_id=?",[$L])->fetch();$o=(int)$c['o'];$v=(int)$c['v'];$occ=($o+$v)?round($o/($o+$v)*100):0;
$col=(float)q("SELECT COALESCE(SUM(pm.amount),0) s $J AND DATE_FORMAT(pm.payment_date,'%Y%m')=DATE_FORMAT(NOW(),'%Y%m')",[$L])->fetch()['s'];
$exp=(float)q("SELECT COALESCE(SUM(c.amount),0) s FROM rent_charges c JOIN leases l ON l.lease_id=c.lease_id JOIN units un ON un.unit_id=l.unit_id JOIN properties p ON p.property_id=un.property_id WHERE p.landlord_id=? AND DATE_FORMAT(c.period,'%Y%m')=DATE_FORMAT(NOW(),'%Y%m')",[$L])->fetch()['s'];
$pct=$exp?min(100,round($col/$exp*100)):0;
$rep=q("SELECT COUNT(*) n FROM maintenance_requests r JOIN units un ON un.unit_id=r.unit_id JOIN properties p ON p.property_id=un.property_id WHERE p.landlord_id=? AND r.status<>'resolved'",[$L])->fetch()['n'];
$df=array_filter(arrears($L),fn($a)=>$a['balance']>0&&$a['days']>0);$owed=array_sum(array_column($df,'balance'));
$ms=[];for($i=5;$i>=0;$i--)$ms[date('Y-m',strtotime(date('Y-m-01')." -$i month"))]=0;
foreach(q("SELECT DATE_FORMAT(pm.payment_date,'%Y-%m') m,SUM(pm.amount) s $J GROUP BY m",[$L]) as $r)if(isset($ms[$r['m']]))$ms[$r['m']]=(float)$r['s'];$mx=max($ms)?:1;
echo "<div class=grid><div class=card><span>Collected this month</span><b>".kes($col)."</b><small>$pct% of ".kes($exp)." expected</small><div class=bar><i style=width:$pct%></i></div></div>
<div class=card><span>Occupancy</span><b>$occ%</b><small>$o occupied &middot; $v vacant</small><div class=bar><i style=width:$occ%></i></div></div>
<div class=card><span>Owed by defaulters</span><b>".kes($owed)."</b><small>".count($df)." overdue</small></div>
<div class=card><span>Open repairs</span><b>$rep</b><small><a href=maintenance.php>View requests</a></small></div></div>
<div class=card><span>Last 6 months</span><div class=chart>";
foreach($ms as $m=>$s)echo "<div><i style='height:".round($s/$mx*100)."%' title='".kes($s)."'></i>".date('M',strtotime("$m-01"))."</div>";
echo "</div></div><h3>Defaulters</h3><table><tr><th>Unit<th>Tenant<th>Owed<th>Days overdue";
foreach($df as $a)echo "<tr><td>".e($a['unit_number'])."<td>".e($a['full_name'])."<td>".kes($a['balance'])."<td><span class=pill>{$a['days']} days</span>";
echo "</table>";
$vac=q("SELECT un.*,p.property_name,p.location FROM units un JOIN properties p ON p.property_id=un.property_id WHERE p.landlord_id=? AND un.status='vacant'",[$L])->fetchAll();
$base=(isset($_SERVER['HTTPS'])?'https':'http').'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']).'/vacancies.php';
echo "<h3>Vacant units</h3>";
if(!$vac)echo "<p>No vacant units.</p>";
else{echo "<div class=grid>";foreach($vac as $r){$txt=rawurlencode("Vacant: {$r['property_name']} {$r['unit_number']} ({$r['unit_type']}), KES ".number_format($r['rent_amount'])."/month, {$r['location']}. Apply: $base");
 echo "<div class=card>".unitimg($r)."<b>".e($r['property_name'].' '.$r['unit_number'])."</b><small>".e($r['unit_type'])." &middot; ".kes($r['rent_amount'])." &middot; ".e($r['location'])."</small><p><a href='https://wa.me/?text=$txt' target=_blank rel=noopener>Share on WhatsApp</a></p></div>";}echo "</div>";}
echo "<p><a href=photos.php>Manage photos</a> &middot; <a href=vacancies.php target=_blank>Public vacancies page</a></p>";
