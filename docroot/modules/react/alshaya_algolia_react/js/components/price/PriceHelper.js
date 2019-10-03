function calculateDiscount(price, final_price) {
  const floatPrice = parseFloat(price);
  const floatFinalPrice = parseFloat(final_price);

  const discount = floatPrice - floatFinalPrice;
  if (floatPrice < 0.1 || floatFinalPrice < 0.1 || discount < 0.1) {
    return 0;
  }

  return parseFloat(Math.round((discount * 100) / floatPrice));
}

function formatPrice(price) {
  const priceParts = [
    drupalSettings.reactTeaserView.price.currency,
    price.toFixed(drupalSettings.reactTeaserView.price.decimalPoints)
  ];

  return drupalSettings.reactTeaserView.price.currencyPosition == 'before'
    ? priceParts.map(item => item).join(' ')
    : priceParts.reverse().map(item => item).join(' ');
}

export { calculateDiscount, formatPrice };
