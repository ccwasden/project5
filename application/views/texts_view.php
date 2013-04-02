<div class="pad">
<?php
	echo "<h2>Texts In</h2>";
	puke($user['textsIn'],"textsIn",array("id"),"texts");
	echo "<h2>Texts Out</h2>";
	puke($user['textsOut'],"textsOut",array("id"),"texts");
?>
</div>