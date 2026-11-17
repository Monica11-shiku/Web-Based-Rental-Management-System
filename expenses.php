<?php require 'config.php';$u=need('landlord','caretaker');$L=$u['landlord_id'];$m='';$C=['Repairs','Utilities','Security','Cleaning','Salaries','Other'];
$props=q('SELECT property_id,property_name FROM properties WHERE landlord_id=? ORDER BY property_name',[$L])->fetchAll();$ids=array_column($props,'property_id');
if($_POST){$a=$_POST['a']??'';
 if($a=='add'){$amt=(float)$_POST['amount'];$d=$_POST['date']??'';$pid=(int)($_POST['prop']??0);
  if($amt<=0||!in_array($_POST['cat'],$C)||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$d)||($pid&&!in_array($pid,$ids)))$m='<span class=err>Check the amount, category and date</span>';
  else{q('INSERT INTO expenses(landlord_id,property_id,category,amount,expense_date,description,created_by) VALUES(?,?,?,?,?,?,?)',[$L,$pid?:null,$_POST['cat'],$amt,$d,substr(trim($_POST['desc']),0,200),$u['id']]);$m='<span class=ok>Expense recorded</span>';}}
 elseif($a=='del'&&$u['role']=='landlord'){q('DELETE FROM expenses WHERE expense_id=? AND landlord_id=?',[(int)$_POST['id'],$L]);$m='<span class=ok>Expense deleted</span>';}}
head('Expenses');echo "<p>$m</p><form method=post>".csrf()."<input type=hidden name=a value=add>Property<select name=prop><option value=0>General (all properties)";
foreach($props as $p)echo "<option value={$p['property_id']}>".e($p['property_name']);
echo "</select>Category<select name=cat>";foreach($C as $c)echo "<option>$c";
echo "</select>Amount (KES)<input name=amount type=number min=1 step=0.01 required>Date<input name=date type=date value='".date('Y-m-d')."' required>Description<input name=desc maxlength=200><button>Record expense</button></form><table><tr><th>Date<th>Property<th>Category<th>Amount<th>Description<th>";
foreach(q("SELECT e.*,p.property_name FROM expenses e LEFT JOIN properties p ON p.property_id=e.property_id WHERE e.landlord_id=? ORDER BY e.expense_date DESC,e.expense_id DESC LIMIT 100",[$L]) as $r){
 echo "<tr><td>{$r['expense_date']}<td>".e($r['property_name']??'General')."<td>".e($r['category'])."<td>".number_format($r['amount'])."<td>".e($r['description'])."<td>";
 if($u['role']=='landlord')echo "<form method=post>".csrf()."<input type=hidden name=a value=del><input type=hidden name=id value={$r['expense_id']}><button onclick=\"return confirm('Delete this expense?')\">Delete</button></form>";}
echo "</table>";
