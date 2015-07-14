var ajaxhost = "";
var category_lookup_timer;
//the commands are WordPress defaults, declared as variables so Joomla can replace them
var cmdFetchCategory = "core/ajax/wp/fetch_category.php";
var cmdFetchLocalCategories = "core/ajax/wp/fetch_local_categories.php";
var cmdFetchTemplateDetails = "core/ajax/wp/fetch_template_details.php";
var cmdGetFeed = "core/ajax/wp/get_feed.php";
var cmdGetFeedStatus = "core/ajax/wp/get_feed_status.php";
var cmdMappingsErase = "core/ajax/wp/attribute_mappings_erase.php";
var cmdRemember = "core/ajax/wp/update_remember.php";
var cmdSearsPostByRestAPI = "core/ajax/wp/sears_post.php";
var cmdSaveAggregateFeedSetting = "core/ajax/wp/save_aggregate_feed_setting.php";
var cmdSelectFeed = "core/ajax/wp/select_feed.php";
var cmdSetAttributeOption = "core/ajax/wp/attribute_mappings_update.php";
var cmdSetAttributeUserMap = "core/ajax/wp/attribute_user_map.php";
var cmdUpdateAllFeeds = "core/ajax/wp/update_all_feeds.php"
var cmdUpdateSetting = "core/ajax/wp/update_setting.php";
var cmdUploadFeed = "core/ajax/wp/upload_feed.php";
var cmdUploadFeedStatus = "core/ajax/wp/upload_feed_status.php";
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

function parseUploadFeedResults(res, provider) {

    //Stop the intermediate status interval
    window.clearInterval(feedFetchTimer);
    feedFetchTimer = null;
    jQuery('#feed-error-display2').html("");
    jQuery('#feed-status-display2').html("Uploading feed...");

    var results = jQuery.parseJSON(res);

    //Show results
    if (results.url.length > 0) {
        jQuery('#feed-error-display2').html("&nbsp;");
        //window.open(results.url);
        var data = {content: results.url, provider: provider};
        jQuery('.remember-field').each(function () {
            data[this.name] = this.value;
        });

        /** DO INVENTORY UPLOAD HERE **/
        if (provider == 'amazonsc') {
            jQuery.ajax({
                type: 'post',
                url: ajaxhost + cmdUploadFeed,
                data: data,
                success: function(result) {
                    console.log('success');
                    console.log(result);
                    parseUploadFeedResultStatus(data, result);
                },
                error: function(result) {
                    console.log('error');
                    console.log(result);
                }
            });
        } else if (provider == 'ebayupload') {
            jQuery.ajax({
                type: 'post',
                url: ajaxhost + cmdUploadFeed,
                data: data,
                success: function(result) {
                    console.log('success');
                    console.log(result);
                    parseUploadFeedResultStatus(data, result);
                },
                error: function(result) {
                    console.log('error');
                    console.log(result);
                }
            });
        } else console.log(provider);
    }
    if (results.errors.length > 0) {
        jQuery('#feed-error-display2').html(results.errors);
        jQuery('#feed-status-display2').html("");
    }
}

function parseUploadFeedResultStatus(data, id) {

    if (data.provider == 'amazonsc') {
        if (isNaN(result)) {
            var errors = JSON.parse(result);
            jQuery('#feed-status-display2').html("");
            jQuery('#feed-error-display2').html("ERROR: " + errors['Caught Exception']);
        } else {
            data['feedid'] = result;
            jQuery.ajax({
                type: 'post',
                url: ajaxhost + cmdUploadFeedStatus,
                data: data,
                success: function(result) {
                    console.log('success');
                    console.log(result);
                    jQuery('#feed-status-display2').html(result);
                },
                error: function(result) {
                    console.log('error');
                    console.log(result);
                }
            });
        }
    } else if (data.provider == 'ebayupload') {
        console.log(result);
    }
}

function parseGetFeedStatus(res) {
	if (feedFetchTimer != null)
		jQuery('#feed-status-display').html(res);
}

function parseUploadFeedStatus(res) {
    if (feedFetchTimer != null)
        jQuery('#feed-status-display2').html(res);
}

function parseLicenseKeyChange(res) {
	jQuery("#tblLicenseKey").remove();
}

function parseSelectFeedChange(res) {
  jQuery('#feedPageBody').html(res);
	doFetchLocalCategories();
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
		//window.location.reload();
	}
}

function doFetchCategory(service_name, partial_data) {
	var shopID = jQuery("#edtRapidCartShop").val();
	if (shopID == null)
		shopID = "";

	jQuery.ajax({
		type: "post",
		url: ajaxhost + cmdFetchCategory,
		data: {service_name: service_name, partial_data: partial_data, shop_id: shopID},
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
	var shopID = jQuery("#edtRapidCartShop").val();
	if (shopID == null)
		shopID = "";

	jQuery.ajax({
		type: "post",
		url: ajaxhost + cmdFetchLocalCategories,
		data: {shop_id: shopID},
		success: function(res){parseFetchLocalCategories(res)}
	});
}

function doUploadFeed(provider, service, userid) {

    jQuery('#feed-error-display2').html("Uploading feed...");
    var thisDate = new Date();
    feedIdentifier = thisDate.getTime();

    var shopID = jQuery("#edtRapidCartShop").val();
    if (shopID == null)
        shopID = "";

    var data = {userid: userid, remember: jQuery("#remember").is(":checked"), provider: service};

    jQuery('.remember-field').each(function () {
        data[this.name] = this.value;
    });

    jQuery.ajax({
        type: "post",
        url: ajaxhost + cmdRemember,
        data: data,
        success: function() {

        }
    });

    jQuery.ajax({
        type: "post",
        url: ajaxhost + cmdGetFeed,
        data: {
            provider: provider,
            local_category: jQuery('#local_category').val(),
            remote_category: jQuery('#remote_category').val(),
            file_name: jQuery('#feed_filename').val(),
            feed_identifier: feedIdentifier,
            feed_id: feed_id,
            shop_id: shopID},
        success: function(res){
            parseUploadFeedResults(res, provider)
        }
    });
    feedFetchTimer = window.setInterval(function(){updateUploadFeedStatus()}, 500);
}

function doGetFeed(provider) {
	jQuery('#feed-error-display').html("Generating feed...");
	var thisDate = new Date();
	feedIdentifier = thisDate.getTime();

	var shopID = jQuery("#edtRapidCartShop").val();
	if (shopID == null)
		shopID = "";

	jQuery.ajax({
		type: "post",
		url: ajaxhost + cmdGetFeed,
		data: {
			provider: provider, 
			local_category: jQuery('#local_category').val(), 
			remote_category: jQuery('#remote_category').val(),
			file_name: jQuery('#feed_filename').val(), 
			feed_identifier: feedIdentifier, 
			feed_id: feed_id, 
			shop_id: shopID},
		success: function(res){parseGetFeedResults(res)}
	});
	feedFetchTimer = window.setInterval(function(){updateGetFeedStatus()}, 500);
}

function doGetAlternateFeed(provider) {

	jQuery('#feed-error-display').html("Generating feed...");
	var thisDate = new Date();
	feedIdentifier = thisDate.getTime();

	var feeds = new Array;
	jQuery(".feedSetting:checked").each(function() {
		feeds.push(jQuery(this).val());
	});

	var shopID = jQuery("#edtRapidCartShop").val();
	if (shopID == null)
		shopID = "";

	jQuery.ajax({
		type: "post",
		url: ajaxhost + cmdGetFeed,
		data: {
			provider: provider, 
			local_category: "0", 
			remote_category: "0",
			file_name: jQuery('#feed_filename').val(), 
			feed_identifier: feedIdentifier, 
			feed_id: feed_id, 
			shop_id: shopID,
			feed_ids: feeds},
		success: function(res){parseGetFeedResults(res)}
	});
	feedFetchTimer = window.setInterval(function(){updateGetFeedStatus()}, 500);
}

function doSelectCategory(category, option, service_name) {
	var shopID = jQuery("#edtRapidCartShop").val();
	if (shopID == null)
		shopID = "";
	document.getElementById("categoryDisplayText").value = category.innerHTML;
	document.getElementById("remote_category").value = option;
	document.getElementById("categoryList").style.display="none";
	document.getElementById("categoryList").style.border = "0px";

	if (service_name == "Amazonsc" || service_name == "kelkoo") {
		//The user has just selected a template.
		//Therefore, we must reload the Optional / Required Mappings
		jQuery.ajax({
			type: "post",
			url: ajaxhost + cmdFetchTemplateDetails,
			data: {shop_id: shopID, template: option, provider: service_name},
			success: function(res){
				jQuery("#attributeMappings").html(res);
			}
		});
	}
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
	//in Joomla, this message is hidden, so unhide
	jQuery('#update-message').css({
		"display": "block"
		});
	jQuery.ajax({
		type: "post",
		url: ajaxhost + cmdUpdateAllFeeds,
		data: "",
		success: function(res){
				jQuery('#update-message').html(res);
			}
	});
}

function doUpdateSetting(source, settingName) {
	//Note: Value must always come last... 
	//and &amp after value will be absorbed into value
	if (jQuery("#cbUniqueOverride").attr('checked') == 'checked')
		unique_setting = '&feedid=' + feed_id;
	else
		unique_setting = '';
	var shopID = jQuery("#edtRapidCartShop").val();
	jQuery.ajax({
		type: "post",
		url: ajaxhost + cmdUpdateSetting,
		data: "setting=" + settingName + unique_setting + "&shop_id=" + shopID + "&value=" + jQuery("#" + source).val(),
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

function searsPostByRestAPI() {
	jQuery.ajax({
		type: "post",
		url: ajaxhost + cmdSearsPostByRestAPI,
		data: {username: jQuery("#edtUsername").val(), password: jQuery("#edtPassword").val()},
		success: function(res){searsPostByRestAPIResults(res)}
	});
}

function searsPostByRestAPIResults(res) {

}

function setAttributeOption(service_name, attribute, select_index) {
	jQuery.ajax({
		type: "post",
		url: ajaxhost + cmdSetAttributeOption,
		data: "service_name=" + service_name + "&attribute=" + attribute + '&mapto=' + jQuery('#attribute_select' + select_index).val(),
	});
}

function setAttributeOptionV2(sender) {
	var service_name = jQuery(sender).attr('service_name');
	var attribute_name = jQuery(sender).val();
	var mapto = jQuery(sender).attr('mapto');
	var shopID = jQuery("#edtRapidCartShop").val();
	if (shopID == null)
		shopID = "";
	jQuery.ajax({
		type: "post",
		url: ajaxhost + cmdSetAttributeUserMap,
		data: {service_name: service_name, attribute: attribute_name, mapto: mapto, shop_id: shopID}
	});
}

function submitLicenseKey(keyname) {
	var r = alert("License field will disappear if key is successful. Please reload the page.");
	jQuery.ajax({
		type: "post",
		url: ajaxhost + cmdUpdateSetting,
		data: {setting: keyname, value: jQuery("#edtLicenseKey").val()},
		success: function(res){parseLicenseKeyChange(res)}
	});
	//window.location.reload(1);
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
	toggleButton.innerHTML = "[ Close Advanced Commands ] ";
	document.getElementById("feed-advanced").style.display = "inline";
  } else {
    //Close the dialog
	toggleButton.innerHTML = "[ Open Advanced Commands ] ";
	document.getElementById("feed-advanced").style.display = "none";
  }
}

function toggleOptionalAttributes() {
  toggleButton = document.getElementById("toggleOptionalAttributes");

  if ( toggleButton.innerHTML.indexOf("h") > 0 ) {
    //Open the dialog
	toggleButton.innerHTML = "[Hide] Additional Attributes";
	document.getElementById("optional-attributes").style.display = "inline";
  } else {
    //Close the dialog
	toggleButton.innerHTML = "[Show] Additional Attributes";
	document.getElementById("optional-attributes").style.display = "none";
  }
}//toggleOptionalAttributes

function toggleRequiredAttributes() {
  toggleButton = document.getElementById("required-attributes");

  if ( toggleButton.style.display == "none" ) {
    //Open the dialog
	document.getElementById("required-attributes").style.display = "inline";
  } else {
    //Close the dialog
	document.getElementById("required-attributes").style.display = "none";
  }
}//toggleRequiredAttributes
function updateGetFeedStatus() {
	jQuery.ajax({
		type: "post",
		url: ajaxhost + cmdGetFeedStatus,
		data: "feed_identifier=" + feedIdentifier,
		success: function(res){parseGetFeedStatus(res)}
	});
}