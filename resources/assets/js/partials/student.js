module.exports = {
  init: function (app, webSection) {
    if (webSection != 'students') {
      return;
    }
    this.termSettings.init(app, this);

    // app.toggleYesInput.init('#restrictions', '#restrictions_yes', app, this);
    app.toggleYesInput.initRadio('.js-loggedOutReasonWrap', 'input[name=logged_out]', '#logged_out_other', app, this);
    app.toggleYesInput.init('#canceled', '#canceled_yes', app, this);
    app.toggleYesInput.init('.js-receivedAtWrap', '#override_received_at', app, this);
  },

  termSettings: {
    fields: null,
    termInput: null,

    init: function () {
      this.fields = $('.configurable');
      this.termInput = $('#term_id');
      this.termInput.change(this.categoryChanged.bind(this)).on('loaded.bs.select', this.filterTermFields.bind(this));
      if (window.CzechitasTermData && window.CzechitasTermSelectedId) {
        this.filterTermFieldsByConfig(window.CzechitasTermData[window.CzechitasTermSelectedId]);
      }
    },

    categoryChanged: function () {
      this.filterTermFields();
    },

    filterTermFields: function () {
      var option = this.termInput.find(':selected');
      // Check val because of Selectpicker placeholder
      if (option.length == 0 || this.termInput.val() == '') {
        this.processFields(this.fields, false);
      } else {
        var termConfig = window.CzechitasTermData[this.termInput.val()];
        if (!termConfig) {
          console.log('Error: Term missing data');
          return;
        }
        this.filterTermFieldsByConfig(termConfig);
      }
    },

    filterTermFieldsByConfig: function (termConfig) {
      for (var item in termConfig) {
        this.processFields(this.fields.filter("[data-part='" + item + "']"), termConfig[item]);
      }
    },

    processFields: function (field, show) {
      if (show) {
        field.show().find('input').prop('disabled', false);
      } else {
        field.hide().find('input').prop('disabled', true);
      }
    },
  },
};
