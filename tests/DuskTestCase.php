<?php
namespace Tests;

use Laravel\Dusk\TestCase as BaseTestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Faker\Factory;

abstract class DuskTestCase extends BaseTestCase
{

    use CreatesApplication;
    /**
     * @var \Faker\Generator
     */
    protected $faker;

    public function setUp()
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     * @return void
     */
    public static function prepare()
    {
        static::startChromeDriver();
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        return RemoteWebDriver::create(
                'http://localhost:9515', DesiredCapabilities::chrome()
        );
    }
}