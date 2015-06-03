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
if ($action == "submitrequest")
{
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

echo("</center><br><br></div>");
?>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script>
$(document).ready(function(){

	var percent_credits_forced_for_ads = <?php echo $percent_credits_forced_for_ads ?>;
	var cash_rate_per_surf_credit = <?php echo $cash_rate_per_surf_credit ?>;
	var mincredits = <?php echo $minimum_credits_allowed_to_request ?>;
	var maxcredits = <?php echo $maxcredits ?>;

	$('#credits_to_convert').keyup(function(event){

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