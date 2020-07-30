<?php

namespace Drupal\smartapp_test_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\SafeMarkup;

/**
 *  base form class
 */
class TestForm extends FormBase
{

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'smartapp_test_form';
  }

  /**
 * {@inheritdoc}
 */
public function buildForm(array $form, FormStateInterface $form_state) {

  $form['firstname'] = [
        '#type' => 'textfield',
        '#title' => $this->t('First Name'),
        '#required' => TRUE,
      ];
  $form['lastname'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Last Name'),
          ];
  $form['subject'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Subject'),
        '#required' => TRUE,
      ];
  $form['message'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Message'),
            '#required' => TRUE,
          ];
  $form['email'] = [
             '#type' => 'email',
             '#title' => $this
               ->t('Your email address'),
             '#required' => TRUE,
           ];
  $form['submit'] = [
    '#type' => 'submit',
    '#value' => $this->t('Send Message'),
  ];

      return $form;
}
/**
   * {@inheritdoc}
   */

public function validateForm(array &$form, FormStateInterface $form_state) {
  $re = '/[\W\d]/u';
  $name = str_replace(' ', '',$form_state->getValue('firstname'));
  if(preg_match_all($re, $name)){
     $form_state->setErrorByName('firstname',"Enter only Letters");
   }

  }
  /**
 * Implements hook_mail().
 */
function TestForm_mail($key, &$message, $params) {
  $options = array(
    'langcode' => $message['langcode'],
  );

  switch ($key) {
    case 'testForm_send':
      $message['from'] = \Drupal::config('system.site')->get('mail');
      $message['subject'] = $params['subject'];
      $message['body'][] = SafeMarkup::checkPlain($params['message']);
      break;
  }
}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $mailManager = \Drupal::service('plugin.manager.mail');
    $langcode = \Drupal::currentUser()->getPreferredLangcode();

  $module = 'smartapp_test_form';
  $key = 'testForm_send';
  $to = $form_state->getValue('email');
  $params['subject'] = $form_state->getValue('subject');
  $params['message'] = $form_state->getValue('message');
  $send = true;
  $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
  if ( ! $result['result']) {
    $message = t('There was a problem sending your email notification to @email about @subject.', array('@name'=>'@email' => $to, '@subject'=>$params['subject']));
/**
*TODO redirect error page
*/
    drupal_set_message($message, 'error');
    \Drupal::logger($module)->error($message);
    return;
  }
  $message = t('Your email notification to @email about @subject was sended.', array('@name'=>'@email' => $to, '@subject'=>$params['subject']));
/**
*TODO redirect error page
*/
  drupal_set_message($message, 'status');
  \Drupal::logger($module)->notice($message);
drupal_set_message( $this->t("Your name is ") . $form_state->getValue('firstname'));
  }

}
