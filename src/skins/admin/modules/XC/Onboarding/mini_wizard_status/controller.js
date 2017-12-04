define('wizard_mini_status', ['ready'], function() {
  var WizardMiniStatus = Object.extend({
    constructor: function(base) {
      var progress = $(base).find('.progress-circle');
      var widget = progress.circleProgress({
        value: progress.data('value'),
        size: 40,
        startAngle: Math.PI / 2,
        fill: 'white',
        emptyFill: 'rgba(255,255,255,0.1)'
      });
    }
  });

  new WizardMiniStatus('#wizard-mini-status');

  return WizardMiniStatus;
});