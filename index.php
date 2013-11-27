<!DOCTYPE html>
<html>
<head>
	<title>CSS Sprites Generator</title>
	<style>
		body {
			font-family: sans-serif;
		}
		.h {
			padding: 1em 20px;
			color: #07B54F;
			background: #EEE;
			text-shadow: 0 1x 0 #FFF;
			border-radius: 3px;
		}
		.h.s {
			padding: 0.6em 20px;
			color: #888;
			font-size: 1.2em;
			background: #F6F6F6;
		}
		.b {
			color: #07B54F;
		}
		.h.e {
			color: #F00;
		}
		
		.heading {
			margin: 2em 0 3em;
			color: #07B54F;
		}
		.heading, .form {
			text-align: center;
		}
		.form hr {
			margin: 1em 20%;
			opacity: 0.15;
		}
		.form label, .form input {
			display: inline-block;
			vertical-align: middle;
			min-width: 240px;
		}
		.form form > div {
			margin: 0.8em 0;
		}
		.form label {
			margin-right: 1em;
			text-align: left;
			cursor: pointer;
		}
		.form input {
			padding: 0.44em 0.66em;
		}
		.form button {
			display: inline-block;
			padding: 0.7em 2em;
			margin: 10px;
			cursor: pointer;
		}
		
		.by {
			position: fixed;
			width: 300px;
			bottom: 15px;
			left: 50%;
			margin-left: -150px;
			color: #999;
			font-size: 0.875em;
			text-align: center;
			z-index: 1;
		}
	</style>
</head>
<body>

<?php
	
	// Configuration
	$dir		= './set/';          ## Images Source Directory
	$sdir		= './sprites/';      ## Sprites Output Directory
	$sname		= 'icon';            ## CSS Base Class Name
	
	// Actions Switch
	switch( TRUE ) {
		
		// Create Sprites
		case ( $_POST AND isset( $_POST['sprites'] ) ) :

			require_once( './sprites.php' );
			
			foreach( $_POST as $v => $i )
			{
				if ( !empty( $i ) AND isset( ${$v} ) AND $$v != $i )
				{
					${$v} = $i;
				}
			}
			
			foreach( array( 'dir', 'sdir' ) as $var )
			{
				${$var}	= str_replace( '//', '/', ${$var} . '/' );
			}
			
			echo '<h1 class="h">Processing Files Found In `'. $dir .'` :</h1>';
			
			$sprites	= new images_to_sprite( $dir, $sdir . $sname, TRUE, TRUE );
			
			if ( $sprites->process() )
			{
				echo "<h2 class='h s'>Sprites successfuly created for <strong class='b'>`{$dir}`</strong>.</h2>";
			}else
			{
				echo "<h2 class='h s e'>Error! Images Directory is empty, or contains invalid files.</h2>";
			}
			break;
		
		
		// Display Actions & Configuration Form
		default:
			echo <<<BLOCK
	<div class="form">
		<h1 class="heading">CSS Sprites Generator</h1>
		<form action="" method="post">
			<div>
				<label for="inputSource">Source Directory:</label>
				<input name="dir" type="text" id="inputSource" value="{$dir}" />
			</div>
			<div>
				<label for="inputSprites">Sprites Directory:</label>
				<input name="sdir" type="text" id="inputSprites" value="{$sdir}" />
			</div>
			<div>
				<label for="inputBase">Base Class Name:</label>
				<input name="sname" type="text" id="inputBase" value="{$sname}" />
			</div>
			<hr />
			<div>
				<button type="submit" name="sprites">
					Create CSS Sprites
				</button>
			</div>
		</form>
	</div>
	
	<div class="by">Created By Mohamed Gamil - <a href="http://gemy.me/" target="_blank">Gemy.me</a>.</div>
BLOCK;

	}
	
?>

</body>
</html>