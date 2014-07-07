var ajaxhost = "";
var category_lookup_timer;
//the cmd's are WordPress defaults
var cmdFetchCategory = "core/ajax/wp/fetch_category.php";
var cmdGetFeed = "core/ajax/wp/get_feed.php";
var cmdGetFeedStatus = "core/ajax/wp/get_feed_status.php";
var cmdMappingsErase = "core/ajax/wp/attribute_mappings_erase.php";
var cmdSelectFeed = "core/ajax/wp/select_feed.php";
var cmdSetAttributeOption = "core/ajax/wp/attribute_mappings_update.php";
var cmdUpdateAllFeeds = "core/ajax/wp/update_all_feeds.php"
var cmdUpdateSetting = "core/ajax/wp/update_setting.php";
var feedIdentifier = 0;
var feedFetchTimer = null;

function parseFetchCategoryResult(res) {
	//jQuery('#categoryList').html(res);
	document.getElementById("categoryList").innerHTML = res;
	if (res.length > 0) {
		document.getElementById("categoryList").style.border = "1px solid #A5ACB2";
		document.getElementById("categoryList").style.display = "inline";
	} else {
		document.getElementById("categoryList").style.border = "0px";
		document.getElementById("categoryList").style.display = "none";
		document.getElementById("remote_category").value = "";
	}
}

function parseGetFeedResults(res) {
	//Stop the intermediate status interval
	window.clearInterval(feedFetchTimer);
	feedFetchTimer = null;
	jQuery('#feed-status-display').html("");

	//Show results
  if (res.indexOf("Success:") > -1) {
		jQuery('#feed-error-display').html("&nbsp;");
		var url = res.substring(9);
		window.open(url);
	} else
		jQuery('#feed-error-display').html(res);
}

function parseGetFeedStatus(res) {
	if (feedFetchTimer != null)
		jQuery('#feed-status-display').html(res);
}

function parseSelectFeedChange(res) {
  jQuery('#feedPageBody').html(res);
}

function parseUpdateSetting(res) {
  jQuery('#updateSettingMessage').html(res);
}

function doEraseMappings(service_name) {
	var r = confirm("This will clear your current Attribute Mappings including saved Maps from previous attributes. Proceed?");
	if (r == true) {
		jQuery.ajax({
			type: "post",
			url: ajaxhost + cmdMappingsErase,
			data: "service_name=" + service_name,
			success: function(res){showEraseConfirmation(res)}
		});
	}
}

function doFetchCategory(service_name, partial_data) {
	jQuery.ajax({
		type: "post",
		url: ajaxhost + cmdFetchCategory,
		data: "service_name=" + service_name + "&partial_data=" + partial_data,
		success: function(res){parseFetchCategoryResult(res)}
	});
}

function doFetchCategory_timed(service_name, partial_data) {
	if (!category_lookup_timer) {
		window.clearTimeout(category_lookup_timer);
	}

	category_lookup_timer = setTimeout(function(){doFetchCategory(service_name, partial_data)}, 100);
}

function doGetFeed(provider) {
	jQuery('#feed-error-display').html("Generating feed...");
	thisDate = new Date();
	feedIdentifier = thisDate.getTime();
	jQuery.ajax({
		type: "post",
		url: ajaxhost + cmdGetFeed,
		data: "provider=" + provider + "&local_category=" + jQuery('#local_category').val() + 
				"&remote_category=" + jQuery('#remote_category').val() + "&file_name=" + jQuery('#feed_filename').val() +
				"&feed_identifier=" + feedIdentifier,
		success: function(res){parseGetFeedResults(res)}
	});
	feedFetchTimer = window.setInterval(function(){updateGetFeedStatus()}, 500);
}

function doSelectCategory(category, option) {
	document.getElementById("categoryDisplayText").value = category.innerHTML;
	document.getElementById("remote_category").value = option;
	//document.getElementById("categoryList").innerHTML = "";
	document.getElementById("categoryList").style.display="none";
	document.getElementById("categoryList").style.border = "0px";
}

function doSelectFeed() {
	jQuery.ajax({
		type: "post",
		url: ajaxhost + cmdSelectFeed,
		data: "feedtype=" + jQuery('#selectFeedType').val(),
		success: function(res){parseSelectFeedChange(res)}
	});
}

function doUpdateAllFeeds() {
	jQuery.ajax({
		type: "post",
		url: ajaxhost + cmdUpdateAllFeeds,
		data: "",
	});
}

function doUpdateSetting(source, settingName) {
	jQuery.ajax({
		type: "post",
		url: ajaxhost + cmdUpdateSetting,
		data: "setting=" + settingName + "&value=" + jQuery("#" + source).val(),
		success: function(res){parseUpdateSetting(res)}
	});
}

function setAttributeOption(service_name, attribute, select_index) {
	jQuery.ajax({
		type: "post",
		url: ajaxhost + cmdSetAttributeOption,
		data: "service_name=" + service_name + "&attribute=" + attribute + '&mapto=' + jQuery('#attribute_select' + select_index).val(),
	});
}

function showEraseConfirmation(res) {
  //alert("Attribute Mappings Cleared"); //Dropped message and just reloaded instead
  doSelectFeed();
}

function toggleAdvancedDialog() {
  toggleButton = document.getElementById("toggleAdvancedSettingsButton");

  if (toggleButton.innerHTML.indexOf("O") > 0) {
    //Open the dialog
	//toggleButton.innerHTML = "[ - ] ";
	toggleButton.innerHTML = "[ Close Advanced Commands ] ";
	document.getElementById("feed-advanced").style.display = "inline";
  } else {
    //Close the dialog
	//toggleButton.innerHTML = "[ + ] ";
	toggleButton.innerHTML = "[ Open Advanced Commands ] ";
	document.getElementById("feed-advanced").style.display = "none";
  }
}

function updateGetFeedStatus() {
	jQuery.ajax({
		type: "post",
		url: ajaxhost + cmdGetFeedStatus,
		data: "feed_identifier=" + feedIdentifier,
		success: function(res){parseGetFeedStatus(res)}
	});
}