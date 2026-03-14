<?php

namespace AKlump\FixtureFramework\Tests;

use AKlump\FixtureFramework\Exception\InvalidRunOptionsException;
use AKlump\FixtureFramework\RunOptionsValidator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\FixtureFramework\RunOptionsValidator
 * @uses \AKlump\FixtureFramework\RunOptionsValidator
 * @uses \AKlump\FixtureFramework\Exception\InvalidRunOptionsException
 */
class RunOptionsValidatorTest extends TestCase {

  private RunOptionsValidator $validator;

  protected function setUp(): void {
    $this->validator = new RunOptionsValidator();
  }

  public function testValidOptionsPass() {
    $options = [
      'env' => 'test',
      'url' => 'https://example.com',
      'timeout' => 30,
      'debug' => true,
      'null_value' => null,
      'nested' => [
        'api' => [
          'key' => '12345',
          'retry' => 3,
        ],
        'list' => [1, 2, 3],
      ],
    ];
    $this->validator->validate($options);
    $this->addToAssertionCount(1);
  }

  /**
   * @dataProvider provideInvalidOptions
   */
  public function testInvalidOptionsThrowException(array $options, string $expectedPath) {
    $this->expectException(InvalidRunOptionsException::class);
    $this->expectExceptionMessage(sprintf('Run options must contain only null, scalar, or array values. Invalid value found at "%s".', $expectedPath));
    $this->validator->validate($options);
  }

  public function provideInvalidOptions(): array {
    return [
      'object at root' => [['client' => new \stdClass()], 'client'],
      'closure nested' => [['api' => ['callback' => function () {}]], 'api.callback'],
      'resource in array' => [['files' => [fopen('php://temp', 'r')]], 'files.0'],
    ];
  }
}
