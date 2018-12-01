(function($, Drupal) {
  Drupal.behaviors.mockme = {
    attach: function (context, settings) {
      // Only call on Form.
      if (context.toString() === '[object HTMLFormElement]') {
        const $container = document
          .getElementById(settings.mockmeSettings.mockmeRoot);
        // If container & RenderMockMe from mockme.js exist, render React.
        if ($container && typeof RenderMockMe !== 'undefined') {
          new RenderMockMe($container, settings.mockmeSettings);
        }
      }
    }
  };
})(jQuery, Drupal);
