<?php
	define('const_max_retrieve_items', 1000);
	
	$labels=multichain_labels();

	no_displayed_error_result($liststreams, multichain('liststreams', '*', true));
	no_displayed_error_result($getinfo, multichain('getinfo'));

	$subscribed=false;
	$viewstream=null;
	
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
											<a href="./?chain=<?php echo html($_GET['chain'])?>&page=<?php echo html($_GET['page'])?>&keys=<?php echo html($stream['createtxid'])?>">
												<?php echo $stream['keys']?>
											</a>
										</td>
									</tr>
									<tr>
										<th>Publishers</th>
										<td>
											<a href="./?chain=<?php echo html($_GET['chain'])?>&page=<?php echo html($_GET['page'])?>&publishers=<?php echo html($stream['createtxid'])?>">
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
				
<?php
	
		if (isset($_GET['keys'])) { // List of publications by an specific key
			$success=no_displayed_error_result($items, multichain('liststreamkeys', $_GET['keys'], '*', true));
		} else if (isset($_GET['publishers'])) { // List of publications by an specific key
			$success=no_displayed_error_result($items, multichain('liststreampublishers', $_GET['publishers'], '*', true));
		}
		
		if ($success) {
?>
			<div class="col-sm-8">
				<h3>Stream <?php echo html($viewstream['name'])?> &ndash; <?php echo count($items)?> of <?php echo count($items)?> <?php echo count($items) == 1 ? 'key' : 'keys'?></h3>
<?php
				$oneoutput=false;
				$items=array_reverse($items); // show most recent first
			
				foreach ($items as $item) {
					$oneoutput=true;
?>
					<table class="table table-bordered table-condensed table-striped table-break-words">
<?php 
						if (isset($item['key'])) {
?>
							<tr>
								<th>Key</th>
								<td>
									<a href="./?chain=<?php echo html($_GET['chain'])?>&page=view&stream=<?php echo html($viewstream['createtxid'])?>&key=<?php echo html($item['key'])?>">
										<?php echo html($item['key'])?>
									</a>
								</td>
							</tr>
<?php
						} else if (isset($item['publisher'])) {
?>						
							<tr>
								<th>Publisher</th>
								<td>
										<a href="./?chain=<?php echo html($_GET['chain'])?>&page=view&stream=<?php echo html($viewstream['createtxid'])?>&publisher=<?php echo html($item['publisher'])?>">
											<?php echo $item['publisher']?>
										</a>
								</td>
							</tr>
<?php	
						}
?>						
						<tr>
							<th>Items</th>
							<td>
								<?php echo $item['items']?>
							</td>
						</tr>

<?php
						if (isset($item['first']['data'])) {
?>						
							<tr>
								<th>
									Last data
								</th>
								<td>
									<?php echo html(pack('H*', $item['last']['data']))?>
								</td>
							</tr>
<?php	
						}
?>
					</table>
<?php
				}
				if (!$oneoutput) {
					echo '<p>No items in stream</p>';
				}
?>				
			</div>
<?php
		}
?>
	</div>