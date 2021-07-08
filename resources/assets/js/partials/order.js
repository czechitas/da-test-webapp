module.exports = {
  init: function (app, webSection) {
    if (webSection != 'orders') {
      return;
    }
    // this.ares.init(app);
  },

  ares: {
    init: function () {
      $('input#ico').change(this.icoFilled.bind(this));
    },

    icoFilled: function (e) {
      var t = $(e.target);
      if (t.val() == '') {
        return;
      }
      if (t.val().match(/^[0-9]{4,8}$/) == null) {
        toastr.error(CzechitasApp.config('orders.notFoundMsg'));
        return;
      }
      var url = CzechitasApp.config('orders.aresUrl');
      if (!url) {
        console.log('Missing route for ARES');
        return;
      }
      this.inputsDisabled(true);
      $.ajax({
        type: 'POST',
        url: url,
        data: { ico: t.val() },
        success: this.dataReceived.bind(this),
        error: this.dataError.bind(this),
        complete: this.inputsDisabled.bind(this, false),
      });
    },

    dataReceived: function (data) {
      $('#address').val(data.address);
      $('#client').val(data.company);
      toastr.success(CzechitasApp.config('orders.successMsg'));
    },

    dataError: function (jqXHR) {
      toastr.error(CzechitasApp.config(jqXHR.status == 404 ? 'orders.notFoundMsg' : 'orders.errorMsg'));
    },

    inputsDisabled: function (disable) {
      var placeholder = disable ? CzechitasApp.config('orders.ares_searching') : '';
      $('#address, #client').prop('disabled', disable).attr('placeholder', placeholder);
    },
  },
};
