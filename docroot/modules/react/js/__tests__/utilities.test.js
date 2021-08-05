import hasValue from '../utilities/conditionsUtility';

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
