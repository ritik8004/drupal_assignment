const toDigits = numString =>
  numString
    .replace(/[^0-9]/g, "")
    .split("")
    .map(Number);

const condTransform = (predicate, value, fn) => {
  if (predicate) {
    return fn(value);
  } else {
    return value;
  }
};

const doubleEveryOther = (current, idx) =>
  condTransform(idx % 2 === 0, current, x => x * 2);

const reduceMultiDigitVals = current =>
  condTransform(current > 9, current, x => x - 9);

const luhn = {};

luhn.validate = numString => {
  const digits = toDigits(numString);
  const len = digits.length;
  const luhnDigit = digits[len - 1];

  const total = digits
    .slice(0, -1)
    .reverse()
    .map(doubleEveryOther)
    .map(reduceMultiDigitVals)
    .reduce((current, accumulator) => current + accumulator, luhnDigit);

  return total % 10 === 0;
};

export default luhn;
