var ajaxhost = "";

function parseSelectFeedChange(res) {
  jQuery('#feedPageBody').html(res);
}

function parseUpdateSetting(res) {
  jQuery('#updateSettingMessage').html(res);
}

function doSelectFeed() {
  jQuery.ajax({
	type: "post",
	url: ajaxhost + "core/dialogs/fetchselectfeed.php",
	data: "feedtype=" + jQuery('#selectFeedType').val(),
	success: function(res){parseSelectFeedChange(res)}
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