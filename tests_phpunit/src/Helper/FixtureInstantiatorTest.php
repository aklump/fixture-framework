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
    $this->instantiator = new FixtureInstantiator(new RunOptions([]), $this->validator);
  }

  public function testThrowsIfClassIsMissing() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Fixture record must have a class.');
    ($this->instantiator)([], $this->store);
  }

  public function testThrowsIfClassDoesNotImplementFixtureInterface() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Fixture class "stdClass" must implement FixtureInterface.');
    ($this->instantiator)(['class' => \stdClass::class], $this->store);
  }

  public function testIdIsResolvedFromAttributeIfMissingInDefinition() {
    $definition = ['class' => FixtureA::class];
    $fixture = ($this->instantiator)($definition, $this->store);
    $this->assertEquals('fixture_a', $fixture->id());
  }

  public function testProvidedIdOverridesAttributeId() {
    $definition = ['class' => FixtureA::class, 'id' => 'overridden'];
    $fixture = ($this->instantiator)($definition, $this->store);
    $this->assertEquals('overridden', $fixture->id());
  }

  public function testThrowsIfIdCannotBeResolved() {
    $class = new class implements FixtureInterface {
      public function id(): string { return ''; }
      public function description(): string { return ''; }
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
    $instantiator = new FixtureInstantiator(new RunOptions($options), $this->validator);
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
    // AbstractFixture implements FixtureDefinitionAwareInterface and uses FixtureMetadataTrait which has a public $fixture property
    $this->assertEquals($definition, $fixture->fixture);
    $this->assertEquals($definition, $fixture->fixture());
  }

  public function testPublicPropertiesAreNotWiredWithoutInterfaces() {
    $class = new class implements \AKlump\FixtureFramework\FixtureInterface {
      public array $fixture = [];
      public RunOptions $options;
      public \AKlump\FixtureFramework\Runtime\RunContext $runContext;
      public function id(): string { return 'test'; }
      public function description(): string { return ''; }
      public function __invoke(): void {}
      public function onSuccess(bool $silent = FALSE) {}
      public function onFailure(\AKlump\FixtureFramework\Exception\FixtureException $e, bool $silent = FALSE) {}
    };
    $className = get_class($class);
    $definition = ['class' => $className, 'id' => 'test'];
    $fixture = ($this->instantiator)($definition, $this->store);

    $this->assertEmpty($fixture->fixture);
    $this->assertFalse(isset($fixture->options));
    $this->assertFalse(isset($fixture->runContext));
  }


  public function testRunContextPopulation() {
    $definition = ['class' => FixtureA::class];
    $fixture = ($this->instantiator)($definition, $this->store);

    $this->assertNotNull($fixture->runContext);
  }

  public function testRunOptionsAwareInterfaceIsWired() {
    $class = new class implements FixtureInterface, \AKlump\FixtureFramework\Interface\RunOptionsAwareInterface {
      public RunOptions $options;
      public function setRunOptions(RunOptions $options): void { $this->options = $options; }
      public function options(): RunOptions { return $this->options; }
      public function id(): string { return 'test'; }
      public function description(): string { return ''; }
      public function __invoke(): void {}
      public function onSuccess(bool $silent = FALSE) {}
      public function onFailure(\AKlump\FixtureFramework\Exception\FixtureException $e, bool $silent = FALSE) {}
    };
    $options = new RunOptions(['foo' => 'bar']);
    $instantiator = new FixtureInstantiator($options);
    $fixture = $instantiator(['class' => get_class($class), 'id' => 'test'], $this->store);
    $this->assertSame($options, $fixture->options());
  }

  public function testFixtureDefinitionAwareInterfaceIsWired() {
    $class = new class implements FixtureInterface, \AKlump\FixtureFramework\Interface\FixtureDefinitionAwareInterface {
      public array $definition;
      public function setFixtureDefinition(array $definition): void { $this->definition = $definition; }
      public function fixture(): array { return $this->definition; }
      public function id(): string { return 'test'; }
      public function description(): string { return ''; }
      public function __invoke(): void {}
      public function onSuccess(bool $silent = FALSE) {}
      public function onFailure(\AKlump\FixtureFramework\Exception\FixtureException $e, bool $silent = FALSE) {}
    };
    $definition = ['class' => get_class($class), 'id' => 'test_id'];
    $fixture = ($this->instantiator)($definition, $this->store);
    $this->assertEquals($definition, $fixture->fixture());
  }

  public function testRunContextAwareInterfaceIsWired() {
    $class = new class implements FixtureInterface, \AKlump\FixtureFramework\Interface\RunContextAwareInterface {
      public \AKlump\FixtureFramework\Runtime\RunContext $runContext;
      public function setRunContext(\AKlump\FixtureFramework\Runtime\RunContext $run_context): void { $this->runContext = $run_context; }
      public function context(): \AKlump\FixtureFramework\Runtime\RunContext { return $this->runContext; }
      public function id(): string { return 'test'; }
      public function description(): string { return ''; }
      public function __invoke(): void {}
      public function onSuccess(bool $silent = FALSE) {}
      public function onFailure(\AKlump\FixtureFramework\Exception\FixtureException $e, bool $silent = FALSE) {}
    };
    $fixture = ($this->instantiator)(['class' => get_class($class), 'id' => 'test'], $this->store);
    $this->assertInstanceOf(\AKlump\FixtureFramework\Runtime\RunContext::class, $fixture->context());
  }

  public function testCustomInstantiator() {
    $customInstantiator = new class(new RunOptions([])) extends FixtureInstantiator {
      public bool $created = FALSE;
      protected function createFixture(array $definition): \AKlump\FixtureFramework\FixtureInterface {
        $this->created = TRUE;
        return new FixtureA();
      }
    };
    $fixture = $customInstantiator(['class' => FixtureA::class], $this->store);
    $this->assertTrue($customInstantiator->created);
    $this->assertInstanceOf(FixtureA::class, $fixture);
  }
}
