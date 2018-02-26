<?php
declare(strict_types=1);

namespace Brille24\Test\Service;

use Brille24\CustomerOptionsPlugin\Entity\CustomerOptions\CustomerOption;
use Brille24\CustomerOptionsPlugin\Entity\CustomerOptions\CustomerOptionInterface;
use Brille24\CustomerOptionsPlugin\Entity\CustomerOptions\CustomerOptionValueInterface;
use Brille24\CustomerOptionsPlugin\Enumerations\CustomerOptionTypeEnum;
use Brille24\CustomerOptionsPlugin\Services\CustomerOptionValueResolver;
use Brille24\CustomerOptionsPlugin\Services\CustomerOptionValueResolverInterface;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class CustomerOptionValueResolverTest extends TestCase
{
    /** @var CustomerOptionValueResolverInterface */
    private $valueResolver;

    public function setUp()
    {
        $this->valueResolver = new CustomerOptionValueResolver();
    }

    private function createCustomOptionValue(
        string $code,
        string $value,
        ?CustomerOptionInterface $customerOption =null
    ): CustomerOptionValueInterface {
        $customerOptionValue = self::createMock(CustomerOptionValueInterface::class);
        $customerOptionValue->method('getCode')->willReturn($code);
        $customerOptionValue->method('getName')->willReturn($value);
        $customerOptionValue->method('getCustomerOption')->willReturn($customerOption);

        return $customerOptionValue;
    }

    /** @dataProvider dataResolveWithNonSelect */
    public function testResolveWithNonSelect(string $type, string $value): void
    {
        $customerOption = self::createMock(CustomerOptionInterface::class);
        $customerOption->method('getType')->willReturn($type);

        $result = $this->valueResolver->resolve($customerOption, $value);
        self::assertNull($result);
    }

    public function dataResolveWithNonSelect(): array
    {
        return [
            'text'     => [CustomerOptionTypeEnum::TEXT, 'some_string'],
            'integer'  => [CustomerOptionTypeEnum::NUMBER, '4334'],
            'boolean'  => [CustomerOptionTypeEnum::BOOLEAN, 'true'],
            'file'     => [CustomerOptionTypeEnum::FILE, 'some_file'],
            'datetime' => [CustomerOptionTypeEnum::DATETIME, date('Y-m-D H:i:s', time())]
        ];
    }

    public function testResolveWithValues(): void
    {

        $customerOption = self::createMock(CustomerOptionInterface::class);
        $customerOption->method('getType')->willReturn(CustomerOptionTypeEnum::SELECT);

        $selectedValue = $this->createCustomOptionValue('some_value', 'value', $customerOption);
        $otherValue = $this->createCustomOptionValue('some_other_value', 'meep value', $customerOption);
        $customerOption->method('getValues')->willReturn(new ArrayCollection([$selectedValue, $otherValue]));


        $value = $this->valueResolver->resolve($customerOption, 'some_value');
        self::assertEquals($selectedValue, $value);
    }

    public function testResolveWithNonExistingValue()
    {
        $customerOption = self::createMock(CustomerOptionInterface::class);
        $customerOption->method('getType')->willReturn(CustomerOptionTypeEnum::SELECT);

        $values = [
            $this->createCustomOptionValue('some_value', 'value', $customerOption)
        ];
        $customerOption->method('getValues')->willReturn(new ArrayCollection($values));

        $value = $this->valueResolver->resolve($customerOption, 'something');
        self::assertNull($value);
    }
}
