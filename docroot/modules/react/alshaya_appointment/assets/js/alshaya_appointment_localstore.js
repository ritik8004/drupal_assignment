/**
 * @file
 * Alshaya appointment clear local store.
 */
var params = window.location.search;
// If URL does not have step parameter, then
// reset the localstore and load page with step parameter
// to start from step 1.
if (params.indexOf('?') === -1 || params.indexOf('step') === -1) {
  Drupal.removeItemFromLocalStorage('appointment_data');
  if (params.indexOf('?') > -1) {
    params += '&step=set';
  } else {
    params += '?step=set';
  }
  document.location.search = params;
}
