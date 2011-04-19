<?php
/**
 * PHPIMS
 *
 * Copyright (c) 2011 Christer Edvartsen <cogo@starzinger.net>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * * The above copyright notice and this permission notice shall be included in
 *   all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @package PHPIMS
 * @subpackage Unittests
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/phpims
 */

namespace PHPIMS\Operation\Plugin\ManipulateImagePlugin\Transformation;

use \Mockery as m;
use \Imagine\ImageInterface;
use \Imagine\Image\Box;

/**
 * @package PHPIMS
 * @subpackage Unittests
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011, Christer Edvartsen
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/christeredvartsen/phpims
 */
class ResizeTest extends \PHPUnit_Framework_TestCase {
    /**
     * @expectedException PHPIMS\Operation\Plugin\ManipulateImagePlugin\Transformation\Exception
     * @expectedExceptionMessage Missing parameters width and/or height
     */
    public function testApplyWithNoParameters() {
        $image = m::mock('Imagine\\ImageInterface');
        $transformation = new Resize;
        $transformation->apply($image);
    }

    public function testApplyWithBothParams() {
        $size  = m::mock('Imagine\\Image\\Size');
        $image = m::mock('Imagine\\ImageInterface');
        $image->shouldReceive('getSize')->once()->andReturn($size);

        $image->shouldReceive('resize')->once()->with(m::on(function(Box $box) {
            return $box->getWidth() === 200 && $box->getHeight() === 100;
        }));

        $transformation = new Resize;
        $transformation->apply($image, array('width' => 200, 'height' => 100));
    }

    public function testApplyWithOnlyWidth() {
        $size = m::mock('Imagine\\Image\\Size');
        $size->shouldReceive('getHeight')->once()->andReturn(1000);
        $size->shouldReceive('getWidth')->once()->andReturn(1000);
        $image = m::mock('Imagine\\ImageInterface');
        $image->shouldReceive('getSize')->once()->andReturn($size);

        $image->shouldReceive('resize')->once()->with(m::on(function(Box $box) {
            return $box->getWidth() === 200 && $box->getHeight() === 200;
        }));

        $transformation = new Resize;
        $transformation->apply($image, array('width' => 200));
    }

    public function testApplyWithOnlyHeight() {
        $size = m::mock('Imagine\\Image\\Size');
        $size->shouldReceive('getHeight')->once()->andReturn(1000);
        $size->shouldReceive('getWidth')->once()->andReturn(1000);
        $image = m::mock('Imagine\\ImageInterface');
        $image->shouldReceive('getSize')->once()->andReturn($size);

        $image->shouldReceive('resize')->with(m::on(function(Box $box) {
            return $box->getWidth() === 200 && $box->getHeight() === 200;
        }))->once();

        $transformation = new Resize;
        $transformation->apply($image, array('height' => 200));
    }
}