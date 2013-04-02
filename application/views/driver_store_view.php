<div class="pad">
<?php
	echo "<h2>ESLs</h2>";
	puke($user['esls'],"esls",array("id"));
	echo "<br>";
	single_field_form("New ESL:","esl",base_url()."app/newESL");
?>
</div>


