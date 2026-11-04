<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
/**
 * Voucher (discount code) engine: validation + discount calculation.
 *
 * Single source of truth used by the front end (cart summary, preview, order processing) and by the admin
 * (booking create / update). Nothing here trusts the browser: prices are always taken from the database /
 * the cart that lives in the session, and the percentage / amount is read from the voucher row.
 *
 * Time handling
 * - All comparisons are made on the "wall clock" of the script (PHP default timezone, which the script sets from
 *   Options > o_timezone, exactly like for the booking slots): a moment is turned into 'Y-m-d H:i:s' with date()
 *   and compared as a string with the voucher window. No strtotime() on user input, so there is no DST / timezone
 *   surprise and the configured 12h/24h display format never influences the stored values.
 * - Voucher times are stored as 24-hour 'HH:MM:SS'. The admin form converts to / from the configured display format
 *   (see to24h() / from24h()); the end time is inclusive up to the end of that minute (HH:MM:59).
 */
class pjVoucherEngine
{
	const SESSION_KEY = 'AppSched_Voucher';
	const SESSION_FAILS = 'AppSched_VoucherFails';
	const MAX_FAILS = 8;
	const FAIL_WINDOW = 300;

	/* ------------------------------------------------------------------ codes */

	public static function cleanCode($code)
	{
		return trim((string) $code);
	}

	public static function isValidCodeFormat($code)
	{
		return (bool) preg_match('/^[A-Za-z0-9][A-Za-z0-9_\-\.]{1,49}$/', (string) $code);
	}

	/* ------------------------------------------------------------- time format */

	/**
	 * 0 = 24 hour, 1 = 12 hour with lowercase am/pm, 2 = 12 hour with uppercase AM/PM (same as pjDateTime props)
	 */
	public static function timeMode($time_format)
	{
		$time_format = (string) $time_format;
		if (strpos($time_format, 'A') !== false)
		{
			return 2;
		}
		if (strpos($time_format, 'a') !== false)
		{
			return 1;
		}
		return 0;
	}

	/**
	 * Admin selects -> 'HH:MM:00' (24h). $ampm is 'am'/'pm' (any case) and only used in the 12 hour modes.
	 * Returns false for anything that is not a real time of the day.
	 */
	public static function to24h($hour, $minute, $ampm, $mode)
	{
		if (!preg_match('/^\d{1,2}$/', (string) $hour) || !preg_match('/^\d{1,2}$/', (string) $minute))
		{
			return false;
		}
		$h = (int) $hour;
		$m = (int) $minute;
		if ($m < 0 || $m > 59)
		{
			return false;
		}
		if ((int) $mode > 0)
		{
			$ampm = strtolower((string) $ampm);
			if ($h < 1 || $h > 12 || !in_array($ampm, array('am', 'pm'), true))
			{
				return false;
			}
			$h = $h % 12;          // 12 AM -> 0 (midnight), 12 PM -> 12 (noon)
			if ($ampm === 'pm')
			{
				$h += 12;
			}
		} else {
			if ($h < 0 || $h > 23)
			{
				return false;
			}
		}
		return sprintf('%02d:%02d:00', $h, $m);
	}

	/**
	 * Stored 'HH:MM[:SS]' -> values for the admin selects in the configured mode.
	 */
	public static function from24h($time, $mode)
	{
		if (!preg_match('/^(\d{1,2}):(\d{2})/', (string) $time, $m))
		{
			$m = array('', 0, 0);
		}
		$h = (int) $m[1];
		$min = sprintf('%02d', (int) $m[2]);
		if ((int) $mode > 0)
		{
			$is_pm = $h >= 12;
			$h12 = $h % 12;
			if ($h12 === 0)
			{
				$h12 = 12;
			}
			$ampm = $is_pm ? 'pm' : 'am';
			if ((int) $mode === 2)
			{
				$ampm = strtoupper($ampm);
			}
			return array('hour' => sprintf('%02d', $h12), 'minute' => $min, 'ampm' => $ampm);
		}
		return array('hour' => sprintf('%02d', $h), 'minute' => $min, 'ampm' => '');
	}

	/**
	 * Display a stored 24h time with the configured o_time_format (never hardcoded).
	 */
	public static function displayTime($time, $time_format)
	{
		if (!preg_match('/^(\d{1,2}):(\d{2})/', (string) $time, $m))
		{
			return '';
		}
		// gmdate: no timezone / DST involved, we only format a clock reading
		return gmdate($time_format, ((int) $m[1]) * 3600 + ((int) $m[2]) * 60);
	}

	/**
	 * Date typed in the admin (in the configured o_date_format) -> strict 'Y-m-d', or false.
	 * 31.02.2026 is rejected instead of silently becoming 03.03.2026.
	 */
	public static function parseDate($value, $date_format)
	{
		$value = trim((string) $value);
		if ($value === '')
		{
			return false;
		}
		$limiter = null;
		foreach (array('.', '-', '/') as $l)
		{
			if (strpos($date_format, $l) !== false)
			{
				$limiter = $l;
				break;
			}
		}
		if ($limiter === null)
		{
			return false;
		}
		$parts = explode($limiter, $value);
		$fmt = array_flip(explode($limiter, $date_format));
		if (count($parts) !== 3 || !isset($fmt['Y']) || (!isset($fmt['d']) && !isset($fmt['j'])) || (!isset($fmt['m']) && !isset($fmt['n'])))
		{
			return false;
		}
		$y = $parts[$fmt['Y']];
		$m = $parts[isset($fmt['m']) ? $fmt['m'] : $fmt['n']];
		$d = $parts[isset($fmt['d']) ? $fmt['d'] : $fmt['j']];
		if (!preg_match('/^\d{4}$/', $y) || !preg_match('/^\d{1,2}$/', $m) || !preg_match('/^\d{1,2}$/', $d) || !checkdate((int) $m, (int) $d, (int) $y))
		{
			return false;
		}
		return sprintf('%04d-%02d-%02d', (int) $y, (int) $m, (int) $d);
	}

	public static function displayDate($date, $date_format)
	{
		if (empty($date) || $date === '0000-00-00')
		{
			return '';
		}
		return pjDateTime::formatDate($date, 'Y-m-d', $date_format);
	}

	/* ------------------------------------------------------- validity window */

	/**
	 * Is $wall ('Y-m-d H:i:s' on the script's clock) inside the voucher validity window?
	 */
	public static function isInWindow($v, $wall)
	{
		switch ($v['valid'])
		{
			case 'always':
				return true;
			case 'fixed':
			case 'period':
				if (empty($v['date_from']) || empty($v['date_to']) || empty($v['time_from']) || empty($v['time_to']))
				{
					return false;
				}
				$from = $v['date_from'] . ' ' . substr($v['time_from'], 0, 5) . ':00';
				$to = $v['date_to'] . ' ' . substr($v['time_to'], 0, 5) . ':59';
				return $wall >= $from && $wall <= $to;
			case 'recurring':
				if (empty($v['every']) || empty($v['time_from']) || empty($v['time_to']))
				{
					return false;
				}
				$day = strtolower(self::weekdayOf(substr($wall, 0, 10)));
				$tod = substr($wall, 11, 8);
				return $day === $v['every'] && $tod >= substr($v['time_from'], 0, 5) . ':00' && $tod <= substr($v['time_to'], 0, 5) . ':59';
		}
		return false;
	}

	/**
	 * 'monday'.. 'sunday' of a Y-m-d date (calendar arithmetic only, independent of timezone / DST)
	 */
	public static function weekdayOf($ymd)
	{
		$days = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
		$dt = DateTime::createFromFormat('!Y-m-d', $ymd, new DateTimeZone('UTC'));
		return $dt ? $days[(int) $dt->format('w')] : '';
	}

	/**
	 * Has the whole validity window already ended / not started yet (for the right error message)?
	 * Returns 'expired', 'not_started' or null
	 */
	public static function windowState($v, $wall)
	{
		if (in_array($v['valid'], array('fixed', 'period'), true) && !empty($v['date_from']))
		{
			if ($wall < $v['date_from'] . ' ' . substr($v['time_from'], 0, 5) . ':00')
			{
				return 'not_started';
			}
			if ($wall > $v['date_to'] . ' ' . substr($v['time_to'], 0, 5) . ':59')
			{
				return 'expired';
			}
		}
		return null;
	}

	/* ------------------------------------------------------------------ data */

	public static function findByCode($code, $calendar_id)
	{
		$code = self::cleanCode($code);
		if ($code === '' || !self::isValidCodeFormat($code))
		{
			return null;
		}
		$arr = pjVoucherModel::factory()
			->where('t1.code', $code)
			->where('t1.calendar_id', (int) $calendar_id)
			->limit(1)
			->findAll()
			->getData();
		if (empty($arr))
		{
			return null;
		}
		// the collation is case-insensitive, make sure we did not match something like a trailing-space variant
		if (strcasecmp($arr[0]['code'], $code) !== 0)
		{
			return null;
		}
		$arr[0]['service_ids'] = array_map('intval', array_values(pjVoucherServiceModel::factory()
			->where('t1.voucher_id', $arr[0]['id'])
			->findAll()
			->getDataPair(null, 'service_id')));
		return $arr[0];
	}

	/**
	 * Bookings that currently "use" the voucher: confirmed ones plus pending ones still inside the pending hold time
	 * (the very same definition the script uses to decide that a booking blocks a time slot).
	 */
	public static function countUsage($code, $calendar_id, $option_arr, $email = null, $exclude_booking_id = 0)
	{
		$sql = sprintf("SELECT COUNT(*) AS `cnt` FROM `%s` WHERE `calendar_id` = :cid AND `voucher_code` = :vcode
			AND (`booking_status` = :st_conf OR (`booking_status` = :st_pend AND UNIX_TIMESTAMP(`created`) >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL %u MINUTE))))",
			pjBookingModel::factory()->getTable(), (int) $option_arr['o_pending_time']);
		$params = array('cid' => (int) $calendar_id, 'vcode' => $code, 'st_conf' => 'confirmed', 'st_pend' => 'pending');
		if ((int) $exclude_booking_id > 0)
		{
			$sql .= " AND `id` <> :xid";
			$params['xid'] = (int) $exclude_booking_id;
		}
		if ($email !== null && $email !== '')
		{
			$sql .= " AND LOWER(TRIM(`c_email`)) = :vemail";
			$params['vemail'] = strtolower(trim($email));
		}
		$row = pjAppModel::factory()->prepare($sql . ';')->exec($params)->getDataIndex(0);
		return empty($row) ? 0 : (int) $row['cnt'];
	}

	/* ------------------------------------------------------------ evaluation */

	/**
	 * Validate $code for the given cart lines and compute the discount.
	 *
	 * @param string $code
	 * @param array  $lines  list of array('service_id'=>int, 'price'=>float, 'date'=>'Y-m-d', 'start_ts'=>int)
	 * @param array  $ctx    calendar_id, option_arr (required); email, now (unix ts), exclude_booking_id, check_usage (bool, default true),
	 *                       skip_window (bool, grandfather an already booked voucher), voucher (preloaded row)
	 * @return array status OK|ERR, reason, text, voucher, discount, line_discounts (index => amount)
	 */
	public static function evaluate($code, $lines, $ctx)
	{
		$err = function ($reason) {
			$labels = array(
				'missing'     => 'front_voucher_missing',
				'not_found'   => 'front_voucher_not_found',
				'inactive'    => 'front_voucher_inactive',
				'expired'     => 'front_voucher_expired',
				'not_started' => 'front_voucher_not_started',
				'service'     => 'front_voucher_not_for_service',
				'time'        => 'front_voucher_not_for_time',
				'limit'       => 'front_voucher_limit',
				'limit_email' => 'front_voucher_limit_email',
				'empty'       => 'front_voucher_empty_cart'
			);
			$text = isset($labels[$reason]) ? __($labels[$reason], true) : '';
			return array('status' => 'ERR', 'code' => 101, 'reason' => $reason, 'text' => $text, 'discount' => 0.0, 'line_discounts' => array());
		};

		$code = self::cleanCode($code);
		if ($code === '')
		{
			return $err('missing');
		}
		if (empty($lines))
		{
			return $err('empty');
		}
		$option_arr = $ctx['option_arr'];
		$calendar_id = (int) $ctx['calendar_id'];
		$v = isset($ctx['voucher']) ? $ctx['voucher'] : self::findByCode($code, $calendar_id);
		if (empty($v))
		{
			return $err('not_found');
		}
		// a date / time window always refers to the APPOINTMENT (the date and time the customer picked), never to the
		// moment the booking is made - also for vouchers saved earlier with the old "valid on: booking" setting
		if ($v['valid'] !== 'always')
		{
			$v['valid_on'] = 'appointment';
		}
		$skip_window = !empty($ctx['skip_window']); // "grandfathered": a voucher already stored on an existing booking
		if ($v['status'] !== 'T' && !$skip_window)
		{
			return $err('inactive');
		}

		$now = isset($ctx['now']) ? (int) $ctx['now'] : time();
		$now_wall = date('Y-m-d H:i:s', $now);

		// 1) which services does it apply to
		$eligible = array();
		$service_miss = false;
		$time_miss = false;
		foreach ($lines as $i => $line)
		{
			if (!empty($v['service_ids']) && !in_array((int) $line['service_id'], $v['service_ids'], true))
			{
				$service_miss = true;
				continue;
			}
			if (!$skip_window && $v['valid'] !== 'always' && $v['valid_on'] === 'appointment')
			{
				$wall = $line['date'] . ' ' . date('H:i:s', (int) $line['start_ts']);
				if (!self::isInWindow($v, $wall))
				{
					$time_miss = true;
					continue;
				}
			}
			$eligible[] = $i;
		}

		// 2) validity of the voucher itself (when it is checked against the moment of booking)
		if (!$skip_window && $v['valid'] !== 'always' && $v['valid_on'] !== 'appointment' && !self::isInWindow($v, $now_wall))
		{
			$state = self::windowState($v, $now_wall);
			return $err($state === 'not_started' ? 'not_started' : 'expired');
		}

		if (empty($eligible))
		{
			return $err($time_miss && !$service_miss ? 'time' : ($service_miss ? 'service' : 'time'));
		}

		// 3) usage limits
		if (!isset($ctx['check_usage']) || $ctx['check_usage'])
		{
			$exclude = isset($ctx['exclude_booking_id']) ? (int) $ctx['exclude_booking_id'] : 0;
			if ((int) $v['max_uses'] > 0 && self::countUsage($v['code'], $calendar_id, $option_arr, null, $exclude) >= (int) $v['max_uses'])
			{
				return $err('limit');
			}
			$email = isset($ctx['email']) ? trim($ctx['email']) : '';
			if ((int) $v['max_uses_per_email'] > 0 && $email !== '' && self::countUsage($v['code'], $calendar_id, $option_arr, $email, $exclude) >= (int) $v['max_uses_per_email'])
			{
				return $err('limit_email');
			}
		}

		// 4) the money
		$calc = self::calculate($v, $lines, $eligible);

		return array(
			'status' => 'OK',
			'code' => 200,
			'reason' => 'ok',
			'text' => __('front_voucher_applied', true),
			'voucher' => $v,
			'discount' => $calc['discount'],
			'line_discounts' => $calc['line_discounts'],
			'eligible' => $eligible
		);
	}

	/**
	 * Discount maths. Rounded to cents, never more than the price of the lines it applies to.
	 *  percent            : pct % of the eligible subtotal
	 *  amount + each      : the amount off EVERY eligible service, each capped at its own price
	 *  amount + total     : the amount once, capped at the eligible subtotal
	 */
	public static function calculate($v, $lines, $eligible)
	{
		$disc = (float) $v['discount'];
		$line_discounts = array();
		$subtotal = 0.0;
		foreach ($eligible as $i)
		{
			$subtotal += max(0.0, (float) $lines[$i]['price']);
		}
		$subtotal = round($subtotal, 2);
		$total_discount = 0.0;

		if ($v['type'] === 'percent')
		{
			$pct = min(100.0, max(0.0, $disc));
			$total_discount = round($subtotal * $pct / 100, 2);
			foreach ($eligible as $i)
			{
				$line_discounts[$i] = round(max(0.0, (float) $lines[$i]['price']) * $pct / 100, 2);
			}
		} elseif ($v['apply'] === 'each') {
			foreach ($eligible as $i)
			{
				$line_discounts[$i] = round(min($disc, max(0.0, (float) $lines[$i]['price'])), 2);
				$total_discount += $line_discounts[$i];
			}
			$total_discount = round($total_discount, 2);
		} else {
			$total_discount = round(min($disc, $subtotal), 2);
			$left = $total_discount;
			foreach ($eligible as $i)
			{
				$part = round(min($left, max(0.0, (float) $lines[$i]['price'])), 2);
				$line_discounts[$i] = $part;
				$left = round($left - $part, 2);
			}
		}
		$total_discount = min($total_discount, $subtotal);
		return array('discount' => max(0.0, $total_discount), 'line_discounts' => $line_discounts);
	}

	/**
	 * Order totals with a discount. With a zero discount the result is exactly what the script always calculated.
	 */
	public static function totals($price, $discount, $option_arr)
	{
		$price = (float) $price;
		$discount = min((float) $discount, $price);
		$net = $price - $discount;
		$tax = 0;
		if ((float) $option_arr['o_tax'] > 0)
		{
			$tax = ($net * (float) $option_arr['o_tax']) / 100;
		}
		$total = $net + $tax;
		$deposit = 0;
		switch ($option_arr['o_deposit_type'])
		{
			case 'percent':
				$deposit = ($total * (float) $option_arr['o_deposit']) / 100;
				break;
			case 'amount':
				$deposit = (float) $option_arr['o_deposit'];
				if ($discount > 0)
				{
					$deposit = min($deposit, $total);
				}
				break;
		}
		if ($discount > 0)
		{
			$tax = round($tax, 2);
			$total = round($total, 2);
			$deposit = round($deposit, 2);
		}
		return compact('price', 'discount', 'net', 'tax', 'total', 'deposit');
	}

	/* ---------------------------------------------------------------- session */

	public static function getSessionCode()
	{
		return (isset($_SESSION[self::SESSION_KEY]) && is_string($_SESSION[self::SESSION_KEY]) && $_SESSION[self::SESSION_KEY] !== '') ? $_SESSION[self::SESSION_KEY] : null;
	}

	public static function setSessionCode($code)
	{
		$_SESSION[self::SESSION_KEY] = (string) $code;
	}

	public static function clearSession()
	{
		if (isset($_SESSION[self::SESSION_KEY]))
		{
			$_SESSION[self::SESSION_KEY] = NULL;
			unset($_SESSION[self::SESSION_KEY]);
		}
	}

	/**
	 * Very small brute-force guard for the code box: MAX_FAILS wrong codes inside FAIL_WINDOW seconds block further tries.
	 */
	public static function isThrottled()
	{
		$f = isset($_SESSION[self::SESSION_FAILS]) ? $_SESSION[self::SESSION_FAILS] : null;
		if (!is_array($f) || time() - (int) $f['since'] > self::FAIL_WINDOW)
		{
			return false;
		}
		return (int) $f['count'] >= self::MAX_FAILS;
	}

	public static function registerFailure()
	{
		$f = isset($_SESSION[self::SESSION_FAILS]) ? $_SESSION[self::SESSION_FAILS] : null;
		if (!is_array($f) || time() - (int) $f['since'] > self::FAIL_WINDOW)
		{
			$f = array('since' => time(), 'count' => 0);
		}
		$f['count']++;
		$_SESSION[self::SESSION_FAILS] = $f;
	}

	public static function resetFailures()
	{
		unset($_SESSION[self::SESSION_FAILS]);
	}

	/* ------------------------------------------------------------------ locks */

	/**
	 * Serialises "check the usage limit + save the booking" for one voucher (concurrent bookings).
	 */
	public static function lock($code, $seconds = 8)
	{
		$name = 'as_vch_' . md5(PJ_PREFIX . PJ_SCRIPT_PREFIX . strtolower($code));
		$row = pjAppModel::factory()->prepare("SELECT GET_LOCK(:name, :sec) AS `l`;")->exec(array('name' => $name, 'sec' => (int) $seconds))->getDataIndex(0);
		$ok = !empty($row) && (int) $row['l'] === 1;
		if ($ok)
		{
			register_shutdown_function(function () use ($name) {
				try {
					pjAppModel::factory()->prepare("SELECT RELEASE_LOCK(:name);")->exec(array('name' => $name));
				} catch (Exception $e) {
				}
			});
		}
		return $ok ? $name : false;
	}

	public static function unlock($name)
	{
		if ($name)
		{
			pjAppModel::factory()->prepare("SELECT RELEASE_LOCK(:name);")->exec(array('name' => $name));
		}
	}

	/* -------------------------------------------------------------- formatting */

	public static function discountLabel($v)
	{
		if (empty($v))
		{
			return '';
		}
		return $v['type'] === 'percent' ? rtrim(rtrim(number_format((float) $v['discount'], 2, '.', ''), '0'), '.') . '%' : html_entity_decode(pjCurrency::formatPrice($v['discount']), ENT_QUOTES, 'UTF-8'); // the currency sign comes as an HTML entity (e.g. &euro;); the label is escaped again when printed
	}
}
?>
