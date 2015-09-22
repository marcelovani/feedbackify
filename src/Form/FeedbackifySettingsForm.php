<?php

/**
 * @file
 * Contains \Drupal\disqus\Form\DisqusSettingsForm.
 */

namespace Drupal\feedbackify\Form;

use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\file\FileUsage\FileUsageInterface;
use Drupal\Core\Form\FormStateInterface;

class FeedbackifySettingsForm extends ConfigFormBase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * A database backend file usage overridable.
   *
   * @var \Drupal\file\FileUsage\FileUsageInterface
   */
  protected $fileUsage;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a \Drupal\disqus\DisqusSettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\file\FileUsage\FileUsageInterface
   *   The file usage overridable.
   * @param \Drupal\Core\Entity\EntityManagerInterface
   *   The entity manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler, FileUsageInterface $file_usage, EntityManagerInterface $entity_manager) {
    parent::__construct($config_factory);
    $this->moduleHandler = $module_handler;
    $this->fileUsage = $file_usage;
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('config.factory'), $container->get('module_handler'), $container->get('file.usage'), $container->get('entity.manager'));
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'feedbackify_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['feedbackify.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $feedbackify_config = $this->config('feedbackify.settings');
    $form['feedbackify_id'] = array(
      '#type' => 'textfield',
      '#title' => t('Feedbackify form ID'),
      //'#description' => t('Grab Feedbackify ID from ...'),
      '#default_value' => $feedbackify_config->get('feedbackify_id'),
      '#required' => TRUE,
    );
    $form['settings'] = array(
      '#type' => 'vertical_tabs',
      '#weight' => 50,
    );
    // Behavior settings.
    $form['confs'] = array(
      '#type' => 'details',
      '#title' => t('Configurations'),
      '#group' => 'settings',
    );
    $form['confs']['feedbackify_color'] = array(
      '#type' => 'textfield',
      '#title' => t('Button color'),
      '#description' => t('Please specify a hexadecimal color value like %color,
      or leave blank for transparent.', array('%color' => '#237BAB')),
      '#default_value' => $feedbackify_config->get('confs.feedbackify_color'),
    );
    $form['confs']['feedbackify_position'] = array(
      '#type' => 'select',
      '#title' => t('Button Position'),
      '#options' => array(
        'left' => t('Left'),
        'right' => t('Right'),
      ),
      '#description' => t('Please specify a hexadecimal color value like %color,
        or leave blank for transparent.', array('%color' => '#237BAB')),
      '#default_value' => $feedbackify_config->get('confs.feedbackify_position'),
    );
    // Advanced settings.
    $form['advanced'] = array(
      '#type' => 'details',
      '#title' => t('Visibility'),
      '#group' => 'settings',
      '#description' => t(''),
    );
    $form['advanced']['feedbackify_visibility'] = array(
      '#type' => 'radios',
      '#title' => t('Display Feedbackify button'),
      '#options' => array(
        t('On every page except the listed pages.'),
        t('On the listed pages only.'),
      ),
      '#default_value' => $feedbackify_config->get('advanced.feedbackify_visibility'),
    );
    $form['advanced']['feedbackify_pages'] = array(
      '#type' => 'textarea',
      '#title' => t('Pages'),
      '#default_value' => $feedbackify_config->get('advanced.feedbackify_pages'),
      '#description' => t("Enter one page per line as Drupal paths. The '*' character
        is a wildcard. Example paths are %blog for the blog page and %blog-wildcard
        for every personal blog. %front is the front page.", array(
        '%blog' => 'blog',
        '%blog-wildcard' => 'blog/*',
        '%front' => '<front>',
      )),
      '#wysiwyg' => FALSE,
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('feedbackify.settings');
    $config->set('feedbackify_id', $form_state->getValue('feedbackify_id'))
      ->set('confs.feedbackify_color', $form_state->getValue('feedbackify_color'))
      ->set('confs.feedbackify_position', $form_state->getValue('feedbackify_position'))
      ->set('advanced.feedbackify_visibility', $form_state->getValue('feedbackify_visibility'))
      ->set('advanced.feedbackify_pages', $form_state->getValue('feedbackify_pages'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Ensure a hexadecimal color value.
    if ($color = $form_state->getValue('feedbackify_color')) {
      if (!preg_match('/^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/', $color)) {
        $form_state->setErrorByName('feedbackify_color', t('Button color must be a hexadecimal color value like %color, or left blank for transparent.', array('%color' => '#237BAB')));
      }
    }
  }

}
