<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2017 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\content\tests\framework;

abstract class trigger_error_db_test_case extends \phpbb_database_test_case
{
	/** @var array */
	private $errors;

	/**
	* Define the extensions to be tested
	*
	* @return array vendor/name of extension(s) to test
	*/
	static protected function setup_extensions()
	{
		return array(
			'blitze/sitemaker',
			'blitze/content',
		);
	}

	/**
	 * Test setup
	 */
	protected function setUp() {
		parent::setUp();

        $this->errors = array();
        set_error_handler(array($this, 'errorHandler'));
    }

	/**
	 * Credit: https://www.sitepoint.com/testing-error-conditions-with-phpunit/
	 */
    public function errorHandler($errno, $errstr, $errfile, $errline, $errcontext)
    {
        $this->errors[] = compact('errno', 'errstr', 'errfile', 'errline', 'errcontext');
    }

	/**
	 * Credit: https://www.sitepoint.com/testing-error-conditions-with-phpunit/
	 */
    public function assertError($errstr)
    {
        foreach ($this->errors as $error)
        {
            if ($error['errstr'] === $errstr)
            {
                return;
            }
        }
        $this->fail('Error message "' . $errstr . '" not found in ', var_export($this->errors, true));
    }
}
