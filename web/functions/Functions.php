<?php

function format_currency($amount)
{
	$currency = "&#163;" . sprintf("%.2f", $amount);

	if($amount < 0)
	{
		$currency = "<span class=\"negativecurrency\">$currency</span>";
	}

	return $currency;
}

function format_date($date)
{
	$date = intval($date);

	if($date == 0)
	{
		return "Never";
	}

	$difference = time() - $date;

	if($difference >= 0)
	{
		// In the past

		if($difference < 3)
		{
			return "Now";
		}
		if($difference < 60)
		{
			return ($difference == 1) ? "1 second ago" : $difference . " seconds ago";
		}
		elseif($difference < 3600)
		{
			$difference = intval(floor($difference / 60));

			return ($difference == 1) ? "1 minute ago" : $difference . " minutes ago";
		}
		elseif($difference < 86400)
		{
			$difference = intval(floor($difference / 3600));

			return ($difference == 1) ? "1 hour ago" : $difference . " hours ago";
		}
		elseif($difference < 2592000)
		{
			$difference = intval(floor($difference / 86400));

			return ($difference == 1) ? "1 day ago" : $difference . " days ago";
		}
		elseif($difference < 31536000)
		{
			$difference = intval(floor($difference / 2592000));

			return ($difference == 1) ? "1 month ago" : $difference . " months ago";
		}
		else
		{
			$difference = intval(floor($difference / 31536000));

			return ($difference == 1) ? "1 year ago" : $difference . " years ago";
		}
	}
	else
	{
		// Flip sign. This prevents potential issues with rounding (PHP can round upwards on negative numbers at times, where the desired behaviour is to round down)
		$difference = -$difference;

		if($difference < 60)
		{
			return "in " . $difference . " seconds";
		}
		elseif($difference < 3600)
		{
			return "in " . intval(floor($difference / 60)) . " minutes";
		}
		elseif($difference < 86400)
		{
			return "in " . intval(floor($difference / 3600)) . " hours";
		}
		elseif($difference < 2592000)
		{
			return "in " . intval(floor($difference / 86400)) . " days";
		}
		elseif($difference < 31536000)
		{
			return "in " . intval(floor($difference / 2592000)) . " months";
		}
		else
		{
			return "in " . intval(floor($difference / 31536000)) . " years";
		}
	}
}

?>
