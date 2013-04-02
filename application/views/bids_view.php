<div class="pad">
<?php
	echo "<h2>Deliveries Ready</h2>";
	puke($user['bidsIn'],"bidsIn",array("id"), "bids");
	echo "<h2>Bids Sent</h2>";
	puke($user['bidsOut'],"bidsOut",array("id"), "bids");
?>
</div>