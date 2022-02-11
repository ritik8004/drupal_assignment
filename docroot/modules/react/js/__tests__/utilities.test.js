import assert from 'assert';
import {hasValue, isString, isNumber, isBoolean, isArray, isObject} from '../utilities/conditionsUtility';

describe('JS Utilities', () => {
  describe('Functions from conditionsUtility.js', () => {
    describe('Test hasValue()', () => {
      it('UNDEFINED', () => {
        const value = undefined;
        const result = hasValue(value);
        expect(result).toEqual(false);
      });

      it('NULL', () => {
        const value = null;
        const result = hasValue(value);
        expect(result).toEqual(false);
      });

      it('Empty Object', () => {
        const value = {};
        const result = hasValue(value);
        expect(result).toEqual(false);
      });

      it('Empty Array', () => {
        const value = [];
        const result = hasValue(value);
        expect(result).toEqual(false);
      });

      it('FALSE', () => {
        const value = false;
        const result = hasValue(value);
        expect(result).toEqual(false);
      });

      it('Empty string', () => {
        const value = '';
        const result = hasValue(value);
        expect(result).toEqual(false);
      });

      it('Number zero', () => {
        const value = 0;
        const result = hasValue(value);
        expect(result).toEqual(false);
      });

      it('TRUE', () => {
        const value = true;
        const result = hasValue(value);
        expect(result).toEqual(true);
      });

      it('Non-zero Number', () => {
        const value = 1;
        const result = hasValue(value);
        expect(result).toEqual(true);
      });

      it('Not-empty String', () => {
        const value = 'test';
        const result = hasValue(value);
        expect(result).toEqual(true);
      });

      it('Object with key', () => {
        const value = {'randomkey': 'randomvalue'};
        const result = hasValue(value);
        expect(result).toEqual(true);
      });

      it('Array with value', () => {
        const value = [];
        value.push('random');
        const result = hasValue(value);
        expect(result).toEqual(true);
      });
    });
  });
});

describe('isString', function() {
  it('should return `true` for strings', function() {
    assert.strictEqual(isString('foo'), true);
    assert.strictEqual(isString(''), true);
    assert.strictEqual(isString(Object('foo')), true);
    assert.strictEqual(isString(new String('foo')), true);
    assert.strictEqual(isString(new String('')), true);
  });

  it('should return `false` for non-strings', function() {
    assert.strictEqual(isString([1, 2, 3]), false);
    assert.strictEqual(isString(true), false);
    assert.strictEqual(isString(new Date), false);
    assert.strictEqual(isString(new Error), false);
    assert.strictEqual(isString({ '0': 1, 'length': 1 }), false);
    assert.strictEqual(isString(1), false);
    assert.strictEqual(isString(/x/), false);
    assert.strictEqual(isString(Object()), false);
    assert.strictEqual(isString(), false);
  });
});

describe('isNumber', function() {
  it('should return `true` for numbers', function() {
    assert.strictEqual(isNumber(1234), true);
    assert.strictEqual(isNumber(Object(1234)), true);
    assert.strictEqual(isNumber(NaN), true);
  });

  it('should return `false` for non-numbers', function() {
    assert.strictEqual(isNumber([1, 2, 3]), false);
    assert.strictEqual(isNumber(true), false);
    assert.strictEqual(isNumber(new Date), false);
    assert.strictEqual(isNumber(new Error), false);
    assert.strictEqual(isNumber({ 'a': 1 }), false);
    assert.strictEqual(isNumber(/x/), false);
    assert.strictEqual(isNumber('a'), false);
    assert.strictEqual(isNumber(Object()), false);
  });
});

describe('isBoolean', function() {
  it('should return `true` for booleans', function() {
    assert.strictEqual(isBoolean(true), true);
    assert.strictEqual(isBoolean(false), true);
    assert.strictEqual(isBoolean(Object(true)), true);
    assert.strictEqual(isBoolean(Object(false)), true);
  });

  it('should return `false` for non-booleans', function() {
    assert.strictEqual(isBoolean([1, 2, 3]), false);
    assert.strictEqual(isBoolean(new Date), false);
    assert.strictEqual(isBoolean(new Error), false);
    assert.strictEqual(isBoolean({ 'a': 1 }), false);
    assert.strictEqual(isBoolean(1), false);
    assert.strictEqual(isBoolean(/x/), false);
    assert.strictEqual(isBoolean('a'), false);
    assert.strictEqual(isBoolean(), false);
    assert.strictEqual(isBoolean(Object()), false);
  });
});

describe('isArray', function() {
  it('should return `true` for arrays', function() {
    assert.strictEqual(isArray([1, 2, 3]), true);
    assert.strictEqual(isArray([]), true);
  });

  it('should return `false` for non-arrays', function() {
    assert.strictEqual(isArray(true), false);
    assert.strictEqual(isArray(new Date), false);
    assert.strictEqual(isArray(new Error), false);
    assert.strictEqual(isArray({ '0': 1, 'length': 1 }), false);
    assert.strictEqual(isArray(1), false);
    assert.strictEqual(isArray(/x/), false);
    assert.strictEqual(isArray('a'), false);
    assert.strictEqual(isArray(), false);
  });
});

describe('isObject', function() {
  it('should return `true` for objects', function() {
    assert.strictEqual(isObject(Object(false)), true);
    assert.strictEqual(isObject(new Date), true);
    assert.strictEqual(isObject(new Error), true);
    assert.strictEqual(isObject({ 'a': 1 }), true);
    assert.strictEqual(isObject(Object(0)), true);
    assert.strictEqual(isObject(/x/), true);
    assert.strictEqual(isObject(Object('a')), true);
    assert.strictEqual(isObject(Object()), true);
  });

  it('should return `false` for non-objects', function() {
    assert.strictEqual(isObject('foo'), false);
    assert.strictEqual(isObject(true), false);
    assert.strictEqual(isObject(false), false);
    assert.strictEqual(isObject(1234), false);
    assert.strictEqual(isObject([1, 2, 3]), false);
    assert.strictEqual(isObject(), false);
  });
});
