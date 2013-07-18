<?php

try {
	require_once 'common.php';
} catch(HTTP_Exception_404 $e) {
	// The request did not match any routes; ignore the 404 exception.
}