<?php
	$dir = dirname(__FILE__);
	include("$dir/lib/init.php");

	load_plugins();
	$instances = $GLOBALS['data']->get_all('instances');

	foreach($instances as $key => $instance) {
		$instance = getPluginInstance($key);
		if(is_object($instance))
		{
			$cron = new SlackCron($instance->icfg['cron_interval']['minute'], $instance->icfg['cron_interval']['hour'], $instance->icfg['cron_interval']['day']);
			if(
					array_key_exists('cron', $instance->icfg) &&
					$instance->icfg['cron'] === TRUE &&
					method_exists($instance, 'onCron') &&
					$cron->isDue()
				) {


				// Run this instance's cron hook
				$ret = $instance->onCron();
				$out = $instance->getLog();

				$uid = uniqid('', true);

				$data->set('crons', $uid, array(
					'ts' => time(),
					'ret' => $ret,
					'out' => $out,
				));

				$list = $data->get('hook_lists', $instance->iid);
				dumper($list);
				$list[] = $uid;
				$data->set('hook_lists', $instance->iid, $list);
			}
		}
	}
?>