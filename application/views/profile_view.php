
<div class="container profile">
	<?php if (isset($userData) && $userData['id']==$profile['id']): ?>
	<table cellpadding="0" cellspacing="0">
		<tr><td class="imgCell">
			<img src="<?=$profile['photo']?>">
		</td><td class="userInfo">
			<h2><?=$profile['firstName']." ".$profile['lastName']?></h2>
			<div>
				<?php if ($checkins['count'] != 0): ?>
					<div>Your delivery_ready url: http://www.wasden.com/cs462/driver/event/<?=$userData['id']?></div>
					<form action="<?=base_url()?>driver/savePhone/<?=$userData['id']?>" method="post">
						Your phone number: <input name="phone" value="<?=$phone?>">
						<button type="submit">Save</button>
					</form><br>
					<div>your checkins (<?=$checkins['count']?>):</div>
					<?php foreach ($checkins['items'] as $checkin): ?>
						<div class="checkin">
							<div class="right"><?=$checkin['venue']['location']['city'].", ".$checkin['venue']['location']['state']?></div>
							<h3><?=$checkin['venue']['name']?></h3>
							<div><?=isset($checkin['shout'])?$checkin['shout']:""?></div>
						</div>
					<?php endforeach; ?>
				<?php else: ?>
					<h3>You have 0 checkins</h3>
				<?php endif; ?>	
			</div>
		</td></tr>
	</table>
	<?php else: ?>
	<table cellpadding="0" cellspacing="0">
		<tr><td class="imgCell">
			<img src="<?=$profile['photo']?>">
		</td><td class="userInfo">
			<h2><?=$profile['firstName']." ".$profile['lastName']?></h2>
			<div>
				<?php if ($checkins['count'] != 0): ?>
					<div>most recent checkin:</div>
					<div class="checkin">
						<h3><?=$checkins['items'][0]['venue']['name']?></h3>
						<div><?=isset($checkins['items'][0]['shout'])?$checkins['items'][0]['shout']:""?></div>
					</div>
				<?php else: ?>
					<h3>0 checkins</h3>
				<?php endif; ?>	
			</div>
		</td></tr>
	</table>
	<?php endif; ?>
</div>