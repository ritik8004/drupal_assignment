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
  var key = encodeURIComponent('step');
  var value = encodeURIComponent('set');

  let s = document.location.search;
  var kvp = `${key}=${value}`;

  var r = new RegExp(`(&|\\?)${key}=[^]*`);

  s = s.replace(r, `$1${kvp}`);

  if (!RegExp.$1) { s += (s.length > 0 ? '&' : '?') + kvp; }

  // Load page with parameter.
  document.location.search = s;
}
