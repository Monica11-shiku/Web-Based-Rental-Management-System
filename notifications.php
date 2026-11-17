<?php require 'config.php';$u=need();
if($_POST)q('UPDATE notifications SET is_read=1 WHERE user_id=?',[$u['id']]);
head('Notifications');
$rows=q('SELECT * FROM notifications WHERE user_id=? ORDER BY notification_id DESC LIMIT 100',[$u['id']])->fetchAll();
if(!$rows)echo "<p>No notifications yet.</p>";
else{echo "<form method=post>".csrf()."<button>Mark all as read</button></form><table><tr><th>Date<th>Message";
 foreach($rows as $r)echo "<tr".($r['is_read']?'':' style=background:#2a2418')."><td>{$r['date_sent']}<td>".($r['is_read']?e($r['message']):'<b>'.e($r['message']).'</b>');
 echo "</table>";}
