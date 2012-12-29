<?php

/**
 * Field handler which shows the word count.
 *
 * @ingroup views_field_handlers
 */
class tmgmt_local_task_handler_field_loop_count extends views_handler_field_entity {

  /**
   * Overrides views_handler_field_entity::render().
   */
  function render($values) {
    $object = $this->get_value($values);
    return $object->getLoopCount();
  }
}