<?php

namespace Wikimedia\Sites;

/**
 * Extension hooks
 */
class Hooks {

	/**
	 * @param array &$paths
	 * @return bool
	 */
	public static function onUnitTestsList( &$paths ) {
		$paths[] = __DIR__ . '/../tests/phpunit';

		return true;
	}

}
