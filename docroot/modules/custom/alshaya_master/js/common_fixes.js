(function () {

  // For all the Drupal JS we want to use Underscore JS only.
  // @todo find better way to identify if the variable is
  //  currently referring to Lodash.
  if (typeof _ !== 'undefined' && typeof _.sortedIndexOf !== 'undefined') {
    window.lodash = _.noConflict();
  }

})();
