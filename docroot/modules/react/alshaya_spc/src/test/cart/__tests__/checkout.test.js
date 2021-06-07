import each from 'jest-each'
import utilsRewire, { getProcessedCheckoutData } from "../../../../js/backend/v2/checkout";

describe('Checkout', () => {
  describe('Checkout functions', () => {
    const getMethodCodeForFrontend = utilsRewire.__get__('getMethodCodeForFrontend');
    each`
     input                           | expectedResult
     ${'foo'}                        | ${'foo'}
     ${'checkout_com_cc_vault'}      | ${'checkout_com'}
     ${'checkout_com_upapi_vault'}   | ${'checkout_com_upapi'}
   `.test('Test that getMethodCodeForFrontend($input) returns "$expectedResult"', ({ input, expectedResult }) => {
      expect(getMethodCodeForFrontend(input)).toBe(expectedResult);
    });

    it('Test formatAddressForFrontend()', async () => {
      const formatAddressForFrontend = utilsRewire.__get__('formatAddressForFrontend');
      const data = [
        {
          city: 'London',
        },
      ];
      const result = formatAddressForFrontend(data);
      expect(result).toEqual({
        data: 'test',
      });
    });

    it('Test getCncStatusForCart()', async () => {
      const getCncStatusForCart = utilsRewire.__get__('getCncStatusForCart');
      const data = [
        {
          city: 'London',
        },
      ];
      const result = getCncStatusForCart(data);
      expect(result).toEqual({
        data: 'test',
      });
    });
  });
});
