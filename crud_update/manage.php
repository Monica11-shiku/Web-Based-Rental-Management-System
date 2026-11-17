<?php require 'config.php';$u=need('landlord');$L=$u['landlord_id'];$m='';$T=['bedsitter','single room','one-bedroom','two-bedroom+'];
if($_POST){$a=$_POST['a']??'';$id=(int)($_POST['id']??0);
 if($a=='pedit'){$n=trim($_POST['name']);if($n==='')$m='<span class=err>Name required</span>';else{q('UPDATE properties SET property_name=?,location=? WHERE property_id=? AND landlord_id=?',[$n,trim($_POST['loc']),$id,$L]);$m='<span class=ok>Property saved</span>';}}
 elseif($a=='pdel'){if(q('SELECT COUNT(*) c FROM units WHERE property_id=?',[$id])->fetch()['c'])$m='<span class=err>Delete or move this property\'s units first</span>';else{q('DELETE FROM properties WHERE property_id=? AND landlord_id=?',[$id,$L]);$m='<span class=ok>Property deleted</span>';}}
 elseif($a=='uedit'){$r=(float)$_POST['rent'];if(!in_array($_POST['type'],$T)||$r<=0||trim($_POST['num'])==='')$m='<span class=err>Check the unit number, type and rent</span>';else{q('UPDATE units un JOIN properties p ON p.property_id=un.property_id SET un.unit_number=?,un.unit_type=?,un.rent_amount=? WHERE un.unit_id=? AND p.landlord_id=?',[trim($_POST['num']),$_POST['type'],$r,$id,$L]);$m='<span class=ok>Unit saved. Existing leases keep their own rent.</span>';}}
 elseif($a=='udel'){$x=q('SELECT un.photo,un.video FROM units un JOIN properties p ON p.property_id=un.property_id WHERE un.unit_id=? AND p.landlord_id=?',[$id,$L])->fetch();
  if(!$x)$m='<span class=err>Unit not found</span>';
  elseif(q('SELECT COUNT(*) c FROM leases WHERE unit_id=?',[$id])->fetch()['c'])$m='<span class=err>Cannot delete: this unit has tenants or payment history</span>';
  else{q('DELETE FROM units WHERE unit_id=?',[$id]);foreach($x as $f)if($f)@unlink(__DIR__."/uploads/$f");$m='<span class=ok>Unit deleted</span>';}}}
head('Edit properties & units');echo "<p>$m</p>";
foreach(q('SELECT * FROM properties WHERE landlord_id=? ORDER BY property_name',[$L]) as $p){
 echo "<div class=card><form method=post>".csrf()."<input type=hidden name=id value={$p['property_id']}><input name=name value='".e($p['property_name'])."'><input name=loc value='".e($p['location'])."'><button name=a value=pedit>Save property</button><button name=a value=pdel onclick=\"return confirm('Delete this property?')\">Delete property</button></form><table><tr><th>Unit<th>Type<th>Rent<th>Status<th>";
 foreach(q('SELECT * FROM units WHERE property_id=? ORDER BY unit_number',[$p['property_id']]) as $r){
  $o='';foreach($T as $t)$o.="<option".($t==$r['unit_type']?' selected':'').">$t";
  echo "<tr><form method=post>".csrf()."<input type=hidden name=id value={$r['unit_id']}><td><input name=num value='".e($r['unit_number'])."'><td><select name=type>$o</select><td><input name=rent type=number min=1 step=0.01 value='{$r['rent_amount']}'><td>{$r['status']}<td><button name=a value=uedit>Save</button><button name=a value=udel onclick=\"return confirm('Delete this unit?')\">Delete</button></form>";}
 echo "</table></div>";}
