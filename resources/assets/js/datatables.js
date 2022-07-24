// var $ = require('jquery');

import 'datatables.net';
import 'datatables.net-bs4';
import 'datatables.net-fixedheader-bs4';
import 'datatables.net-responsive-bs4';

window.DatatablesTranslation = {
  cs: {
    decimal: ',',
    thousands: '',
    sEmptyTable: 'Žádné záznamy nenalezeny',
    sInfo: 'Zobrazeno _START_ až _END_ záznamů z _TOTAL_',
    sInfoEmpty: 'Žádné záznamy nenalezeny',
    sInfoFiltered: '(filtrováno z _MAX_ záznamů)',
    sInfoPostFix: '',
    sInfoThousands: ' ',
    sLengthMenu: 'Zobraz _MENU_ záznamů',
    sLoadingRecords: 'Načítám...',
    sProcessing: 'Provádím...',
    sSearch: 'Hledat:',
    sZeroRecords: 'Žádné záznamy nebyly nalezeny',
    oPaginate: {
      sFirst: 'První',
      sLast: 'Poslední',
      sNext: 'Další',
      sPrevious: 'Předchozí',
    },
    oAria: {
      sSortAscending: ': aktivujte pro řazení sloupce vzestupně',
      sSortDescending: ': aktivujte pro řazení sloupce sestupně',
    },
  },
  en: $.fn.DataTable.defaults.oLanguage,
};
CzechitasApp.datatables.show(false);
