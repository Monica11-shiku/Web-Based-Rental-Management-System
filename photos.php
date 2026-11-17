<?php require 'config.php';$u=need('landlord','caretaker');$L=$u['landlord_id'];$m='';
if($_POST){$id=(int)($_POST['unit']??0);
 $ok=q('SELECT 1 FROM units un JOIN properties p ON p.property_id=un.property_id WHERE un.unit_id=? AND p.landlord_id=?',[$id,$L])->fetch();
 if(isset($_FILES['video'])){$col='video';$f=$_FILES['video'];
  $t=($f['error']===0&&$f['size']<=20000000)?(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']):'';
  $ext=['video/mp4'=>'mp4','video/webm'=>'webm'][$t]??null;}
 else{$col='photo';$f=$_FILES['photo']??['error'=>1];
  $i=($f['error']===0&&$f['size']<=2000000)?@getimagesize($f['tmp_name']):false;
  $ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$i['mime']??'']??null;}
 if(!$ok||!$ext)$m='<span class=err>'.($col=='video'?'Choose an MP4 or WebM video under 20 MB':'Choose a JPG, PNG or WebP image under 2 MB').'</span>';
 else{@mkdir(__DIR__.'/uploads',0755);$n=bin2hex(random_bytes(8)).".$ext";
  if(move_uploaded_file($f['tmp_name'],__DIR__."/uploads/$n")){$old=q("SELECT $col c FROM units WHERE unit_id=?",[$id])->fetch()['c'];if($old)@unlink(__DIR__."/uploads/$old");q("UPDATE units SET $col=? WHERE unit_id=?",[$n,$id]);$m='<span class=ok>Saved</span>';}
  else $m='<span class=err>Could not save the file</span>';}}
head('Unit photos');echo "<p>$m</p><div class=grid>";
foreach(q("SELECT un.*,p.property_name FROM units un JOIN properties p ON p.property_id=un.property_id WHERE p.landlord_id=? ORDER BY p.property_name,un.unit_number",[$L]) as $r){
 $h="<input type=hidden name=unit value={$r['unit_id']}>";
 echo "<div class=card>".unitimg($r)."<b>".e($r['property_name'].' '.$r['unit_number'])."</b><small>{$r['status']}</small>
<form method=post enctype=multipart/form-data>".csrf()."$h<input type=file name=photo accept='image/jpeg,image/png,image/webp' required><button>Upload</button></form>
<form method=post enctype=multipart/form-data>".csrf()."$h<input type=file name=video accept='video/mp4,video/webm' required><button>Upload video</button></form></div>";}
echo "</div>";
