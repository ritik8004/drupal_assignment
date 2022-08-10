// Reset options in case of any error.
export default function resetBenefitOptions(benefitOptions, benefitApplyId, eventType) {
  let flag = false;
  document.getElementById(benefitApplyId).disabled = true;
  if (benefitOptions.length > 0) {
    Object.entries(benefitOptions).forEach(
      ([, element]) => {
        if (element.checked) {
          if (eventType !== 'change') {
            element.checked = false; // eslint-disable-line no-param-reassign
          }
          flag = true;
        }
      },
    );
    // Enable/Disable submit handler.
    if (flag === true && eventType === 'change') {
      document.getElementById(benefitApplyId).disabled = false;
    } else if (flag === false && benefitApplyId === 'benefit_voucher') {
      document.getElementById('voucher-err-msg').innerHTML = '';
    }
  }
}
