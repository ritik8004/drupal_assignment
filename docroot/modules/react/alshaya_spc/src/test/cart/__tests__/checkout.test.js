jest.mock('axios');
import axios from 'axios';
import each from 'jest-each'
import utilsRewire, { getCncStores } from "../../../../js/backend/v2/checkout";
import { drupalSettings } from '../globals';
import * as cartData from '../data/cart.json';
import * as storeData_re1_4429_vif from '../data/store_RE1-4429-VIF.json';
import * as store_qatestsourcemap_mmcsp_740 from '../data/store_QATESTSOURCE_MMCSP-740.json';
import cncStoreList from '../data/cnc_stores_list.js';
import { getCart } from '../../../../js/backend/v2/common';
import * as productStatus from '../data/product_status.json';
import paymentMethods from '../data/paymentMethods';

describe('Checkout', () => {
  describe('Checkout functions', () => {

    beforeEach(() => {
      window.drupalSettings = drupalSettings;
    });

    afterEach(() => {
      // Clear and reset any mocks set by other tests.
      jest.clearAllMocks();
      jest.resetAllMocks();
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

    it('Test formatShippingEstimatesAddress()', async () => {
      axios.mockResolvedValueOnce({ data: cartData, status: 200 });
      jest
        .spyOn(window.commerceBackend, 'getCartId')
        .mockImplementation(() => '1234');
      const response = await getCart();
      const address = response.data.shipping.address;
      const formatShippingEstimatesAddress = utilsRewire.__get__('formatShippingEstimatesAddress');
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


    describe('Test getDefaultAddress()', () => {
      const getDefaultAddress = utilsRewire.__get__('getDefaultAddress');
      it('With cart data', async () => {
        axios.mockResolvedValueOnce({ data: cartData, status: 200 });
        jest
          .spyOn(window.commerceBackend, 'getCartId')
          .mockImplementation(() => '1234');

        const response = await getCart();
        const result = getDefaultAddress(response.data);
        expect(result.customer_address_id).toEqual('69');
        expect(result.city).toEqual('Al Awir');
        expect(result.default_shipping).toEqual('1');
      });

      it('Without cart data', () => {
        const result = getDefaultAddress({});
        expect(result).toEqual(null);
      });

      it('Without customer data', () => {
        const result = getDefaultAddress({ customer: 'foo' });
        expect(result).toEqual(null);
      });

      it('Last item is default shipping', () => {
        const result = getDefaultAddress(
          {
            customer: {
              addresses: [
                {
                  default_shipping: '0',
                  customer_address_id: '1',
                },
                {
                  customer_address_id: '2',
                },
                {
                  default_shipping: '1',
                  customer_address_id: '3',
                },
              ],
            },
          },
        );
        expect(result.customer_address_id).toEqual('3');
      });

      it('No default shipping', () => {
        const result = getDefaultAddress(
          {
            customer: {
              addresses: [
                {
                  default_shipping: '0',
                  customer_address_id: '1',
                },
                {
                  customer_address_id: '2',
                },
                {
                  default_shipping: null,
                  customer_address_id: '3',
                },
              ],
            },
          },
        );
        expect(result.customer_address_id).toEqual('1');
      });
    });

    it('Test formatShippingEstimatesAddress() with extension attributes', async () => {
      axios.mockResolvedValueOnce({ data: cartData, status: 200 });
      jest
        .spyOn(window.commerceBackend, 'getCartId')
        .mockImplementation(() => '1234');

      const response = await getCart();
      const address = response.data.shipping.address;
      // Add extension_attributes
      address.extension_attributes = {
        attr1: '1',
        attr2: '2',
      };
      // Remove custom_attributes
      delete (address.custom_attributes);

      const formatShippingEstimatesAddress = utilsRewire.__get__('formatShippingEstimatesAddress');
      const result = formatShippingEstimatesAddress(address);
      expect(result.custom_attributes).toEqual([
        {
          attribute_code: 'attr1',
          value: '1'
        },
        {
          attribute_code: 'attr2',
          value: '2'
        },
      ]);
    });

    describe('Test formatAddressForFrontend()', () => {
      const formatAddressForFrontend = utilsRewire.__get__('formatAddressForFrontend');
      each`
       input                           | expectedResult
       ${null}                         | ${null}
       ${{}}                           | ${null}
       ${{ country_id: '' }}           | ${null}
     `.test('With "$input" it should return "$expectedResult"', ({ input, expectedResult }) => {
        expect(formatAddressForFrontend(input)).toBe(expectedResult);
      });

      it('Without custom attributes', async () => {
        const address = {
          country_id: 'AE',
        };
        const result = formatAddressForFrontend(address);
        expect(result.country_id).toEqual('AE');
        expect(result.custom_attributes).toEqual(undefined);
      });

      it('With Address data', async () => {
        axios.mockResolvedValueOnce({ data: cartData, status: 200 });

        jest
          .spyOn(window.commerceBackend, 'getCartId')
          .mockImplementation(() => '1234');

        const response = await getCart();
        const address = response.data.cart.billing_address;
        const result = formatAddressForFrontend(address);

        expect(result.address_city_segment).toEqual('1');
        expect(result.address_apartment_segment).toEqual('1');
        expect(result.address_building_segment).toEqual('foo');
        expect(result.area).toEqual('13');
        expect(result.country_id).toEqual('AE');
        expect(result.custom_attributes).toEqual(undefined);
      });
    });

    describe('Test formatAddressForShippingBilling()', () => {
      const formatAddressForShippingBilling = utilsRewire.__get__('formatAddressForShippingBilling');

      it('With empty value', async () => {
        const result = formatAddressForShippingBilling({});
        expect(result).toEqual({});
      });

      it('With null value', async () => {
        const result = formatAddressForShippingBilling(null);
        expect(result).toEqual({});
      });

      it('Without static data', async () => {
        const address = {
          address_region_segment: '1025',
        };
        const result = formatAddressForShippingBilling(address);
        expect(result.firstname).toEqual(undefined);
      });

      it('With address data', async () => {
        const address = {
          static: {
            firstname: 'John',
            lastname: 'Smith',
          },
          address_region_segment: '1025',
          street: '1 London Rd',
          carrier_info: { code: 'alshayadelivery', method: 'qd2_qd002' },
        };
        const result = formatAddressForShippingBilling(address);

        expect(result.firstname).toEqual('John');
        expect(result.lastname).toEqual('Smith');
        expect(result.static).toEqual(undefined);
        expect(result.carrier_info).toEqual(undefined);
        expect(result.street).toEqual(['1 London Rd']);
        expect(result.customAttributes[0].attributeCode).toEqual('address_region_segment');
        expect(result.customAttributes[0].value).toEqual('1025');
        expect(result.customAttributes[1].attributeCode).toEqual('street');
        expect(result.customAttributes[1].value).toEqual('1 London Rd');
      });
    });

    describe('Tests getCncStatusForCart()', () => {
      it('Without cart data', async () => {
        const getCncStatusForCart = utilsRewire.__get__('getCncStatusForCart');
        window.commerceBackend.setRawCartDataInStorage(null);
        const result = await getCncStatusForCart();
        expect(result).toEqual(null);
      });

      it('With CNC Enabled', async () => {
        axios.mockResolvedValue(productStatus);
        window.commerceBackend.setRawCartDataInStorage(cartData);
        const getCncStatusForCart = utilsRewire.__get__('getCncStatusForCart');
        const result = await getCncStatusForCart();
        expect(result).toEqual(true);
      });

      it('With CNC Disabled', async () => {
        window.commerceBackend.setRawCartDataInStorage(cartData);
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

    describe('Test getStoreInfo()', () => {
      it('When proper store data parameter is provided', async () => {
        axios.mockResolvedValue({ data: storeData_re1_4429_vif, status: 200 });
        const getStoreInfo = utilsRewire.__get__('getStoreInfo');
        const store = cncStoreList[0];
        const result = await getStoreInfo(store);

        expect(result.phone_number).toEqual('044190246 / 044190247');
        expect(result.code).toEqual(storeData_re1_4429_vif.code);
        expect(result.delivery_time).toEqual('1-2 days');
        expect(result.formatted_distance).toEqual(25.77);
        expect(axios).toHaveBeenCalled();
      });

      it('When provided store code is empty', async () => {
        axios.mockResolvedValue({ data: storeData_re1_4429_vif, status: 200 });
        const getStoreInfo = utilsRewire.__get__('getStoreInfo');
        // Create a deep copy so as to not modify the original variable.
        const storeList = JSON.parse(JSON.stringify(cncStoreList));
        const store = storeList[0];
        store.code = '';
        const result = await getStoreInfo(store);

        expect(result).toEqual(null);
      });
    });

    describe('Test getCartStores()', () => {
      it('When proper store data parameter is provided', async () => {
        axios
          .mockResolvedValueOnce({ data: cncStoreList, status: 200 })
          .mockResolvedValueOnce({ data: storeData_re1_4429_vif, status: 200 })
          .mockResolvedValueOnce({ data: store_qatestsourcemap_mmcsp_740, status: 200 })

        const getCartStores = utilsRewire.__get__('getCartStores');
        jest
          .spyOn(window.commerceBackend, 'getCartId')
          .mockImplementation(() => '1234');

        let result = await getCartStores(10, 20);

        expect(axios).toHaveBeenCalled();
        expect(axios.mock.calls.length).toEqual(3);
        expect(result.length).toEqual(2);
        expect(result[0].code).toEqual('RE1-4429-VIF');
        expect(result[0].formatted_distance).toEqual(25.77);
        expect(result[1].code).toEqual('QATESTSOURCE_MMCSP-740');
        expect(result[1].formatted_distance).toEqual(25.77);
      });

      it('When fetching 1 store info fails', async () => {
        axios
          .mockResolvedValueOnce({ data: cncStoreList, status: 200 })
          .mockResolvedValueOnce({ data: storeData_re1_4429_vif, status: 200 })
          .mockResolvedValueOnce({ data: [], status: 200 })

        const getCartStores = utilsRewire.__get__('getCartStores');
        jest
          .spyOn(window.commerceBackend, 'getCartId')
          .mockImplementation(() => '1234');

        let result = await getCartStores(10, 20);

        expect(axios.mock.calls.length).toEqual(3);
        expect(result.length).toEqual(2);
        expect(result[0].code).toEqual('RE1-4429-VIF');
        expect(result[0].formatted_distance).toEqual(25.77);
        expect(result[1].code).toEqual('QATESTSOURCE_MMCSP-740');
        expect(result[1].address).toBeUndefined();
      });

      it('When fetching the cnc store list fails', async () => {
        axios
          .mockResolvedValueOnce({ data: cncStoreList, status: 500 })

        const getCartStores = utilsRewire.__get__('getCartStores');
        jest
          .spyOn(window.commerceBackend, 'getCartId')
          .mockImplementation(() => '1234');

        let result = await getCartStores(10, 20);

        expect(axios.mock.calls.length).toEqual(1);
        expect(result.length).toEqual(0);
      });
    });

    describe('Test getCncStores()', () => {
      it('When proper lat lon parameters is provided', async () => {
        axios
          .mockResolvedValueOnce({ data: cncStoreList, status: 200 })
          .mockResolvedValueOnce({ data: storeData_re1_4429_vif, status: 200 })
          .mockResolvedValueOnce({ data: store_qatestsourcemap_mmcsp_740, status: 200 })

        jest
          .spyOn(window.commerceBackend, 'getCartId')
          .mockImplementation(() => '1234');

        let result = await getCncStores(10, 20);

        expect(axios).toHaveBeenCalled();
        expect(axios.mock.calls.length).toEqual(3);
        expect(result.length).toEqual(2);
        expect(result[0].code).toEqual('RE1-4429-VIF');
        expect(result[0].formatted_distance).toEqual(25.77);
        expect(result[1].code).toEqual('QATESTSOURCE_MMCSP-740');
        expect(result[1].formatted_distance).toEqual(25.77);
      });

      it('When lat is not provided', async () => {
        jest
          .spyOn(window.commerceBackend, 'getCartId')
          .mockImplementation(() => '1234');

        let result = await getCncStores(null, 20);

        expect(result).toEqual([]);
      });

      it('When lon is not provided', async () => {
        jest
          .spyOn(window.commerceBackend, 'getCartId')
          .mockImplementation(() => '1234');

        let result = await getCncStores(10, null);

        expect(result).toEqual([]);
      });

      it('When cart ID is not provided', async () => {
        jest
          .spyOn(window.commerceBackend, 'getCartId')
          .mockImplementation(() => null);

        let result = await getCncStores(10, 20);

        expect(axios.mock.calls.length).toEqual(0);
        expect(result).toEqual(expect.objectContaining({
          'error': true,
          'error_code': 404,
        }));
      });

      it('When fetching 1 store info fails', async () => {
        axios
          .mockResolvedValueOnce({ data: cncStoreList, status: 200 })
          .mockResolvedValueOnce({ data: storeData_re1_4429_vif, status: 200 })
          .mockResolvedValueOnce({ data: [], status: 200 })

        jest
          .spyOn(window.commerceBackend, 'getCartId')
          .mockImplementation(() => '1234');

        let result = await getCncStores(10, 20);

        expect(axios.mock.calls.length).toEqual(3);
        expect(result.length).toEqual(2);
        expect(result[0].code).toEqual('RE1-4429-VIF');
        expect(result[0].formatted_distance).toEqual(25.77);
        expect(result[1].code).toEqual('QATESTSOURCE_MMCSP-740');
        expect(result[1].address).toBeUndefined();
      });

      it('When fetching the cnc store list fails', async () => {
        axios
          .mockResolvedValueOnce({ data: cncStoreList, status: 500 })

        jest
          .spyOn(window.commerceBackend, 'getCartId')
          .mockImplementation(() => '1234');

        let result = await getCncStores(10, 20);

        expect(axios.mock.calls.length).toEqual(1);
        expect(result.length).toEqual(0);
      });
    });

    describe('Test getPaymentMethods()', () => {
      const getPaymentMethods = utilsRewire.__get__('getPaymentMethods');

      it('With Shipping type for getPaymentMethods', async () => {
        const data = paymentMethods;
        window.commerceBackend.setRawCartDataInStorage(null);
        cartData.shipping = {
          method: {
            type: 'home_delivery',
          },
        };

        axios
          .mockResolvedValueOnce({data: cartData, status: 200 })
          .mockResolvedValueOnce({data, status: 200});

        jest
          .spyOn(window.commerceBackend, 'getCartId')
          .mockImplementation(() => '1234');

        let result = await getPaymentMethods();

        expect(axios).toHaveBeenCalled();
        expect(result.length).toEqual(4);
        expect(result[0].code).toEqual('checkout_com_upapi_vault');
        expect(result[0].title).toEqual('Saved Cards (Checkout.com UPAPI)');
        expect(result[3].code).toEqual('cashondelivery');
        expect(result[3].title).toEqual('Cash On Delivery');
      });

      it('With null value when shipping method is not provided', async () => {
        const data = {};
        window.commerceBackend.setRawCartDataInStorage(null);
        cartData.shipping = {
          method: {
            type: {},
          },
        };

        axios
          .mockResolvedValueOnce({data: cartData, status: 200 })
          .mockResolvedValueOnce({data, status: 200});
        jest
          .spyOn(window.commerceBackend, 'getCartId')
          .mockImplementation(() => '1234');

        let result = await getPaymentMethods();
        expect(result).toEqual({});
      });
    });
  });
});
