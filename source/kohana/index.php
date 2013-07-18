<?php

require_once 'common.php';

// Execute the request
Request::$initial->execute()
	->execute()
	->send_headers(TRUE)
	->body();
