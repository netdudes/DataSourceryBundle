<?php
namespace Netdudes\DataSourceryBundle\Tests\Extension;

use Netdudes\DataSourceryBundle\Extension\BuiltInFunctionsExtension;
use Netdudes\DataSourceryBundle\Extension\Context;
use Netdudes\DataSourceryBundle\Extension\ContextAwareUqlFunction;
use Netdudes\DataSourceryBundle\Extension\Exception\FunctionNotFoundException;
use Netdudes\DataSourceryBundle\Extension\UqlExtensionContainer;
use Netdudes\DataSourceryBundle\Extension\UqlFunction;
use Netdudes\DataSourceryBundle\Extension\UqlFunctionCaller;
use Netdudes\DataSourceryBundle\Util\CurrentDateTimeProvider;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UqlFunctionCallerTest extends \PHPUnit_Framework_TestCase
{
    public function testItThrowsAnExceptionIsFunctionIsNotFound()
    {
        $uqlFunction = $this->prophesize(UqlFunction::class)->reveal();

        $uqlExtensionContainerProphecy = $this->prophesize(UqlExtensionContainer::class);
        $uqlExtensionContainerProphecy->getFunctions()->willReturn(['UqlFunction' => $uqlFunction]);
        $context = $this->prophesize(Context::class)->reveal();

        $uqlFunctionCaller = new UqlFunctionCaller($uqlExtensionContainerProphecy->reveal());
        $this->setExpectedException(FunctionNotFoundException::class, 'Could not find UQL function OtherUqlFunction');
        $uqlFunctionCaller->callFunction('OtherUqlFunction', [], $context);
    }

    public function testItCallsFunctionWithContextParameter()
    {
        $context = $this->prophesize(Context::class)->reveal();

        $uqlFunctionProphecy = $this->prophesize(UqlFunction::class);
        $uqlFunctionProphecy->call([], $context)->shouldBeCalled();

        $uqlExtensionContainerProphecy = $this->prophesize(UqlExtensionContainer::class);
        $uqlExtensionContainerProphecy->getFunctions()->willReturn(['UqlFunction' => $uqlFunctionProphecy->reveal()]);

        $uqlFunctionCaller = new UqlFunctionCaller($uqlExtensionContainerProphecy->reveal());
        $uqlFunctionCaller->callFunction('UqlFunction', [], $context);
    }
}
