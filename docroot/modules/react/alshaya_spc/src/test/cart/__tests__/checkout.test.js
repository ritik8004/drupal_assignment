import each from 'jest-each'
import utilsRewire, { getProcessedCheckoutData } from "../../../../js/backend/v2/checkout";
import * as cartData from '../data/cart.json';

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

    it('Test formatShippingEstimatesAddress()', () => {
      const formatShippingEstimatesAddress = utilsRewire.__get__('formatShippingEstimatesAddress');
      const shipping_assignments = cartData.cart.extension_attributes.shipping_assignments;
      const address = [...shipping_assignments].shift().shipping.address;
      const result = formatShippingEstimatesAddress(address);
      expect(result).toEqual({
        email: 'osmarwado@gmail.com',
        firstname: 'Osmar',
        lastname: 'Wado',
        telephone: '+971555666777',
        country_id: 'AE',
        city: 'Al Awir',
        custom_attributes: [
          {
            attribute_code: 'address_city_segment',
            value: '1',
          },
          {
            attribute_code: 'area',
            value: '13',
          },
        ],
      });
    });


    it('Test formatShippingEstimatesAddress() with extension attributes', () => {
      const formatShippingEstimatesAddress = utilsRewire.__get__('formatShippingEstimatesAddress');
      const shipping_assignments = cartData.cart.extension_attributes.shipping_assignments;
      const address = [...shipping_assignments].shift().shipping.address;

      // Add extension_attributes
      address.extension_attributes = {
        "attr1": "1",
        "attr2": "2",
      };

      // Remove custom_attributes
      delete (address.custom_attributes);

      const result = formatShippingEstimatesAddress(address);
      expect(result.custom_attributes).toEqual([
        {
          "attribute_code": "attr1",
          "value": "1"
        },
        {
          "attribute_code": "attr2",
          "value": "2"
        },
      ]);
    });

    it('Test formatAddressForFrontend()', async () => {
      const formatAddressForFrontend = utilsRewire.__get__('formatAddressForFrontend');
      const data = [
        {
          foo: 'bar',
        },
      ];
      const result = formatAddressForFrontend(data);
      expect(result).toEqual([{
        foo: 'bar',
      }]);
    });

    it('Test getCncStatusForCart()', async () => {
      const getCncStatusForCart = utilsRewire.__get__('getCncStatusForCart');
      const data = [
        {
          foo: 'bar',
        },
      ];
      const result = getCncStatusForCart(data);
      expect(result).toEqual([{
        foo: 'bar',
      }]);
    });
  });
});
