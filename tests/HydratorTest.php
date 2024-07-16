<?php

namespace SergiX44\Hydrator\Tests;

use Illuminate\Container\Container;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SergiX44\Hydrator\Exception;
use SergiX44\Hydrator\Exception\InvalidObjectException;
use SergiX44\Hydrator\Hydrator;
use SergiX44\Hydrator\HydratorInterface;
use SergiX44\Hydrator\Tests\Fixtures\DI\Forest;
use SergiX44\Hydrator\Tests\Fixtures\DI\Leaves;
use SergiX44\Hydrator\Tests\Fixtures\DI\Sun;
use SergiX44\Hydrator\Tests\Fixtures\DI\Tree;
use SergiX44\Hydrator\Tests\Fixtures\DI\Wood;
use SergiX44\Hydrator\Tests\Fixtures\ObjectWithAbstract;
use SergiX44\Hydrator\Tests\Fixtures\ObjectWithArrayOfAbstracts;
use SergiX44\Hydrator\Tests\Fixtures\ObjectWithInvalidAbstract;
use SergiX44\Hydrator\Tests\Fixtures\Resolver\AppleResolver;
use SergiX44\Hydrator\Tests\Fixtures\Store\Apple;
use SergiX44\Hydrator\Tests\Fixtures\Store\AppleJack;
use SergiX44\Hydrator\Tests\Fixtures\Store\AppleSauce;
use SergiX44\Hydrator\Tests\Fixtures\Store\Fruit;
use SergiX44\Hydrator\Tests\Fixtures\Store\Tag;
use SergiX44\Hydrator\Tests\Fixtures\Store\TagPrice;
use TypeError;

class HydratorTest extends TestCase
{
    public function testContracts(): void
    {
        $hydrator = new Hydrator();

        $this->assertInstanceOf(HydratorInterface::class, $hydrator);
    }

    public function testInvalidObject(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The '.Hydrator::class.'::hydrate() method '.
            'expects an object or name of an existing class.');

        (new Hydrator())->hydrate('Undefined', []);
    }

    public function testUninitializableObject(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The '.Fixtures\UninitializableObject::class.' object '.
            'cannot be hydrated because its constructor has required parameters.');

        (new Hydrator())->hydrate(Fixtures\UninitializableObject::class, []);
    }

    public function testInvalidData(): void
    {
        $this->expectException(TypeError::class);

        (new Hydrator())->hydrate(Fixtures\ObjectWithString::class, null);
    }

    public function testInvalidJson(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to decode JSON: Syntax error');

        (new Hydrator())->hydrateWithJson(Fixtures\ObjectWithString::class, '!');
    }

    public function testIgnoreStaticalProperty(): void
    {
        $object = (new Hydrator())->hydrate(Fixtures\ObjectWithStaticalProperty::class, ['value' => 'foo']);

        $this->assertNotSame('foo', $object::$value);
    }

    public function testUntypedProperty(): void
    {
        $this->expectException(Exception\UntypedPropertyException::class);
        $this->expectExceptionMessage('The ObjectWithUntypedProperty.value property '.
            'is not typed.');

        (new Hydrator())->hydrate(Fixtures\ObjectWithUntypedProperty::class, []);
    }

    public function testUnionPropertyType(): void
    {
        if (\PHP_MAJOR_VERSION < 8) {
            $this->markTestSkipped('php >= 8 is required.');
        }

        $this->expectException(Exception\UnsupportedPropertyTypeException::class);
        $this->expectExceptionMessage('The ObjectWithIntOrFloat.value property cannot be hydrated automatically. Please define an union type resolver attribute or remove the union type.');

        (new Hydrator())->hydrate(Fixtures\ObjectWithIntOrFloat::class, []);
    }

    public function testAnnotatedUnionPropertyWithTagPriceType(): void
    {
        if (\PHP_MAJOR_VERSION < 8) {
            $this->markTestSkipped('php >= 8 is required.');
        }

        $o = (new Hydrator())->hydrate(Fixtures\ObjectWithUnionAndAttribute::class, [
            'tag' => [
                'name'  => 'foo',
                'price' => 1.00,
            ],
        ]);

        $this->assertInstanceOf(TagPrice::class, $o->tag);
    }

    public function testAnnotatedUnionPropertyWithTagType(): void
    {
        if (\PHP_MAJOR_VERSION < 8) {
            $this->markTestSkipped('php >= 8 is required.');
        }

        $o = (new Hydrator())->hydrate(Fixtures\ObjectWithUnionAndAttribute::class, [
            'tag' => [
                'name' => 'foo',
            ],
        ]);

        $this->assertInstanceOf(Tag::class, $o->tag);
    }

    public function testHydrateAnnotatedPropertyWhenDisabledAnnotations(): void
    {
        $this->expectException(Exception\MissingRequiredValueException::class);
        $this->expectExceptionMessage('The ObjectWithAnnotatedAlias.value property '.
            'is required.');

        (new Hydrator())->hydrate(Fixtures\ObjectWithAnnotatedAlias::class, ['non-normalized-value' => 'foo']);
    }

    public function testHydrateAttributedProperty(): void
    {
        if (\PHP_MAJOR_VERSION < 8) {
            $this->markTestSkipped('php >= 8 is required.');
        }

        $object = (new Hydrator())->hydrate(
            Fixtures\ObjectWithAttributedAlias::class,
            ['non-normalized-value' => 'foo']
        );

        $this->assertSame('foo', $object->value);
    }

    public function testHydrateAttributedPropertyUsingNormalizedKey(): void
    {
        if (\PHP_MAJOR_VERSION < 8) {
            $this->markTestSkipped('php >= 8 is required.');
        }

        $object = (new Hydrator())->hydrate(Fixtures\ObjectWithAttributedAlias::class, ['value' => 'foo']);

        $this->assertSame('foo', $object->value);
    }

    public function testRequiredProperty(): void
    {
        $this->expectException(Exception\MissingRequiredValueException::class);
        $this->expectExceptionMessage('The ObjectWithString.value property '.
            'is required.');

        (new Hydrator())->hydrate(Fixtures\ObjectWithString::class, []);
    }

    public function testUnsupportedPropertyType(): void
    {
        $this->expectException(Exception\UnsupportedPropertyTypeException::class);
        $this->expectExceptionMessage('The ObjectWithUnsupportedType.value property '.
            'contains an unsupported type iterable.');

        (new Hydrator())->hydrate(Fixtures\ObjectWithUnsupportedType::class, ['value' => false]);
    }

    public function testOptionalProperty(): void
    {
        $object = (new Hydrator())->hydrate(Fixtures\ObjectWithOptionalString::class, []);

        $this->assertSame('75c4c2a0-e352-4eda-b2ed-b7f713ffb9ff', $object->value);
    }

    public function testHydrateObject(): void
    {
        $source = new Fixtures\ObjectWithString();

        // should return the source object...
        $object = (new Hydrator())->hydrate($source, ['value' => 'foo']);

        $this->assertSame($source, $object);
        $this->assertSame('foo', $source->value);
    }

    public function testHydrateUsingDataObject(): void
    {
        $object = (new Hydrator())->hydrate(Fixtures\ObjectWithString::class, (object) ['value' => 'foo']);

        $this->assertSame('foo', $object->value);
    }

    public function testHydrateWithJson(): void
    {
        $object = (new Hydrator())->hydrateWithJson(Fixtures\ObjectWithString::class, '{"value": "foo"}');

        $this->assertSame('foo', $object->value);
    }

    public function testConvertEmptyStringToNullForNonStringType(): void
    {
        $object = (new Hydrator())->hydrate(Fixtures\ObjectWithNullableInt::class, ['value' => '']);

        $this->assertNull($object->value);
    }

    public function testHydrateNullablePropertyWithNull(): void
    {
        $object = (new Hydrator())->hydrate(Fixtures\ObjectWithNullableString::class, ['value' => null]);

        $this->assertNull($object->value);
    }

    public function testHydrateUnnullablePropertyWithNull(): void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithString.value property '.
            'cannot accept null.');

        (new Hydrator())->hydrate(Fixtures\ObjectWithString::class, ['value' => null]);
    }

    /**
     * @dataProvider booleanValueProvider
     */
    public function testHydrateBooleanProperty($value, $expected): void
    {
        $object = (new Hydrator())->hydrate(Fixtures\ObjectWithBool::class, ['value' => $value]);

        $this->assertSame($expected, $object->value);
    }

    public function booleanValueProvider(): array
    {
        return [
            [true, true],
            [1, true],
            ['1', true],
            ['on', true],
            ['yes', true],
            [false, false],
            [0, false],
            ['0', false],
            ['off', false],
            ['no', false],
        ];
    }

    public function testHydrateBooleanPropertyWithInvalidValue(): void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithBool.value property expects a boolean.');

        (new Hydrator())->hydrate(Fixtures\ObjectWithBool::class, ['value' => 'foo']);
    }

    /**
     * @dataProvider integerValueProvider
     */
    public function testHydrateIntegerProperty($value, $expected): void
    {
        $object = (new Hydrator())->hydrate(Fixtures\ObjectWithInt::class, ['value' => $value]);

        $this->assertSame($expected, $object->value);
    }

    public function integerValueProvider(): array
    {
        return [
            [42, 42],
            ['42', 42],
        ];
    }

    public function testHydrateIntegerPropertyWithInvalidValue(): void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithInt.value property expects an integer.');

        (new Hydrator())->hydrate(Fixtures\ObjectWithInt::class, ['value' => 'foo']);
    }

    /**
     * @dataProvider numberValueProvider
     */
    public function testHydrateNumberProperty($value, $expected): void
    {
        $object = (new Hydrator())->hydrate(Fixtures\ObjectWithFloat::class, ['value' => $value]);

        $this->assertSame($expected, $object->value);
    }

    public function numberValueProvider(): array
    {
        return [
            [42, 42.0],
            ['42', 42.0],
            [42.0, 42.0],
            ['42.0', 42.0],
        ];
    }

    public function testHydrateNumberPropertyWithInvalidValue(): void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithFloat.value property expects a number.');

        (new Hydrator())->hydrate(Fixtures\ObjectWithFloat::class, ['value' => 'foo']);
    }

    public function testHydrateStringableProperty(): void
    {
        $object = (new Hydrator())->hydrate(Fixtures\ObjectWithString::class, ['value' => 'foo']);

        $this->assertSame('foo', $object->value);
    }

    public function testHydrateStringablePropertyWithEmptyString(): void
    {
        $object = (new Hydrator())->hydrate(Fixtures\ObjectWithString::class, ['value' => '']);

        $this->assertSame('', $object->value);
    }

    public function testHydrateStringablePropertyWithInvalidValue(): void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithString.value property expects a string.');

        (new Hydrator())->hydrate(Fixtures\ObjectWithString::class, ['value' => 42]);
    }

    public function testHydrateArrayableProperty(): void
    {
        $object = (new Hydrator())->hydrate(Fixtures\ObjectWithArray::class, ['value' => ['foo']]);

        $this->assertSame(['foo'], $object->value);
    }

    public function testHydrateTypedArrayableProperty(): void
    {
        $object = (new Hydrator())->hydrate(Fixtures\ObjectWithTypedArray::class, [
            'value' => [
                ['name' => 'foo'],
                ['name' => 'bar'],
            ],
        ]);

        $this->assertIsArray($object->value);
        $this->assertInstanceOf(Tag::class, $object->value[0]);
        $this->assertInstanceOf(Tag::class, $object->value[1]);

        $this->assertSame('foo', $object->value[0]->name);
        $this->assertSame('bar', $object->value[1]->name);
    }

    public function testHydrateTypedNestedArrayableProperty(): void
    {
        $object = (new Hydrator())->hydrate(Fixtures\ObjectWithTypedArrayOfArray::class, [
            'value' => [
                [['name' => 'foo'], ['name' => 'fef']],
                [['name' => 'bar'], ['name' => 'fif']],
            ],
        ]);

        $this->assertIsArray($object->value);
        $this->assertIsArray($object->value[0]);
        $this->assertIsArray($object->value[1]);
        $this->assertInstanceOf(Tag::class, $object->value[0][0]);
        $this->assertInstanceOf(Tag::class, $object->value[0][1]);
        $this->assertInstanceOf(Tag::class, $object->value[1][0]);
        $this->assertInstanceOf(Tag::class, $object->value[1][1]);

        $this->assertSame('foo', $object->value[0][0]->name);
        $this->assertSame('fef', $object->value[0][1]->name);
        $this->assertSame('bar', $object->value[1][0]->name);
        $this->assertSame('fif', $object->value[1][1]->name);
    }

    public function testHydrateArrayablePropertyWithInvalidValue(): void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithArray.value property expects an array.');

        (new Hydrator())->hydrate(Fixtures\ObjectWithArray::class, ['value' => 'foo']);
    }

    public function testHydrateObjectableProperty(): void
    {
        $value = (object) ['value' => 'foo'];

        $object = (new Hydrator())->hydrate(Fixtures\ObjectWithObject::class, ['value' => $value]);

        $this->assertSame($value, $object->value);
    }

    public function testHydrateObjectablePropertyWithInvalidValue(): void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithObject.value property expects an object.');

        (new Hydrator())->hydrate(Fixtures\ObjectWithObject::class, ['value' => 'foo']);
    }

    /**
     * @dataProvider timestampValueProvider
     */
    public function testHydrateDateTimeProperty($value, $expected): void
    {
        $object = (new Hydrator())->hydrate(Fixtures\ObjectWithDateTime::class, ['value' => $value]);

        $this->assertSame($expected, $object->value->format('Y-m-d'));
    }

    /**
     * @dataProvider timestampValueProvider
     */
    public function testHydrateDateTimeImmutableProperty($value, $expected): void
    {
        $object = (new Hydrator())->hydrate(Fixtures\ObjectWithDateTimeImmutable::class, ['value' => $value]);

        $this->assertSame($expected, $object->value->format('Y-m-d'));
    }

    public function timestampValueProvider(): array
    {
        return [
            [1262304000, '2010-01-01'],
            ['1262304000', '2010-01-01'],
            ['2010-01-01', '2010-01-01'],
        ];
    }

    public function testHydrateDateTimePropertyWithInvalidValue(): void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithDateTime.value property '.
            'expects a valid date-time string or timestamp.');

        (new Hydrator())->hydrate(Fixtures\ObjectWithDateTime::class, ['value' => 'foo']);
    }

    public function testHydrateDateTimeImmutablePropertyWithInvalidValue(): void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithDateTimeImmutable.value property '.
            'expects a valid date-time string or timestamp.');

        (new Hydrator())->hydrate(Fixtures\ObjectWithDateTimeImmutable::class, ['value' => 'foo']);
    }

    public function testHydrateDateIntervalProperty(): void
    {
        $object = (new Hydrator())->hydrate(Fixtures\ObjectWithDateInterval::class, ['value' => 'P33Y']);

        $this->assertSame(33, $object->value->y);
    }

    public function testHydrateDateIntervalPropertyWithInvalidValue(): void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithDateInterval.value property '.
            'expects a string.');

        (new Hydrator())->hydrate(Fixtures\ObjectWithDateInterval::class, ['value' => 42]);
    }

    public function testHydrateDateIntervalPropertyWithInvalidFormat(): void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithDateInterval.value property '.
            'expects a valid date-interval string based on ISO 8601.');

        (new Hydrator())->hydrate(Fixtures\ObjectWithDateInterval::class, ['value' => 'foo']);
    }

    public function testHydrateArrayOfStringableEnumProperty(): void
    {
        $object = (new Hydrator())->hydrate(Fixtures\ObjectWithArrayOfStringableEnum::class, ['value' => [
            'c1200a7e-136e-4a11-9bc3-cc937046e90f',
            'a2b29b37-1c5a-4b36-9981-097ddd25c740',
            'c1ea3762-9827-4c0c-808b-53be3febae6d',
        ]]);

        $this->assertSame([
            Fixtures\StringableEnum::foo,
            Fixtures\StringableEnum::bar,
            Fixtures\StringableEnum::baz,
        ], $object->value);
    }

    public function testHydrateArrayOfStringableEnumPropertyWithoutMatchingEnum(): void
    {
        $object = (new Hydrator())->hydrate(Fixtures\ObjectWithArrayOfStringableEnum::class, ['value' => [
            'c1200a7e-136e-4a11-9bc3-cc937046e90f',
            'a2b29b37-1c5a-4b36-9981-097ddd25c740',
            'c1ea3762-9827-4c0c-808b-53be3febae6d',
            'bbb',
        ]]);

        $this->assertSame([
            Fixtures\StringableEnum::foo,
            Fixtures\StringableEnum::bar,
            Fixtures\StringableEnum::baz,
            'bbb',
        ], $object->value);
    }

    public function testHydrateStringableEnumUnionPropertyString(): void
    {
        $object = (new Hydrator())->hydrate(Fixtures\ObjectWithStringableEnumUnion::class, ['value' => 'bbb']);

        $this->assertSame('bbb', $object->value);
    }

    public function testHydrateStringableEnumUnionPropertyInt(): void
    {
        $object = (new Hydrator())->hydrate(Fixtures\ObjectWithStringableEnumUnion::class, ['value' => 123]);

        $this->assertSame(123, $object->value);
    }

    public function testHydrateStringableEnumUnionPropertyFloat(): void
    {
        $object = (new Hydrator())->hydrate(Fixtures\ObjectWithStringableEnumUnion::class, ['value' => .23]);

        $this->assertSame(.23, $object->value);
    }

    public function testHydrateStringableEnumUnionPropertyNull(): void
    {
        $object = (new Hydrator())->hydrate(Fixtures\ObjectWithStringableEnumNullableUnion::class, ['value' => null]);

        $this->assertNull($object->value);
    }

    public function testHydrateStringableEnumUnionPropertyNullNonSet(): void
    {
        $object = (new Hydrator())->hydrate(Fixtures\ObjectWithStringableEnumNullableUnion::class, []);

        $this->assertNull($object->value);
    }

    public function testHydrateStringableEnumUnionPropertyBool(): void
    {
        $this->expectException(Exception\UnsupportedPropertyTypeException::class);
        (new Hydrator())->hydrate(Fixtures\ObjectWithStringableEnumUnion::class, ['value' => false]);
    }

    /**
     * @dataProvider stringableEnumValueProvider
     */
    public function testHydrateStringableEnumProperty($value, $expected): void
    {
        $object = (new Hydrator())->hydrate(Fixtures\ObjectWithStringableEnum::class, ['value' => $value]);

        $this->assertSame($expected, $object->value);
    }

    public function stringableEnumValueProvider(): array
    {
        return [
            ['c1200a7e-136e-4a11-9bc3-cc937046e90f', Fixtures\StringableEnum::foo],
            ['a2b29b37-1c5a-4b36-9981-097ddd25c740', Fixtures\StringableEnum::bar],
            ['c1ea3762-9827-4c0c-808b-53be3febae6d', Fixtures\StringableEnum::baz],
        ];
    }

    public function testHydrateStringableEnumPropertyWithInvalidValue(): void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithStringableEnum.value property '.
            'expects the following type: string.');

        (new Hydrator())->hydrate(Fixtures\ObjectWithStringableEnum::class, ['value' => 42]);
    }

    public function testHydrateStringableEnumPropertyWithInvalidUnknownCase(): void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithStringableEnum.value property '.
            'expects one of the following values: '.
            \implode(', ', Fixtures\StringableEnum::values()).'.');

        (new Hydrator())->hydrate(Fixtures\ObjectWithStringableEnum::class, ['value' => 'foo']);
    }

    /**
     * @dataProvider numerableEnumValueProvider
     */
    public function testHydrateNumerableEnumProperty($value, $expected): void
    {
        $object = (new Hydrator())->hydrate(Fixtures\ObjectWithNumerableEnum::class, ['value' => $value]);

        $this->assertSame($expected, $object->value);
    }

    public function numerableEnumValueProvider(): array
    {
        return [
            [1, Fixtures\NumerableEnum::foo],
            [2, Fixtures\NumerableEnum::bar],
            [3, Fixtures\NumerableEnum::baz],

            // should convert strings to integers...
            ['1', Fixtures\NumerableEnum::foo],
            ['2', Fixtures\NumerableEnum::bar],
            ['3', Fixtures\NumerableEnum::baz],
        ];
    }

    public function testHydrateNumerableEnumPropertyWithInvalidValue(): void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithNumerableEnum.value property '.
            'expects the following type: int.');

        (new Hydrator())->hydrate(Fixtures\ObjectWithNumerableEnum::class, ['value' => 'foo']);
    }

    public function testHydrateNumerableEnumPropertyWithInvalidUnknownCase(): void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithNumerableEnum.value property '.
            'expects one of the following values: '.
            \implode(', ', Fixtures\NumerableEnum::values()).'.');

        (new Hydrator())->hydrate(Fixtures\ObjectWithNumerableEnum::class, ['value' => 42]);
    }

    public function testHydrateAssociatedProperty(): void
    {
        $o = (new Hydrator())->hydrate(Fixtures\ObjectWithAssociation::class, ['value' => ['value' => 'foo']]);

        $this->assertSame('foo', $o->value->value);
    }

    public function testHydrateAssociatedPropertyUsingDataObject(): void
    {
        $o = (new Hydrator())->hydrate(Fixtures\ObjectWithAssociation::class, ['value' => (object) ['value' => 'foo']]);

        $this->assertSame('foo', $o->value->value);
    }

    public function testHydrateAssociatedPropertyWithInvalidData(): void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithAssociation.value property '.
            'expects an associative array or object.');

        (new Hydrator())->hydrate(Fixtures\ObjectWithAssociation::class, ['value' => 'foo']);
    }

    public function testHydrateAssociationCollectionProperty(): void
    {
        $o = (new Hydrator())->hydrate(Fixtures\ObjectWithAssociations::class, [
            'value' => [
                'foo' => ['value' => 'foo'],
                'bar' => ['value' => 'bar'],
            ],
        ]);

        $this->assertNotNull($o->value['foo']);
        $this->assertSame('foo', $o->value['foo']->value);

        $this->assertNotNull($o->value['bar']);
        $this->assertSame('bar', $o->value['bar']->value);
    }

    public function testHydrateAssociationCollectionPropertyUsingDataObject(): void
    {
        $o = (new Hydrator())->hydrate(Fixtures\ObjectWithAssociations::class, [
            'value' => (object) [
                'foo' => (object) ['value' => 'foo'],
                'bar' => (object) ['value' => 'bar'],
            ],
        ]);

        $this->assertNotNull($o->value['foo']);
        $this->assertSame('foo', $o->value['foo']->value);

        $this->assertNotNull($o->value['bar']);
        $this->assertSame('bar', $o->value['bar']->value);
    }

    public function testHydrateAssociationCollectionPropertyWithInvalidData(): void
    {
        $this->expectException(Exception\InvalidValueException::class);
        $this->expectExceptionMessage('The ObjectWithAssociations.value property '.
            'expects an array.');

        (new Hydrator())->hydrate(Fixtures\ObjectWithAssociations::class, ['value' => 'foo']);
    }

    public function testHydrateAssociationCollectionPropertyWithInvalidChild(): void
    {
        $this->expectException(TypeError::class);

        (new Hydrator())->hydrate(Fixtures\ObjectWithAssociations::class, ['value' => ['foo']]);
    }

    public function testInvalidValueExceptionProperty(): void
    {
        try {
            (new Hydrator())->hydrate(Fixtures\ObjectWithString::class, ['value' => 42]);
        } catch (Exception\InvalidValueException $e) {
            $this->assertSame('value', $e->getProperty()->getName());
            $this->assertSame('ObjectWithString.value', $e->getPropertyPath());
        }
    }

    public function testHydrateProductWithJsonAsArray(): void
    {
        $json = <<<'JSON'
        {
            "name": "ac7ce13e-9b2e-4b09-ae7a-973769ea43df",
            "category": {
                "name": "a0127d1b-28b6-40a9-9a62-cfb2e2b44abd"
            },
            "tags": [
                {
                    "name": "a9878435-506c-4757-92b0-69ea2bd15bc3"
                },
                {
                    "name": "73dc4db1-7965-41b6-88cb-4dc9df6fb3ea"
                }
            ],
            "status": 2
        }
        JSON;

        $product = (new Hydrator())->hydrateWithJson(Fixtures\Store\Product::class, $json);

        $this->assertSame('ac7ce13e-9b2e-4b09-ae7a-973769ea43df', $product->name);
        $this->assertSame('a0127d1b-28b6-40a9-9a62-cfb2e2b44abd', $product->category->name);
        $this->assertSame('a9878435-506c-4757-92b0-69ea2bd15bc3', $product->tags[0]->name);
        $this->assertSame('73dc4db1-7965-41b6-88cb-4dc9df6fb3ea', $product->tags[1]->name);
        $this->assertSame(2, $product->status->value);
    }

    public function testHydrateProductWithJsonAsObject(): void
    {
        $json = <<<'JSON'
        {
            "name": "0f61ac0e-f732-4088-8082-cc396e7dcb80",
            "category": {
                "name": "d342d030-3c0c-431e-be54-2e933b722b7c"
            },
            "tags": [
                {
                    "name": "3635627a-e348-4ca4-8e62-4e5cd78043d2"
                },
                {
                    "name": "dccd816f-bb28-41f3-b1a9-ddaff1fdec5b"
                }
            ],
            "status": 2
        }
        JSON;

        $product = (new Hydrator())->hydrateWithJson(Fixtures\Store\Product::class, $json, 0);

        $this->assertSame('0f61ac0e-f732-4088-8082-cc396e7dcb80', $product->name);
        $this->assertSame('d342d030-3c0c-431e-be54-2e933b722b7c', $product->category->name);
        $this->assertSame('3635627a-e348-4ca4-8e62-4e5cd78043d2', $product->tags[0]->name);
        $this->assertSame('dccd816f-bb28-41f3-b1a9-ddaff1fdec5b', $product->tags[1]->name);
        $this->assertSame(2, $product->status->value);
    }

    public function testHydrateAbstractObject(): void
    {
        $o = (new Hydrator())->hydrate(Apple::class, [
            'type'      => 'sauce',
            'sweetness' => 100,
            'category'  => null,
        ]);

        $this->assertInstanceOf(AppleSauce::class, $o);
        $this->assertSame('sauce', $o->type);
        $this->assertSame(100, $o->sweetness);
    }

    public function testHydrateAbstractObjectWithoutInterface(): void
    {
        $this->expectException(InvalidObjectException::class);

        (new Hydrator())->hydrate(Fruit::class, ['name' => 'apple']);
    }

    public function testItReturnsTheConcreteResolver(): void
    {
        $resolver = (new Hydrator())->getConcreteResolverFor(Apple::class);

        $this->assertInstanceOf(AppleResolver::class, $resolver);
        $this->assertSame([AppleJack::class, AppleSauce::class], $resolver->getConcretes());
    }

    public function testHydrateAbstractProperty(): void
    {
        $o = (new Hydrator())->hydrate(new ObjectWithAbstract(), [
            'value' => [
                'type'      => 'jack',
                'sweetness' => null,
                'category'  => 'brandy',
            ],
        ]);

        $this->assertInstanceOf(ObjectWithAbstract::class, $o);
        $this->assertInstanceOf(AppleJack::class, $o->value);
        $this->assertSame('jack', $o->value->type);
        $this->assertSame('brandy', $o->value->category);
    }

    public function testHydrateArrayAbstractProperty(): void
    {
        $o = (new Hydrator())->hydrate(new ObjectWithArrayOfAbstracts(), [
            'value' => [[
                'type'      => 'jack',
                'sweetness' => null,
                'category'  => 'brandy',
            ]],
        ]);

        $this->assertInstanceOf(ObjectWithArrayOfAbstracts::class, $o);
        $this->assertIsArray($o->value);

        $value = $o->value[0];

        $this->assertInstanceOf(AppleJack::class, $value);
        $this->assertSame('jack', $value->type);
        $this->assertSame('brandy', $value->category);
    }

    public function testHydrateArrayAbstractPropertyWithObject(): void
    {
        $o = (new Hydrator())->hydrate(new ObjectWithArrayOfAbstracts(), [
            'value' => [(object) [
                'type'      => 'jack',
                'sweetness' => null,
                'category'  => 'brandy',
            ]],
        ]);

        $this->assertInstanceOf(ObjectWithArrayOfAbstracts::class, $o);
        $this->assertIsArray($o->value);

        $value = $o->value[0];

        $this->assertInstanceOf(AppleJack::class, $value);
        $this->assertSame('jack', $value->type);
        $this->assertSame('brandy', $value->category);
    }

    public function testHydrateInvalidAbstractObject(): void
    {
        $this->expectException(InvalidObjectException::class);

        (new Hydrator())->hydrate(new ObjectWithInvalidAbstract(), [
            'value' => [
                'name' => 'apple',
            ],
        ]);
    }

    public function testHydrateWithContainer(): void
    {
        $sun = new Sun('andromeda');

        $container = new Container();
        $container->instance(Sun::class, $sun);

        $hydrator = new Hydrator($container);

        $o = $hydrator->hydrate(Tree::class, [
            'name'   => 'foo',
            'leaves' => [
                'n' => 100,
            ],
            'wood' => [
                'kg' => 120,
            ],
        ]);

        $this->assertSame('foo', $o->name);
        $this->assertSame(100, $o->leaves->n);
        $this->assertSame(120, $o->wood->kg);
        $this->assertNotNull($o->getSun());
        $this->assertNotNull($o->leaves->getSun());
        $this->assertNotNull($o->wood->getSun());
        $this->assertSame($sun, $o->getSun());
        $this->assertSame($sun, $o->leaves->getSun());
        $this->assertSame($sun, $o->wood->getSun());
    }

    public function testHydrateWithContainerWithNestedInstances(): void
    {
        $sun = new Sun('andromeda');

        $container = new Container();
        $container->instance(Sun::class, $sun);

        $hydrator = new Hydrator($container);

        $o = $hydrator->hydrate(Forest::class, [
            'trees' => [
                [
                    'name'   => 'foo',
                    'leaves' => [
                        'n' => 100,
                    ],
                    'wood' => [
                        'kg' => 120,
                    ],
                ],
                [
                    'name'   => 'foo2',
                    'leaves' => [
                        'n' => 200,
                    ],
                    'wood' => [
                        'kg' => 220,
                    ],
                ],
            ],
        ]);

        $this->assertIsArray($o->trees);
        $this->assertcount(2, $o->trees);

        $this->assertSame('foo', $o->trees[0]->name);
        $this->assertInstanceOf(Leaves::class, $o->trees[0]->leaves);
        $this->assertSame(100, $o->trees[0]->leaves->n);
        $this->assertInstanceOf(Sun::class, $o->trees[0]->leaves->getSun());
        $this->assertSame('andromeda', $o->trees[0]->leaves->getSun()->getFrom());
        $this->assertInstanceOf(Wood::class, $o->trees[0]->wood);
        $this->assertSame(120, $o->trees[0]->wood->kg);
        $this->assertInstanceOf(Sun::class, $o->trees[0]->wood->getSun());
        $this->assertSame('andromeda', $o->trees[0]->wood->getSun()->getFrom());
        $this->assertInstanceOf(Sun::class, $o->trees[0]->getSun());
        $this->assertSame('andromeda', $o->trees[0]->getSun()->getFrom());

        $this->assertSame('foo2', $o->trees[1]->name);
        $this->assertInstanceOf(Leaves::class, $o->trees[1]->leaves);
        $this->assertSame(200, $o->trees[1]->leaves->n);
        $this->assertInstanceOf(Sun::class, $o->trees[1]->leaves->getSun());
        $this->assertSame('andromeda', $o->trees[1]->leaves->getSun()->getFrom());
        $this->assertInstanceOf(Wood::class, $o->trees[1]->wood);
        $this->assertSame(220, $o->trees[1]->wood->kg);
        $this->assertInstanceOf(Sun::class, $o->trees[1]->wood->getSun());
        $this->assertSame('andromeda', $o->trees[1]->wood->getSun()->getFrom());
        $this->assertInstanceOf(Sun::class, $o->trees[1]->getSun());
        $this->assertSame('andromeda', $o->trees[1]->getSun()->getFrom());
    }

    public function testSkipConstructor(): void
    {
        $object = (new Hydrator())->hydrate(Fixtures\ObjectWithEnumInConstructor::class, [
            'stringableEnum' => 'c1200a7e-136e-4a11-9bc3-cc937046e90f',
            'numerableEnums' => [1],
        ]);

        $this->assertSame(Fixtures\StringableEnum::foo, $object->stringableEnum);
        $this->assertSame(Fixtures\NumerableEnum::foo, $object->numerableEnums[0]);
    }

    public function testSkipConstructorWithContainer(): void
    {
        $container = new Container();

        $object = (new Hydrator($container))->hydrate(Fixtures\ObjectWithEnumInConstructor::class, [
            'stringableEnum' => 'c1200a7e-136e-4a11-9bc3-cc937046e90f',
            'numerableEnums' => [1],
        ]);

        $this->assertSame(Fixtures\StringableEnum::foo, $object->stringableEnum);
        $this->assertSame(Fixtures\NumerableEnum::foo, $object->numerableEnums[0]);
    }

    public function testMutateProperty(): void
    {
        $object = (new Hydrator())->hydrate(Fixtures\ObjectWithArrayToDeserialize::class, [
            'name'  => 'foo',
            'value' => json_encode(['foo' => 'bar'], JSON_THROW_ON_ERROR),
        ]);

        $this->assertSame('foo', $object->name);
        $this->assertIsArray($object->value);
        $this->assertSame(['foo' => 'bar'], $object->value);
    }

    public function testHydrateAdditionalWithMagicMethod()
    {
        $object = (new Hydrator())->hydrate(Fixtures\ObjectWithMagicSet::class, [
            'name'   => 'foo',
            'value'  => 'bar',
            'type'   => false,
            'number' => 42,
        ]);

        $this->assertSame('foo', $object->name);
        $this->assertSame('bar', $object->value);
        $this->assertFalse($object->type);
        $this->assertSame(42, $object->number);
    }
}
