<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
/**
 * Admin > Vouchers (discount codes).
 *
 * All values coming from the form are validated here, on the server (the JavaScript validation is only a convenience).
 * Times are posted as hour / minute (+ am/pm in the 12 hour format) and stored as 24-hour HH:MM:SS, see pjVoucherEngine.
 */
class pjAdminVouchers extends pjAdmin
{
	private function getServiceList()
	{
		return pjServiceModel::factory()
			->select("t1.id, t2.content AS `name`")
			->join('pjMultiLang', "t2.model='pjService' AND t2.foreign_id=t1.id AND t2.locale='".$this->getLocaleId()."' AND t2.field='name'", 'left outer')
			->where('t1.calendar_id', $this->getForeignId())
			->orderBy('`name` ASC')
			->findAll()
			->getData();
	}

	private function parseDate($value)
	{
		return pjVoucherEngine::parseDate($value, $this->option_arr['o_date_format']);
	}

	private function parseTime($prefix)
	{
		$mode = pjVoucherEngine::timeMode($this->option_arr['o_time_format']);
		return pjVoucherEngine::to24h(
			$this->_post->toString($prefix . '_hour_from'),
			$this->_post->toString($prefix . '_minute_from'),
			$this->_post->toString($prefix . '_ampm_from'),
			$mode
		);
	}

	/**
	 * Reads + validates the posted form. Returns array($data, $service_ids, $errors)
	 * $errors = list of label keys (translated by the view)
	 */
	private function collect($id = 0)
	{
		$data = array();
		$errors = array();
		$mode = pjVoucherEngine::timeMode($this->option_arr['o_time_format']);

		$code = pjVoucherEngine::cleanCode($this->_post->toString('code'));
		if (!pjVoucherEngine::isValidCodeFormat($code))
		{
			$errors[] = 'voucher_err_code';
		} else {
			$taken = pjVoucherModel::factory()->where('t1.calendar_id', $this->getForeignId())->where('t1.code', $code);
			if ((int) $id > 0)
			{
				$taken->where('t1.id !=', (int) $id);
			}
			if ($taken->findCount()->getData() > 0)
			{
				$errors[] = 'voucher_err_code_exists';
			}
		}
		$data['code'] = $code;

		$type = $this->_post->toString('type');
		$data['type'] = in_array($type, array('percent', 'amount'), true) ? $type : 'percent';
		$data['apply'] = $this->_post->toString('apply') === 'total' ? 'total' : 'each';

		$discount_raw = str_replace(',', '.', trim($this->_post->toString('discount')));
		$discount = is_numeric($discount_raw) ? round((float) $discount_raw, 2) : -1;
		if ($discount <= 0 || ($data['type'] === 'percent' && $discount > 100) || $discount > 99999999)
		{
			$errors[] = 'voucher_err_discount';
		}
		$data['discount'] = $discount > 0 ? $discount : 0;

		$valid = $this->_post->toString('valid');
		$data['valid'] = in_array($valid, array('always', 'fixed', 'period', 'recurring'), true) ? $valid : 'always';
		$data['valid_on'] = $valid === 'always' ? 'booking' : 'appointment'; // windows always refer to the appointment
		$data['date_from'] = ':NULL';
		$data['date_to'] = ':NULL';
		$data['time_from'] = ':NULL';
		$data['time_to'] = ':NULL';
		$data['every'] = ':NULL';
		switch ($data['valid'])
		{
			case 'always':
				$data['valid_on'] = 'booking';
				break;
			case 'fixed':
				$date = $this->parseDate($this->_post->toString('f_date'));
				$from = $this->parseTime('f');
				$to = pjVoucherEngine::to24h($this->_post->toString('f_hour_to'), $this->_post->toString('f_minute_to'), $this->_post->toString('f_ampm_to'), $mode);
				if ($date === false || $from === false || $to === false)
				{
					$errors[] = 'voucher_err_dates';
				} elseif ($to <= $from) {
					$errors[] = 'voucher_err_time_order';
				} else {
					$data['date_from'] = $date;
					$data['date_to'] = $date;
					$data['time_from'] = $from;
					$data['time_to'] = $to;
				}
				break;
			case 'period':
				$date_from = $this->parseDate($this->_post->toString('p_date_from'));
				$date_to = $this->parseDate($this->_post->toString('p_date_to'));
				$from = $this->parseTime('p');
				$to = pjVoucherEngine::to24h($this->_post->toString('p_hour_to'), $this->_post->toString('p_minute_to'), $this->_post->toString('p_ampm_to'), $mode);
				if ($date_from === false || $date_to === false || $from === false || $to === false)
				{
					$errors[] = 'voucher_err_dates';
				} elseif ($date_from . ' ' . $from >= $date_to . ' ' . $to) {
					$errors[] = 'voucher_err_period_order';
				} else {
					$data['date_from'] = $date_from;
					$data['date_to'] = $date_to;
					$data['time_from'] = $from;
					$data['time_to'] = $to;
				}
				break;
			case 'recurring':
				$every = $this->_post->toString('r_every');
				$from = $this->parseTime('r');
				$to = pjVoucherEngine::to24h($this->_post->toString('r_hour_to'), $this->_post->toString('r_minute_to'), $this->_post->toString('r_ampm_to'), $mode);
				if (!in_array($every, array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'), true) || $from === false || $to === false)
				{
					$errors[] = 'voucher_err_dates';
				} elseif ($to <= $from) {
					$errors[] = 'voucher_err_time_order';
				} else {
					$data['every'] = $every;
					$data['time_from'] = $from;
					$data['time_to'] = $to;
				}
				break;
		}

		foreach (array('max_uses', 'max_uses_per_email') as $field)
		{
			$raw = trim($this->_post->toString($field));
			if ($raw === '' || $raw === '0')
			{
				$data[$field] = ':NULL';
			} elseif (preg_match('/^\d{1,9}$/', $raw) && (int) $raw > 0) {
				$data[$field] = (int) $raw;
			} else {
				$errors[] = 'voucher_err_limit';
				$data[$field] = ':NULL';
			}
		}

		$data['status'] = $this->_post->toString('status') === 'F' ? 'F' : 'T';

		$service_ids = array();
		$posted = $this->_post->toArray('service_id');
		if (!empty($posted))
		{
			$valid_ids = array();
			foreach ($this->getServiceList() as $service)
			{
				$valid_ids[] = (int) $service['id'];
			}
			foreach ($posted as $sid)
			{
				if (in_array((int) $sid, $valid_ids, true))
				{
					$service_ids[] = (int) $sid;
				}
			}
			$service_ids = array_values(array_unique($service_ids));
			if (empty($service_ids))
			{
				// services were selected but none of them exists (tampered form / service deleted meanwhile):
				// never fall back to "all services" silently
				$errors[] = 'voucher_err_services';
			}
		}

		return array($data, $service_ids, array_values(array_unique($errors)));
	}

	private function saveServices($voucher_id, $service_ids)
	{
		$model = pjVoucherServiceModel::factory();
		$model->where('voucher_id', (int) $voucher_id)->eraseAll();
		foreach ($service_ids as $sid)
		{
			$model->reset()->setAttributes(array('voucher_id' => (int) $voucher_id, 'service_id' => (int) $sid))->insert();
		}
	}

	private function prepareForm()
	{
		$this->set('service_arr', $this->getServiceList());

		$this->appendCss('css/select2.min.css', PJ_THIRD_PARTY_PATH . 'select2/');
		$this->appendJs('js/select2.full.min.js', PJ_THIRD_PARTY_PATH . 'select2/');
		$this->appendCss('datepicker.css', PJ_THIRD_PARTY_PATH . 'bootstrap_datepicker/');
		$this->appendJs('bootstrap-datepicker.js', PJ_THIRD_PARTY_PATH . 'bootstrap_datepicker/');
		$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
		$this->appendJs('pjAdminVouchers.js');
	}

	/**
	 * Values for the form fields from a posted form (after a validation error)
	 */
	private function postedArr()
	{
		$arr = array();
		foreach (array('code', 'type', 'apply', 'discount', 'valid', 'valid_on', 'max_uses', 'max_uses_per_email', 'status', 'f_date', 'p_date_from', 'p_date_to', 'r_every') as $k)
		{
			$arr[$k] = $this->_post->toString($k);
		}
		foreach (array('f', 'p', 'r') as $p)
		{
			foreach (array('hour_from', 'minute_from', 'ampm_from', 'hour_to', 'minute_to', 'ampm_to') as $k)
			{
				$arr[$p . '_' . $k] = $this->_post->toString($p . '_' . $k);
			}
		}
		$arr['posted'] = 1;
		return $arr;
	}

	public function pjActionCheckCode()
	{
		$this->setAjax(true);

		if ($this->isXHR())
		{
			$code = pjVoucherEngine::cleanCode($this->_get->check('code') ? $this->_get->toString('code') : '');
			if (!pjVoucherEngine::isValidCodeFormat($code))
			{
				echo 'false';
				exit;
			}
			$model = pjVoucherModel::factory()->where('t1.calendar_id', $this->getForeignId())->where('t1.code', $code);
			if ($this->_get->check('id') && $this->_get->toInt('id') > 0)
			{
				$model->where('t1.id !=', $this->_get->toInt('id'));
			}
			echo $model->findCount()->getData() == 0 ? 'true' : 'false';
		}
		exit;
	}

	public function pjActionCreate()
	{
		$this->checkLogin();

		if (!pjAuth::factory()->hasAccess())
		{
			$this->sendForbidden();
			return;
		}

		if (self::isPost() && $this->_post->check('voucher_create'))
		{
			list($data, $service_ids, $errors) = $this->collect(0);
			if (empty($errors))
			{
				$data['calendar_id'] = $this->getForeignId();
				$id = pjVoucherModel::factory()->setAttributes($data)->insert()->getInsertId();
				if ($id !== false && (int) $id > 0)
				{
					$this->saveServices($id, $service_ids);
					pjUtil::redirect(sprintf("%s?controller=pjAdminVouchers&action=pjActionIndex&err=AV01", $_SERVER['PHP_SELF']));
				}
				pjUtil::redirect(sprintf("%s?controller=pjAdminVouchers&action=pjActionIndex&err=AV02", $_SERVER['PHP_SELF']));
			}
			$this->set('errors', $errors);
			$this->set('arr', $this->postedArr());
			$this->set('ve_arr', array_flip($service_ids));
		}
		$this->prepareForm();
	}

	public function pjActionUpdate()
	{
		$this->checkLogin();

		if (!pjAuth::factory()->hasAccess())
		{
			$this->sendForbidden();
			return;
		}

		$id = self::isPost() && $this->_post->check('id') ? $this->_post->toInt('id') : $this->_get->toInt('id');
		$arr = pjVoucherModel::factory()->where('t1.id', $id)->where('t1.calendar_id', $this->getForeignId())->limit(1)->findAll()->getData();
		if (empty($arr))
		{
			pjUtil::redirect(sprintf("%s?controller=pjAdminVouchers&action=pjActionIndex&err=AV08", $_SERVER['PHP_SELF']));
		}
		$arr = $arr[0];

		if (self::isPost() && $this->_post->check('voucher_update'))
		{
			list($data, $service_ids, $errors) = $this->collect($id);
			if (empty($errors))
			{
				$data['id'] = $id;
				pjVoucherModel::factory()->set('id', $id)->modify($data);
				$this->saveServices($id, $service_ids);
				pjUtil::redirect(sprintf("%s?controller=pjAdminVouchers&action=pjActionIndex&err=AV03", $_SERVER['PHP_SELF']));
			}
			$this->set('errors', $errors);
			$this->set('arr', array_merge($arr, $this->postedArr()));
			$this->set('ve_arr', array_flip($service_ids));
		} else {
			$this->set('arr', $arr);
			$this->set('ve_arr', pjVoucherServiceModel::factory()->where('t1.voucher_id', $id)->findAll()->getDataPair('service_id', 'service_id'));
		}
		$this->set('id', $id);
		$this->prepareForm();
	}

	public function pjActionDeleteVoucher()
	{
		$this->setAjax(true);

		if (!$this->isXHR())
		{
			self::jsonResponse(array('status' => 'ERR', 'code' => 100, 'text' => 'Missing headers.'));
		}
		if (!self::isPost())
		{
			self::jsonResponse(array('status' => 'ERR', 'code' => 101, 'text' => 'HTTP method not allowed.'));
		}
		if (!pjAuth::factory()->hasAccess())
		{
			self::jsonResponse(array('status' => 'ERR', 'code' => 102, 'text' => 'Access denied.'));
		}
		$id = $this->_get->toInt('id');
		if ($id <= 0)
		{
			self::jsonResponse(array('status' => 'ERR', 'code' => 103, 'text' => 'Missing, empty or invalid parameters.'));
		}
		$pjVoucherModel = pjVoucherModel::factory();
		$arr = $pjVoucherModel->where('t1.id', $id)->where('t1.calendar_id', $this->getForeignId())->limit(1)->findAll()->getData();
		if (empty($arr))
		{
			self::jsonResponse(array('status' => 'ERR', 'code' => 103, 'text' => 'Voucher not found.'));
		}
		// bookings keep their own copy of the code and the discount, so nothing else has to be changed
		if ($pjVoucherModel->reset()->set('id', $id)->erase()->getAffectedRows() == 1)
		{
			pjVoucherServiceModel::factory()->where('voucher_id', $id)->eraseAll();
			self::jsonResponse(array('status' => 'OK', 'code' => 200, 'text' => ''));
		}
		self::jsonResponse(array('status' => 'ERR', 'code' => 105, 'text' => ''));
	}

	public function pjActionDeleteVoucherBulk()
	{
		$this->setAjax(true);

		if (!$this->isXHR())
		{
			self::jsonResponse(array('status' => 'ERR', 'code' => 100, 'text' => 'Missing headers.'));
		}
		if (!self::isPost())
		{
			self::jsonResponse(array('status' => 'ERR', 'code' => 101, 'text' => 'HTTP method not allowed.'));
		}
		if (!pjAuth::factory()->hasAccess())
		{
			self::jsonResponse(array('status' => 'ERR', 'code' => 102, 'text' => 'Access denied.'));
		}
		if (!$this->_post->has('record') || !($record = $this->_post->toArray('record')))
		{
			self::jsonResponse(array('status' => 'ERR', 'code' => 103, 'text' => 'Missing, empty or invalid data.'));
		}
		$ids = array_map('intval', $record);
		$mine = pjVoucherModel::factory()->where('t1.calendar_id', $this->getForeignId())->whereIn('t1.id', $ids)->findAll()->getDataPair(null, 'id');
		if (!empty($mine))
		{
			pjVoucherModel::factory()->whereIn('id', $mine)->eraseAll();
			pjVoucherServiceModel::factory()->whereIn('voucher_id', $mine)->eraseAll();
		}
		self::jsonResponse(array('status' => 'OK', 'code' => 200, 'text' => ''));
	}

	public function pjActionGetVoucher()
	{
		$this->setAjax(true);

		if (!$this->isXHR())
		{
			exit;
		}
		$pjVoucherModel = pjVoucherModel::factory()->where('t1.calendar_id', $this->getForeignId());

		if ($q = $this->_get->toString('q'))
		{
			$q = str_replace(array('%', '_'), array('\%', '\_'), trim($q));
			$pjVoucherModel->where('t1.code LIKE', "%$q%");
		}
		if ($this->_get->check('valid') && in_array($this->_get->toString('valid'), array('always', 'fixed', 'period', 'recurring'), true))
		{
			$pjVoucherModel->where('t1.valid', $this->_get->toString('valid'));
		}

		$column = 'id';
		$direction = 'DESC';
		$allowed_columns = array('id', 'code', 'type', 'discount', 'valid', 'status', 'max_uses', 'created');
		if ($this->_get->check('direction') && $this->_get->check('column') && in_array(strtoupper($this->_get->toString('direction')), array('ASC', 'DESC')) && in_array($this->_get->toString('column'), $allowed_columns))
		{
			$column = $this->_get->toString('column');
			$direction = strtoupper($this->_get->toString('direction'));
		}

		$total = $pjVoucherModel->findCount()->getData();
		$rowCount = $this->_get->check('rowCount') && $this->_get->toInt('rowCount') > 0 ? $this->_get->toInt('rowCount') : 10;
		$pages = ceil($total / $rowCount);
		$page = $this->_get->check('page') && $this->_get->toInt('page') > 0 ? $this->_get->toInt('page') : 1;
		if ($page > $pages)
		{
			$page = max(1, $pages);
		}
		$offset = ((int) $page - 1) * $rowCount;

		$data = $pjVoucherModel
			->select(array(sprintf("t1.*,
				(SELECT GROUP_CONCAT(TL.content SEPARATOR ', ')
					FROM `%s` AS TVS
					INNER JOIN `%s` AS TL ON TL.model='pjService' AND TL.foreign_id=TVS.service_id AND TL.locale='%u' AND TL.field='name'
					WHERE TVS.voucher_id = t1.id) AS `services`",
				pjVoucherServiceModel::factory()->getTable(),
				pjMultiLangModel::factory()->getTable(),
				$this->getLocaleId()
			)))
			->orderBy("`$column` $direction")->limit($rowCount, $offset)->findAll()->getData();

		$daynames = __('voucher_days', true);
		$valids = __('voucher_valids', true);
		$types = __('voucher_types', true);
		$o_time = $this->option_arr['o_time_format'];
		$o_date = $this->option_arr['o_date_format'];
		foreach ($data as $k => $v)
		{
			$data[$k]['discount_f'] = pjVoucherEngine::discountLabel($v);
			$data[$k]['services'] = !empty($v['services']) ? pjSanitize::html($v['services']) : __('lblAll', true);
			$data[$k]['code'] = pjSanitize::html($v['code']);
			$data[$k]['type_f'] = @$types[$v['type']];
			switch ($v['valid'])
			{
				case 'fixed':
					$data[$k]['valid_f'] = sprintf('%s, %s - %s', pjVoucherEngine::displayDate($v['date_from'], $o_date), pjVoucherEngine::displayTime($v['time_from'], $o_time), pjVoucherEngine::displayTime($v['time_to'], $o_time));
					break;
				case 'period':
					$data[$k]['valid_f'] = sprintf('%s %s &divide; %s %s', pjVoucherEngine::displayDate($v['date_from'], $o_date), pjVoucherEngine::displayTime($v['time_from'], $o_time), pjVoucherEngine::displayDate($v['date_to'], $o_date), pjVoucherEngine::displayTime($v['time_to'], $o_time));
					break;
				case 'recurring':
					$data[$k]['valid_f'] = sprintf('%s, %s - %s', @$daynames[$v['every']], pjVoucherEngine::displayTime($v['time_from'], $o_time), pjVoucherEngine::displayTime($v['time_to'], $o_time));
					break;
				default:
					$data[$k]['valid_f'] = @$valids['always'];
			}
			$data[$k]['status_f'] = $v['status'] === 'T' ? __('voucher_status_active', true) : __('voucher_status_inactive', true);
		}

		pjAppController::jsonResponse(compact('data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction'));
	}

	/**
	 * Inline status switch of the list (the only inline-editable column)
	 */
	public function pjActionSaveVoucher()
	{
		$this->setAjax(true);

		if (!$this->isXHR())
		{
			self::jsonResponse(array('status' => 'ERR', 'code' => 100, 'text' => 'Missing headers.'));
		}
		if (!self::isPost())
		{
			self::jsonResponse(array('status' => 'ERR', 'code' => 101, 'text' => 'HTTP method not allowed.'));
		}
		if (!pjAuth::factory('pjAdminVouchers', 'pjActionUpdate')->hasAccess())
		{
			self::jsonResponse(array('status' => 'ERR', 'code' => 102, 'text' => 'Access denied.'));
		}
		$id = $this->_get->toInt('id');
		$arr = pjVoucherModel::factory()->where('t1.id', $id)->where('t1.calendar_id', $this->getForeignId())->limit(1)->findAll()->getData();
		if (empty($arr) || $this->_post->toString('column') !== 'status' || !in_array($this->_post->toString('value'), array('T', 'F'), true))
		{
			self::jsonResponse(array('status' => 'ERR', 'code' => 103, 'text' => 'Missing, empty or invalid data.'));
		}
		pjVoucherModel::factory()->where('id', $id)->limit(1)->modifyAll(array('status' => $this->_post->toString('value')));
		self::jsonResponse(array('status' => 'OK', 'code' => 201, 'text' => ''));
	}

	public function pjActionIndex()
	{
		$this->checkLogin();

		if (!pjAuth::factory()->hasAccess())
		{
			$this->sendForbidden();
			return;
		}

		$this->set('has_create', pjAuth::factory('pjAdminVouchers', 'pjActionCreate')->hasAccess());
		$this->set('has_update', pjAuth::factory('pjAdminVouchers', 'pjActionUpdate')->hasAccess());
		$this->set('has_delete', pjAuth::factory('pjAdminVouchers', 'pjActionDeleteVoucher')->hasAccess());
		$this->set('has_delete_bulk', pjAuth::factory('pjAdminVouchers', 'pjActionDeleteVoucherBulk')->hasAccess());

		$this->appendJs('jquery.datagrid.js', PJ_FRAMEWORK_LIBS_PATH . 'pj/js/');
		$this->appendJs('pjAdminVouchers.js');
	}
}
?>
