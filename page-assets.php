<?php
	define('const_max_retrieve_items', 1000);

	$success=false; // set default value

	no_displayed_error_result($listassets, multichain('listassets', '*', true));
	
	foreach ($listassets  as $asset) {
		if (@$_POST['subscribe_'.$asset['issuetxid']]) {
			if (no_displayed_error_result($result, multichain('subscribe', $asset['issuetxid']))) {
				output_success_text('Successfully subscribed to asset: '.$asset['name']);
				$subscribed=true;
			}
		} else if (@$_POST['unsubscribe_'.$asset['issuetxid']]) {
			if (no_displayed_error_result($result, multichain('unsubscribe', $asset['issuetxid']))) {
				output_success_text('Successfully unsubscribed to asset: '.$asset['name']);
				$unsubscribed=true;
			}
		}
			
		if (@$_GET['asset']==$asset['issuetxid']) {
			$viewasset=$asset;
		}
	}

	// Reload asset list
	if ($subscribed || $unsubscribed) {
		no_displayed_error_result($listassets, multichain('listassets', '*', true)); //reload
	}
?>

	<div class="row">
		<!-- LEFT SIDE SUBSCRIBED/OTHER ASSETS -->
		<div class="col-sm-4">
			<form method="post" action="./?chain=<?php echo html($_GET['chain'])?>&page=<?php echo html($_GET['page'])?>">
<?php
				for ($subscribed=1; $subscribed>=0; $subscribed--) {
?>
					<h3><?php echo $subscribed ? 'Subscribed assets' : 'Other assets'?></h3>
<?php
					foreach ($listassets as $asset) {
						if ($asset['subscribed']==$subscribed) {
							$name=$asset['name'];
							$issuer=$asset['issues'][0]['issuers'][0];
?>
							<table class="table table-bordered table-condensed table-break-words <?php echo ($success && ($name==@$_POST['name'])) ? 'bg-success' : 'table-striped'?>">
								<tr>
									<th style="width:30%;">Name</th>
									<td>
<?php 
										if ($asset['subscribed']) {
?>
											<a href="./?chain=<?php echo html($_GET['chain'])?>&page=<?php echo html($_GET['page'])?>&asset=<?php echo html($asset['issuetxid'])?>">
												<?php echo html($asset['name'])?>
											</a>
											&nbsp; <input class="btn btn-default btn-xs" type="submit" name="unsubscribe_<?php echo html($asset['issuetxid'])?>" value="Unsubscribe">
<?php
										} else {
?>
											<?php echo html($asset['name'])?> &nbsp; <input class="btn btn-default btn-xs" type="submit" name="subscribe_<?php echo html($asset['issuetxid'])?>" value="Subscribe">
<?php
										}
?>
										<?php echo $asset['open'] ? '' : '(closed)'?>
									</td>
								</tr>
								<tr>
									<th>Quantity</th>
									<td><?php echo html($asset['issueqty'])?></td>
								</tr>
								<tr>
									<th>Units</th>
									<td><?php echo html($asset['units'])?></td>
								</tr>
								<tr>
									<th>Issuer</th>
									<td class="td-break-words small"><?php echo format_address_html($issuer, @$keymyaddresses[$issuer], $labels)?></td>
								</tr>
<?php
								$details=array();
								$detailshistory=array();
				
								foreach ($asset['issues'] as $issue)
									foreach ($issue['details'] as $key => $value) {
										$detailshistory[$key][$issue['txid']]=$value;
										$details[$key]=$value;
									}
								
								if (count(@$detailshistory['@file'])) {
?>
									<tr>
										<th>File:</th>
										<td>
<?php
										$countoutput=0;
										$countprevious=count($detailshistory['@file'])-1;

										foreach ($detailshistory['@file'] as $txid => $string) {
											$fileref=string_to_fileref($string);
											if (is_array($fileref)) {
											
												$file_name=$fileref['filename'];
												$file_size=$fileref['filesize'];
												
												if ($countoutput==1) // first previous version
													echo '<br/><small>('.$countprevious.' previous '.(($countprevious>1) ? 'files' : 'file').': ';
												
												echo '<a href="./download-file.php?chain='.html($_GET['chain']).'&txid='.html($txid).'&vout='.html($fileref['vout']).'">'.
													(strlen($file_name) ? html($file_name) : 'Download').
													'</a>'.
													( ($file_size && !$countoutput) ? html(' ('.number_format(ceil($file_size/1024)).' KB)') : '');
												
												$countoutput++;
											}
										}
						
										if ($countoutput>1)
											echo ')</small>';								
?>
										</td>
									</tr>	
<?php
								}
		
								foreach ($details as $key => $value) {
									if ($key=='@file')
										continue;
?>
									<tr>
										<th>
											<?php echo html($key)?>
										</th>
										<td>
											<?php echo html($value)?>
<?php								
											if (count($detailshistory[$key])>1)
												echo '<br/><small>(previous values: '.html(implode(', ', array_slice(array_reverse($detailshistory[$key]), 1))).')</small>';
?>
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

		<!-- RIGHT SIDE SEE ASSET TRANSACTIONS -->
<?php 
		if (isset($_GET['asset'])) {
			$success=no_displayed_error_result($transactionList, multichain('listassettransactions', $viewasset['issuetxid'], false, const_max_retrieve_items));
			$transactionList = array_reverse($transactionList); // Sorting newest first
		} else if (isset($_GET['address'])) {
			$success=no_displayed_error_result($addressBalance, multichain('getaddressbalances', $_GET['address'], const_max_retrieve_items));
		}

		if ($success) {
			
			if (isset($_GET['asset'])) { // SHOWING SOME ASSET TRANSACTIONS
?>
				<div class="col-sm-8">
					<form method="post" action="./?chain=<?php echo html($_GET['chain'])?>&page=<?php echo html($_GET['page'])?>">
						<h3>Asset <?php echo html($viewasset['name'])?> &ndash; <?php echo count($transactionList)?> <?php echo count($transactionList) == 1 ? 'item' : 'items'?> transactions</h3>
<?php
						foreach($transactionList as $transaction) {
?>
							<table class="table table-bordered table-condensed table-break-words table-striped">
<?php
								foreach($transaction['addresses'] as $address => $quantity) {
?>
									<tr>
										<th style="width:17%;">Address</th>
										<td><a href="./?chain=<?php echo html($_GET['chain'])?>&page=<?php echo html($_GET['page'])?>&address=<?php echo html($address)?>"><?php echo html($address)?></a></td>
										<th style="width:17%;">Quantity</th>
										<td style="width:17%;"><?php echo $quantity?></td>
									</tr>
<?php
								}
?>	
<?php
								if ($transaction['data']) {
?>
									<tr>
										<th>Data</th>
										<td><?php echo html(pack('H*', $transaction['data'][0]))?></td>
									</tr>
<?php
								}
?>	
								<tr>
									<th>Received</th>
									<td><?php echo gmdate('Y-m-d H:i:s', $transaction['blocktime'])?> GMT<?php echo isset($transaction['blocktime']) ? ' (confirmed)' : '-'?></td>
								</tr>
							</table>
<?php
						}
?>
					</form>
				</div>
<?php
			} else if (isset($_GET['address'])) { //SHOWING ADDRESS BALANCES
?>
				<div class="col-sm-8">
<?php
					if (count($transactionList) > 0) {
?>					
						<form method="post" action="./?chain=<?php echo html($_GET['chain'])?>&page=<?php echo html($_GET['page'])?>">
							<h3>Address <?php echo html($_GET['address'])?> &ndash; asset balances</h3>
<?php
							foreach($addressBalance as $assetBalance) {
?>
								<table class="table table-bordered table-condensed table-break-words table-striped">
									<tr>
										<th>Name</th>
										<td><?php echo html($assetBalance['name'])?></td>
									</tr>
									<tr>
										<th>Quantity</th>
										<td><?php echo html($assetBalance['qty'])?></td>
									</tr>
								</table>
<?php
							}
?>
						</form>
<?php
					} else {
?>
						<h4>This address has no assets.</h4>
<?php
					}
?>
				</div>
<?php
			}
		}
?>
	</div>