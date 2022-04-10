<?php

namespace Drupal\myclock\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Locale\CountryManagerInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Provides a 'Multiple Clock' Block.
 *
 * @Block(
 *   id = "Multiple_timezone_clock_block",
 *   subject = @Translation("Advance World Clock Block"),
 *   admin_label = @Translation("Multiple Timezone Clock")
 * )
 */
class MyClockBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The country manager.
   *
   * @var \Drupal\Core\Locale\CountryManagerInterface
   */
  protected $countryManager;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new WorkspaceSwitcherBlock instance.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Locale\CountryManagerInterface $country_manager
   *   The country manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, CountryManagerInterface $country_manager, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->countryManager = $country_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('country_manager'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $admin_config = $this->configFactory->get('myclock.settings');
    $options_pages = $admin_config->get('names_fieldset');
    $markup = '';
    if (!empty($options_pages)) {
      foreach ($options_pages as $value) {
        $markup .= '<div id="clock_' . $value['name'] . '"></div>';
      }
    }
    else {
      $markup .= $this->t('Please go to the multiple timezone clock admin setting page and select the country timezone as per your requirement.');
    }
    $all_options_pages = $admin_config->get('names_fieldset_combi_value');
    $output = [
      '#type' => 'markup',
      '#markup' => $markup,
      '#attached' => [
        'library' => [
          'myclock/myclockcss',
          'myclock/myclockjs',
        ],
        'drupalSettings' => [
          'timezone_clock' => [
            'country_name' => $all_options_pages,
            'module_path' => drupal_get_path('module', 'myclock'),
          ],
        ],
      ],
    ];
    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), [
      'config:myclock.settings',
    ]);
  }

}
