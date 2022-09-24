export default {
  modules: {},
  webSection: '',

  init: function () {
    this.ajax.initCSRF();
    var match = window.document.body.className.match(/section--([a-z0-9_]*)/);
    if (match) {
      this.webSection = match[1];
    }

    for (var moduleName in this.modules) {
      this.modules[moduleName].init(this, this.webSection);
      if (this[moduleName] === undefined) {
        this[moduleName] = this.modules[moduleName];
      } else {
        console.log('Name overlap: modules.' + moduleName);
      }
    }
    this.bootstrapInputGroup.init();
    this.disableNumberScroll.init();
    this.tipr.init();
    this.flatpickr.init();
    this.bootstrap.init();
    this.footerNewsLinkFlash.init();
    this.flagModal.init();

    $('#logout-link').click(function (e) {
      e.preventDefault();
      document.getElementById('logout-form').submit();
    });
  },

  ajax: {
    initCSRF: function () {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        },
      });
    },
    logoutModal: function () {
      var modalBody = $('<div>')
        .addClass('modal-body')
        .append($('<p>').text('Byli jste dlouho neaktivní a došlo k odhlášení.'));
      var modalFooter = $('<div>')
        .addClass('modal-footer')
        .append($('<a>').attr('href', CzechitasApp.config('loginRoute')).addClass('btn btn-primary').text('Znovu přihlásit'));
      $('body').append(
        $('<div>')
          .attr({ class: 'modal', role: 'dialog', tabindex: -1, id: 'logoutModal' })
          .append(
            $('<div>')
              .addClass('modal-dialog')
              .append($('<div>').addClass('modal-content').append(modalBody).append(modalFooter))
          )
      );
      $('#logoutModal').modal({ backdrop: 'static', keyboard: false });
    },
  },

  config: function (name, defaultValue) {
    var configPath = name.split('.').filter(function (n) {
      return n != '';
    });
    if (configPath.length < 1) {
      return defaultValue;
    }
    var configObject = CzechitasAppConfig;
    for (var i = 0; i < configPath.length - 1; i++) {
      if (configObject[configPath[i]] == undefined) {
        return defaultValue;
      }
      configObject = configObject[configPath[i]];
    }
    return configObject[configPath[configPath.length - 1]] || defaultValue;
  },

  addToConfig: function (name, value, override) {
    override = override || false;
    var configPath = name.split('.').filter(function (n) {
      return n != '';
    });
    if (configPath.length != 1) {
      return null;
    }
    configPath = configPath[0];
    if (CzechitasAppConfig[configPath] !== undefined && !override) {
      return false;
    }
    CzechitasAppConfig[configPath] = value;
    return true;
  },

  asset: function (path) {
    return CzechitasAppConfig.assetPath.replace(/[/]*$/gm, '') + '/' + path.replace(/^[/]*/gm, '');
  },

  loadCss: function (path) {
    $('head').append(
      $('<link>').attr({
        href: path,
        rel: 'stylesheet',
      })
    );
  },

  loadJs: function (path) {
    // Use Google Analytics version -> does not show deprecation
    var script = document.createElement('script');
    script.type = 'text/javascript';
    script.async = true;
    script.src = path;
    var el = document.getElementsByTagName('script')[0];
    el.parentNode.insertBefore(script, el);
  },

  bootstrapInputGroup: {
    init: function () {
      $('body').on('click', '.input-group-append,.input-group-prepend', this.click.bind(this));
    },
    click: function (e) {
      var el = $(e.target);
      el.parents('.input-group').find('input, select').focus();
    },
  },

  disableNumberScroll: {
    init: function () {
      $(document).on('wheel', 'input[type=number]', function () {
        $(this).blur();
      });
    },
  },

  flagModal: {
    init: function () {
      $('body').on('click', '.flagChangeWrap button', function (e) {
        e.preventDefault();
        var button = $(e.target);
        if (!button.is('button')) {
          button = button.parents('button:first');
        }
        var modal = button.parents('.modal');

        $.ajax({
          url: button.parents('form').attr('action'),
          method: 'PUT',
          data: { flag: button.val() },
          success: function (data) {
            toastr.success(data.text, data.title);
            modal.modal('hide');
            $("a[href='#" + modal.attr('id') + "']")
              .removeClass()
              .addClass(button.attr('class'))
              .find('i')
              .removeClass()
              .addClass(button.find('i').attr('class').replace('times', 'plus'));
          },
          error: function (jqXHR) {
            if (jqXHR.status == 401 || jqXHR.status == 419) {
              CzechitasApp.ajax.logoutModal();
            } else {
              toastr.error('Při načítání nastala chyba, zkuste to prosím později', 'Neznámá chyba');
            }
          },
        });
      });
    },
  },

  datatables: {
    loaded: false,
    predefinedDom: {
      inCard:
        "<'px-3 pt-3 datatable_header_wrapper'<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'px-2 pb-3 small datatable_footer_wrapper'<'row'<'col-12'i><'col-12'p>>>",
      inCardWithArchive:
        "<'px-3 pt-3 datatable_header_wrapper'<'row'<'col-sm-12 col-md-4'l><'col-sm-12 col-md-8 withArchive'f>>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'px-2 pb-3 small datatable_footer_wrapper'<'row'<'col-12'i><'col-12'p>>>",
    },
    dtConfig: {},
    instance: null,

    init: function (initConfig) {
      initConfig = initConfig || {};
      this.dtConfig = initConfig;
      this.addResources();
    },

    addResources: function () {
      if (this.loaded === false) {
        this.loaded = null;
        CzechitasApp.loadJs(CzechitasApp.asset(CzechitasApp.config('datatables.jsPath', 'js/datatables.js')));
        CzechitasApp.loadCss(CzechitasApp.asset(CzechitasApp.config('datatables.cssPath', 'css/datatables.css')));
      }
    },

    show: function () {
      this.loaded = true;

      var $tableEl = $(this.dtConfig.selector || 'table[data-table]');
      if ($tableEl) {
        $tableEl.css('width', '100%');
      }
      if (this.dtConfig.withArchiveFilter === true) {
        $(document)
          .one('preInit.dt', this.addArchiveFilter.bind(this))
          .on('stateSaveParams.dt', function (e, settings, data) {
            data.archive = { year: $('.js-archiveFilterSelect').val() };
          })
          .on('stateLoadParams.dt', function (e, settings, data) {
            if (data.archive && data.archive.year) {
              $(".js-archiveFilterSelect option[value='" + data.archive.year + "']").prop('selected', true);
            }
          });
        this.dtConfig.dom = this.predefinedDom.inCardWithArchive;
        if (this.dtConfig.ajax.data) {
          console.error('Datatables config for AJAX contains data method, cannot override with archive');
        } else {
          this.dtConfig.ajax.data = function (data) {
            data.archive = $('.js-archiveFilterSelect').val();
          };
        }
      }

      // if (this.dtConfig.columns) {
      //   var targets = [];
      //   for (var i = 0; i < this.dtConfig.columns.length; i++) {
      //     targets.push(i)
      //   }

      //   if (this.dtConfig.columnDefs) {
      //     this.dtConfig.columnDefs.push({targets:targets, render: this.preventXSSRenderer});
      //   }else{
      //     this.dtConfig.columnDefs = [{targets:targets, render: this.preventXSSRenderer}];
      //   }
      // }

      // Init DataTable
      this.instance = $tableEl.DataTable(
        $.extend(
          {
            stateSave: true,
            stateDuration: 300,
            responsive: true,
            language: DatatablesTranslation[CzechitasApp.config('datatables.lang')],
            dom: this.predefinedDom.inCard,
            aLengthMenu: [15, 30, 60, 100],
            iDisplayLength: 30,
            initComplete: function (settings) {
              if (this.dtConfig.disableSearchAutoFocus !== false) {
                $('#' + settings.sTableId + '_filter input').focus();
              }
            }.bind(this),
            classes: {
              sInfo: 'dataTables_info text-muted pt-0',
              sPaging: 'dataTables_paginate mt-2 paging_',
            },
          },
          this.dtConfig
        )
      );

      if ($tableEl.length) {
        // Init fixed DataTable header
        new $.fn.dataTable.FixedHeader(this.instance);
      }
      $tableEl.trigger('finished.dt.custom');
    },

    formatColumns: function (columnNames, extraColumns) {
      var ret = [];
      for (var i = 0; i < columnNames.length; i++) {
        ret.push({ data: columnNames[i] });
      }
      if (extraColumns) {
        ret = ret.concat(this.formatColumns(extraColumns));
      }
      return ret;
    },

    ajaxError: function (jqXHR) {
      var options = { timeOut: 0, extendedTimeOut: 0 };
      if (jqXHR.status == 401 || jqXHR.status == 419) {
        CzechitasApp.ajax.logoutModal();
      } else {
        toastr.error('Při načítání nastala chyba, zkuste to prosím později', 'Neznámá chyba', options);
      }
    },

    getRenderer: function (renderer, templateEl) {
      return this[renderer + 'Renderer'].bind(this, templateEl);
    },

    actionRenderer: function (templateEl, data, type, row) {
      return this.replaceTemplate(templateEl, data, row);
    },

    flagRenderer: function (templateEl, data, type, row) {
      row['class_name'] = data ? 'btn-' + data : 'text-muted';
      row.flag_icon = row.flag_icon || 'fa-plus';
      data = row.id;
      return this.replaceTemplate(templateEl, data, row);
    },

    preventXSSRenderer: function (data, type, row, meta) {
      return $("<span>").text(data).html();
    },

    replaceTemplate: function (templateEl, data, row) {
      var template = templateEl.clone();
      var attrs = {
        A: 'href',
        DIV: 'id',
        FORM: 'action',
      };
      if (row['policy']) {
        template.find('[data-can]').each(function () {
          var can = $(this).attr('data-can');
          if (!row['policy'][can]) {
            $(this).remove();
          }
        });
      }
      template.find('a, div.js-rendererReplace, form').each(function () {
        var link = $(this).attr(attrs[this.nodeName]).replace('__placeholder__', data);
        $(this).attr(attrs[this.nodeName], link);
      });
      return template
        .html()
        .replace(/\{= data\.([a-z0-9_-]+) =\}/gi, function (match, dataIndex /*, offset, input_string*/) {
          if (row[dataIndex]) {
            return $("<span>").text(row[dataIndex]).html();
          }
          return match;
        });
    },

    addArchiveFilter: function () {
      var filter = $('.js-archiveFilter');
      filter.removeClass('d-none').prependTo('.withArchive .dataTables_filter');
      filter
        .find('select')
        .addClass('form-control-sm')
        .on(
          'change',
          function () {
            this.instance.draw();
          }.bind(this)
        );
    },
  },

  easymde: {
    loaded: false,
    smdeConfig: {},
    editor: {},
    connectedInput: {},

    init: function (name, initConfig) {
      initConfig = initConfig || {};
      if (!$.isPlainObject(initConfig)) {
        initConfig = {
          element: initConfig,
        };
      }
      initConfig['element'] = $(initConfig.element)[0];

      this.smdeConfig[name] = initConfig;
      if (this.loaded === true) {
        this.show(name);
      } else {
        this.addResources();
      }
    },

    addResources: function () {
      if (this.loaded === false) {
        this.loaded = null;
        CzechitasApp.loadJs(CzechitasApp.asset(CzechitasApp.config('easymde.jsPath', 'js/easymde.js')));
        CzechitasApp.loadCss(CzechitasApp.asset(CzechitasApp.config('easymde.cssPath', 'css/easymde.css')));
      }
    },

    show: function (name) {
      if (name == '__loaded__' && this.loaded !== true) {
        this.loaded = true;
        for (var smdeName in this.smdeConfig) {
          this._showOne(smdeName, this.smdeConfig[smdeName]);
        }
        return true;
      } else if (this.loaded === true) {
        this._showOne(name, this.smdeConfig[name]);
        return true;
      }
      return false;
    },

    _showOne: function (name, config) {
      if (this.loaded !== true) {
        console.log('Not loaded');
        return false;
      }

      // All toolbar items
      var toolbar = [
        'undo',
        'redo',
        '|',
        'bold',
        'italic',
        'strikethrough',
        '|',
        'heading',
        'heading-smaller',
        'heading-bigger',
        'heading-1',
        'heading-2',
        'heading-3',
        '|',
        'code',
        'quote',
        'unordered-list',
        'ordered-list',
        '|',
        'link',
        'image',
        'table',
        'horizontal-rule',
        'clean-block',
        '|',
        'preview',
        'side-by-side',
        'fullscreen',
        'guide',
      ];

      // Default disabled + extra from config
      var toolbarExclude = [
        'heading',
        'heading-smaller',
        'heading-bigger',
        'heading-1',
        'heading-2',
        'heading-3',
        'quote',
        'table',
        'guide',
      ].concat(config.toolbarExclude || []);
      // Remove from disabling which are enabled from config
      if (config.toolbarInclude instanceof Array) {
        toolbarExclude = toolbarExclude.filter(function (el) {
          return config.toolbarInclude.indexOf(el) < 0;
        });
      }
      toolbar = toolbar.filter(function (el) {
        return toolbarExclude.indexOf(el) < 0;
      });
      for (var i = 1; i < toolbar.length; i++) {
        if (toolbar[i - 1] == '|' && toolbar[i] == '|') {
          toolbar.splice(i, 1);
          i--;
        }
      }
      if (toolbar[toolbar.length - 1] == '|') {
        toolbar.splice(toolbar.length - 1, 1);
      }

      toolbar.push({
        name: 'guide',
        action: this.showHelp.bind(this),
        className: 'fa fa-question-circle',
        buttonClassName: 'w-auto px-1 with_text',
        title: 'Nápověda',
        text: 'Nápověda',
        default: true,
      });

      new EasyMDE(
        $.extend(
          {
            autoDownloadFontAwesome: false,
            toolbar: toolbar,
            indentWithTabs: false,
            promptURLs: true,
            spellChecker: false,
            tabSize: 4,
            // insertTexts: {
            //     image: ["![%%Popisek%%](#url#", ")"],
            //     link: ["[%%Text odkazu%%", "](#url#)"],
            // },
          },
          config
        )
      );
    },

    showHelp: function () {
      var modal = $('#mdHelpModal');
      if (modal.length == 0) {
        var url = CzechitasApp.config('mdHelpLink', null);
        if (!url) {
          return;
        }
        var modalBody = $('<div>').addClass('modal-body').load(url);
        modal = $('<div>')
          .addClass('modal')
          .attr({ tabindex: '-1', id: 'mdHelpModal' })
          .append($('<div>').addClass('modal-dialog').append($('<div>').addClass('modal-content').append(modalBody)));
        $('body').append(modal);
      }
      modal.modal('show');
    },
  },

  tipr: {
    init: function () {
      toastr.options.closeButton = true;
    },
  },
  flatpickr: {
    instances: {},
    init: function (selector) {
      $(selector || '.js-datepicker').each(
        function (i, el) {
          var t = $(el).attr('autocomplete', 'off');
          var enableTime = t.is('[data-enabletime]');
          var noCalendar = t.is('[data-nocalendar]');
          var minDate = t.attr('data-fp-mindate');
          var instance = t.flatpickr({
            allowInput: true,
            locale: CzechitasAppConfig.lang,
            enableTime: enableTime,
            noCalendar: noCalendar,
            dateFormat:
              (noCalendar ? '' : 'd.m.Y') + (enableTime && !noCalendar ? ' ' : '') + (enableTime ? 'H:i' : ''),
            time_24hr: true,
          });
          if (t.attr('id')) {
            this.instances[t.attr('id')] = instance;
            if (minDate) {
              $(minDate).on('change', this.onchange.bind(this, t.attr('id'), 'minDate'));
            }
          }
        }.bind(this)
      );
    },

    onchange: function (target, option, e) {
      if (option == 'minDate' && this.instances[target] && this.instances[e.target.id]) {
        this.instances[target].set(option, this.instances[e.target.id].selectedDates[0]);
      }
    },
  },

  bootstrap: {
    init: function () {
      this.initTooltip();
    },
    initTooltip: function () {
      $('body').tooltip({ selector: '[data-toggle=tooltip]' });
    },
  },

  toggleYesInput: {
    init: function (inputId, toggleFieldSelector) {
      this.initRadio(inputId, toggleFieldSelector, null);
    },
    initRadio: function (inputId, toggleFieldSelector, trueToggleSelector) {
      var input = $(inputId);
      var toggleField = $(toggleFieldSelector);
      var trueToggleField = $(trueToggleSelector || toggleField);
      toggleField.on('change', this.toggleInput.bind(this, input, trueToggleField)).trigger('change');
    },
    toggleInput: function (inputWrap, checkField) {
      var input = inputWrap.is('input, textarea') ? inputWrap : inputWrap.find('input, textarea');
      if (checkField.is(':checked')) {
        inputWrap.show();
        input.prop('disabled', false).siblings('.invalid-feedback').show();
      } else {
        inputWrap.hide();
        input.prop('disabled', true).siblings('.invalid-feedback').hide();
      }
    },
  },

  footerNewsLinkFlash: {
    timers: {},
    init: function () {
      $('.footer__news a').click(this.linkClicked.bind(this));
    },
    linkClicked: function (e) {
      var searchId = $(e.target)
        .attr('href')
        .match(/#[a-z_0-9]+$/);
      if (searchId && searchId[0]) {
        searchId = searchId[0];
        var el = $(searchId).addClass('flash');
        if (el.length) {
          if (this.timers[searchId]) {
            clearTimeout(this.timers[searchId]);
          }
          this.timers[searchId] = setTimeout(this.removeClass.bind(this, searchId), 2000);
        }
      }
    },
    removeClass: function (searchId) {
      $(searchId).removeClass('flash');
      if (this.timers[searchId]) {
        delete this.timers[searchId];
      }
    },
  },

  magnificPopup: {
    loaded: false,
    init: function () {
      this.addResources();
    },
    addResources: function () {
      if (this.loaded === false) {
        this.loaded = null;
        CzechitasApp.loadJs(CzechitasApp.asset(CzechitasApp.config('magnificPopup.jsPath', 'js/magnific-popup.js')));
        CzechitasApp.loadCss(CzechitasApp.asset(CzechitasApp.config('magnificPopup.cssPath', 'css/magnific-popup.css')));
      }
    },
    close: function () {
      $.magnificPopup.close();
    },
    open: function (items, settings) {
      $.magnificPopup.open(
        $.extend(
          {
            items: items,
            mainClass: 'mfp-iframe-wrapper',
            enableEscapeKey: false,
            closeOnBgClick: false,
            callbacks: {
              open: function () {
                $('.mfp-bg').css('height', '100%');
              },
            },
          },
          settings
        )
      );
    },
  },
};
