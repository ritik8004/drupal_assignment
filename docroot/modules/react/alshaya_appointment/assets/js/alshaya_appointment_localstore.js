/**
 * @file
 * Alshaya appointment clear local store.
 */
var params = new URLSearchParams(location.search);
var step = params.get('step');
// If URL does not have step parameter, then
// reset the localstore and load page with step parameter
// to start from step 1.
if (step === null) {
  localStorage.removeItem('appointment_data');
  params.set('step', 'set');
  document.location.search = params.toString();
}
