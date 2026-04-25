<?php

namespace AKlump\FixtureFramework\Tests\Helper;

use AKlump\FixtureFramework\FixtureInterface;
use AKlump\FixtureFramework\Runtime\FixtureInstantiator;
use AKlump\FixtureFramework\Runtime\RunContextStore;
use AKlump\FixtureFramework\Runtime\RunContextValidator;
use AKlump\FixtureFramework\Runtime\RunOptions;
use AKlump\FixtureFramework\Tests\Fixtures\FixtureA;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\FixtureFramework\Runtime\FixtureInstantiator
 * @uses \AKlump\FixtureFramework\Helper\GetFixtureIdByClass
 * @uses \AKlump\FixtureFramework\Runtime\RunOptions
 * @uses \AKlump\FixtureFramework\Runtime\RunOptionsValidator
 * @uses \AKlump\FixtureFramework\Runtime\RunContext
 * @uses \AKlump\FixtureFramework\Runtime\RunContextStore
 * @uses \AKlump\FixtureFramework\Runtime\RunContextValidator
 * @uses \AKlump\FixtureFramework\AbstractFixture
 * @uses \AKlump\FixtureFramework\Fixture
 * @uses \AKlump\FixtureFramework\Traits\FixtureMetadataTrait
 * @uses \AKlump\FixtureFramework\Traits\FixtureOptionsTrait
 * @uses \AKlump\FixtureFramework\Traits\FixtureRunContextTrait
 */
class FixtureInstantiatorTest extends TestCase {

  private FixtureInstantiator $instantiator;
  private RunContextStore $store;
  private RunContextValidator $validator;

  protected function setUp(): void {
    $this->store = new RunContextStore();
    $this->validator = new RunContextValidator();
    $this->instantiator = new FixtureInstantiator([], $this->validator);
  }

  public function testThrowsIfClassIsMissing() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Fixture record must have a class.');
    ($this->instantiator)([], $this->store);
  }

  public function testThrowsIfIdCannotBeResolved() {
    $class = new class implements FixtureInterface {
      public function id(): string { return ''; }
      public function __invoke(): void {}
      public function onSuccess(bool $silent = FALSE) {}
      public function onFailure(\AKlump\FixtureFramework\Exception\FixtureException $e, bool $silent = FALSE) {}
    };
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Fixture id must be a non-empty string on class "' . get_class($class) . '".');
    ($this->instantiator)(['class' => get_class($class)], $this->store);
  }

  public function testInstantiationWithGlobalOptionsAsArray() {
    $definition = ['class' => FixtureA::class];
    $options = ['env' => 'test_array'];
    $instantiator = new FixtureInstantiator($options, $this->validator);
    $fixture = $instantiator($definition, $this->store);

    $this->assertInstanceOf(FixtureA::class, $fixture);
    $this->assertEquals('test_array', $fixture->options->get('env'));
  }

  public function testInstantiationWithGlobalOptionsAsRunOptions() {
    $definition = ['class' => FixtureA::class];
    $options = new RunOptions(['env' => 'test_object']);
    $instantiator = new FixtureInstantiator($options, $this->validator);
    $fixture = $instantiator($definition, $this->store);

    $this->assertInstanceOf(FixtureA::class, $fixture);
    $this->assertEquals('test_object', $fixture->options->get('env'));
  }

  public function testFixturePropertyPopulation() {
    $definition = ['class' => FixtureA::class, 'id' => 'custom_a', 'foo' => 'bar'];
    $fixture = ($this->instantiator)($definition, $this->store);

    $this->assertEquals('custom_a', $fixture->id());
    // AbstractFixture uses FixtureMetadataTrait which has a public $fixture property
    $this->assertEquals($definition, $fixture->fixture);
  }

  public function testRunContextPopulation() {
    $definition = ['class' => FixtureA::class];
    $fixture = ($this->instantiator)($definition, $this->store);

    $this->assertNotNull($fixture->runContext);
  }
}
