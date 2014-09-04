var ajaxhost = "";
var category_lookup_timer;
//the commands are WordPress defaults, declared as variables so Joomla can replace them
var cmdFetchCategory = "core/ajax/wp/fetch_category.php";
var cmdFetchLocalCategories = "core/ajax/wp/fetch_local_categories.php";
var cmdGetFeed = "core/ajax/wp/get_feed.php";
var cmdGetFeedStatus = "core/ajax/wp/get_feed_status.php";
var cmdMappingsErase = "core/ajax/wp/attribute_mappings_erase.php";
var cmdSelectFeed = "core/ajax/wp/select_feed.php";
var cmdSetAttributeOption = "core/ajax/wp/attribute_mappings_update.php";
var cmdUpdateAllFeeds = "core/ajax/wp/update_all_feeds.php"
var cmdUpdateSetting = "core/ajax/wp/update_setting.php";
var feedIdentifier = 0; //A value we create and inform the server of that allows us to track errors during feed generation
var feed_id = 0; //A value the server gives us if we're in a feed that exists already. Will be needed when we want to set overrides specific to this feed
var feedFetchTimer = null;
var localCategories = {children: []};

function parseFetchCategoryResult(res) {
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

function parseFetchLocalCategories(res) {
	localCategories = jQuery.parseJSON(res);
}

function parseGetFeedResults(res) {
	//Stop the intermediate status interval
	window.clearInterval(feedFetchTimer);
	feedFetchTimer = null;
	jQuery('#feed-status-display').html("");

	results = jQuery.parseJSON(res);

	//Show results
	if (results.url.length > 0) {
		jQuery('#feed-error-display').html("&nbsp;");
		window.open(results.url);
	}
	if (results.errors.length > 0)
		jQuery('#feed-error-display').html(results.errors);
}

function parseGetFeedStatus(res) {
	if (feedFetchTimer != null)
		jQuery('#feed-status-display').html(res);
}

function parseLicenseKeyChange(res) {
	jQuery("#tblLicenseKey").remove();
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

function doFetchLocalCategories() {
	jQuery.ajax({
		type: "post",
		url: ajaxhost + cmdFetchLocalCategories,
		success: function(res){parseFetchLocalCategories(res)}
	});
}

function doGetFeed(provider) {
	jQuery('#feed-error-display').html("Generating feed...");
	thisDate = new Date();
	feedIdentifier = thisDate.getTime();
	if (feed_id != 0)
		strFeedID = "&feed_id=" + feed_id;
	else
		strFeedID = "";

	jQuery.ajax({
		type: "post",
		url: ajaxhost + cmdGetFeed,
		data: "provider=" + provider + "&local_category=" + jQuery('#local_category').val() + 
				"&remote_category=" + jQuery('#remote_category').val() + "&file_name=" + jQuery('#feed_filename').val() +
				"&feed_identifier=" + feedIdentifier + strFeedID,
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

function doSelectLocalCategory(id) {

	//Build a list of checked boxes
	var category_string = "";
	var category_ids = "";
	jQuery(".cbLocalCategory").each(
		function(index) {
			tc = document.getElementById(jQuery(this).attr('id'));
			if (tc.checked) {
			//if (jQuery(this).attr('checked') == 'checked') {
				category_string += jQuery(this).val() + ", ";
				category_ids += jQuery(this).attr('category') + ",";
			}
		}
	);

	//Trim the trailing commas
	category_ids = category_ids.substring(0, category_ids.length - 1);
	category_string = category_string.substring(0, category_string.length - 2);

	//Push the results to the form
	jQuery("#local_category").val(category_ids);
	jQuery("#local_category_display").val(category_string);

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
	jQuery('#update-message').html("Updating feeds...");
	jQuery.ajax({
		type: "post",
		url: ajaxhost + cmdUpdateAllFeeds,
		data: "",
		success: function(res){jQuery('#update-message').html(res);}
	});
}

function doUpdateSetting(source, settingName) {
	if (jQuery("#cbUniqueOverride").attr('checked') == 'checked')
		unique_setting = '&feedid=' + feed_id;
	else
		unique_setting = '';
	jQuery.ajax({
		type: "post",
		url: ajaxhost + cmdUpdateSetting,
		data: "setting=" + settingName + unique_setting + "&value=" + jQuery("#" + source).val(),
		success: function(res){parseUpdateSetting(res)}
	});
}

function getLocalCategoryBranch(branch, gap, chosen_categories) {
	var result = '';
	var span = '<span style="width: ' + gap + 'px; display: inline-block;">&nbsp;</span>';
	for (var i = 0; i < branch.length; i++) {
		if (jQuery.inArray( branch[i].id, chosen_categories) > -1)
			checkedState = ' checked="true"';
		else
			checkedState = '';
		result += '<div>' + span + '<input type="checkbox" class="cbLocalCategory" id="cbLocalCategory' + branch[i].id + '" value="' + branch[i].title + 
			'" onclick="doSelectLocalCategory(' + branch[i].id + ')" category="' + branch[i].id + '"' + checkedState + ' />' + branch[i].title + '(' + branch[i].tally + ')</div>';
		result += getLocalCategoryBranch(branch[i].children, gap + 20, chosen_categories);
	}
	return result;
}

function getLocalCategoryList(chosen_categories) {
	return getLocalCategoryBranch(localCategories.children, 0, chosen_categories);
}

function setAttributeOption(service_name, attribute, select_index) {
	jQuery.ajax({
		type: "post",
		url: ajaxhost + cmdSetAttributeOption,
		data: "service_name=" + service_name + "&attribute=" + attribute + '&mapto=' + jQuery('#attribute_select' + select_index).val(),
	});
}

function submitLicenseKey(keyname) {
	jQuery.ajax({
		type: "post",
		url: ajaxhost + cmdUpdateSetting,
		data: "setting=" + keyname + "&value=" + jQuery("#edtLicenseKey").val(),
		success: function(res){parseLicenseKeyChange(res)}
	});
}

function showEraseConfirmation(res) {
  //alert("Attribute Mappings Cleared"); //Dropped message and just reloaded instead
	if (document.getElementById("selectFeedType") == null)
		jQuery(".attribute_select").val("");
	else
		doSelectFeed();
}

function showLocalCategories(provider) {
	chosen_categories = jQuery("#local_category").val();
	chosen_categories = chosen_categories.split(",");
	jQuery.colorbox({html:"<div class='categoryListLocalFrame'><div class='categoryListLocal'><h1>Categories</h1>" + getLocalCategoryList(chosen_categories) + "</div></div>"});
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