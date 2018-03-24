<?php
	
	require_once 'functions.php';
	
	$config=read_config();
	$chain=@$_GET['chain'];
	
	if (strlen($chain))
		$name=@$config[$chain]['name'];
	else
		$name='';

?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		
		<title>MultiChain Demo</title>
		<!--
			<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
			<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
		-->
		<link rel="stylesheet" href="bootstrap.min.css">
		<link rel="stylesheet" href="styles.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
	</head>
	<body>
		<div class="container">
			<h1><a href="./">MultiChain Demo</a><?php if (strlen($name)) { ?> &ndash; <?php echo html($name)?><?php } ?></h1>
<?php
	if (strlen($chain)) {
		$name=@$config[$chain]['name'];
?>
			
			<nav class="navbar navbar-default">
				<div id="navbar" class="navbar-collapse collapse">
					<ul class="nav navbar-nav">
						
						<li><a class="pair-first <?php echo $_GET['page'] == null ? 'active': ''?>" href="./?chain=<?php echo html($chain)?>">Node</a></li>
						<li class="pair-first pair-second"> | </li>
						<li><a class="pair-first pair-second <?php echo $_GET['page'] == 'addresses' ? 'active': ''?>" href="./?chain=<?php echo html($chain)?>&page=addresses">Addresses</a></li>
						<li class="pair-first pair-second"> | </li>
						<li><a class="pair-second <?php echo $_GET['page'] == 'permissions' ? 'active': ''?>" href="./?chain=<?php echo html($chain)?>&page=permissions">Permissions</a></li>

						<li><a class="pair-first <?php echo $_GET['page'] == 'assets' ? 'active': ''?>" href="./?chain=<?php echo html($chain)?>&page=assets">Assets</a></li>
						<li class="pair-first pair-second"> | </li>
						<li><a class="pair-first pair-second <?php echo $_GET['page'] == 'issue' ? 'active': ''?>" href="./?chain=<?php echo html($chain)?>&page=issue">Issue Asset</a></li>
						<li class="pair-first pair-second"> | </li>
						<li><a class="pair-second <?php echo $_GET['page'] == 'update' ? 'active': ''?>" href="./?chain=<?php echo html($chain)?>&page=update">Update Asset</a></li>
						
						<!--li><a href="./?chain=<?php echo html($chain)?>&page=offer" class="pair-first">Create Offer</a></li>
						<li><a href="./?chain=<?php echo html($chain)?>&page=accept" class="pair-second">| Accept</a></li-->
						
						<li><a class="pair-first <?php echo $_GET['page'] == 'create' ? 'active': ''?>" href="./?chain=<?php echo html($chain)?>&page=create">Create Stream</a></li>
						<li class="pair-first pair-second"> | </li>
						<li><a class="pair-first pair-second <?php echo $_GET['page'] == 'keys' ? 'active': ''?>" href="./?chain=<?php echo html($chain)?>&page=keys">Stream Keys</a></li>
						<li class="pair-first pair-second"> | </li>
						<li><a class="pair-second <?php echo $_GET['page'] == 'view' ? 'active': ''?>" href="./?chain=<?php echo html($chain)?>&page=view">Stream Transactions</a></li>
						
						<li><a class="<?php echo $_GET['page'] == 'send' ? 'active': ''?>" href="./?chain=<?php echo html($chain)?>&page=send">Send</a></li>
						<li><a class="<?php echo $_GET['page'] == 'publish' ? 'active': ''?>" href="./?chain=<?php echo html($chain)?>&page=publish">Publish</a></li>
					</ul>
				</div>
			</nav>

<?php
		set_multichain_chain($config[$chain]);
		
		switch (@$_GET['page']) {
			case 'addresses':
			case 'permissions':
			case 'assets':
			case 'issue':
			case 'update':
			case 'send':
			case 'offer':
			case 'accept':
			case 'create':
			case 'publish':
			case 'view':
			case 'keys':
			case 'asset-file':
				require_once 'page-'.$_GET['page'].'.php';
				break;
				
			default:
				require_once 'page-default.php';
				break;
		}
		
	} else {
?>
			<p class="lead"><br/>Choose an available node to get started:</p>
		
			<p>
<?php
		foreach ($config as $chain => $rpc)
			if (isset($rpc['rpchost']))
				echo '<p class="lead"><a href="./?chain='.html($chain).'">'.html($rpc['name']).'</a><br/>';
?>
			</p>
<?php
	}
?>
		</div>
	</body>
</html>