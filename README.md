# Strongly typed hydrator for PHP 8.0+

---

Fork of the original project https://github.com/sunrise-php/hydrator.

## Installation

```bash
composer require nutgram/hydrator
```

## How to use?

```php
use SergiX44\Hydrator\Hydrator;

$hydrator = new Hydrator();

// create and hydrate an object with an array
$data = [/* the class props here */];
$object = $hydrator->hydrate(SomeDto::class, $data);

// hydrate an object with an array
$data = [/* the class props here */];
$hydrator->hydrate($object, $data);

// creates and hydrate an object with JSON
$json = '{...}';
$object = $hydrator->hydrateWithJson(SomeDto::class, $json);

// hydrate an object with JSON
$json = '{...}';
$hydrator->hydrateWithJson($object, $json);

// pass JSON decoding flags
$options = JSON_OBJECT_AS_ARRAY|JSON_BIGINT_AS_STRING;
$hydrator->hydrateWithJson($object, $json, $options);
```

## Allowed property types

### Required

If a property has no a default value, then the property is required.

```php
public string $value;
```

### Optional

If a property has a default value, then the property is optional.

```php
public string $value = 'foo';
```

### Null

If a property is nullable, then the property can accept null.

```php
public ?string $value;
```

If the property should be optional, then it must has a default value.

```php
public ?string $value = null;
```

### Boolean

Accepts the following values: true, false, 1, 0, "1", "0", "yes", "no", "on" and "no".

```php
public bool $value;
```

```php
['value' => true];
['value' => 'yes'];
```

## Integer

Accepts only integers (also as a string).

```php
public int $value;
```

```php
['value' => 42];
['value' => '42'];
```

## Number<int|float>

Accepts only numbers (also as a string).

```php
public float $value;
```

```php
['value' => 42.0];
['value' => '42.0'];
```

## String

Accepts only strings.

```php
public string $value;
```

```php
['value' => 'foo'];
```

## Array<array-key, mixed>

Accepts only arrays.

```php
public array $value;
```

```php
['value' => [1, 2, 'foo']];
```

## Array<array-key, class>

Accept a list of objects.

```php
final class SomeDto {
    public readonly string $value;
}
```

```php
use SergiX44\Hydrator\Annotation\ArrayType;

#[ArrayType(SomeDto::class)]
public array $value;
```

```php
[
    'value' => [
        [
            'value' => 'foo',
        ],
        [
            'value' => 'bar',
        ],
    ],
],
```

## Object

Accepts only objects.

```php
public object $value;
```

```php
['value' => new stdClass];
```

## DateTime/DateTimeImmutable

Integers (also as a string) will be handled as a timestamp, otherwise accepts only valid date-time strings.

```php
public DateTimeImmutable $value;
```

```php
// 2010-01-01
['value' => 1262304000];
// 2010-01-01
['value' => '1262304000'];
// normal date
['value' => '2010-01-01'];
```

## DateInterval

Accepts only valid date-interval strings based on ISO 8601.

```php
public DateInterval $value;
```

```php
['value' => 'P1Y']
```

## Enum<BackedEnum>

Accepts only values that exist in an enum.

```php
enum SomeEnum: int {
    case foo = 0;
    case bar = 1;
}
```

```php
public SomeEnum $value;
```

```php
['value' => 0]
['value' => '1']
```

## Association

Accepts a valid structure for an association

```php
final class SomeDto {
    public string $value;
}
```

```php
public SomeDto $value;
```

```php
[
    'value' => [
        'value' => 'foo',
    ],
]
```

## Property alias

If you need to get a non-normalized key, use aliases.

For example, the Google Recaptcha API returns the following response:

```json
{
    "success": false,
    "error-codes": []
}
```

To correctly map the response, use the following model:

```php
use SergiX44\Hydrator\Annotation\Alias;

final class RecaptchaVerificationResult {
    public bool $success;

    #[Alias('error-codes')]
    public array $errorCodes = [];
}
```

## Examples

```php
final class Product {
    public string $name;
    public Category $category;
    #[ArrayType(Tag::class)]
    public array $tags;
    public Status $status;
}

final class Category {
    public string $name;
}

final class Tag {
    public string $name;
}

enum Status: int {
    case ENABLED = 1;
    case DISABLED = 0;
}
```

```php
$product = $hydrator->hydrate(Product::class, [
    'name' => 'Stool',
    'category' => [
        'name' => 'Furniture',
    ],
    'tags' => [
        [
            'name' => 'Wood',
        ],
        [
            'name' => 'Lacquered',
        ],
    ],
    'status' => 0,
]);
```

---

## Test run

```bash
composer test
```
