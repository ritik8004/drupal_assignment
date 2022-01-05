/**
 * @file
 * Globaly required scripts.
 */

(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.kidzania = {
    attach: function (context) {
      $('.datepicker')
        .datepicker({
          dateFormat: 'yy-mm-dd',
          minDate: 0,
          maxDate: '+6m'
        })
        .attr('readonly', true)
        .keypress(function (e) {
          e.preventDefault();
        });

      $.fn.kidzania = function (options) {
        var book_visit_date;
        var book_shifts;
        var defaults = {speed: 500};
        var settings = $.extend({}, defaults, options);
        var body = $('html, body');
        var actionBut = 'a.actionBut';
        var formErrClass = 'formErr';
        var sections = this.children();
        var isFormValid = false;
        if (localStorage.getItem('booking_info') !== null) {
          localStorage.removeItem('booking_info');
        }
        var errorEle = $('.error_block'),
          timeEle = $('.time-to-visit'),
          cartEle = $('.sticky-cart .add-item'),
          ticketNextEle = $('#step-3 .nextBtn'),
          totalEle = $('#totalWrapper'),
          formEle = $('#paymentForm'),
          eleStep0 = $('#step-0'),
          eleStep3 = $('#step-3'),
          eleStep4 = $('#step-4'),
          eleTotWrapper = $('#totalWrapper'),
          eleFormErrMsg = $('.formErrMessage'),
          eleLoader = $('.loading-overlay'),
          eleCartPrice = $('.cart .total-price'),
          eleCartIcon = $('.sticky-cart .cart-icon');

        var genderResponse = [],
          ticketTypes = [],
          ticketTypesFinal = {};

        var actions = {
          init: function () {
            eleLoader.addClass('active');
            this.hideEle([timeEle, errorEle]);
            this.showEle(eleStep0);
            this.getParks();
            this.progressBar(-1);
            ticketNextEle.addClass('disable');
            eleCartPrice.html(0);
          },
          getParks: function () {
            $.get(
              Drupal.url('get-parks'),
              function (data) {
                if (data.getParksResult) {
                  $('.countryBtn .value, .countryDisplay').html(data.getParksResult.Park.Name);
                  this.getGender();
                  eleLoader.removeClass('active');
                }
              }.bind(this)
            );
          },
          getGender: function () {
            $.get(Drupal.url('get-sexes'), function (data) {
              if (data.getSexesResult.Sex) {
                genderResponse = data.getSexesResult.Sex;
              }
            });
          },
          scrollTo: function (target, speed) {
            body
              .stop()
              .animate({scrollTop: target.offset().top - 75}, speed)
              .delay(100);
          },
          hideEle: function (target) {
            var elements = [].concat(target || []);
            for (var i = 0; i < elements.length; i++) {
              elements[i].hide();
            }
          },
          showEle: function (target) {
            target.show();
          },
          ticketUI: function (index, icon, minage, maxage, description, price, ticket) {
            var isTicket = '';
            var isCount = 'disable';
            var unitPrice = parseFloat(price).toFixed(3);
            if (ticket) {
              isCount = ticket.count ? '' : 'disable';
              var ticketTotal = parseFloat(ticket.total).toFixed(3);
              isTicket = "<div class='ticket-subtotal unit'><span class='value'><span>" + ticket.count + "</span><span class='icon icon-ticket'></span></span></div><div class='ticket-subtotal cost'><span class='value'>KWD <span>" + ticketTotal + '</span></span></div>';
            }
            return (
              "<div class='ticket_information clearfix'><input type='hidden' name='ticktIndex' value='" + index + "'><div class='age-group'><div class='age-group-icon'><span class='icon icon_" + icon + "'></span></div><div class='age-range'>" + description + "<br><span class='ticket-age'>(" + minage + '-' + maxage + ")</span></div></div><div class='ticket-unit-price'>KWD <span>" + unitPrice + "</span><div class='ticket-qty clearfix'><a class='plusBtn'><span class='icon icon-plus'></span></a><a class='minusBtn " + isCount + "'><span class='icon icon-minus'></span></a></div></div><div class='ticket-count'>" + isTicket + '</div></div>'
            );
          },
          generateUI: function () {
            var ele = $('#visitorTypes');
            var html = '';
            ticketTypes.forEach((item, i) => {
              html += this.ticketUI(i, item.ID, item.MinAge, item.MaxAge, item.Description, item.Price, item.Ticket);
            });
            ele.html(html);
          },
          checkExists: function (index) {
            return ticketTypes[index]['Ticket'] ? true : false;
          },
          updatePrice: function (index, direction) {
            var checkExists = this.checkExists(index);
            var price = ticketTypes[index].Price;
            if (checkExists) {
              var checkCountExists = ticketTypes[index].Ticket.count;
              let count = direction ? checkCountExists + 1 : checkCountExists - 1;
              let total = count * price;
              ticketTypes[index].Ticket.count = count;
              ticketTypes[index].Ticket.total = total;
              if (!count) {
                delete ticketTypes[index].Ticket;
              }
            }
            else {
              var local = {count: 1, total: 1 * price};
              var adlutIndex = this.getIndexByDes('Adult');
              var toddIndex = this.getIndexByID(1).toString();
              ticketTypes[index]['Ticket'] = local;
              if (
                (index === '0' || index === toddIndex) && (!ticketTypes[adlutIndex]['Ticket'] || !ticketTypes[adlutIndex]['Ticket']['count'])
              ) {
                this.updatePrice(adlutIndex, true);
              }
            }
            this.generateUI();
            this.filterArr();
          },
          total: function () {
            totalEle.show();
            var tCount = 0;
            var tPrice = 0;
            ticketTypes.forEach(item => {
              let context = item['Ticket'];
              if (context && context.count) {
                tCount = tCount + context.count;
                tPrice = tPrice + context.total;
              }
            });
            if (tCount) {
              isFormValid = true;
              totalEle.addClass('active');
              eleCartIcon.addClass('full');
              tPrice = parseFloat(tPrice).toFixed(3);
              var html = "<span class='amount'><span id='#'>" + tCount + "</span><span class='icon icon-ticket'></span>KWD <span id='#'>" + tPrice + '</span></span>';
              totalEle.find('.total_price').html(html);
              eleCartPrice.html(tPrice);
              $('.totalDisplay').html(tPrice);
            }
            else {
              isFormValid = false;
              totalEle.removeClass('active');
              eleCartIcon.removeClass('full');
              eleCartPrice.html(0);
            }
          },
          progressBar: function (index) {
            index = index + 1;
            var html = '';
            var className = '';
            for (let i = 0; i < sections.length; i++) {
              className = index === i ? 'current' : index > i ? 'done' : '';
              html += "<li class='step " + className + "'>" + (i + 1) + '</li>';
            }
            $('.progress-steps').html(html);
          },
          getIndexByDes: function (description) {
            return ticketTypes
              .map(function (ticket) {
                return ticket.Description;
              })
              .indexOf(description);
          },
          getIndexByID: function (id) {
            return ticketTypes
              .map(function (ticket) {
                return ticket.ID;
              })
              .indexOf(id);
          },
          checkValidField: function (id) {
            var index = this.getIndexByID(id);
            var local = ticketTypes[index]['Ticket'];
            return local && local['count'] ? local['count'] : '';
          },
          validations: function (index) {
            var entryBaby = this.checkValidField(0);
            var entryTodd = this.checkValidField(1);
            var entryKid = this.checkValidField(2);
            var entryAdult = this.checkValidField(4);
            errorEle.children().hide();
            errorEle.hide();
            isFormValid = true;

            if (entryBaby || entryTodd) {
              if (!entryAdult) {
                errorEle.show();
                isFormValid = false;
                if (entryBaby) {
                  errorEle.find('.errorBaby').show();
                }
                if (entryTodd) {
                  errorEle.find('.errorToddler').show();
                }
              }
            }
            else if (entryAdult) {
              if (entryKid) {
                errorEle.hide();
                isFormValid = true;
              }
              else if (!entryBaby || !entryTodd) {
                errorEle.show();
                isFormValid = false;
                errorEle.find('.errorAdult').show();
              }
            }

            if (entryKid) {
              if (!entryAdult) {
                errorEle.show();
                errorEle.find('.errorChildren').show();
              }
            }

            if (!entryBaby && !entryTodd && !entryKid && !entryAdult) {
              isFormValid = false;
            }

            if (isFormValid) {
              ticketNextEle.removeClass('disable');
            }
            else {
              ticketNextEle.addClass('disable');
            }
          },
          manualDataChange: function () {
            var babyIndex = this.getIndexByID(0);
            var okuIndex = this.getIndexByDes('OKU');
            var kidIndex = this.getIndexByDes('Kid');
            var toddIndex = this.getIndexByDes('Toddler');
            ticketTypes.splice(okuIndex, 1);
            ticketTypes[babyIndex].Description = 'Infant';
            ticketTypes[kidIndex].Description = 'Children';
            ticketTypes[toddIndex].Description = 'Children';
          },
          filterArr: function () {
            ticketTypesFinal = {data: [], total: {}};
            var fCount = 0;
            var fPrice = 0;
            ticketTypes.forEach((item, i) => {
              if (item['Ticket'] && item['Ticket']['count']) {
                fCount = fCount + item['Ticket']['count'];
                fPrice = fPrice + item['Ticket']['total'];
                ticketTypesFinal.data.push(item);
              }
            });
            ticketTypesFinal.total = {count: fCount, price: fPrice};
          },
          prepareForm: function (index, id, name, min, max) {
            var visitorName = Drupal.t('Visitor Name');
            var visitorAge = Drupal.t('Age');
            var options = '';
            min = parseInt(min, 10);
            max = parseInt(max, 10);
            for (var i = min, j = max + 1; i < j; i++) {
              options += "<option value='" + i + "'>" + i + '</option>';
            }
            var genderHtml = '';
            for (var k = 0, l = genderResponse.length; k < l; k++) {
              let desc = genderResponse[k].Description;
              let isMale = (desc === 'MALE') ? true : false;
              let getIcon = (id < 4) ? (isMale) ? 'boy' : 'girl' : (isMale) ? 'man' : 'woman';
              genderHtml += "<input type='radio' name='gender_" + id + '_' + index + "' value='" + desc + '_' + genderResponse[k].Initial + "'><span class='icon icon-" + getIcon + "'></span>";
            }

            return (
              "<div class='visitor-tickets clearfix'><input type='hidden' name='visitorID' value='" + id + "'><div class='tbl-content'><span class='icon icon_" + id + "'></span><span>" + name + "</span></div><div class='tbl-content'><input type='text' autocomplete='off' class='form-control onlyAlpha' name='name' placeholder='" + visitorName + "' maxlenght='40'/></div><div class='tbl-content'><select class='form-control' name='age'><option value=''>" + visitorAge + " (" + min + '-' + max + ')</option>' + options + "</select></div><div class='tbl-content'>" + genderHtml + '</div></div>'
            );
          },
          generateFormUI: function () {
            var html = '';
            ticketTypesFinal.data.forEach((item, i) => {
              for (let j = 0, k = item['Ticket']['count']; j < k; j++) {
                html += actions.prepareForm(j, item.ID, item.Description, item.MinAge, item.MaxAge);
              }
            });
            formEle.html(html);
          }
        };

        actions.init();
        $('.datepicker').on('change', function () {
          var val = $(this).val();
          if (val) {
            ticketTypes = [];
            actions.generateUI();
            actions.hideEle([eleStep3, eleStep4, eleTotWrapper, timeEle]);
            ticketNextEle.addClass('disable');
            $('.dateDisplay').html(val);
            eleLoader.addClass('active');
            eleCartPrice.html(0);
            eleCartIcon.removeClass('full');
            var call = $.post(Drupal.url('get-shifts'), {
              visit_date: val
            });
            call.done(function (data) {
              if (data) {
                timeEle
                  .show()
                  .find('a')
                  .after('<input type="hidden" value="' + val + '" id="book-visit-date">') // store visit date.
                  .after('<input type="hidden" id="book-shifts">'); // store shifts data.
                $('#book-shifts').val(JSON.stringify(data));
                eleLoader.removeClass('active');
              }
            });
          }
        });

        $('.time_block').on('click', 'a', function () {
          actions.hideEle([eleStep3, eleStep4, eleTotWrapper, errorEle.children(), errorEle]);
          ticketNextEle.addClass('disable');
          book_visit_date = $('#book-visit-date').val();
          book_shifts = $('#book-shifts').val();
          eleLoader.addClass('active');
          eleCartPrice.html(0);
          eleCartIcon.removeClass('full');
          $.post(Drupal.url('get-visitor-types'), {
            visit_date: book_visit_date,
            shifts: book_shifts
          }, function (data) {
            ticketTypes = data.getVisitorTypesResult.VisitorType;
            if (ticketTypes) {
              actions.showEle(eleStep3);
              ticketTypes.sort((a, b) => (a.ID > b.ID ? 1 : -1));
              actions.manualDataChange();
              actions.generateUI();
              eleLoader.removeClass('active');
            }
          });
        });

        $('#visitorTypes').on('click', '.plusBtn, .minusBtn', function (e) {
          var direction = false;
          actions.hideEle([eleFormErrMsg, eleStep4]);
          var getIndex = $(this)
            .closest('.ticket_information')
            .find('input[name="ticktIndex"]')
            .val();
          if (e.currentTarget.className === 'plusBtn') {
            direction = true;
            cartEle.addClass('add');
          }
          else {
            cartEle.addClass('remove');
          }
          setTimeout(function () {
            cartEle.removeClass('add remove');
          }, settings.speed);
          actions.updatePrice(getIndex, direction);
          actions.total();
          actions.validations(getIndex);
          e.preventDefault();
        });

        $('#paymentForm').on('keypress input', '.onlyAlpha', function (e) {
          var $this = $(this);
          var val = $this.val().trim();
          var regex = new RegExp('^[a-zA-Z ]+$');
          if (e.type === 'keypress') {
            var key = String.fromCharCode(!e.charCode ? e.which : e.charCode);
            if (!regex.test(key)) {
              e.preventDefault();
            }
          }
          else {
            if (!val.match(regex)) {
              $this.val(val.replace(/[^A-Za-z ]/g, '').replace(/ {1,}/g, ' '));
            }
          }
        });

        $('.continueBtn').on('click', function (e) {
          var isValid = true;
          for (var i = 0, j = ticketTypesFinal.data.length; i < j; i++) {
            if (ticketTypesFinal.data[i]['Book']) {
              delete ticketTypesFinal.data[i]['Book'];
            }
          }
          $('#paymentForm .visitor-tickets').each(function (i, context) {
            var local = {};
            var $this = $(this);
            var eleHidden = $this.find('input[name="visitorID"]');
            var eleFormName = $this.find('input[name="name"]');
            var eleFormAge = $this.find('select[name="age"]');
            var eleFormGender = $this.find('input[type=radio]');
            var eleFormGChecked = $this.find('input[type=radio]:checked');

            var getIndex = parseInt(eleHidden.val(), 10);
            var name = eleFormName.val();
            var age = eleFormAge.val();
            var gender = eleFormGChecked.val();
            if (name && gender && age) {
              gender = {
                description: gender.split('_')[0],
                initial: gender.split('_')[1]
              };
              $this.find('input, select').removeClass(formErrClass);
              local = {name: name, gender: gender, age: age};
              var index = null;
              for (var j = 0, k = ticketTypesFinal.data.length; j < k; j++) {
                if (ticketTypesFinal.data[j]['ID'] === getIndex) {
                  index = j;
                  break;
                }
              }
              var entryBook = ticketTypesFinal.data[index];
              if (entryBook['Book']) {
                entryBook['Book'].push(local);
              }
              else {
                entryBook['Book'] = [local];
              }
            }
            else {
              isValid = false;
              eleFormName.toggleClass(formErrClass, !name);
              eleFormAge.toggleClass(formErrClass, !age);
              eleFormGender.toggleClass(formErrClass, !gender);
            }
          });
          if (isValid) {
            actions.hideEle(eleFormErrMsg);
            eleLoader.addClass('active');
            ticketTypesFinal['visit_date'] = book_visit_date;
            // Pre validate visitors at server level.
            $.post(Drupal.url('validate-visitor-details'), {
              final_visitor_list: ticketTypesFinal,
              shifts: book_shifts
            }, function (data) {
              if (data.err) {
                eleLoader.removeClass('active');
                actions.showEle(eleFormErrMsg);
                eleFormErrMsg.html(data.message);
              }
              else {
                if (data.status) {
                  localStorage.setItem('booking_info', JSON.stringify(data));
                  $(location).attr('href', Drupal.url('payment'));
                }
              }
            });
          }
          else {
            actions.showEle(eleFormErrMsg);
          }
          e.preventDefault();
        });

        return sections.each(function (i, context) {
          var $this = $(this);
          $(this).on('click', actionBut, function (e) {
            actions.showEle($this.next());
            actions.scrollTo($this.next(), settings.speed);
            actions.progressBar(i);
            if (i === 3) {
              actions.generateFormUI();
            }
            e.preventDefault();
          });
          if (i) {
            actions.hideEle($this);
          }
        });
      };
      $('#kizForm').kidzania();
    }
  };
})(jQuery, Drupal, drupalSettings);
