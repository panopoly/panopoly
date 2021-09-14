<?php

namespace Drupal\Tests\panopoly_magic\Unit;

use Drupal\Component\Plugin\Exception\ContextException;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Plugin\Context\ContextDefinitionInterface;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Plugin\Context\ContextInterface;
use Drupal\panopoly_magic\BlockPreviewInterface;
use Drupal\panopoly_magic\BlockPreviewRenderer;
use Drupal\Tests\UnitTestCase;

/**
 * Unit tests for the BlockPreviewRenderer.
 *
 * @group PanopolyMagic
 * @group Panopoly
 */
class BlockPreviewRendererTest extends UnitTestCase {

  /**
   * The block manager.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $blockManager;

  /**
   * The context handler.
   *
   * @var \Drupal\Core\Plugin\Context\ContextHandlerInterface|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $contextHandler;

  /**
   * The block preview renderer.
   *
   * @var \Drupal\panopoly_magic\BlockPreviewRenderer
   */
  protected $renderer;

  /**
   * {@inheritDoc}
   */
  public function setUp() {
    $this->blockManager = $this->prophesize(BlockManagerInterface::class);
    $this->contextHandler = $this->prophesize(ContextHandlerInterface::class);
    $this->renderer = new BlockPreviewRenderer($this->blockManager->reveal(), $this->contextHandler->reveal());
    $this->renderer->setStringTranslation($this->getStringTranslationStub());
  }

  /**
   * Tests a simple block that is just rendered normally as its preview.
   */
  public function testPlainBlock() {
    $block_id = 'system_powered_by_block';
    $block_definition = [
      'id' => $block_id,
      'admin_label' => 'Powered by Drupal',
    ];

    // BlockPluginInterface doesn't extend ContextAwarePluginInterface.
    /** @var Drupal\Core\Block\BlockPluginInterface|\Prophecy\Prophecy\ObjectProphecy $block_plugin */
    $block_plugin = $this->prophesize(BlockPluginInterface::class);
    $block_plugin->build()
      ->willReturn(['#markup' => 'Powered by Drupal']);

    $this->blockManager->getDefinition($block_id)
      ->willReturn($block_definition);
    $this->blockManager->createInstance($block_id)
      ->willReturn($block_plugin->reveal());

    $rendered = $this->renderer->buildBlockPreview($block_id);

    $this->assertEquals(['#markup' => 'Powered by Drupal'], $rendered);
  }

  /**
   * Creates a mock context definition.
   *
   * @param bool $required
   *   Whether or not the context is required.
   *
   * @return \Drupal\Core\Plugin\Context\ContextDefinitionInterface|\Prophecy\Prophecy\ObjectProphecy
   *   The mock context definition.
   */
  protected function mockContextDefinition($required = FALSE) {
    /** @var \Drupal\Core\Plugin\Context\ContextDefinitionInterface|\Prophecy\Prophecy\ObjectProphecy $context_definition */
    $context_definition = $this->prophesize(ContextDefinitionInterface::class);
    $context_definition->isRequired()->willReturn($required);
    return $context_definition;
  }

  /**
   * Tests a simple block that is context aware.
   */
  public function testPlainContextAwareBlock() {
    $block_id = 'context_aware_block';
    $block_definition = [
      'id' => $block_id,
      'admin_label' => 'Feel the context',
    ];

    $block_context_mapping = [
      'slot_1' => '@service.context1',
    ];
    $block_context_definitions = [
      'slot_1' => $this->mockContextDefinition(TRUE),
      'slot_2' => $this->mockContextDefinition(TRUE),
      'slot_3' => $this->mockContextDefinition(FALSE),
    ];

    $contexts = [
      '@service.context1' => $this->prophesize(ContextInterface::class)->reveal(),
      '@service.context2' => $this->prophesize(ContextInterface::class)->reveal(),
      '@service.context3' => $this->prophesize(ContextInterface::class)->reveal(),
    ];

    // BlockBase extends ContextAwarePluginInterface (even though most plugins
    // don't actually do anything with context).
    /** @var \Drupal\Core\Block\BlockBase|\Prophecy\Prophecy\ObjectProphecy $block_plugin */
    $block_plugin = $this->prophesize(BlockBase::class);
    $block_plugin->build()
      ->willReturn(['#markup' => 'Feel the context']);
    $block_plugin->getContextMapping()
      ->willReturn($block_context_mapping);
    $block_plugin->getContextDefinitions()
      ->willReturn($block_context_definitions);

    $this->blockManager->getDefinition($block_id)
      ->willReturn($block_definition);
    $this->blockManager->createInstance($block_id)
      ->willReturn($block_plugin->reveal());

    $this->contextHandler->getMatchingContexts($contexts, $block_context_definitions['slot_2'])
      ->willReturn([
        '@service.context2' => $contexts['@service.context2'],
        '@service.context3' => $contexts['@service.context3'],
      ])
      ->shouldBeCalledTimes(1);
    $this->contextHandler->applyContextMapping($block_plugin->reveal(), $contexts, [
      'slot_1' => '@service.context1',
      'slot_2' => '@service.context2',
    ])
      ->shouldBeCalledTimes(1);

    $rendered = $this->renderer->buildBlockPreview($block_id, $contexts);

    $this->assertEquals(['#markup' => 'Feel the context'], $rendered);
  }

  /**
   * Tests a block that provides its own preview.
   */
  public function testBlockWithPreview() {
    $block_id = 'block_with_preview';
    $block_definition = [
      'id' => $block_id,
      'admin_label' => 'Block with preview',
    ];

    /** @var BlockWithPreviewInterface|\Prophecy\Prophecy\ObjectProphecy $block_plugin */
    $block_plugin = $this->prophesize(BlockWithPreviewInterface::class);
    $block_plugin->build()
      ->willReturn(['#markup' => 'Normal block content'])
      ->shouldNotBeCalled();
    $block_plugin->buildPreview()
      ->willReturn(['#markup' => 'Block preview content'])
      ->shouldBeCalledTimes(1);

    $this->blockManager->getDefinition($block_id)
      ->willReturn($block_definition);
    $this->blockManager->createInstance($block_id)
      ->willReturn($block_plugin->reveal());

    $rendered = $this->renderer->buildBlockPreview($block_id);

    $this->assertEquals(['#markup' => 'Block preview content'], $rendered);
  }

  /**
   * Renders a preview for a block plugin.
   *
   * @param \Drupal\Core\Block\BlockPluginInterface $block_plugin
   *   The block plugin.
   *
   * @return array
   *   A render array.
   */
  public function previewCallback(BlockPluginInterface $block_plugin) {
    return ['#markup' => 'Block preview callback content'];
  }

  /**
   * Tests a block that has been altered to use a preview callback.
   */
  public function testBlockWithPreviewCallback() {
    $block_id = 'block_with_preview_callback';
    $block_definition = [
      'id' => $block_id,
      'admin_label' => 'Block with preview callback',
      'preview_callback' => [$this, 'previewCallback'],
    ];

    /** @var \Drupal\Core\Block\BlockPluginInterface|\Prophecy\Prophecy\ObjectProphecy $block_plugin */
    $block_plugin = $this->prophesize(BlockPluginInterface::class);
    $block_plugin->build()
      ->willReturn(['#markup' => 'Normal block content'])
      ->shouldNotBeCalled();

    $this->blockManager->getDefinition($block_id)
      ->willReturn($block_definition);
    $this->blockManager->createInstance($block_id)
      ->willReturn($block_plugin->reveal());

    $rendered = $this->renderer->buildBlockPreview($block_id);

    $this->assertEquals(['#markup' => 'Block preview callback content'], $rendered);
  }

  /**
   * Tests a block that has been altered to use a preview image.
   */
  public function testBlockWithPreviewImage() {
    $block_id = 'block_with_preview_image';
    $block_definition = [
      'id' => $block_id,
      'admin_label' => 'Block with preview image',
      'preview_image' => 'http://example.com/image.png',
      'preview_alt' => 'Preview image alt',
    ];

    /** @var \Drupal\Core\Block\BlockPluginInterface|\Prophecy\Prophecy\ObjectProphecy $block_plugin */
    $block_plugin = $this->prophesize(BlockPluginInterface::class);
    $block_plugin->build()
      ->willReturn(['#markup' => 'Normal block content'])
      ->shouldNotBeCalled();

    $this->blockManager->getDefinition($block_id)
      ->willReturn($block_definition);
    $this->blockManager->createInstance($block_id)
      ->willReturn($block_plugin->reveal());

    $rendered = $this->renderer->buildBlockPreview($block_id);

    $expected = [
      '#theme' => 'image',
      '#uri' => 'http://example.com/image.png',
      '#alt' => 'Preview image alt',
    ];
    $this->assertEquals($expected, $rendered);
  }

  /**
   * Tests that the first exception is handled correctly.
   */
  public function testException1() {
    $block_id = 'context_aware_block';
    $block_definition = [
      'id' => $block_id,
      'admin_label' => 'Feel the context',
    ];

    // BlockBase extends ContextAwarePluginInterface (even though most plugins
    // don't actually do anything with context).
    /** @var \Drupal\Core\Block\BlockBase|\Prophecy\Prophecy\ObjectProphecy $block_plugin */
    $block_plugin = $this->prophesize(BlockBase::class);
    $block_plugin->build()
      ->willReturn(['#markup' => 'Feel the context'])
      ->shouldNotBeCalled();
    $block_plugin->getContextMapping()
      ->willReturn([]);
    $block_plugin->getContextDefinitions()
      ->willReturn([]);

    $this->blockManager->getDefinition($block_id)
      ->willReturn($block_definition);
    $this->blockManager->createInstance($block_id)
      ->willReturn($block_plugin->reveal());

    $this->contextHandler->applyContextMapping($block_plugin->reveal(), [], [])
      ->willThrow(new ContextException("TEST"));

    $rendered = $this->renderer->buildBlockPreview($block_id, []);

    $this->assertEquals(['#markup' => 'Missing required context'], $rendered);
  }

  /**
   * Tests that the second exception is handled correctly.
   */
  public function testException2() {
    $block_id = 'system_powered_by_block';
    $block_definition = [
      'id' => $block_id,
      'admin_label' => 'Powered by Drupal',
    ];

    /** @var Drupal\Core\Block\BlockPluginInterface|\Prophecy\Prophecy\ObjectProphecy $block_plugin */
    $block_plugin = $this->prophesize(BlockPluginInterface::class);
    $block_plugin->build()
      ->willThrow(new ContextException("TEST"));

    $this->blockManager->getDefinition($block_id)
      ->willReturn($block_definition);
    $this->blockManager->createInstance($block_id)
      ->willReturn($block_plugin->reveal());

    $rendered = $this->renderer->buildBlockPreview($block_id);

    $this->assertEquals(['#markup' => 'Missing required context'], $rendered);
  }

}

/**
 * Test interface for a block that provides its own preview.
 */
interface BlockWithPreviewInterface extends BlockPluginInterface, BlockPreviewInterface {}
