<?php global $pfcore; ?>
<script type="text/javascript">
jQuery( document ).ready(function() {		
		var shopID = jQuery("#edtRapidCartShop").val();
		if (shopID == null)
			shopID = "";
		var template = jQuery("#remote_category").val();
		if (template != null && template.length > 0) {
			jQuery.ajax({
				type: "post",
				url: ajaxhost + cmdFetchTemplateDetails,
				data: {shop_id: shopID, template: template, provider: "amazonsc"},
				success: function(res){
					jQuery("#attributeMappings").html(res);
				}
			});
		}
	});
</script>
<div class="attributes-mapping">
	<div id="poststuff">
		<div class="postbox">

			<!-- *************** 
					Page Header 
					****************** -->

			<h3 class="hndle"><?php echo $this->service_name_long; ?></h3>
			<div class="inside export-target">

				<!-- *************** 
						LEFT SIDE 
						****************** -->

				<!-- Attribute Mapping DropDowns -->
				<div class="feed-left" id="attributeMappings">
					<label for="categoryDisplayText">
					<br>Templates differ by region, please refer to the table below. <br>
					Type the template name in the field on the right.</label>
					<br/>
					<div class='amazon-template-table'>
					<table>
					<tr>
						<th>Region</th>
						<th>Template prefix</th>
						<th>Example</th>
					</tr>
					<tr>
						<td>US</td>
						<td>Type template as is</td>
						<td>Clothing</td>
					</tr>
					<tr>
						<td>UK / GB</td>
						<td>UK/</td>
						<td>UK/ Home</td>
					</tr>
					<tr>
						<td>Spain</td>
						<td>ES/</td>
						<td>ES/ Books</td>
					</tr>
					<tr>
						<td>France</td>
						<td>FR/</td>
						<td>FR/ Lighting</td>
					</tr>
					<tr>					
						<td>Germany</td>
						<td>DE/</td>
						<td>DE/ Sports</td>
					</tr>
					<tr>						
						<td>Italy</td>
						<td>IT/</td>
						<td>IT/ Luggage</td>
					</tr>
					</table>
					</div>
					<p>Example: To target the "Amazon UK Home template", type: UK/ Home<br>Template names are in English</p>
					<?php //echo $this->attributeMappings(); ?>
				</div>

				<!-- *************** 
						RIGHT SIDE 
						****************** -->

				<div class="feed-right">

					<!-- ROW 1: Local Categories -->
					<div class="feed-right-row">
						<span class="label"><?php echo $pfcore->cmsPluginName; ?> Category : </span>
						<?php echo $this->localCategoryList; ?>
					</div>

					<!-- ROW 2: Remote Categories -->
					<?php echo $this->line2(); ?>
					<div class="feed-right-row">
						<?php echo $this->categoryList($initial_remote_category); ?>
					</div>

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
						<input class="cupid-green" type="button" onclick="doGetFeed('<?php echo $this->service_name; ?>')" value="Get Feed" />
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

				<div>
					<label class="un_collapse_label" title="Advanced" id="toggleAdvancedSettingsButton" onclick="toggleAdvancedDialog()">[ Open Advanced Commands ]</label>
					<label class="un_collapse_label" title="Erase existing mappings" id="erase_mappings" onclick="doEraseMappings('<?php echo $this->service_name; ?>')">[ Reset Attribute Mappings ]</label>
				</div>


				<div class="feed-advanced" id="feed-advanced">
					<textarea class="feed-advanced-text" id="feed-advanced-text"><?php echo $this->advancedSettings; ?></textarea>
					<?php echo $this->cbUnique; ?>
					<button class="navy_blue_button" id="bUpdateSetting" name="bUpdateSetting" onclick="doUpdateSetting('feed-advanced-text', 'cp_advancedFeedSetting-<?php echo $this->service_name; ?>'); return false;" >Update</button>
					<div id="updateSettingMessage">&nbsp;</div>
				</div>
			</div>
		</div>
	</div>
</div>