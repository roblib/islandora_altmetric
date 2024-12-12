<?php

declare(strict_types=1);

namespace Drupal\islandora_altmetric\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an islandora metric block.
 *
 * @Block(
 *   id = "islandora_altmetric_islandora_altmetric",
 *   admin_label = @Translation("Islandora Altmetric"),
 *   category = @Translation("Custom"),
 * )
 */
final class IslandoraAltmetricBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  /**
   * The route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs the plugin instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_typemanager,
    RouteMatchInterface $route_match,
    ConfigFactoryInterface $configFactory
  ) {
    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_typemanager;
    $this->configFactory = $configFactory;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('current_route_match'),
      $container->get('config.factory'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $config = $this->configFactory->get('islandora_altmetric.settings');
    $node = $this->routeMatch->getParameter('node');
    if (!$node) {
      return [];
    }
    $doi_field = $config->get('doi_field') ?? 'field_doi';
    $doi = !empty($node->$doi_field->value) ? $node->$doi_field->value : null;
    if (!$doi) {
      return [];
    }
    $style = $config->get('style');
    $markup = <<<EOD
    <div class="altmetric-embed" data-badge-type='$style' data-badge-popover="right" data-doi="$doi"></div>
    <script async src="//badge.altmetric.com/embeds.js"></script>
   EOD;

    $build['content'] = [
      '#markup' => $markup,
    ];

    $build['content'] = [
      '#markup' => $markup,
      '#attached' => [
        'library' => [
          'islandora_altmetric/islandora_altmetric',
        ],
      ],
    ];
    $build['#cache'] = [
      'max-age' => 0,
    ];
    return $build;
  }

}
