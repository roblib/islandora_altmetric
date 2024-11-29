<?php

declare(strict_types=1);

namespace Drupal\islandora_altmetric\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;

/**
 * Configure Islandora Altmetric settings for this site.
 */
final class AltmetricSettingsForm extends ConfigFormBase {

  /**
   * The entity field manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Constructs a AltimetricSettingsForm object.
   *
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager service.
   */
  public function __construct(EntityFieldManagerInterface $entity_field_manager) {
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'islandora_altmetric_altimetric_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['islandora_altmetric.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $fields = $this->entityFieldManager->getFieldDefinitions('node', 'islandora_object');
    $candidate_fields = [];
    foreach ($fields as $field_name => $field_definition) {
      if ($field_definition->getType() === 'string') {
        if (str_starts_with($field_name, 'field')) {
          $candidate_fields[$field_name] = $field_definition->getLabel();
        }
      }
    }
    $form['doi_field'] = [
      '#type' => 'select',
      '#title' => $this->t('DOI Field'),
      '#options' => $candidate_fields,
      '#description' => $this->t("Field from content model with DOI"),
      '#default_value' => $this->config('islandora_altmetric.settings')->get('doi_field'),
    ];
    $styles = [
      '4' => $this->t('Default'),
      'donut' => $this->t('Donut'),
      'medium-donut' => $this->t('Medium Donut'),
      'large-donut' => $this->t('Large Donut'),

    ];
    $form['style'] = [
      '#type' => 'select',
      '#title' => $this->t('Badge Style'),
      '#options' => $styles,
      '#description' => $this->t("Display Style"),
      '#default_value' => $this->config('islandora_altmetric.settings')->get('style'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // @todo Validate the form here.
    // Example:
    // @code
    //   if ($form_state->getValue('example') === 'wrong') {
    //     $form_state->setErrorByName(
    //       'message',
    //       $this->t('The value is not correct.'),
    //     );
    //   }
    // @endcode
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('islandora_altmetric.settings')
      ->set('doi_field', $form_state->getValue('doi_field'))
      ->set('style', $form_state->getValue('style'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
