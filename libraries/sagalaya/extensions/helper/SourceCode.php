<?php

namespace sagalaya\extensions\helper;

use lithium\analysis\Inspector;

class SourceCode extends \lithium\template\Helper {

	public function lines($data, $start, $end) {
		return Inspector::lines($data, range($start, $end));
	}
}

?>