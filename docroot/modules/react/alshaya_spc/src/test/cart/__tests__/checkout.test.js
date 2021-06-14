jest.mock('axios');
import axios from 'axios';
import each from 'jest-each'
import utilsRewire, { getProcessedCheckoutData } from "../../../../js/backend/v2/checkout";
import { drupalSettings } from '../globals';
import * as cartData from '../data/cart.json';
import * as storeData from '../data/store.json';
import * as productStatus from '../data/product_status.json';

describe('Checkout', () => {
  describe('Checkout functions', () => {

    beforeEach(() => {
      window.drupalSettings = drupalSettings;
    });

    afterEach(() => {
      jest.clearAllMocks();
    });

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

    describe('Test formatAddressForFrontend()', () => {
      it('With empty object', () => {
        const formatAddressForFrontend = utilsRewire.__get__('formatAddressForFrontend');
        const result = formatAddressForFrontend({});
        expect(result).toEqual(null);
      });
      it('With empty country Id', () => {
        const formatAddressForFrontend = utilsRewire.__get__('formatAddressForFrontend');
        const result = formatAddressForFrontend({ country_id: '' });
        expect(result).toEqual(null);
      });
      it('With Address data', () => {
        const address = cartData.cart.billing_address;
        const formatAddressForFrontend = utilsRewire.__get__('formatAddressForFrontend');
        const result = formatAddressForFrontend(address);
        expect(result.address_city_segment).toEqual('1');
        expect(result.address_apartment_segment).toEqual('1');
        expect(result.address_building_segment).toEqual('foo');
        expect(result.area).toEqual('13');
        expect(result.country_id).toEqual('AE');
        expect(result.custom_attributes).toEqual(undefined);
      });
    });

    describe('Tests getCncStatusForCart()', () => {
      it('Without cart data', async () => {
        const getCncStatusForCart = utilsRewire.__get__('getCncStatusForCart');
        const result = await getCncStatusForCart();
        expect(result).toEqual(null);
      });

      it('With CNC Enabled', async () => {
        axios.mockResolvedValue(productStatus);
        window.commerceBackend.setCartDataInStorage(cartData);
        const getCncStatusForCart = utilsRewire.__get__('getCncStatusForCart');
        const result = await getCncStatusForCart();
        expect(result).toEqual(true);
      });

      it('With CNC Disabled', async () => {
        axios.mockResolvedValue({
          cnc_enabled: false,
          in_stock: true,
          max_sale_qty: 0,
          stock: 978,
        });
        window.commerceBackend.setCartDataInStorage(cartData);
        const getCncStatusForCart = utilsRewire.__get__('getCncStatusForCart');
        const result = await getCncStatusForCart();
        expect(result).toEqual(false);
      });
    });

    describe('Tests getProductStatus()', () => {
      it('Without SKU', async () => {
        const getProductStatus = utilsRewire.__get__('getProductStatus');
        const result = await getProductStatus();
        expect(result).toEqual(null);
        expect(axios).not.toHaveBeenCalled();
      });
      it('With SKU', async () => {
        axios.mockResolvedValue(productStatus);
        const getProductStatus = utilsRewire.__get__('getProductStatus');
        const result = await getProductStatus('WZBOWZ107');
        expect(result).toEqual(productStatus);
        expect(axios).toHaveBeenCalled();
      });
    });

    it('Test getStoreInfo()', async () => {
      axios.mockResolvedValue({ data: storeData, status: 200 });
      const getStoreInfo = utilsRewire.__get__('getStoreInfo');
      const result = await getStoreInfo('RE1-3763-BOO');
      expect(result.data).toEqual(storeData);
      expect(axios).toHaveBeenCalled();
    });
  });
});
