<?php

	$success=false; // set default value

	no_displayed_error_result($liststreams, multichain('liststreams', '*', true));

	$subscribed=false;
	$unsubscribed = false;
	
	foreach ($liststreams as $stream) {
		if (@$_POST['subscribe_'.$stream['createtxid']]) {
			if (no_displayed_error_result($result, multichain('subscribe', $stream['createtxid']))) {
				output_success_text('Successfully subscribed to stream: '.$stream['name']);
				$subscribed=true;
			}
		} else if (@$_POST['unsubscribe_'.$stream['createtxid']]) {
			if (no_displayed_error_result($result, multichain('unsubscribe', $stream['createtxid']))) {
				output_success_text('Successfully unsubscribed to stream: '.$stream['name']);
				$unsubscribed=true;
			}
		}
		if (@$_GET['keys']==$stream['createtxid'] || @$_GET['publishers']==$stream['createtxid']) {
			$viewstream=$stream;
		}
	}
			
	if ($subscribed || $unsubscribed) { // reload streams list
		no_displayed_error_result($liststreams, multichain('liststreams', '*', true));
	}

	if (@$_POST['createstream']) {
		if ($_POST['name']) {
			$success=no_displayed_error_result($createtxid, multichain('createfrom',
				$_POST['from'], 'stream', $_POST['name'], true));

			if ($success) {
				output_success_text('Stream successfully created in transaction '.$createtxid);
			}
		} else {
			output_rpc_error(array(code => "", message => "Name field is mandatory"));
		}
				
	}
	
	$labels=multichain_labels();

	if (no_displayed_error_result($getaddresses, multichain('getaddresses', true))) {
		foreach ($getaddresses as $index => $address)
			if (!$address['ismine'])
				unset($getaddresses[$index]);
				
		if (no_displayed_error_result($listpermissions,
			multichain('listpermissions', 'create', implode(',', array_get_column($getaddresses, 'address')))
		))
			$createaddresses=array_unique(array_get_column($listpermissions, 'address'));
	}
	
	no_displayed_error_result($liststreams, multichain('liststreams', '*', true));

?>

	<div class="row">
		<!-- RIGHT SIDE STREAM LIST -->
		<div class="col-sm-4">
			<form method="post" action="./?chain=<?php echo html($_GET['chain'])?>&page=<?php echo html($_GET['page'])?>">
<?php
				for ($subscribed=1; $subscribed>=0; $subscribed--) { // SUBSCRIBED 1 => SUBSCRIBED STREAMS; SUBSCRIBED 0 => UNSUBSCRIBED STREAMS
?>
					<h3><?php echo $subscribed ? 'Subscribed streams' : 'Other streams'?></h3>
<?php
					foreach ($liststreams as $stream) {
						if ($stream['subscribed']==$subscribed) {
?>
							<table class="table table-bordered table-condensed table-break-words table-striped">
								<tr>
									<th style="width:30%;">Name</th>
<?php
									if ($subscribed) {
?>	
										<td>
											<?php echo html($stream['name'])?>
											&nbsp; <input class="btn btn-default btn-xs" type="submit" name="unsubscribe_<?php echo html($stream['createtxid'])?>" value="Unsubscribe">
										</td>
<?php
									} else {
										$parts=explode('-', $stream['streamref']);
										if (is_numeric($parts[0])) {
											$suffix=' ('.($getinfo['blocks']-$parts[0]+1).' blocks)';
										} else {
											$suffix='';
										}
?>	
										<td><?php echo html($stream['name'])?> &nbsp; <input class="btn btn-default btn-xs" type="submit" name="subscribe_<?php echo html($stream['createtxid'])?>" value="Subscribe"></td>
<?php
									}
?>
								</tr>
								<tr>
									<th>Created by</th>
									<td class="td-break-words small"><?php echo format_address_html($stream['creators'][0], false, $labels)?></td>
								</tr>
<?php
								if ($subscribed) {
?>
									<tr>
										<th>Keys</th>
										<td>
											<a href="./?chain=<?php echo html($_GET['chain'])?>&page=keys&keys=<?php echo html($stream['createtxid'])?>">
												<?php echo $stream['keys']?>
											</a>
										</td>
									</tr>
									<tr>
										<th>Publishers</th>
										<td>
											<a href="./?chain=<?php echo html($_GET['chain'])?>&page=keys&publishers=<?php echo html($stream['createtxid'])?>">
												<?php echo $stream['publishers']?>
											</a>
										</td>
									</tr>
<?php
								}
?>
							</table>
<?php
						}
					}
				}
?>
			</form>
		</div>
		
		<div class="col-sm-8">
			<h3>Create Stream</h3>
			
			<form class="form-horizontal" method="post" action="./?chain=<?php echo html($_GET['chain'])?>&page=<?php echo html($_GET['page'])?>">
				<div class="form-group">
					<label for="from" class="col-sm-2 control-label">From address:</label>
					<div class="col-sm-9">
						<select class="form-control col-sm-6" name="from" id="from">
<?php
							foreach ($createaddresses as $address) {
?>
								<option value="<?php echo html($address)?>"><?php echo format_address_html($address, true, $labels)?></option>
<?php
							}
?>						
						</select>
					</div>
				</div>
				<div class="form-group">
					<label for="name" class="col-sm-2 control-label">Stream name:</label>
					<div class="col-sm-9">
						<input class="form-control" name="name" id="name" placeholder="stream1">
						<span id="helpBlock" class="help-block">In this demo, the stream will be open, so anyone can write to it.</span>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-9">
						<input class="btn btn-default" type="submit" name="createstream" value="Create Stream">
					</div>
				</div>
			</form>
		</div>
	</div>
