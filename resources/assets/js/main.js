///////////////
// Libraries //
///////////////

import jQuery from 'jquery';
import toastr from 'toastr';

import flatpickr from '../../../node_modules/flatpickr/dist/flatpickr.js'; // Don't need babel then
import 'flatpickr/dist/l10n/cs.js';

import * as bsUtils from 'bootstrap/js/dist/util.js';
import 'bootstrap/js/dist/collapse.js';
import 'bootstrap/js/dist/dropdown.js';
import 'bootstrap/js/dist/modal.js';
import 'bootstrap/js/dist/tooltip.js';
import 'bootstrap/js/dist/tab.js';

import 'bootstrap-select';
import 'bootstrap-select/dist/js/i18n/defaults-cs_CZ.js';

import app from './partials/app.js';
import order from './partials/order.js';
import student from './partials/student.js';

window.$ = window.jQuery = jQuery;
window.jQuery.fn.flatpickr = function (config) {
  return flatpickr(this, config);
};
window.toastr = toastr;
window.Util = bsUtils;

/////////////////
// Application //
/////////////////
window.CzechitasApp = app;
window.CzechitasApp.modules['order'] = order;
window.CzechitasApp.modules['student'] = student;

CzechitasApp.init();
