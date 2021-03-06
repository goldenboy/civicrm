<?php

/**
 * Field handler to provide acess control for the email field
 *
 * @ingroup civicrm_field_handlers
 */
class civicrm_handler_field_mail extends civicrm_handler_field {
    function option_definition() {
        $options = parent::option_definition();
        $options['link_to_civicrm'] = array('default' => 'mailto');
        return $options;
    }

    function options_form(&$form, &$form_state) {
        parent::options_form($form, $form_state);
        $form['link_to_civicrm'] = array(
                                         '#title' => t('Link this field'),
                                         '#type' => 'radios',
                                         '#options' => array(
                                                             0 => t('No link'),
                                                             'user' => t('To the user'),
                                                             'mailto' => t("With a mailto:"),
                                                             ),
                                         '#default_value' => $this->options['link_to_civicrm'],
                                         );
    }

    function render($values) {
        if ($this->options['link_to_civicrm'] == 'mailto') {
            return l($values->{$this->field_alias}, "mailto:" . $values->{$this->field_alias});
        }
        return $this->render_link(check_plain($values->{$this->field_alias}), $values);
    }
}
?>