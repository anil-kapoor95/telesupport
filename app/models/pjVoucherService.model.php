<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjVoucherServiceModel extends pjAppModel
{
	protected $primaryKey = null;

	protected $table = 'vouchers_services';

	protected $schema = array(
		array('name' => 'voucher_id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'service_id', 'type' => 'int', 'default' => ':NULL')
	);

	public static function factory($attr=array())
	{
		return new pjVoucherServiceModel($attr);
	}
}
?>
