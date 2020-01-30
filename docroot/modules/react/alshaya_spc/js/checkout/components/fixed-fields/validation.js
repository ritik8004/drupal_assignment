import axios from 'axios';

export const fixedFieldValidation = function (e) {
  let valid_form = true;

  // If first name not available.
  if (e.target.elements.fname.value.length === 0) {
    document.getElementById('fname-error').innerHTML = Drupal.t('Please enter first name.');
    document.getElementById('fname-error').classList.add('error');
    valid_form = false;
  }
  else{
    // Remove error class and any error message.
    document.getElementById('fname-error').innerHTML = '';
    document.getElementById('fname-error').classList.remove('error');
  }

  // If last name not available.
  if (e.target.elements.lname.value.length === 0) {
    document.getElementById('lname-error').innerHTML = Drupal.t('Please enter last name.');
    document.getElementById('lname-error').classList.add('error');
    valid_form = false;
  }
  else {
    // Remove error class and any error message.
    document.getElementById('lname-error').innerHTML = '';
    document.getElementById('lname-error').classList.remove('error');
  }

  // If email not available.
  if (e.target.elements.email.value.length === 0) {
    document.getElementById('email-error').innerHTML = Drupal.t('Please enter your email.');
    document.getElementById('email-error').classList.add('error');
    valid_form = false;
  }
  else {
    // Remove error class and any error message.
    document.getElementById('email-error').innerHTML = '';
    document.getElementById('email-error').classList.remove('error');
  }

  // If mobile number not available.
  if (e.target.elements.mobile.value.length === 0) {
    document.getElementById('mobile-error').innerHTML = Drupal.t('Please enter your mobile number.');
    document.getElementById('mobile-error').classList.add('error');
    valid_form = false;
  }
  else {
    verifyValidMobileNumber(e.target.elements.mobile.value);
  }

  return valid_form;
}
