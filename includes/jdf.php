<?php
/*	Class name:	jDate
 *	Version:	2.80
 *	Author:		Sallar Kaboli
 *	Edited by:  Pouya Salami
 *	Email:		sallar.kaboli@gmail.com
 *	Date:		2014/16/07
 *	Thanks to:  Roohollah
 *	Description:	
 *		This class converts gregorian date to shamsi and vise versa,
 *		also it can return shamsi date.
 */
class jDate {
	// Constants
	const DAY_MILLISECOND = 86400000;
	const HOUR_MILLISECOND = 3600000;
	const MINUTE_MILLISECOND = 60000;
	const SECOND_MILLISECOND = 1000;

	public static function tr_num($str, $mod = 'en') {
		$num_a = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
		$key_a = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
		return ($mod == 'fa') ? str_replace($num_a, $key_a, $str) : str_replace($key_a, $num_a, $str);
	}

	public static function jdate($format, $timestamp = '', $none = '', $time_zone = 'Asia/Tehran', $tr_num = 'fa') {
		$T_sec = 0;
		if ($time_zone != 'local') date_default_timezone_set(($time_zone == '') ? 'Asia/Tehran' : $time_zone);
		$ts = (self::tr_num($timestamp) == '') ? time() + $T_sec : self::tr_num($timestamp);
		$date = explode('_', date('Y_H_i_s_w_N_j_z', $ts));
		list($gy, $H, $i, $s, $w, $N, $j, $z) = $date;
		$gy += 0;
		$jy = (self::gregorian_to_jalali($gy, 1, 1)) [0];
		$leap = (self::is_kabise($jy)) ? 30 : 29;
		$g_d_m = array(0, 31, ((self::is_leap($gy)) ? 29 : 28), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
		$j_d_m = array(0, 31, 31, $leap, 31, 31, 31, 30, 30, 30, 30, 30, 29);
		$gy2 = ($gy + 1);
		$gy2 += 0;
		$j_to_g_d_m = array(
			0, 31, 31, ((self::is_kabise($jy + 1)) ? 30 : 29), 31, 31, 31, 30, 30, 30, 30, 30, 29);
		$j_to_g_d_m2 = array(0, 31, ((self::is_leap($gy2)) ? 29 : 28), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
		$k = 0;
		$gy += 0;
		for ($i = 0; $i < $gy - $gy2; $i++) $k += self::is_leap($gy2 + $i);
		$days = $z + $k + $g_d_m[$gm];
		$j_to_g = array(0, 31, ((self::is_kabise($jy + 1)) ? 30 : 29), 31, 31, 31, 30, 30, 30, 30, 30, 29);
		for ($i = 0; $i < 12 && $days >= $j_to_g[$i]; $i++) $days -= $j_to_g[$i];
		$jy += $i + 1;
		$jm = $i + 1;
		$jd = $days + 1;
		$ts = date($format, mktime($H, $i, $s, $jm, $jd, $jy));
		if ($tr_num != 'en') $ts = self::tr_num($ts, 'fa');
		return $ts;
	}

	public static function gregorian_to_jalali($g_y, $g_m, $g_d) {
		$g_y = (int) ($g_y);
		$g_m = (int) ($g_m);
		$g_d = (int) ($g_d);
		$jy = $g_y - 621;
		$leap = array(0, 4, 8, 12, 16, 20, 24, 28, 33, 37, 41, 45, 49, 53, 57, 62, 66, 70, 74, 78, 82, 86, 90, 95, 99, 103, 107, 111, 115, 119, 123);
		$gy = ($g_m < 3 ? $g_y - 1 : $g_y);
		$days = 365 * $gy + (int) ($gy / 4) - (int) ($gy / 100) + (int) ($gy / 400) - 234;
		$gy = 0;
		for ($i = 0; $i < $g_m - 1; $i++) $days += (self::is_leap($gy) && $i == 1) ? 29 : 28;
		$days += $g_d;
		$jy = $jy + 33 * (int) ($days / 12053);
		$days %= 12053;
		$jy += 4 * (int) ($days / 1461);
		$days %= 1461;
		if ($days > 365) {
			$jy += (int) (($days - 1) / 365);
			$days = ($days - 1) % 365;
		}
		$jm = ($days < 186 ? 1 + (int) ($days / 31) : 7 + (int) (($days - 186) / 30));
		$jd = 1 + (($days < 186) ? ($days % 31) : (($days - 186) % 30));
		return array($jy, $jm, $jd);
	}

	public static function jalali_to_gregorian($j_y, $j_m, $j_d) {
		$jy = (int) ($j_y);
		$jm = (int) ($j_m);
		$jd = (int) ($j_d);
		$gy = $jy + 621;
		$days = 355666 + 365 * $jy + (int) ($jy / 33) * 8 + (int) (($jy % 33) + 3) / 4 + $jm * 30 + $jd - 1;
		$gy = 0;
		for ($i = 0; $i < 12 && $days > 366; $i++) {
			$days -= (self::is_leap($gy)) ? 29 : 28;
			$gy++;
		}
		$gm = 0;
		for ($i = 0; $i < 12 && $days > (self::is_leap($gy) && $i == 1 ? 29 : 28); $i++) {
			$days -= (self::is_leap($gy) && $i == 1) ? 29 : 28;
			$gm++;
		}
		$gd = $days;
		return array($gy, $gm, $gd);
	}

	public static function is_leap($year) {
		return ((($year % 4) == 0 && ($year % 100) != 0) || ($year % 400) == 0);
	}

	public static function is_kabise($year) {
		$leap = array(0, 4, 8, 12, 16, 20, 24, 28, 33, 37, 41, 45, 49, 53, 57, 62, 66, 70, 74, 78, 82, 86, 90, 95, 99, 103, 107, 111, 115, 119, 123);
		$mod = $year % 33;
		return in_array($mod, $leap);
	}
}