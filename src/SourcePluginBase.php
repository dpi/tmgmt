<?php

/**
 * @file
 * Contains Drupal\tmgmt\DefaultSourcePluginController.
 */

namespace Drupal\tmgmt;

use Drupal\Component\Plugin\PluginBase;
use Drupal\tmgmt\Entity\JobItem;


/**
 * Default controller class for source plugins.
 *
 * @ingroup tmgmt_source
 */
abstract class SourcePluginBase extends PluginBase implements SourcePluginInterface {

  /**
   * {@inheritdoc}
   */
  public function getLabel(JobItem $job_item) {
    return t('@plugin item unavailable (@item)', array('@plugin' => $this->pluginDefinition['label'], '@item' => $job_item->getItemType() . ':' . $job_item->getItemId()));
  }

  /**
   * {@inheritdoc}
   */
  public function getUri(JobItem $job_item) {
    return array(
      'path' => '',
      'options' => array(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getItemTypes() {
    return isset($this->pluginDefinition['item types']) ? $this->pluginDefinition['item types'] : array();
  }

  /**
   * {@inheritdoc}
   */
  public function getItemTypeLabel($type) {
    $types = $this->getItemTypes();
    if (isset($types[$type])) {
      return $types[$type];
    }
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getType(JobItem $job_item) {
    return ucfirst($job_item->getItemType());
  }

  /**
   * {@inheritdoc}
   */
  public function getExistingLangCodes(JobItem $job_item) {
    return array();
  }

}
