<?php
/////////////////////////////////////////////////////////////////////////
// Cash Converter Mod for LF                                           //
// (c) 2015 Sabrina Markon. PHPSiteScripts. All rights reserved.       //
// http://phpsitescripts.com                                           //
// sabrina.markon@gmail.com                                            //
// License does not permit copy/resale.                                //
/////////////////////////////////////////////////////////////////////////

require_once "../inc/filter.php";
// Prevent anyone who isn't logged in from opening this page
if(!isset($_SESSION["adminid"])) { exit; };

extract($_GET);
extract($_POST);

echo("<center><br><br>");

$settings_r = mysql_query("select * from sm_cashconverter_settings order by id limit 1");
$settings_rows =  mysql_num_rows($settings_r);
if ($settings_rows < 1)
{
echo "<p align=\"center\"><b>Credit-to-Cash converter settings are missing! Cannot continue.</b></p><br>";
exit;
}
if ($settings_rows > 0)
{
$cash_converter_form_html = mysql_result($settings_r,0,"cash_converter_form_html");
$cash_rate_per_surf_credit = mysql_result($settings_r,0,"cash_rate_per_surf_credit");
$percent_credits_forced_for_ads = mysql_result($settings_r,0,"percent_credits_forced_for_ads");
$minimum_credits_allowed_to_request = mysql_result($settings_r,0,"minimum_credits_allowed_to_request");
$maximum_credits_allowed_to_request = mysql_result($settings_r,0,"maximum_credits_allowed_to_request");
}

// Cash conversion requests and related admin settings as well as table design for cash converter form in members area.

if (isset($_POST['action']))
{
$action = $_POST['action'];
}

if ($action == "savesettings")
{
	if (isset($_POST['cash_rate_per_surf_credit']))
		{
		$cash_rate_per_surf_credit = $_POST['cash_rate_per_surf_credit'];
		}
	if (isset($_POST['percent_credits_forced_for_ads']))
		{
		$percent_credits_forced_for_ads = $_POST['percent_credits_forced_for_ads'];
		}
	if (isset($_POST['minimum_credits_allowed_to_request']))
		{
		$minimum_credits_allowed_to_request = $_POST['minimum_credits_allowed_to_request'];
		}
	if (isset($_POST['maximum_credits_allowed_to_request']))
		{
		$maximum_credits_allowed_to_request = $_POST['maximum_credits_allowed_to_request'];
		}

	if ($cash_rate_per_surf_credit < 0)
	{
		$cash_rate_per_surf_credit = 0.000;
	}
	if (($minimum_credits_allowed_to_request < 0) || (!ctype_digit($minimum_credits_allowed_to_request)))
	{
		$minimum_credits_allowed_to_request = 0;
	}
	if (($maximum_credits_allowed_to_request < 0) || ($maximum_credits_allowed_to_request <= $minimum_credits_allowed_to_request) || (!ctype_digit($maximum_credits_allowed_to_request)))
	{
		$maximum_credits_allowed_to_request = 10000;
	}
	$cash_rate_per_surf_credit = sprintf("%.3f", $cash_rate_per_surf_credit);
	$minimum_credits_allowed_to_request = round($minimum_credits_allowed_to_request);
	$minimum_credits_allowed_to_request = round($minimum_credits_allowed_to_request);

	mysql_query("update sm_cashconverter_settings set cash_rate_per_surf_credit=\"" . $cash_rate_per_surf_credit . "\", percent_credits_forced_for_ads=\"" . $percent_credits_forced_for_ads . "\", minimum_credits_allowed_to_request=\"" . $minimum_credits_allowed_to_request . "\", maximum_credits_allowed_to_request=\"" . $maximum_credits_allowed_to_request . "\"") or die(mysql_error());

	echo "<p align=\"center\"><b>Your cash converter settings were saved</b></p><p align=\"center\"><a href=\"admin.php?f=sm_converter\">Return</a></p><br>";

exit;
} # if ($action == "savesettings")

if ($action == "savehtml")
{
	if (isset($_POST['cash_converter_form_html']))
		{
		$cash_converter_form_html = $_POST['cash_converter_form_html'];
		}

	$cash_converter_form_html = stripslashes(mysql_real_escape_string($cash_converter_form_html));

	mysql_query("update sm_cashconverter_settings set cash_converter_form_html=\"" . $cash_converter_form_html . "\"") or die(mysql_error());

	echo "<p align=\"center\"><b>Your cash converter form design was saved!</b></p><p align=\"center\"><a href=\"admin.php?f=sm_converter\">Return</a></p><br>";

exit;
} # if ($action == "savehtml")

if ($action == "resethtml")
{
	$cash_converter_form_html_default = "";
	$cash_converter_form_html_default .= "<div style=\"width:600px;background:#fff;border:1px solid #000;padding:4px;\">";
	$cash_converter_form_html_default .= "<table border=\"0\" cellpadding=\"2\" width=\"600\"><tbody>";
	$cash_converter_form_html_default .= "<tr><td align=\"center\"><div style=\"font-size:18px;color:#000;\">You currently have <font style=\"background:#ffff00;\">[MEMBERS_TOTAL_CREDITS_AVAILABLE]</font> Surfing Credits</div></td></tr>";
	$cash_converter_form_html_default .= "<tr><td align=\"center\"><div style=\"font-size:22px;font-weight:bold;color:#000;\">Let's Process Your Surfing Credits!</div></td></tr>";
	$cash_converter_form_html_default .= "<tr><td align=\"left\"><div style=\"font-size:16px;font-weight:bold;color:#000;\"><font style=\"background:#ffff00;\">[PERCENT_CREDITS_FORCED_TO_ADS]%</font> of Credits to be Processed will be allocated <font style=\"background:#ffff00;\">equitably</font> to your ads!</div></td></tr>";
	$cash_converter_form_html_default .= "<tr><td align=\"left\"><div style=\"font-size:16px;font-weight:bold;color:#000;\">You cannot process any credits if you do not have ads in the system!</div></td></tr>";
	$cash_converter_form_html_default .= "<tr><td align=\"center\">";
	$cash_converter_form_html_default .= "<form action=\"sm_cashconverter.php\" method=\"post\">";
	$cash_converter_form_html_default .= "<table align=\"center\" cellpadding=\"2\" cellspacing=\"2\" width=\"75%;\" border=\"0\" style=\"border:1px solid #000;\">";
	$cash_converter_form_html_default .= "<tr><tr><td style=\"font-size:14px;color:#000;\"><b>Minimum</b> Conversion Request Credits:</td><td>[MINIMUM_CREDITS_TO_REQUEST]</td></tr>";
	$cash_converter_form_html_default .= "<tr><tr><td style=\"font-size:14px;color:#000;\"><b>Maximum</b> Conversion Request Credits:</td><td>[MAXIMUM_CREDITS_TO_REQUEST]</td></tr>";
	$cash_converter_form_html_default .= "<tr style=\"border:1px solid #000;\"><td style=\"font-size:14px;color:#000;\">Credits to <b>Convert</b></td><td><input type=\"text\" name=\"credits_to_convert\" id=\"credits_to_convert\" value=\"[CREDITS_THAT_CAN_BE_CONVERTED]\"></td></tr>";
	$cash_converter_form_html_default .= "<tr style=\"border:1px solid #000;\"><tr><td style=\"font-size:14px;color:#000;\">Will be Allocated to Ads [PERCENT_CREDITS_FORCED_TO_ADS]%</td><td><span id=\"credits_forced_to_ads\">[CREDITS_FORCED_TO_ADS]</span></td></tr>";
	$cash_converter_form_html_default .= "<tr style=\"border:1px solid #000;\"><tr><td style=\"font-size:14px;color:#000;\"><b>Credits Left for Cash Conversion</b></td><td><span id=\"credits_left_for_conversion\">[CREDITS_LEFT_FOR_CONVERSION]</span></td></tr>";
	$cash_converter_form_html_default .= "<tr><tr><td style=\"font-size:14px;color:#000;\"><b>Today's Cash Conversion Rate (per credit)</b></td><td>[CASH_RATE_PER_CREDIT]</td></tr>";
	$cash_converter_form_html_default .= "<tr style=\"border:1px solid #000;\"><tr><td style=\"font-size:14px;color:#000;\">Cash <b>Value</b></td><td>\$<span id=\"cash_value_of_request\">[CASH_VALUE_OF_REQUEST]</span></td></tr>";
	$cash_converter_form_html_default .= "</table>";
	$cash_converter_form_html_default .= "</td></tr>";
	$cash_converter_form_html_default .= "<tr><td align=\"center\"><input type=\"hidden\" name=\"action\" value=\"submitrequest\"><input type=\"submit\" style=\"font-size:16px;font-weight:bold;color:#000;\" value=\"SUBMIT REQUEST CLICK HERE\"></form></td></tr>";
	$cash_converter_form_html_default .= "</tbody></table>";
	$cash_converter_form_html_default .= "</div>";

	$cash_converter_form_html_default = mysql_real_escape_string($cash_converter_form_html_default);

	mysql_query("update sm_cashconverter_settings set cash_converter_form_html=\"" . $cash_converter_form_html_default . "\"") or die(mysql_error());

	echo "<p align=\"center\"><b>Your cash converter form design was reset to default!</b></p><p align=\"center\"><a href=\"admin.php?f=sm_converter\">Return</a></p><br>";

exit;
} # if ($action == "resethtml")

if ($action == "deleterecords")
{
	if (isset($_POST['history_records']))
	{
	$history_records = $_POST['history_records'];
	}
	else
	{
	echo "<p align=\"center\"><b>You need to check at least one record to delete.</b></p><p align=\"center\"><a href=\"admin.php?f=sm_converter\">Return</a></p><br>";
	exit;
	}

	foreach ($history_records as $each_one_to_delete)
	{
		$getinfo_r = mysql_query("select * from sm_cashconverter_records where id=\"" . $each_one_to_delete . "\"");
		$getinfo_rows = mysql_num_rows($getinfo_r);
		if ($getinfo_rows > 0)
		{
		$userid = mysql_result($getinfo_r,0,"userid");
		$approved = mysql_result($getinfo_r,0,"approved");
		$credits_requested_to_convert = mysql_result($getinfo_r,0,"credits_requested_to_convert");
		if ($approved == "no")
			{
			mysql_query("update " . $prefix . "members set credits=credits+" . $credits_requested_to_convert . " where Id=\"$userid\"");
			}
		}
	mysql_query("delete from sm_cashconverter_records where id=\"" . $each_one_to_delete . "\"");
	}

	echo "<p align=\"center\"><b>The checked cash converter records were deleted.<br>The credits were been returned to the member ONLY if the ad was never approved.</b></p><p align=\"center\"><a href=\"admin.php?f=sm_converter\">Return</a></p><br>";

exit;
} # if ($action == "deleterecords")

if ($action == "approverecords")
{
	if (isset($_POST['history_records']))
	{
	$history_records = $_POST['history_records'];
	}
	else
	{
	echo "<p align=\"center\"><b>You need to check at least one record to approve.</b></p><p align=\"center\"><a href=\"admin.php?f=sm_converter\">Return</a></p><br>";
	exit;
	}

	foreach ($history_records as $each_one_to_approve)
	{
		$getinfo_r = mysql_query("select * from sm_cashconverter_records where approved=\"no\" and id=\"" . $each_one_to_approve . "\"");
		$getinfo_rows = mysql_num_rows($getinfo_r);
		if ($getinfo_rows > 0)
		{
		$userid = mysql_result($getinfo_r,0,"userid");
		$username = mysql_result($getinfo_r,0,"username");
		$credits_allocated_to_ads = mysql_result($getinfo_r,0,"credits_allocated_to_ads");
		// assign forced ad credits divided equally to members ads.
		$getsites_r = mysql_query("select * from " . $prefix . "msites where memid=\"$userid\"");
		$getsites_rows = mysql_num_rows($getsites_r);
		if ($getsites_rows > 0)
			{
			mysql_query("update sm_cashconverter_records set approved=\"yes\" where id=\"" . $each_one_to_approve . "\"");
			// total credits per ad
			$credits_per_ad = round($credits_allocated_to_ads/$getsites_rows);
			if ($credits_per_ad < 1)
				{
				$credits_per_ad = 1;
				}
			// assign credits_per_ad to each ad.
			while ($getsites_rowz = mysql_fetch_array($getsites_r))
				{
				$site_id = $getsites_rowz['id'];
				$site_name = $getsites_rowz['sitename'];
				$site_url = $getsites_rowz['url'];
				mysql_query("update " . $prefix . "msites set credits=credits+" . $credits_per_ad . " where id=\"" . $site_id . "\"");
				echo "<p align=\"center\">" . $credits_per_ad . " credits auto-assigned to ad <a href=\"" . $site_url . "\" target=\"_blank\">" . $site_name ."</a> (for UserID #" . $userid . " - Username " . $username . ").</p>";
				}
			}
		if ($getsites_rows < 1)
			{
			// member doesn't have any sites to assign credits to!
			echo "<p align=\"center\">UserID #" . $userid . " - Username " . $username . " doesn't have any ads to assign credits to! They may have deleted their ads after submitting the conversion request.</p>";
			}
		}
	}

	echo "<p align=\"center\"><b>The checked cash converter records were approved.</b></p><p align=\"center\"><a href=\"admin.php?f=sm_converter\">Return</a></p><br>";

exit;
} # if ($action == "approverecords")

// get form for settings
$show_settings_form = "";
$show_settings_form .= "<p align=\"center\">";
$show_settings_form .= "<form action=\"admin.php?f=sm_converter\" method=\"post\">";
$show_settings_form .= "<table width=800 border=0 cellpadding=2 cellspacing=2 align=\"center\" bgcolor=\"#989898\">";
$show_settings_form .= "<tr bgcolor=\"#d3d3d3\"><td align=\"center\" colspan=\"2\" style=\"font-size:18px;font-weight:bold;\">Credit-to-Cash Converter Settings</td></tr>";
$show_settings_form .= "<tr bgcolor=\"#eeeeee\"><td>Cash Rate Per Surf Credit:</td><td><input type=\"text\" name=\"cash_rate_per_surf_credit\" value=\"" . $cash_rate_per_surf_credit . "\" maxlength=\"6\" size=\"6\"></td></tr>";
$show_settings_form .= "<tr bgcolor=\"#eeeeee\"><td>Percent of Request Allocated to Ads:</td><td>";
$show_settings_form .= "<select name=\"percent_credits_forced_for_ads\">";
for ($i=0;$i<=100;$i++)
	{
	if ($i == $percent_credits_forced_for_ads)
		{
		$selected = "selected";
		}
	else
		{
		$selected = "";
		}
	$show_settings_form .= "<option value=\"" . $i . "\" " . $selected . ">" . $i . "</option>";
	}
$show_settings_form .= "</select>";
$show_settings_form .= "</td></tr>";
$show_settings_form .= "<tr bgcolor=\"#eeeeee\"><td>Minimum Credits Allowed to Convert to Cash:</td><td><input type=\"text\" name=\"minimum_credits_allowed_to_request\" value=\"" . $minimum_credits_allowed_to_request . "\" maxlength=\"6\" size=\"6\"></td></tr>";
$show_settings_form .= "<tr bgcolor=\"#eeeeee\"><td>Maximum Credits Allowed to Convert to Cash:</td><td><input type=\"text\" name=\"maximum_credits_allowed_to_request\" value=\"" . $maximum_credits_allowed_to_request . "\" maxlength=\"6\" size=\"6\"></td></tr>";
$show_settings_form .= "<tr bgcolor=\"#d3d3d3\"><td align=\"center\" colspan=\"2\"><input type=\"hidden\" name=\"action\" value=\"savesettings\"><input type=\"submit\" value=\"SAVE\"></form></td></tr>";
$show_settings_form .= "</table></p><br>";

// get form for html code
$show_html_form = "";
$show_html_form .= "<p align=\"center\">";
$show_html_form .= "<form action=\"admin.php?f=sm_converter\" method=\"post\">";
$show_html_form .= "<table width=800 border=0 cellpadding=2 cellspacing=2 align=\"center\" bgcolor=\"#989898\">";
$show_html_form .= "<tr bgcolor=\"#d3d3d3\"><td align=\"center\" style=\"font-size:18px;font-weight:bold;\">HTML Code for the Cash Converter Form Box (Members Area)</td></tr>";
$show_html_form .= "<tr bgcolor=\"#eeeeee\"><td align=\"center\"><textarea name=\"cash_converter_form_html\" id=\"cash_converter_form_html\" cols=\"90\" rows=\"20\">" . $cash_converter_form_html . "</textarea></td></tr>";
$show_html_form .= "<tr bgcolor=\"#d3d3d3\"><td align=\"center\">";
$show_html_form .= "<input type=\"button\" name=\"preview_form\" id=\"preview_form\" value=\"PREVIEW\" onclick=\"
previewer = window.open('', 'displayWindow', 'toolbar=no,scrollbars=yes,status=no,width=700,height=500');
previewer.document.open();
previewer.document.write('<center>'+document.getElementById('cash_converter_form_html').value);
previewer.document.close();previewer.focus();\">";
$show_html_form .= "</td></tr>";
$show_html_form .= "<tr bgcolor=\"#eeeeee\"><td align=\"center\"><input type=\"hidden\" name=\"action\" value=\"savehtml\"><input type=\"submit\" value=\"SAVE\"></form></td></tr>";
$show_html_form .= "<form action=\"admin.php?f=sm_converter\" method=\"post\">";
$show_html_form .= "<tr bgcolor=\"#d3d3d3\"><td align=\"center\"><input type=\"hidden\" name=\"action\" value=\"resethtml\"><input type=\"submit\" value=\"RESET TO DEFAULT\"></form></td></tr>";
$show_html_form .= "</table></p><br>";

// get history
$show_history = "";
$history_r = mysql_query("select * from sm_cashconverter_records order by id desc");
$history_rows =  mysql_num_rows($history_r);
if ($history_rows < 1)
{
$show_history .= "<p align=\"center\"><table width=800 border=0 cellpadding=2 cellspacing=2 align=\"center\" bgcolor=\"#989898\">";
$show_history .= "<tr bgcolor=\"#d3d3d3\"><td align=\"center\" style=\"font-size:18px;font-weight:bold;\">Credits-to-Cash Conversion History</td></tr>";
$show_history .= "<tr bgcolor=\"#eeeeee\"><td align=\"center\"><b>There are currently no cash conversion requests to show.</b></td></tr>";
$show_history .= "</table></p><br><br>";
}
if ($history_rows > 0)
{
	$show_history .= "<p align=\"center\">";
	$show_history .= "<form action=\"admin.php?f=sm_converter\" method=\"post\">";
	$show_history .= "<table width=800 border=0 cellpadding=2 cellspacing=2 align=\"center\" bgcolor=\"#989898\">";
	$show_history .= "<tr bgcolor=\"#d3d3d3\"><td align=\"center\" colspan=\"10\" style=\"font-size:18px;font-weight:bold;\">Credits-to-Cash Conversion History</td></tr>";
	$show_history .= "<tr bgcolor=\"#eeeeee\">";
	$show_history .= "<td align=\"center\"><input type=\"checkbox\" id=\"checkUsAll\"></td>";
	$show_history .= "<td align=\"center\">ID</td>";
	$show_history .= "<td align=\"center\">UserID</td>";
	$show_history .= "<td align=\"center\">Username</td>";
	$show_history .= "<td align=\"center\">Requested Total Credits</td>";
	$show_history .= "<td align=\"center\">Percent For Ads</td>";
	$show_history .= "<td align=\"center\">Credits For Ads</td>";
	$show_history .= "<td align=\"center\">Cash Per Credit</td>";
	$show_history .= "<td align=\"center\">Total Cash Requested</td>";
	$show_history .= "<td align=\"center\">Approved</td>";
	$show_history .= "</tr>";
	$bg = "";
	while ($history_rowz = mysql_fetch_array($history_r))
	{
		$id = $history_rowz['id'];
		$userid = $history_rowz['userid'];
		$username = $history_rowz['username'];
		$credits_requested_to_convert = $history_rowz['credits_requested_to_convert'];
		$percentage_allocated_to_ads = $history_rowz['percentage_allocated_to_ads'];
		$credits_allocated_to_ads = $history_rowz['credits_allocated_to_ads'];
		$cash_conversion_per_credit = $history_rowz['cash_conversion_per_credit'];
		$total_cash_requested = $history_rowz['total_cash_requested'];
		$approved = $history_rowz['approved'];
		if ($bg == "#d3d3d3")
		{
			$showbg = "#eeeeee";
		}
		if ($bg != "#d3d3d3")
		{
			$showbg = "#d3d3d3";
		}
	
	$show_history .= "<tr bgcolor=\"" . $showbg . "\">";
	$show_history .= "<td align=\"center\"><input type=\"checkbox\" class=\"checkme\" name=\"history_records[]\" value=\"" . $id . "\"></td>";
	$show_history .= "<td align=\"center\">" . $id . "</td>";
	$show_history .= "<td align=\"center\">" . $userid . "</td>";
	$show_history .= "<td align=\"center\">" . $username . "</td>";
	$show_history .= "<td align=\"center\">" . $credits_requested_to_convert . "</td>";
	$show_history .= "<td align=\"center\">" . $percentage_allocated_to_ads . "%</td>";
	$show_history .= "<td align=\"center\">" . $credits_allocated_to_ads . "</td>";
	$show_history .= "<td align=\"center\">" . $cash_conversion_per_credit . "</td>";
	$show_history .= "<td align=\"center\">" . $total_cash_requested . "</td>";
	$show_history .= "<td align=\"center\">" . $approved . "</td>";
	$show_history .= "</tr>";

		if ($showbg != "#d3d3d3")
		{
		$bg = "#d3d3d3";
		}
		if ($showbg == "#d3d3d3")
		{
		$bg = "#eeeeee";
		}

	} # while ($history_rowz = mysql_fetch_array($history_r))
	
	$show_history .= "<tr bgcolor=\"" . $bg . "\">";
	$show_history .= "<td colspan=\"10\" align=\"center\">";
	$show_history .= "<select name=\"action\">";
	$show_history .= "<option value=\"approverecords\">Approve</option>";
	$show_history .= "<option value=\"deleterecords\">Delete</option>";
	$show_history .= "</select>&nbsp;&nbsp;";
	$show_history .= "<input type=\"submit\" value=\"SUBMIT\">";
	$show_history .= "</td>";
	$show_history .= "</tr>";

	$show_history .= "</table></form></p><br><br>";
	$show_history .= "<script src=\"http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js\"></script>";
	$show_history .= "<script>";
	$show_history .= "\$(document).ready(function(){
		\$('#checkUsAll').click(function(){
			if(this.checked){
				\$('.checkme').each(function(){
					this.checked = true;
				});
			}else{
				\$('.checkme').each(function(){
					this.checked = false;
				});
			}
		});
	});";
	$show_history .= "</script>";

} # if ($history_rows > 0)


echo $show_settings_form;

echo $show_html_form;

echo $show_history;

exit;
?>