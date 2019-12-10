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
      {string: navigator.userAgent, subString: 'Trident', identity: 'Explorer'}
    ]
  };

  BrowserDetect.init();

  var bv = BrowserDetect.browser;
  var b = document.getElementsByTagName('body')[0];

  // Adding specific classes as per the detected browser to body.
  if (bv === 'MS Edge') {
    b.className += ' edge';
  }
  else if (bv === 'Explorer') {
    b.className += ' ie';
  }

})();
