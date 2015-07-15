<?php
/**
 * Created by PhpStorm.
 * User: andkon
 * Date: 18.05.15
 * Time: 16:08
 */

namespace tests;

/**
 * Class Base
 *
 * @package tests
 */
class Base extends \PHPUnit_Framework_TestCase
{
    /**
     * @param null   $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        if (!defined('TEST')) {
            define('TEST', true);
        }
    }
}
