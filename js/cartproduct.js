var ajaxhost = "";
var category_lookup_timer;

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

function parseSelectFeedChange(res) {
  jQuery('#feedPageBody').html(res);
}

function parseUpdateSetting(res) {
  jQuery('#updateSettingMessage').html(res);
}

function doFetchCategory(service_name, partial_data) {
  jQuery.ajax({
	type: "post",
	url: ajaxhost + "core/dialogs/fetchcategory.php",
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
	url: ajaxhost + "core/dialogs/fetchselectfeed.php",
	data: "feedtype=" + jQuery('#selectFeedType').val(),
	success: function(res){parseSelectFeedChange(res)}
  });
}

function doUpdateAllFeeds() {
  jQuery.ajax({
	type: "post",
	url: ajaxhost + "core/feeds/updateAllFeedsNow.php",
	data: "",
  });
}

function doUpdateSetting(source, settingName) {
  jQuery.ajax({
	type: "post",
	url: ajaxhost + "core/classes/updatesetting.php",
	data: "setting=" + settingName + "&value=" + jQuery("#" + source).val(),
	success: function(res){parseUpdateSetting(res)}
  });
}

function setAttributeOption(service_name, attribute, select_index) {
  jQuery.ajax({
	type: "post",
	url: ajaxhost + "core/feeds/updateAttributeMappings.php",
	data: "service_name=" + service_name + "&attribute=" + attribute + '&mapto=' + jQuery('#attribute_select' + select_index).val(),
	//success: function(res){alert("success:" + res)}
  });
}

function toggleAdvancedDialog() {
  toggleButton = document.getElementById("toggleAdvancedSettingsButton");
  
  if (toggleButton.innerHTML.indexOf("+") > 0) {
    //Open the dialog
	toggleButton.innerHTML = "[ - ] ";
	document.getElementById("feed-advanced").style.display = "inline";
  } else {
    //Close the dialog
	toggleButton.innerHTML = "[ + ] ";
	document.getElementById("feed-advanced").style.display = "none";
  }
}