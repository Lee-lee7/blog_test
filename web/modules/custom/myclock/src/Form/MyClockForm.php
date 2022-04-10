<?php

namespace Drupal\myclock\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Locale\CountryManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\CacheBackendInterface;

/**
 * Configure regional settings for this site.
 *
 * @internal
 */
class MyClockForm extends ConfigFormBase {

  /**
   * The cache render service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheRender;

  /**
   * The country manager.
   *
   * @var \Drupal\Core\Locale\CountryManagerInterface
   */
  protected $countryManager;

  /**
   * Constructs a MultipleTimezoneClockForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheRender
   *   A cache backend interface instance.
   * @param \Drupal\Core\Locale\CountryManagerInterface $country_manager
   *   The country manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, CacheBackendInterface $cacheRender, CountryManagerInterface $country_manager) {
    parent::__construct($config_factory);
    $this->cacheRender = $cacheRender;
    $this->countryManager = $country_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('cache.config'),
      $container->get('country_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'my_clock_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['myclock.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $countries = $this->countryManager->getList();
    $config = $this->config('myclock.settings');
    $options_pages = $config->get('names_fieldset');
    // Gather the number of names in the form already.
    $num_names = $form_state->get('num_names');
    // We have to ensure that there is at least one name field.
    if ($num_names === NULL) {

      if (!empty($options_pages)) {
        $form_state->set('num_names', count($options_pages));
        $num_names = count($options_pages);
      }
      else {
        $form_state->set('num_names', 1);
        $num_names = 1;
      }
    }

    $form['#tree'] = TRUE;
    $form['names_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Multiple timezone clock'),
      '#prefix' => '<div id="names-fieldset-wrapper">',
      '#suffix' => '</div>',
    ];

    // Foreach ($options_pages as $key => $value) {.
    for ($i = 0; $i < $num_names; $i++) {
      $form['names_fieldset']['names_fieldset2'][$i] = [
        '#type' => 'fieldset',
        '#Collapsible' => TRUE,
        '#title' => $this->t('Select clock country'),
      ];
      $form['names_fieldset']['names_fieldset2'][$i]['name'] = [
        '#type' => 'select',
        '#title' => $this->t('Country name'),
        '#default_value' => $options_pages[$i]['name'] ?? '',
        '#options' => $countries,
        '#required' => TRUE,
        '#attributes' => ['class' => ['country-detect']],
      ];
      $form['names_fieldset']['names_fieldset2'][$i]['skin'] = [
        '#type' => 'select',
        '#title' => $this->t('Clock skin'),
        '#default_value' => $options_pages[$i]['skin'] ?? '',
        '#options' => [
          "1" => '1',
          "2" => '2',
          "3" => '3',
          "4" => '4',
          "5" => '5',
        ],
        '#required' => TRUE,
        '#attributes' => ['class' => ['clock-zone']],
      ];
      $form['names_fieldset']['names_fieldset2'][$i]['zone'] = [
        '#type' => 'select',
        '#title' => $this->t('Timezone'),
        '#default_value' => $options_pages[$i]['zone'] ?? '',
        '#options' => system_time_zones(NULL, TRUE),
        '#required' => TRUE,
        '#attributes' => ['class' => ['timezone-detect']],
      ];
      $form['names_fieldset']['names_fieldset2'][$i]['date'] = [
        '#type' => 'select',
        '#title' => $this->t('Date'),
        '#default_value' => $options_pages[$i]['date'] ?? [],
        '#options' => [
          '1' => $this->t('None'),
          'M' => $this->t('MM/DD/YYYY'),
          'D' => $this->t('DD/MM/YYYY'),
        ],
      ];
      $form['names_fieldset']['names_fieldset2'][$i]['time'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Show time 24 hours'),
        '#default_value' => $options_pages[$i]['time'] ?? [],
        '#options' => ['1' => $this->t('Yes')],
      ];
    }

    $form['names_fieldset']['actions'] = [
      '#type' => 'actions',
    ];
    $form['names_fieldset']['actions']['add_name'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add one more'),
      '#submit' => ['::addOne'],
      '#ajax' => [
        'callback' => '::addmoreCallback',
        'wrapper' => 'names-fieldset-wrapper',
      ],
    ];
    // If there is more than one name, add the remove button.
    if ($num_names > 1) {
      $form['names_fieldset']['actions']['remove_name'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remove one'),
        '#submit' => ['::removeCallback'],
        '#ajax' => [
          'callback' => '::addmoreCallback',
          'wrapper' => 'names-fieldset-wrapper',
        ],
      ];
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * Callback for both ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   */
  public function addmoreCallback(array &$form, FormStateInterface $form_state) {
    return $form['names_fieldset'];
  }

  /**
   * Submit handler for the "add-one-more" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public function addOne(array &$form, FormStateInterface $form_state) {
    $name_field = $form_state->get('num_names');
    $add_button = $name_field + 1;
    $form_state->set('num_names', $add_button);
    // Since our buildForm() method relies on the value of 'num_names' to
    // generate 'name' form elements, we have to tell the form to rebuild. If we
    // don't do this, the form builder will not call buildForm().
    $form_state->setRebuild();
  }

  /**
   * Submit handler for the "remove one" button.
   *
   * Decrements the max counter and causes a form rebuild.
   */
  public function removeCallback(array &$form, FormStateInterface $form_state) {
    $name_field = $form_state->get('num_names');
    if ($name_field > 1) {
      $remove_button = $name_field - 1;
      $form_state->set('num_names', $remove_button);
    }
    // Since our buildForm() method relies on the value of 'num_names' to
    // generate 'name' form elements, we have to tell the form to rebuild. If we
    // don't do this, the form builder will not call buildForm().
    $form_state->setRebuild();
  }

  /**
   * Final submit handler.
   *
   * Reports what values were finally set.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $full_country_list = $this->countryManager->getList();
    $config = $this->config('myclock.settings');
    $values = $form_state->getValues();
    $options_pages = [];
    $all_options_pages = '';
    foreach ($values['names_fieldset']['names_fieldset2'] as $key => $value) {
      $options_pages[$key]['name'] = $value['name'] ?? '';
      $options_pages[$key]['skin'] = $value['skin'] ?? '';
      $options_pages[$key]['zone'] = $value['zone'] ?? '';
      $options_pages[$key]['date'] = $value['date'] ?: [];
      $options_pages[$key]['time'] = $value['time'] ?: [];
      // Getting current time as per the selection of the zones
      // And convert in number of hours.
      date_default_timezone_set($value['zone']);
      $time_division = explode(":", date('P'));
      if ($time_division[1] > 00) {
        $total_sum = $time_division[0] + ($time_division[1] / 60);
        $time_duration = substr(date('P'), 0, 1) . '' . $total_sum;
      }
      else {
        $time_duration = $time_division[0] + ($time_division[1] / 60);
      }
      $all_options_pages .= $full_country_list[$value['name']] . ',' . $value['skin'] . ',' . $time_duration . ',' . $value['name'] . ',' . $value['date'] . ',' . $value['time'][1] . '|';
      $config->set('names_fieldset', $options_pages);
    }
    $config->set('names_fieldset_combi_value', rtrim($all_options_pages, '|'));
    $config->save();
    return parent::submitForm($form, $form_state);
  }

}
