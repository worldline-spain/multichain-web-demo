<?php
	if (isset($_GET['address'])) {
    $offset = isset($_GET['offset']) ? isset($_GET['offset']) : 0;
    $numTransactions = 100;
    no_displayed_error_result($addressTransactions, multichain('listaddresstransactions', $_GET['address'], $numTransactions, $offset));
    $addressTransactions=array_reverse($addressTransactions); // show most recent first
  }
?>
  <div>
    <div class="row">
      <div class="col-sm-4">
        <!-- ADDRESSES FROM OUR NODE -->
        <h3>My Addresses</h3>
<?php
        if (no_displayed_error_result($getaddresses, multichain('getaddresses', true))) {
          $addressmine=array();
      
          foreach ($getaddresses as $getaddress) {
            $addressmine[$getaddress['address']]=$getaddress['ismine'];
          }

          $addresspermissions=array();
      
          if (no_displayed_error_result($listpermissions, multichain('listpermissions', 'all', implode(',', array_keys($addressmine))))) {
            foreach ($listpermissions as $listpermission) {
              $addresspermissions[$listpermission['address']][$listpermission['type']]=true;
            }
          }
                  
          $labels=multichain_labels();
    
          foreach ($addressmine as $address => $ismine) {
            if (count(@$addresspermissions[$address])) {
              $permissions=implode(', ', @array_keys($addresspermissions[$address]));
            } else {
              $permissions='none';
            }	
            $label=@$labels[$address];
            $cansetlabel=$ismine && @$addresspermissions[$address]['send'];
        
            if ($ismine && !$cansetlabel) {
              $permissions.=' (cannot set label)';
            }
?>
            <table class="table table-bordered table-condensed table-break-words <?php echo ($address==@$getnewaddress) ? 'bg-success' : 'table-striped'?>">
              <tr>
                <th style="width:30%;">Label</th>
                <td>
                  <?php echo (isset($label) || $cansetlabel) ? html(@$label) : '-'?>
                </td>
              </tr>
              <tr>
                <th style="width:30%;">Address</th>
                <td class="td-break-words small">
                  <a href="./?chain=<?php echo html($_GET['chain'])?>&page=<?php echo html($_GET['page'])?>&address=<?php echo html($address)?>">
                    <?php echo html($address)?><?php echo $ismine ? '' : ' (watch-only)'?>
                  </a>
                </td>
              </tr>
              <tr>
                <th>Permissions</th>
                <td>
                  <?php echo html($permissions)?>
                  <?php echo ' &ndash; <a href="'.chain_page_url_html($chain, 'permissions', array('address' => $address)).'">change</a>'?>
                </td>
              </tr>
            </table>
<?php
          }
        }
?>

        <!-- OTHER ADDRESSES -->
        <h3>Other Addresses</h3>
<?php
			  if (no_displayed_error_result($peerinfo, multichain('getpeerinfo'))) {
?>
				  <table class="table table-bordered table-striped table-break-words">
<?php
            if (count($peerinfo)) {
              foreach ($peerinfo as $peer) {
                ?>
              <tr>
                <th style="width:30%;">Address</th>
                <td class="td-break-words small">
                  <a href="./?chain=<?php echo html($_GET['chain'])?>&page=<?php echo html($_GET['page'])?>&address=<?php echo html($peer['handshake'])?>">
                    <?php echo html($address)?><?php echo $ismine ? '' : ' (watch-only)'?>
                  </a>
                </td>
              </tr>
              <tr>
                <th>IP address</th>
                <td><?php echo html(strtok($peer['addr'], ':'))?></td>
              </tr>
<?php
              }
            } else {
?>
              <?php echo html('There are no addresses conected') ?>

<?php
            }
?>
				  </table>
<?php	
	      }
?>
      </div>
<?php 
      if (isset($addressTransactions)) { // SHOWING TRANSACTIONS FOR ADDRESS ON QUERY PARAMS
?>
        <div class="col-sm-8">
          <h3>Address <?php echo html($_GET['address'])?> transactions</h3>
<?php
            foreach($addressTransactions as $transaction) {
?>
              <table class="table table-bordered table-striped table-break-words">
                <tr>
                  <th style="width:30%;">From</th>
                  <td><?php echo format_address_html($transaction['myaddresses'][0], in_array($transaction['myaddresses'][0], $addressmine), $labels)?></td>
                  <!--td><?php //echo html($transaction['myaddresses'][0])?></td-->
                </tr>
<?php 
                if ($transaction['addresses'][0]) { // SHOWING TRANSACTIONS FOR ADDRESS ON QUERY PARAMS
?>
                  <tr>
                    <th style="width:30%;">To</th>
                    <td><?php echo format_address_html($transaction['addresses'][0], in_array($transaction['myaddresses'][0], $addressmine), $labels)?></td>
                    <!--td><?php //echo html($transaction['addresses'][0])?></td-->
                  </tr>
<?php 
                }
?>
                <tr>
                  <th style="width:30%;">Type</th>
<?php
                  if ($transaction['items'][0]['type'] == 'stream') { // STREAMS
                    $item = $transaction['items'][0];
?>
                      <td><?php echo html('Stream')?></td>
                    </tr>
                    <tr>
                      <th style="width:30%;">Name</th>
                      <td><?php echo html($item['name'])?></td>
                    </tr>
                    <tr>
                      <th style="width:30%;">Key</th>
                      <td><?php echo html($item['key'])?></td>
                    </tr>
                    <tr>
                      <th style="width:30%;">Data</th>
                      <td><?php echo html(pack('H*', $item['data']))?></td>
                    </tr>
<?php
                 } else if (isset($transaction['balance']['assets'][0])) { // ASSET TRANSACTION
?>
                      <td><?php echo html('Asset')?></td>
                    </tr>
<?php
                    foreach($transaction['balance']['assets'] as $assetTransaction) { 
?>
                      <tr>
                        <th style="width:30%;">Name</th>
                        <td><?php echo html($assetTransaction['name'])?></td>
                      </tr>
                      <tr>
                        <th style="width:30%;">Quantity</th>
                        <td><?php echo html($assetTransaction['qty'])?></td>
                      </tr>
<?php
                    }
                    if ($transaction['data'][0]) {
?>
                      <tr>
                        <th style="width:30%;">Data</th>
                        <td><?php echo html(pack('H*', $transaction['data'][0]))?></td>
                      </tr>
<?php
                    }

                  } else if ($transaction['issue']) { // ASSET ISSUE
                    $issue = $transaction['issue'];
?>
                      <td><?php echo html('Issue')?></td>
                    </tr>
                    <tr>
                      <th style="width:30%;">Name</th>
                      <td><?php echo html($issue['name'])?></td>
                    </tr>
                    <tr>
                      <th style="width:30%;">Units</th>
                      <td><?php echo html($issue['units'])?></td>
                    </tr>
                    <tr>
                      <th style="width:30%;">Open</th>
                      <td><?php echo html($issue['open'] ? 'true' : 'false')?></td>
                    </tr>
                    <tr>
                      <th style="width:30%;">Quantity</th>
                      <td><?php echo html($issue['qty'])?></td>
                    </tr>
<?php
                  } else if (isset($transaction['permissions'])) { // PERMISSION TRANSACTION
?> 
                      <td><?php echo html('Permission')?></td>
                    </tr>
                    <tr>
                      <th>Action</th>
                      <td><?php echo html($transaction['permissions'][0]['endblock'] ? 'Grant' : 'Revoke')?></td>
                    </tr>
<?php
                    $totalPermissions = '';
                    $addedAtLeastOne = false;
                    foreach($transaction['permissions'][0] as $permissionKey => $permissionValue) {
                      if ($permissionValue && in_array($permissionKey, ['connect', 'send', 'receive', 'create', 'issue', 'mine', 'admin'])) {
                        $comma = $addedAtLeastOne ? ', ' : '';
                        $totalPermissions = $totalPermissions.$comma.$permissionKey;
                        $addedAtLeastOne = true;
                      }
                    }
?>
                    <tr>
                      <th>Permissions</th>
                      <td><?php echo html($totalPermissions)?></td>
                    </tr>
<?php                  
                  } else {
?> 
                      <td><?php echo html('???')?></td>
                    </tr>
<?php                  
                  }
?>
                <tr>
                  <th style="width:30%;">Txid</th>
                  <td><?php echo html($transaction['txid'])?></td>
                </tr>
                <tr>
                  <th>Added</th>
                  <td><?php echo gmdate('Y-m-d H:i:s', isset($transaction['blocktime']) ? $transaction['blocktime'] : $transaction['time'])?> GMT<?php echo isset($transaction['blocktime']) ? ' (confirmed)' : ''?></td>
                </tr>
              </table>
<?php
            }
?>
          </table>
        </div>
<?php 
      }
?>  
    </div>
  </div>
