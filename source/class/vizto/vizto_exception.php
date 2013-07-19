<?php defined('IN_DISCUZ') || die('Access Denied');
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 13-5-6
 * Time: 上午7:25
 * To change this template use File | Settings | File Templates.
 */


class vizto_exception extends Exception {

	public static $php_errors = array(
		E_ERROR             => 'Fatal Error',
		E_USER_ERROR        => 'User Error',
		E_PARSE             => 'Parse Error',
		E_WARNING           => 'Warning',
		E_USER_WARNING      => 'User Warning',
		E_STRICT            => 'Strict',
		E_NOTICE            => 'Notice',
		E_RECOVERABLE_ERROR => 'Recoverable Error',

		E_CORE_ERROR        => 'Core Error',
		E_CORE_WARNING      => 'Core Warning',
		E_COMPILE_ERROR     => 'Compile Error',
		E_COMPILE_WARNING   => 'Compile Warning',
		E_USER_NOTICE       => 'User Notice',
		E_ALL               => 'All',
	);

	/**
	 * Creates a new translated exception.
	 *
	 *     throw new Kohana_Exception('Something went terrible wrong, :user',
	 *         array(':user' => $user));
	 *
	 * @param   string         $message    error message
	 * @param   array          $variables  translation variables
	 * @param   integer|string $code       the exception code
	 *
	 * @return  void
	 */
	public function __construct($message, array $variables = NULL, $code = 0) {
		if(defined('E_DEPRECATED')) {
			// E_DEPRECATED only exists in PHP >= 5.3.0
			self::$php_errors[E_DEPRECATED]      = 'Deprecated';
			self::$php_errors[E_USER_DEPRECATED] = 'User Deprecated';
		}
		/**/

		// Set the message
		$message = __($message, $variables);

		// Pass the message and integer code to the parent
		parent::__construct($message, (int)$code);

		// Save the unmodified code
		// @link http://bugs.php.net/39615
		$this->code = $code;
	}

	/**
	 * Get a single line of text representing the exception:
	 *
	 * Error [ Code ]: Message ~ File [ Line ]
	 *
	 * @param   Exception $e
	 *
	 * @return  string
	 */
	public static function text(Exception $e) {
		return sprintf(
			'%s [ %s ]: %s ~ %s [ %d ]',
			get_class($e), $e->getCode(), strip_tags($e->getMessage()), vizto_debug::path($e->getFile()), $e->getLine()
		);
	}

	/**
	 * Magic object-to-string method.
	 *
	 *     echo $exception;
	 *
	 * @uses    Kohana_Exception::text
	 * @return  string
	 */
	public function __toString() {
		return self::text($this);
	}

	public static function Error_Handler($code, $error, $file = NULL, $line = NULL, array $errcontext = array()) {
		if(error_reporting() & $code) {
			list($showtrace, $logtrace) = self::debug_backtrace();

			GLOBAL $_ERRORS;
			gettype($_ERRORS) != 'array' && $_ERRORS = array();
			array_push($_ERRORS, array(self::$php_errors[$code], $error, $file, $line, $errcontext, $showtrace));
		}
		if(error_reporting() && $code) {
			if(($error = error_get_last()) && $error['type']) {



			}
			// This error is not suppressed by current error reporting settings
			// Convert the error into an ErrorException
			//throw new ErrorException($error, $code, 0, $file, $line);
		}

		// Do not execute the PHP error handler
		return TRUE;
	}

	/**
	 * Inline exception handler, displays the error message, source of the
	 * exception, and the stack trace of the error.
	 *
	 * @uses    Kohana_Exception::text
	 *
	 * @param   Exception $e
	 *
	 * @return  boolean
	 */
	public static function Exception_Handler(Exception $e) {
		try {
			// Get the exception information
			$type    = get_class($e);
			$code    = $e->getCode();
			$message = $e->getMessage();
			$file    = $e->getFile();
			$line    = $e->getLine();
			global $_G;
			$_G['Exception'][] = array($e);
			// Get the exception backtrace
			$trace = $e->getTrace();

			if($e instanceof ErrorException) {
				if(isset(self::$php_errors[$code])) {
					// Use the human-readable error name
					$code = self::$php_errors[$code];
				}

				if(version_compare(PHP_VERSION, '5.3', '<')) {
					// Workaround for a bug in ErrorException::getTrace() that
					// exists in all PHP 5.2 versions.
					// @link http://bugs.php.net/45895
					for($i = count($trace) - 1; $i > 0; --$i) {
						if(isset($trace[$i - 1]['args'])) {
							// Re-position the args
							$trace[$i]['args'] = $trace[$i - 1]['args'];

							// Remove the args
							unset($trace[$i - 1]['args']);
						}
					}
				}
			}

			// Create a text version of the exception
			$error = self::text($e);


			// Start an output buffer
			ob_start();
			// Include the exception HTML
			if($view_file = DISCUZ_ROOT.'source/kohana/system/views/kohana/error.php') {
				include $view_file;
			} else {
				throw new self('Error view file does not exist: views/:file', array(
					':file' => self::$error_view,
				));
			}

			// Display the contents of the output buffer
			echo ob_get_clean();

			exit(1);
		} catch(Exception $e) {
			// Clean the output buffer if one exists
			ob_get_level() and ob_clean();

			// Display the exception text
			echo self::text($e), "\n";

			// Exit with an error status
			exit(1);
		}
	}

	public static function Shutdown_Handler() {
		//print_r(spl_autoload_functions (  ));
	}

	public static function debug_backtrace() {
		$skipfunc[] = 'core::handleError';
		$skipfunc[] = 'vizto_exception::debug_backtrace';
		$skipfunc[] = 'vizto_exception::Error_Handler';
		$skipfunc[] = 'discuz_error->debug_backtrace';
		$skipfunc[] = 'discuz_error->db_error';
		$skipfunc[] = 'discuz_error->template_error';
		$skipfunc[] = 'discuz_error->system_error';
		$skipfunc[] = 'db_mysql->halt';
		$skipfunc[] = 'db_mysql->query';
		$skipfunc[] = 'DB::_execute';

		$show            = $log = '';
		$debug_backtrace = debug_backtrace();
		krsort($debug_backtrace);
		foreach($debug_backtrace as $k => $error) {
			$file = isset($error['file']) ? str_replace(DISCUZ_ROOT, '', $error['file']) : '';
			$func = isset($error['class']) ? $error['class'] : '';
			$func .= isset($error['type']) ? $error['type'] : '';
			$func .= isset($error['function']) ? $error['function'] : '';
			if(in_array($func, $skipfunc)) {
				break;
			}
			$error['line'] = isset($error['line']) ? sprintf('%04d', $error['line']) : '';

			$show .= "<li>[Line: $error[line]]".$file."($func)</li>";
			$log .= !empty($log) ? ' -> ' : '';
			$file.':'.$error['line'];
			$log .= $file.':'.$error['line'];
		}

		return array($show, $log);
	}
}

// Enable Kohana exception handling, adds stack traces and error source.
//set_exception_handler(array('vizto_exception', 'handler'));

// Enable Kohana error handling, converts all PHP errors to exceptions.
//set_error_handler(array('vizto_exception', 'error_handler'));

