<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Test\Unit\Model;

use Magento\PageCache\Model\DepersonalizeChecker;

class DepersonalizeCheckerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Framework\Module\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleManagerMock;

    /**
     * @var \Magento\PageCache\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheConfigMock;

    public function setup()
    {
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $this->moduleManagerMock = $this->getMock('Magento\Framework\Module\Manager', [], [], '', false);
        $this->cacheConfigMock = $this->getMock('Magento\PageCache\Model\Config', [], [], '', false);
    }

    /**
     * @param array $requestResult
     * @param bool $moduleManagerResult
     * @param bool $cacheConfigResult
     * @param bool $layoutResult
     * @param bool $can Depersonalize
     * @dataProvider checkIfDepersonalizeDataProvider
     */
    public function testCheckIfDepersonalize(
        array $requestResult,
        $moduleManagerResult,
        $cacheConfigResult,
        $layoutResult,
        $canDepersonalize
    ) {
        $this->requestMock->expects($this->any())->method('isAjax')->willReturn($requestResult['ajax']);
        $this->requestMock->expects($this->any())->method('isGet')->willReturn($requestResult['get']);
        $this->requestMock->expects($this->any())->method('isHead')->willReturn($requestResult['head']);
        $this->moduleManagerMock
            ->expects($this->any())
            ->method('isEnabled')
            ->with('Magento_PageCache')
            ->willReturn($moduleManagerResult);

        $this->cacheConfigMock->expects($this->any())->method('isEnabled')->willReturn($cacheConfigResult);
        $layoutMock = $this->getMockForAbstractClass('Magento\Framework\View\LayoutInterface', [], '', false);
        $layoutMock->expects($this->any())->method('isCacheable')->willReturn($layoutResult);

        $object = new DepersonalizeChecker($this->requestMock, $this->moduleManagerMock, $this->cacheConfigMock);
        $this->assertEquals($canDepersonalize, $object->checkIfDepersonalize($layoutMock));
    }

    /**
     * return array
     */
    public function checkIfDepersonalizeDataProvider()
    {
        return [
            [['ajax' => false, 'get' => true, 'head' => false], true, true, true, true],
            [['ajax' => false, 'get' => false, 'head' => true], true, true, true, true],
            [['ajax' => false, 'get' => false, 'head' => false], true, true, true, false],
            [['ajax' => true, 'get' => true, 'head' => false], true, true, true, false],
            [['ajax' => false, 'get' => true, 'head' => false], false, true, true, false],
            [['ajax' => false, 'get' => true, 'head' => false], true, false, true, false],
            [['ajax' => false, 'get' => true, 'head' => false], true, true, false, false],
        ];
    }
}
