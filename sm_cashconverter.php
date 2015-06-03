<?php
require_once "inc/filter.php";
session_start();
/////////////////////////////////////////////////////////////////////////
// Cash Converter Mod for LF                                           //
// (c) 2015 Sabrina Markon. PHPSiteScripts. All rights reserved.       //
// http://phpsitescripts.com                                           //
// sabrina.markon@gmail.com                                            //
// License does not permit copy/resale.                                //
/////////////////////////////////////////////////////////////////////////
include "inc/userauth.php";
$userid = $_SESSION["userid"];
$getuserdata = mysql_query("select * from ".$prefix."members where Id=$userid");
$username = mysql_result($getuserdata, 0, "Username");
$useremail = mysql_result($getuserdata, 0, "email");
$mtype = mysql_result($getuserdata, 0, "mtype");
$memtype = mysql_result($getuserdata, 0, "mtype");
$joindate = mysql_result($getuserdata, 0, "joindate");
$credits = mysql_result($getuserdata, 0, "credits");
$credits = round($credits);
#include "inc/theme.php";
#load_template ($theme_dir."/header.php");
#load_template ($theme_dir."/mmenu.php");
echo ("<div class=\"content\" class=\"fr\">");
echo("<script language=\"javascript\">
	if (top.location == self.location) {
		top.location.href='members.php';
	}
</script>
<link rel=\"stylesheet\" href=\"spstyle.css\" />");
?>
<style type="text/css">
<!--
.style1 {font-weight:bold;font-size: 12px;}
.style2 {
	font-size: 18px;
	font-weight: bold;
}
.style3 {
	font-size: 16px;
	font-weight: bold;
}
.style4 {font-size: 16px}

table.history td { width: 68px; font-size: 75%; }
-->
</style>
<?php
$settings_r = mysql_query("select * from sm_cashconverter_settings order by id limit 1");
$settings_rows =  mysql_num_rows($settings_r);
if ($settings_rows < 1)
{
echo "<p align=\"center\"><b>Credit-to-Cash converter settings are missing! Cannot continue.</b></p><br>";
echo("</center><br><br></div>");
#include $theme_dir."/footer.php";
exit;
}
$cash_converter_form_html = mysql_result($settings_r,0,"cash_converter_form_html");
$cash_rate_per_surf_credit = mysql_result($settings_r,0,"cash_rate_per_surf_credit");
$percent_credits_forced_for_ads = mysql_result($settings_r,0,"percent_credits_forced_for_ads");
$minimum_credits_allowed_to_request = mysql_result($settings_r,0,"minimum_credits_allowed_to_request");
$maximum_credits_allowed_to_request = mysql_result($settings_r,0,"maximum_credits_allowed_to_request");

// default maximum credits available
if ($credits > $maximum_credits_allowed_to_request)
{
	$maxcredits = $maximum_credits_allowed_to_request;
}
else
{
	$maxcredits = $credits;
}

//////////////////////////////////////////////////////////////////////
if (isset($_POST['action']))
{
	$action = $_POST['action'];
}
if ($action == "deleterecords")
{
	if (isset($_POST['history_records']))
	{
	$history_records = $_POST['history_records'];
	}
	else
	{
	echo "<p align=\"center\"><b>You need to check at least one record to delete.</b></p><p align=\"center\"><a href=\"sm_cashconverter.php\">Return</a></p><br>";
	echo("</center><br><br></div>");
	#include $theme_dir."/footer.php";
	exit;
	}

	foreach ($history_records as $each_one_to_delete)
	{
		$getinfo_r = mysql_query("select * from sm_cashconverter_records where userid=\"$userid\" and id=\"" . $each_one_to_delete . "\"");
		$getinfo_rows = mysql_num_rows($getinfo_r);
		if ($getinfo_rows > 0)
		{
		$approved = mysql_result($getinfo_r,0,"approved");
		$credits_requested_to_convert = mysql_result($getinfo_r,0,"credits_requested_to_convert");
		if ($approved == "no")
			{
			mysql_query("update " . $prefix . "members set credits=credits+" . $credits_requested_to_convert . " where Id=\"$userid\"");
			}
		}
	mysql_query("delete from sm_cashconverter_records where id=\"" . $each_one_to_delete . "\"");
	}
	
	echo "<p align=\"center\"><b>Your checked records were deleted.<br>Your credits were returned to you ONLY if the ad was never approved by the admin.</b></p><p align=\"center\"><a href=\"sm_cashconverter.php\">Return</a></p><br>";
	echo("</center><br><br></div>");
	#include $theme_dir."/footer.php";
	exit;

exit;
} # if ($action == "deleterecords")
if ($action == "submitrequest")
{
	// only allow one unapproved request at a time
	$already_r = mysql_query("select * from sm_cashconverter_records where approved=\"no\" and userid=\"$userid\"");
	$already_rows = mysql_num_rows($already_r);
	if ($already_rows > 0)
	{
	echo "<p align=\"center\"><b>You already have a pending request. Once the admin approves this pending request then you will be able to submit another.</b></p><p align=\"center\"><a href=\"sm_cashconverter.php\">Return</a></p><br>";
	echo("</center><br><br></div>");
	#include $theme_dir."/footer.php";
	exit;
	}
	$getsites_r = mysql_query("select * from " . $prefix . "msites where memid=\"$userid\"");
	$getsites_rows = mysql_num_rows($getsites_r);
	if ($getsites_rows < 1)
	{
	echo "<p align=\"center\"><b>You don't have any sites set up that can be auto-assigned credits.</b></p><p align=\"center\"><a href=\"sm_cashconverter.php\">Return</a></p><br>";
	echo("</center><br><br></div>");
	#include $theme_dir."/footer.php";
	exit;
	}
	if (isset($_POST['credits_to_convert']))
	{
	$credits_to_convert = $_POST['credits_to_convert'];
	$credits_to_convert = round($credits_to_convert);
	$credits_to_convert = sprintf("%u", $credits_to_convert);
	if (($credits_to_convert < 1) || ($credits_to_convert <  $minimum_credits_allowed_to_request))
		{
		echo "<p align=\"center\"><b>The amount of credits you entered is less than your minimum allowed: " . $minimum_credits_allowed_to_request . "</b></p><p align=\"center\"><a href=\"sm_cashconverter.php\">Return</a></p><br>";
		echo("</center><br><br></div>");
		#include $theme_dir."/footer.php";
		exit;
		}
	if ($credits_to_convert > $maxcredits)
		{
		echo "<p align=\"center\"><b>The amount of credits you entered is more than your maximum allowed: " . $maxcredits . "</b></p><p align=\"center\"><a href=\"sm_cashconverter.php\">Return</a></p><br>";
		echo("</center><br><br></div>");
		#include $theme_dir."/footer.php";
		exit;
		}

	$credits_allocated_to_ads = round($credits_to_convert * $percent_credits_forced_for_ads / 100);
	$total_cash_requested = sprintf("%.3f", ($credits_to_convert - $credits_allocated_to_ads) * $cash_rate_per_surf_credit);

	mysql_query("insert into sm_cashconverter_records (userid,username,credits_requested_to_convert,percentage_allocated_to_ads,credits_allocated_to_ads,cash_conversion_per_credit,total_cash_requested) values (\"$userid\",\"$username\",\"$credits_to_convert\",\"$percent_credits_forced_for_ads\",\"$credits_allocated_to_ads\",\"$cash_rate_per_surf_credit\",\"$total_cash_requested\")") or die(mysql_error());

	mysql_query("update " . $prefix . "members set credits=credits-" . $credits_to_convert . " where Id=\"$userid\"");

	echo "<p align=\"center\"><b>Your request was submitted!</b></p><p align=\"center\"><a href=\"sm_cashconverter.php\">Return</a></p><br>";
	echo("</center><br><br></div>");
	#include $theme_dir."/footer.php";
	exit;
	}
	else
	{
	echo "<p align=\"center\"><b>You left the field blank!</b></p><p align=\"center\"><a href=\"sm_cashconverter.php\">Return</a></p><br>";
	echo("</center><br><br></div>");
	#include $theme_dir."/footer.php";
	exit;
	}
} # if ($action == "submitrequest")
//////////////////////////////////////////////////////////////////////

// php substitution in form html
$cash_converter_form_html = str_replace("[MEMBERS_TOTAL_CREDITS_AVAILABLE]",$credits,$cash_converter_form_html);
$cash_converter_form_html = str_replace("[PERCENT_CREDITS_FORCED_TO_ADS]",$percent_credits_forced_for_ads,$cash_converter_form_html);
$cash_converter_form_html = str_replace("[CASH_RATE_PER_CREDIT]",$cash_rate_per_surf_credit,$cash_converter_form_html);
$cash_converter_form_html = str_replace("[MINIMUM_CREDITS_TO_REQUEST]",$minimum_credits_allowed_to_request,$cash_converter_form_html);
$cash_converter_form_html = str_replace("[MAXIMUM_CREDITS_TO_REQUEST]",$maximum_credits_allowed_to_request,$cash_converter_form_html);

$calculated_credits_left_for_conversion = $maxcredits;
$cash_converter_form_html = str_replace("[CREDITS_THAT_CAN_BE_CONVERTED]",$calculated_credits_left_for_conversion,$cash_converter_form_html);

$calculated_credits_forced_for_ads = round($maxcredits * $percent_credits_forced_for_ads / 100);
$cash_converter_form_html = str_replace("[CREDITS_FORCED_TO_ADS]",$calculated_credits_forced_for_ads,$cash_converter_form_html);

$calculated_number_of_credits_for_cash = round($maxcredits - $calculated_credits_forced_for_ads);
$cash_converter_form_html = str_replace("[CREDITS_LEFT_FOR_CONVERSION]",$calculated_number_of_credits_for_cash,$cash_converter_form_html);

$calculated_cash_value_of_request = sprintf("%.3f", $calculated_number_of_credits_for_cash * $cash_rate_per_surf_credit);
$cash_converter_form_html = str_replace("[CASH_VALUE_OF_REQUEST]",$calculated_cash_value_of_request,$cash_converter_form_html);

echo $cash_converter_form_html;

// get history
$show_history = "";
$history_r = mysql_query("select * from sm_cashconverter_records where userid=\"$userid\" order by approved desc,id desc");
$history_rows =  mysql_num_rows($history_r);
if ($history_rows < 1)
{
$show_history .= "<p align=\"center\"><table width=544 border=0 cellpadding=2 cellspacing=2 align=\"center\" bgcolor=\"#989898\">";
$show_history .= "<tr bgcolor=\"#d3d3d3\"><td align=\"center\" style=\"font-size:18px;font-weight:bold;\">Your Credits-to-Cash Request History</td></tr>";
$show_history .= "<tr bgcolor=\"#eeeeee\"><td align=\"center\">You don't have any current requests to show.</td></tr>";
$show_history .= "</table></p><br><br>";
}
if ($history_rows > 0)
{
	$show_history .= "<p align=\"center\">";
	$show_history .= "<form action=\"sm_cashconverter.php\" method=\"post\">";
	$show_history .= "<table class=\"history\" width=544 border=0 cellpadding=2 cellspacing=2 align=\"center\" bgcolor=\"#989898\">";
	$show_history .= "<tr bgcolor=\"#d3d3d3\"><td align=\"center\" colspan=\"8\" style=\"font-size:18px;font-weight:bold;\">Credits-to-Cash Conversion History</td></tr>";
	$show_history .= "<tr bgcolor=\"#eeeeee\">";
	$show_history .= "<td align=\"center\"><input type=\"checkbox\" id=\"checkUsAll\"></td>";
	$show_history .= "<td align=\"center\">ID</td>";
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
	$show_history .= "<td colspan=\"8\" align=\"center\">";
	$show_history .= "<input type=\"hidden\" name=\"action\" value=\"deleterecords\">";
	$show_history .= "<input type=\"submit\" value=\"DELETE\">";
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

echo $show_history;

echo("</center><br><br></div>");
?>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script>
$(document).ready(function(){

	var percent_credits_forced_for_ads = <?php echo $percent_credits_forced_for_ads ?>;
	var cash_rate_per_surf_credit = <?php echo $cash_rate_per_surf_credit ?>;
	var mincredits = <?php echo $minimum_credits_allowed_to_request ?>;
	var maxcredits = <?php echo $maxcredits ?>;

	$('#credits_to_convert').blur(function(event){

		function isNormalInteger(str){
			var n = ~~Number(str);
			return String(n) === str && n >= mincredits;
		}

		var credits_member_wants_to_convert = $(this).val();
		var credits_member_wants_to_convert_is_ok = isNormalInteger(credits_member_wants_to_convert);

		// check input even more
		if ((credits_member_wants_to_convert > maxcredits) || (maxcredits < mincredits) || (credits_member_wants_to_convert_is_ok == false))
			{
			if (credits_member_wants_to_convert != '')
				{
				credits_member_wants_to_convert = maxcredits;
				$('#credits_to_convert').val(maxcredits);
				}
			}

		var calculated_credits_forced_for_ads = Math.abs(Math.round(credits_member_wants_to_convert * percent_credits_forced_for_ads / 100));
		var calculated_credits_left_for_conversion = Math.abs(Math.round(credits_member_wants_to_convert - calculated_credits_forced_for_ads));
		var calculated_cash_value_of_request = Math.abs(((credits_member_wants_to_convert - calculated_credits_forced_for_ads) * cash_rate_per_surf_credit).toFixed(3));
		
		// update the form to show the calculated values
		$('#credits_forced_to_ads').text(calculated_credits_forced_for_ads);
		$('#credits_left_for_conversion').text(calculated_credits_left_for_conversion);
		$('#cash_value_of_request').text(calculated_cash_value_of_request.toFixed(3));

	});
});
</script>
<?php
#include $theme_dir."/footer.php";
exit;
?>