<?php

declare(strict_types=1);

namespace Drupal\islandora_altmetric\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an islandora altimetric block.
 *
 * @Block(
 *   id = "islandora_altmetric_islandora_altimetric",
 *   admin_label = @Translation("Islandora Altimetric"),
 *   category = @Translation("Custom"),
 * )
 */
final class IslandoraAltimetricBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
   * Constructs the plugin instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_typemanager,
    RouteMatchInterface $route_match
  ) {
    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_typemanager;
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
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $node = $this->routeMatch->getParameter('node');
    $doi = $node->field_doi->value;
    $markup = <<<EOD
    <div class="altmetric-embed" data-badge-popover="right" data-doi="$doi"></div>
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
    return $build;
  }

}
