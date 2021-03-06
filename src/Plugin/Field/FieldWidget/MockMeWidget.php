<?php

namespace Drupal\mockme\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\image\Plugin\Field\FieldWidget\ImageWidget;

/**
 * Plugin implementation of the 'mockme_image_field_widget' widget.
 *
 * @FieldWidget(
 *   id = "mockme_image_field_widget",
 *   label = @Translation("MockMe Image"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class MockMeWidget extends ImageWidget {

  /**
   * @var string  React Root.
   */
  private $mockmeRoot = 'mockme-root';

  // TODO: SettingsForm, no save on empty.

  /**
   * {@inheritdoc}
   */
//  public static function defaultSettings() {
//    return [
//      'size' => 60,
//      'placeholder' => '',
//    ] + parent::defaultSettings();
//  }

  /**
   * {@inheritdoc}
   */
//  public function settingsForm(array $form, FormStateInterface $form_state) {
//    $elements = [];
//
//    $elements['size'] = [
//      '#type' => 'number',
//      '#title' => t('Size of textfield'),
//      '#default_value' => $this->getSetting('size'),
//      '#required' => TRUE,
//      '#min' => 1,
//    ];
//    $elements['placeholder'] = [
//      '#type' => 'textfield',
//      '#title' => t('Placeholder'),
//      '#default_value' => $this->getSetting('placeholder'),
//      '#description' => t('Text that will be shown inside the field until a value is entered. This hint is usually a sample value or a brief description of the expected format.'),
//    ];
//
//    return $elements;
//  }

  /**
   * {@inheritdoc}
   */
//  public function settingsSummary() {
//    $summary = [];
//
//    $summary[] = t('Textfield size: @size', ['@size' => $this->getSetting('size')]);
//    if (!empty($this->getSetting('placeholder'))) {
//      $summary[] = t('Placeholder: @placeholder', ['@placeholder' => $this->getSetting('placeholder')]);
//    }
//
//    return $summary;
//  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $element['#process'][] = [get_class($this), 'processMockMeWidget'];
    $element['#previous_value_callback'] = $element['#value_callback'];
    $element['#value_callback'] = [get_class($this), 'valueCallbackMockMeWidget'];
    $element['#description'] = '';
    $element['#upload_location'] .= '/mockme';

    $fieldName = $element['#field_name'];
    $title = $element['#title'];

    $element['mockme_hidden'] = [
      '#type' => 'hidden',
      '#value' => '',
    ];

    $element['mockme_root'] = [
      '#type' => 'fieldset',
      '#open' => TRUE,
      '#markup' => '<div id="' . $this->mockmeRoot . '"></div>',
      '#title' => $title,
      '#description' => '',
      '#weight' => -6,
      '#attached' => [
        'drupalSettings' => [
          'mockmeSettings' => [
            'drupal' => TRUE,
            'mockmeRoot' => $this->mockmeRoot,
            'fieldName' => $fieldName,
            'sgEndpoint' => \Drupal::request()->getSchemeAndHttpHost() . '/mockme/cs',
          ],
        ],
        'library' => [
          'mockme/react',
          'mockme/component',
          'mockme/mockme',
        ],
      ],
      '#upload_location' => $element['#upload_location'],
    ];

    return $element;
  }


  /**
   * Form API callback: Processes a screenshot widget element.
   *
   * This method is assigned as a #process callback in formElement() method.
   *
   * @param $element
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param $form
   *
   * @return
   */
  public static function processMockMeWidget($element, FormStateInterface $form_state, $form) {
    $element['#description'] = '';
    $element['upload']['#attributes']['class'][] = 'hidden';
    unset($element['upload_button']['#attributes']['class']);
    $element['upload_button']['#value'] = t('Save MockMe Mock Up');

    if (!empty($element['#files']) && $element['#preview_image_style']) {
      $element['mockme_root']['#type'] = 'hidden';
    }
    else {
      $element['mockme_root']['#type'] = 'fieldset';
    }

    return $element;
  }


  /**
   * Value callback for MockMeWidget element.
   *
   * @param $element
   * @param $input
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return bool|mixed
   */
  public static function valueCallbackMockMeWidget(&$element, $input, FormStateInterface $form_state) {
    $imageData = FALSE;
    if (!empty($input['mockme_hidden'])) {
      $imageInfo = explode('...---IMAGE-DATA---...', $input['mockme_hidden']);

      $imageData = [
        'imageInfo' => json_decode($imageInfo[0], TRUE),
        'data' => $imageInfo[1],
      ];
    }

    $uploadLocation = $element['#upload_location'];

    if ($imageData) {
      if (empty($uploadLocation)) {
        $uploadLocation = file_default_scheme() . '://';
      }
      // Try to get Image and save it.
      try {
        if ($file = self::saveMockUpImage($imageData, $uploadLocation)) {
          $input['fids'] = ($file) ? $file->id() : '';
        }
      } catch (\Exception $exception) {

      }
    }
    return call_user_func_array($element['#previous_value_callback'], [&$element, $input, &$form_state]);
  }


  /**
   * Saves an image sent from a browser.
   *
   * @param $imageData            URL-Base64 encoded Image String.
   * @param $destination String   URI to place file.
   * @param string $fileName      Filename without extension.
   *
   * @return \Drupal\file\FileInterface|false
   * @throws \Exception
   */
  public static function saveMockUpImage($imageData, $destination, $fileName = '') {
    $file = false;
    $currentFileName = $fileName;
    $base64data = $imageData['data'];
    if (empty($currentFileName)) {
      if (isset($imageData['imageInfo']['device']['deviceName']) &&
          isset($imageData['imageInfo']['device']['deviceOrientation']) &&
          isset($imageData['imageInfo']['device']['deviceColor']) &&
          isset($imageData['imageInfo']['imageString'])) {
        $currentFileName = trim("{$imageData['imageInfo']['device']['deviceName']}"
                          ."_{$imageData['imageInfo']['device']['deviceOrientation']}"
                          ."_{$imageData['imageInfo']['device']['deviceColor']}"
                          . '_'
                          . preg_replace('/\W/', '',
                              $imageData['imageInfo']['imageString']));
      }
      else {
        $currentFileName = 'test' . time();
      }
    }
    // Do we have a correct String format? Then extract Data & Type.
    if (preg_match('/^data:image\/(\w+);base64,/', $base64data, $type)) {
      $data = substr($base64data, strpos($base64data, ',') + 1);
      $type = strtolower($type[1]);

      if (!in_array($type, [ 'jpg', 'jpeg', 'gif', 'png' ])) {
        throw new \Exception('invalid image type');
      }

      $data = base64_decode($data);
    }
    if (!empty($data)) {
      file_prepare_directory($destination, FILE_CREATE_DIRECTORY);
      $file = file_save_data($data, $destination . '/' . $currentFileName . '.' . $type);
    }
    return $file;
  }

  /**
   * Creates a name for the screenshot.
   */
  public function createFileName() {

  }

}
