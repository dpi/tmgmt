<?php

namespace Drupal\tmgmt_notification\Form;

use Drupal\Core\Form\FormBase;
use Drupal\courier\Entity\TemplateCollection;
use Drupal\courier\Entity\CourierContext;

class CourierTemp extends FormBase {

  public function getFormId() {
    return 'courier_temp';
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $tcids = \Drupal::state()->get('tmgmt_notification.tcids', []);

    /** @var \Drupal\tmgmt_courier\TriggerRepository $tr */
    $tr = \Drupal::service('tmgmt_courier.trigger_repository');

    $form['help']['#plain_text'] = 'if something goes wrong, check the drupal log.';

    // List items.
    $form['list'] = [
      '#type' => 'courier_template_collection_list',
      '#title' => $this->t('bleh'),
      '#items' => [],
    ];

    foreach ($tr->getAll() as $trigger => $definition) {
      if (isset($tcids[$trigger]) && ($template_collection = TemplateCollection::load($tcids[$trigger]))) {
        $form['list']['#items'][$trigger] = [
          '#title' => $trigger,
          '#description' => $trigger,
          '#template_collection' => $template_collection,
        ];
      }
    }

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('run once!'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $tcids = \Drupal::state()->get('tmgmt_notification.tcids', []);

    /** @var \Drupal\courier\Service\CourierManagerInterface $courier_manager */
    $courier_manager = \Drupal::service('courier.manager');

    /** @var \Drupal\tmgmt_courier\TriggerRepository $tr */
    $tr = \Drupal::service('tmgmt_courier.trigger_repository');
    foreach ($tr->getAll() as $trigger => $definition) {
      $context_id = $definition['context'];
      if (!$context = CourierContext::load($context_id)) {
        $context = CourierContext::create([
          'label' => $context_id,
          'id' => $context_id,
          'tokens' => $definition['tokens'],
        ]);
        $context->save();
      }

      // Create the TC if it doesnt exist.
      if (!isset($tcids[$trigger]) || !TemplateCollection::load($tcids[$trigger])) {
        $template_collection = TemplateCollection::create();
        $template_collection->setContext($context);
        $template_collection->save();

        $courier_manager->addTemplates($template_collection);
        $template_collection->save();

        $tcids[$trigger] = $template_collection->id();
        drupal_set_message('created tc for ' . $trigger);

        // default content
        if ($courier_email = $template_collection->getTemplate('courier_email')) {
          /** @var \Drupal\courier\EmailInterface $courier_email */
          $courier_email->setSubject('' . $trigger . '!!!! [tmgmt_job:label]');
          $courier_email->setBody('hello [identity:label]<br />this is some default content for ' . $trigger . '<br />job: [tmgmt_job:label] | [tmgmt_job:url] <br />[tmgmt_job_item:label] [tmgmt_job_item:url] (---------these tokens wont work for tmgmt_notification_job context)');
          $courier_email->save();
        }
      }
    }

    \Drupal::state()->set('tmgmt_notification.tcids', $tcids);
  }

}