<?php $venue = $user['last_checkin']['venue']; ?>
<div class="container">
	<img src="<?=$user['photoUrl']?>">
	<div class="inline pad">
		<h1><?=$user['firstName'].' '.$user['lastName']?></h1>
		<?=single_field_form("Phone","phone",base_url()."app/savePhone",$user['phone'])?>
		<div>Last Checkin: <span><?=$venue['name'].' - '.$venue['location']['city'].', '.$venue['location']['state']?></span></div>
		<br><div><?=$user['id']?></div>
	</div>
</div>