<!DOCTYPE html
	PUBLIC "-//W3C//DTD HTML 4.01//EN"
      "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<link rel="icon" 
      type="image/png" 
      href="http://www.wasden.com/ccw/favicon.png">
<head profile="http://www.w3.org/2005/10/profile">
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"> 
	<title>Driver Portal</title>
	
	<link type="text/css" href="<?php echo base_url();?>static/css/styles.css" rel="stylesheet"/>

</head>
<body>

		<?php if(isset($user)){ ?>
			<div class="header">
				<div class="menu right">
					<h3>Welcome, <?=$user['firstName']?></h3>
					<a href="<?=base_url()?>app/logout">logout</a>
				</div>
				<h1>Driver Portal</h1>
				<ul>
					<li><a href="<?=base_url()?>app/">profile</a></li>
					<li><a href="<?=base_url()?>app/esls">esls</a></li>
					<li><a href="<?=base_url()?>app/texts">texts</a></li>
					<li><a href="<?=base_url()?>app/bids">bids</a></li>
				</ul>
			</div>
		<?php } ?>
	

</div>