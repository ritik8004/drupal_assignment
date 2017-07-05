/**
 * @file
 * For Browser detection and adding a class to the body. This helps to target the specific browser in css.
 */

(function () {
  'use strict';

  var BrowserDetect = {
    init: function () {
      this.browser = this.searchString(this.dataBrowser) || 'Other';
      this.version = this.searchVersion(navigator.userAgent) || this.searchVersion(navigator.appVersion) || 'Unknown';
    },
    searchString: function (data) {
      for (var i = 0; i < data.length; i++) {
        var dataString = data[i].string;
        this.versionSearchString = data[i].subString;

        if (dataString.indexOf(data[i].subString) !== -1) {
          return data[i].identity;
        }
      }
    },
    searchVersion: function (dataString) {
      var index = dataString.indexOf(this.versionSearchString);
      if (index === -1) {
        return;
      }

      var rv = dataString.indexOf('rv:');
      if (this.versionSearchString === 'Trident' && rv !== -1) {
        return parseFloat(dataString.substring(rv + 3));
      }
      else {
        return parseFloat(dataString.substring(index + this.versionSearchString.length + 1));
      }
    },

    dataBrowser: [
      {string: navigator.userAgent, subString: 'Edge', identity: 'MS Edge'},
      {string: navigator.userAgent, subString: 'MSIE', identity: 'Explorer'},
      {string: navigator.userAgent, subString: 'Trident', identity: 'Explorer'},
      {string: navigator.userAgent, subString: 'Firefox', identity: 'Firefox'},
      {string: navigator.userAgent, subString: 'Opera', identity: 'Opera'},
      {string: navigator.userAgent, subString: 'OPR', identity: 'Opera'},
      {string: navigator.userAgent, subString: 'UC', identity: 'UC Browser'},
      {string: navigator.userAgent, subString: 'Chrome', identity: 'Chrome'},
      {string: navigator.userAgent, subString: 'Safari', identity: 'Safari'}
    ]
  };

  BrowserDetect.init();

  var bv = BrowserDetect.browser;
  var b = document.getElementsByTagName('body')[0];

  // Adding specific classes as per the detected browser to body.
  if (bv === 'Chrome') {
    b.className += ' chrome';
  }
  else if (bv === 'MS Edge') {
    b.className += ' edge';
  }
  else if (bv === 'Opera') {
    b.className += ' opera';
  }
  else if (bv === 'Explorer') {
    b.className += ' ie';
  }
  else if (bv === 'Firefox') {
    b.className += ' firefox';
  }
  else if (bv === 'UC Browser') {
    b.className += ' ucbrowser';
  }

  if (navigator.userAgent.match(/SAMSUNG|SGH-[I|N|T]|GT-[I|P|N]|SM-[N|P|T|Z|G]|SHV-E|SCH-[I|J|R|S]|SPH-L/i)) {
    b.className += ' samsung';
    if (navigator.userAgent.match(/SAMSUNG|GT-P[3100|3110|5113]/i)) {
      b.className += ' s-tab2';
    }
  }

})();
