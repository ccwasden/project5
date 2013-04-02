
<div class="container">
<h1>Members</h1>
<ul class="memberList">
<?php foreach ($users as $user): ?><li>
	<img src="<?=$user['photo']?>">
	<div class="right profileLink"><a href="<?=base_url()?>driver/profile/<?=$user['id']?>">view profile</a></div>
	<div class="userInfo">
	<h3><?=$user['firstName']." ".$user['lastName']?></h3>
	<div><?php
		$cnt = $user['checkins']['count'];
		echo $cnt." Checkin".($cnt == 1 ? "" : "s");
		?>
	</div>
</li><?php endforeach; ?>
</ul>
</div>