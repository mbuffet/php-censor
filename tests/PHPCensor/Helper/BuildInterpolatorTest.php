<?php

/**
 * PHPCI - Continuous Integration for PHP
 *
 * @copyright    Copyright 2015, Block 8 Limited.
 * @license      https://github.com/Block8/PHPCI/blob/master/LICENSE.md
 * @link         https://www.phptesting.org/
 */

namespace Tests\PHPCensor\Plugin\Helper;

use PHPCensor\Helper\BuildInterpolator;

class BuildInterpolatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BuildInterpolator
     */
    protected $testedInterpolator;

    protected function setUp()
    {
        parent::setup();
        $this->testedInterpolator = new BuildInterpolator();
    }

    public function testInterpolate_LeavesStringsUnchangedByDefault()
    {
        $string = "Hello World";
        $expectedOutput = "Hello World";

        $actualOutput = $this->testedInterpolator->interpolate($string);

        $this->assertEquals($expectedOutput, $actualOutput);
    }

    public function testInterpolate_LeavesStringsUnchangedWhenBuildIsSet()
    {
        $build = $this->prophesize('PHPCensor\\Model\\Build')->reveal();

        $string         = "Hello World";
        $expectedOutput = "Hello World";

        $this->testedInterpolator->setupInterpolationVars(
            $build,
            "/buildpath/",
            "php-censor.local"
        );

        $actualOutput = $this->testedInterpolator->interpolate($string);

        $this->assertEquals($expectedOutput, $actualOutput);
    }
}

