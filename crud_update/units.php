<?php require 'config.php';$u=need('landlord','caretaker');$L=$u['landlord_id'];$m='';
if($_POST&&$u['role']=='landlord'){
 if(isset($_POST['pname']))q('INSERT INTO properties(landlord_id,property_name,location) VALUES(?,?,?)',[$L,$_POST['pname'],$_POST['loc']]);
 elseif(q('SELECT 1 FROM properties WHERE property_id=? AND landlord_id=?',[$_POST['pid'],$L])->fetch()&&$_POST['rent']>0)q('INSERT INTO units(property_id,unit_number,unit_type,rent_amount) VALUES(?,?,?,?)',[$_POST['pid'],$_POST['num'],$_POST['type'],$_POST['rent']]);
 else $m='Invalid unit';}
head('Properties & units');echo "<p><a href=manage.php>Edit or delete properties and units</a></p>";echo "<p class=err>".e($m)."</p>";
$ps=q('SELECT * FROM properties WHERE landlord_id=?',[$L])->fetchAll();
if($u['role']=='landlord'){?><form method=post><?=csrf()?>Property name<input name=pname required>Location<input name=loc><button>Add property</button></form>
<form method=post><?=csrf()?><select name=pid><?php foreach($ps as $p)echo "<option value={$p['property_id']}>".e($p['property_name'])?></select>Unit no.<input name=num required><select name=type><option>bedsitter<option>single room<option>one-bedroom<option>two-bedroom+</select>Rent<input name=rent type=number min=1 required><button>Add unit</button></form><?php }
echo "<table><tr><th>Property<th>Unit<th>Type<th>Rent<th>Status";
foreach(q('SELECT p.property_name,un.* FROM units un JOIN properties p USING(property_id) WHERE p.landlord_id=? ORDER BY 1,unit_number',[$L]) as $r)echo "<tr><td>".e($r['property_name'])."<td>".e($r['unit_number'])."<td>{$r['unit_type']}<td>".number_format($r['rent_amount'])."<td>{$r['status']}";
echo "</table>";
