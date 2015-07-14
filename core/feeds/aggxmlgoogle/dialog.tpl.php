<?php global $pfcore; ?>
<div class="attributes-mapping">
	<div id="poststuff">
		<div class="postbox" style="width: 98%;">

			<!-- *************** 
					Page Header 
					****************** -->

			<h3 class="hndle"><?php echo $this->service_name_long; ?></h3>
			<div class="inside export-target">

				<!-- *************** 
						LEFT SIDE 
						****************** -->

			<div class="feed-left">

				<table cellspacing="5">
				<tr>
					<th>id</th>
					<th></th>
					<th>Type</th>
					<th>Filename</th>
					<th>#Products</th>
				</tr>
				<?php foreach ($this->feeds as $index => $thisFeed): ?>
					<tr>
						<td><?php echo $thisFeed->id; ?></td>
						<td><input type="checkbox" class="feedSetting" name="feedChoice<?php echo $index; ?>" value="<?php echo $thisFeed->id; ?>" <?php echo $thisFeed->checkedString; ?> /></td>
						<td><?php echo $thisFeed->type; ?></td>
						<td><?php echo $thisFeed->filename; ?></td>
						<td><?php echo $thisFeed->product_count; ?></td>
					</tr>
				<?php endforeach; ?>
				</table>

			</div>

				<!-- *************** 
						RIGHT SIDE 
						****************** -->

				<div class="feed-right">

					<!-- ROW 3: Filename -->
					<div class="feed-right-row">
						<span class="label">File name for feed : </span>
						<span ><input type="text" name="feed_filename" id="feed_filename" class="text_big" value="<?php echo $this->initial_filename; ?>" /></span>
					</div>
					<div class="feed-right-row">
						<label>* If you use an existing file name, the file will be overwritten.</label>
					</div>

					<!-- ROW 4: Get Feed Button -->
					<div class="feed-right-row">
						<input type="hidden" name="RequestCode" value="<?php echo $this->service_name; ?>" />
						<input class="cupid-green" type="button" onclick="doGetAlternateFeed('<?php echo $this->servName; ?>')" value="Get Feed" />
						<div id="feed-error-display">&nbsp;</div>
						<div id="feed-status-display">&nbsp;</div>
					</div>
				</div>

				<!-- *************** 
						Termination DIV
						****************** -->

				<div style="clear: both;">&nbsp;</div>

				<!-- *************** 
						FOOTER
						****************** -->

	</div>
</div>