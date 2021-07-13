jest.mock('axios');
import _ from 'lodash';
import axios from 'axios';
import each from 'jest-each'
import utilsRewire, { getCncStores } from "../../../../js/backend/v2/checkout";
import { getCart } from '../../../../js/backend/v2/common';
import { drupalSettings, Drupal } from '../globals';
import paymentMethods from '../data/paymentMethods';
import homeDeliveryShippingMethods from '../data/homeDeliveryShippingMethods';
import cncStoreList from '../data/stores/cnc_stores_list.js';
import * as cartData from '../data/cart.json';
import * as RA1_Q314_HEN_001 from '../data/stores/RA1-Q314-HEN-001.json';
import * as RA1_Q314_HEN_002 from '../data/stores/RA1-Q314-HEN-002.json';
import * as RA1_Q314_HEN_003 from '../data/stores/RA1-Q314-HEN-003.json';
import * as productStatus from '../data/product_status.json';

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
        street: [
          '1 London Rd',
        ],
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

    it('Test getCartCustomerEmail()', async () => {
      const getCartCustomerEmail = utilsRewire.__get__('getCartCustomerEmail');

      axios.mockResolvedValueOnce({ data: cartData, status: 200 });

      jest
        .spyOn(window.commerceBackend, 'getCartId')
        .mockImplementation(() => '1234');

      expect(await getCartCustomerEmail()).toEqual('osmarwado@gmail.com');
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

    describe('Test getCustomerPublicData()', () => {
      const getCustomerPublicData = utilsRewire.__get__('getCustomerPublicData');

      it('With empty data', async () => {
        const result = getCustomerPublicData({});
        expect(result).toEqual({});
      });

      it('With invisible characters', () => {
        const data = {
          firstname: 'Foo',
          lastname: '&#8203;',
        };
        const result = getCustomerPublicData(data);
        expect(result.firstname).toEqual('Foo');
        expect(result.lastname).toEqual('');
      });

      it('With customer data', async () => {
        axios.mockResolvedValueOnce({ data: cartData, status: 200 });

        jest
          .spyOn(window.commerceBackend, 'getCartId')
          .mockImplementation(() => '1234');

        const response = await getCart();
        const data = response.data.customer;
        const result = getCustomerPublicData(data);

        expect(result.id).toEqual('478');
        expect(result.firstname).toEqual('Osmar');
        expect(result.lastname).toEqual('Wado');
        expect(result.email).toEqual('osmarwado@gmail.com');
        expect(result.addresses.length).toEqual(2);
        expect(result.addresses[0].id).toEqual(undefined);
        expect(result.addresses[0].customer_address_id).toEqual('69');
        expect(result.addresses[0].region).toEqual('0');
        expect(result.addresses[0].region_id).toEqual('0');
        expect(result.addresses[1].address_city_segment).toEqual('2');
        expect(result.addresses[1].area).toEqual('207');
      });
    });

    describe('Test addShippingInfo()', () => {
      const addShippingInfo = utilsRewire.__get__('addShippingInfo');

      beforeEach(() => {
        jest
          .spyOn(window.commerceBackend, 'getCartId')
          .mockImplementation(() => '1234');

        // Reset static cache to allow Axios to get called.
        window.commerceBackend.setRawCartDataInStorage(null);
      });

      it('With empty data', async () => {
        const result = await addShippingInfo({}, 'update shipping', true);
        expect(result).toEqual(null);
        expect(axios).not.toHaveBeenCalled();
      });

      it('With address data', async () => {
        // Mock for getCart().
        axios.mockResolvedValue({ data: cartData, status: 200 });
        // Mock for update shipping.
        axios.mockResolvedValue({ data: cartData, status: 200 });
        // Mock for update billing.
        axios.mockResolvedValue({ data: cartData, status: 200 });

        const data = {
          address: {
            static: {
              firstname: 'Johnny',
            },
            street: '1 Long st',
            carrier_info: {
              code: 300,
              method: 'foo',
            },
          },
        };
        // Call addShippingInfo();
        await addShippingInfo(data, 'update shipping', true);

        expect(axios.mock.calls.length).toBe(3);

        // We cannot check the result of updateCart() but we can check if it
        // is being called with the correct parameters provided by addShippingInfo().
        expect(axios).toHaveBeenNthCalledWith(
          2,
          {
            data: '{"shipping":{"shipping_address":{"firstname":"Johnny","street":["1 Long st"],"custom_attributes":[]}},"extension":{"action":"update shipping"}}',
            headers: {
              'Alshaya-Channel': 'web',
              'Content-Type': 'application/json',
            },
            method: 'POST',
            url: 'v1/en_gb/rest/V1/guest-carts/1234/updateCart',
          },
        );
      });

      it('With address data and customer_address_id', async () => {
        // Mock for getCart().
        axios.mockResolvedValue({ data: cartData, status: 200 });
        // Mock for update shipping.
        axios.mockResolvedValue({ data: cartData, status: 200 });
        // Mock for update billing.
        axios.mockResolvedValue({ data: cartData, status: 200 });

        const data = {
          customer_address_id: '461',
          address: {
            city: 'London',
            street: '1 Long st',
          },
        };
        // Call addShippingInfo();
        await addShippingInfo(data, 'update shipping', true);

        expect(axios.mock.calls.length).toBe(3);

        // We cannot check the result of updateCart() but we can check if it
        // is being called with the correct parameters provided by addShippingInfo().
        expect(axios).toHaveBeenNthCalledWith(
          2,
          {
            data: '{"shipping":{"shipping_address":{"city":"London","street":"1 Long st"}},"extension":{"action":"update shipping"}}',
            headers: {
              'Alshaya-Channel': 'web',
              'Content-Type': 'application/json',
            },
            method: 'POST',
            url: 'v1/en_gb/rest/V1/guest-carts/1234/updateCart',
          },
        );

        // We cannot check the result of updateBilling() but we can check if it
        // is being called with the correct parameters provided by addShippingInfo().
        expect(axios).toHaveBeenNthCalledWith(
          3,
          {
            data: '{"extension":{"action":"update billing"},"billing":{"city":"London","street":"1 Long st"}}',
            headers: {
              'Alshaya-Channel': 'web',
              'Content-Type': 'application/json',
            },
            method: 'POST',
            url: 'v1/en_gb/rest/V1/guest-carts/1234/updateCart',
          },
        );
      });
    });

    describe('Test formatAddressForShippingBilling()', () => {
      const formatAddressForShippingBilling = utilsRewire.__get__('formatAddressForShippingBilling');

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
          carrier_info: {
            code: 'alshayadelivery',
            method: 'qd2_qd002',
          },
          foo: '',
        };
        const result = formatAddressForShippingBilling(address);

        expect(result.static).toEqual(undefined);
        expect(result.carrier_info).toEqual(undefined);
        expect(result.firstname).toEqual('John');
        expect(result.lastname).toEqual('Smith');
        expect(result.street).toEqual(['1 London Rd']);
        expect(result.custom_attributes[0].attribute_code).toEqual('address_region_segment');
        expect(result.custom_attributes[0].value).toEqual('1025');
        expect(result.custom_attributes.length).toEqual(1);
      });
    });

    describe('Test selectCnc()', () => {
      const selectCnc = utilsRewire.__get__('selectCnc');
      let address = null;

      axios.mockResolvedValueOnce({ data: cartData, status: 200 });

      beforeEach(async () => {
        jest
          .spyOn(window.commerceBackend, 'getCartId')
          .mockImplementation(() => '1234');

        const response = await getCart();
        address = response.data.shipping.address;
      });

      it('With invalid address area', async () => {
        // Mock for validateAddressAreaCity().
        axios.mockResolvedValueOnce({ data: false, status: 200 });
        const result = await selectCnc({ code: 1234 }, address, address);
        expect(axios.mock.calls.length).toBe(1);
        expect(result).toEqual(false);
      });

      it('With no extension_attributes', async () => {
        // Mock for validateAddressAreaCity().
        axios.mockResolvedValueOnce({ data: { address: true }, status: 200 });
        delete address.custom_attributes;
        delete address.extension_attributes;
        const result = await selectCnc({ code: 1234 }, address, address);
        expect(axios.mock.calls.length).toBe(1);
        expect(result).toEqual(false);
      });

      it('With address data', async () => {
        // Mock for validateAddressAreaCity().
        axios.mockResolvedValueOnce({ data: { address: true }, status: 200 });
        // Mock for updateCart().
        axios.mockResolvedValueOnce({ data: cartData, status: 200 });
        // Mock for updateCart().
        axios.mockResolvedValueOnce({ data: cartData, status: 200 });

        delete address.custom_attributes;
        address.extension_attributes = {
          foo: 'bar',
        };
        address.customer_address_id = '1';

        const result = await selectCnc({ code: 1234 }, address, address);
        expect(axios.mock.calls.length).toBe(3);
        const data = result.data.cart;
        expect(data.billing_address.city).toEqual('Al Awir');
        expect(data.billing_address.customer_address_id).toEqual('69');
        expect(data.billing_address.custom_attributes[0].attribute_code).toEqual('address_city_segment');
        // @todo check calling params for axios
      });
    });

    describe('Tests getCncStatusForCart()', () => {
      const getCncStatusForCart = utilsRewire.__get__('getCncStatusForCart');

      it('Without cart data', async () => {
        const result = await getCncStatusForCart(null);
        expect(result).toEqual(true);
      });

      it('With CNC Enabled', async () => {
        axios.mockResolvedValue({ data: productStatus });

        const result = await getCncStatusForCart(cartData);
        expect(result).toEqual(true);
      });

      it('With CNC Disabled', async () => {
        axios.mockResolvedValue({
          data: {
            cnc_enabled: false,
            in_stock: true,
            max_sale_qty: 0,
            stock: 978,
          },
        });

        const data = {
          cart: {
            items: [
              {
                sku: 'WZBOWZ108',
              },
            ],
          }
        };

        const result = await getCncStatusForCart(data);
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
        axios.mockResolvedValue({ data: productStatus });
        const getProductStatus = utilsRewire.__get__('getProductStatus');
        const result = await getProductStatus('WZBOWZ777');
        expect(result).toEqual(productStatus);
        expect(axios).toHaveBeenCalled();
      });
    });

    describe('Test addCncShippingInfo()', () => {
      const addCncShippingInfo = utilsRewire.__get__('addCncShippingInfo');

      const shippingData = {
        static: {
          firstname: 'Foo',
          lastname: 'Bar',
          email: 'FooBar@example.com',
          telephone: '+971555666777',
          country_id: 'AE',
        },
        store: {
          name: 'DUBAI FESTIVAL CITY MALL',
          code: 'RE1-3763-BOO',
          rnc_available: false,
          cart_address: {
            city: 'Abu Hail',
            country_id: 'AE',
            telephone: '+99999999',
            street: 'Crescent Rd-Dubai Festival City-Dubai',
            extension: {
              address_apartment_segment: '',
              address_building_segment: '',
              area: '9',
              address_city_segment: '1',
            },
          },
        },
        carrier_info: {
          code: 'alshaya1',
          method: 'click_and_collect',
        },
      };

      beforeEach(() => {
        axios
          // Mocks for update with shipping data.
          .mockResolvedValue({data: cartData, status: 200})
          // Mocks for update with billing data.
          .mockResolvedValue({data: cartData, status: 200});

        jest
          .spyOn(window.commerceBackend, 'getCartId')
          .mockImplementation(() => '1234');

        // Pre-populate static cart.
        window.commerceBackend.setRawCartDataInStorage(cartData);
      });

      it('With shipping data', async () => {
        // Keep a copy of original.
        const original = _.cloneDeep(shippingData);
        await addCncShippingInfo(shippingData, 'update shipping', false);

        // Make sure the original object was not changed.
        expect(original).toEqual(shippingData);

        expect(axios.mock.calls.length).toBe(2);

        // We cannot check the result of updateCart() but we can check if it
        // is being called with the correct parameters provided by addShippingInfo().
        expect(axios).toHaveBeenNthCalledWith(
          1,
          {
            data: '{"extension":{"action":"update shipping"},"shipping":{"shipping_address":{"city":"Abu Hail","country_id":"AE","telephone":"+971555666777","firstname":"Foo","lastname":"Bar","email":"FooBar@example.com","street":["Crescent Rd-Dubai Festival City-Dubai"],"custom_attributes":[{"attribute_code":"area","value":"9"},{"attribute_code":"address_city_segment","value":"1"}]},"shipping_carrier_code":"alshaya1","shipping_method_code":"click_and_collect","extension_attributes":{"click_and_collect_type":"ship_to_store","store_code":"RE1-3763-BOO"}}}',
            headers: {
              'Alshaya-Channel': 'web',
              'Content-Type': 'application/json',
            },
            method: 'POST',
            url: 'v1/en_gb/rest/V1/guest-carts/1234/updateCart',
          },
        );
      });
    });

    describe('Test getStoreInfo()', () => {
      const getStoreInfo = utilsRewire.__get__('getStoreInfo');

      it('When proper store data parameter is provided', async () => {
        axios.mockResolvedValue({ data: RA1_Q314_HEN_001, status: 200 });

        const store = cncStoreList[0];
        const result = await getStoreInfo(store);

        expect(result.phone_number).toEqual(RA1_Q314_HEN_001.phone_number);
        expect(result.code).toEqual(RA1_Q314_HEN_001.code);
        expect(result.delivery_time).toEqual(RA1_Q314_HEN_001.delivery_time);
        expect(result.formatted_distance).toEqual(8.8);
        expect(axios).toHaveBeenCalled();
      });

      it('When provided store code is empty', async () => {
        axios.mockResolvedValue({ data: RA1_Q314_HEN_001, status: 200 });
        const result = await getStoreInfo({});
        expect(result).toEqual(null);
      });
    });

    describe('Test getCartStores()', () => {
      const getCartStores = utilsRewire.__get__('getCartStores');

      beforeEach(() => {
        jest
          .spyOn(window.commerceBackend, 'getCartId')
          .mockImplementation(() => '1234');
      });

      it('When proper store data parameter is provided', async () => {
        axios
          .mockResolvedValueOnce({ data: cncStoreList, status: 200 })
          .mockResolvedValueOnce({ data: RA1_Q314_HEN_001, status: 200 })
          .mockResolvedValueOnce({ data: RA1_Q314_HEN_002, status: 200 })
          .mockResolvedValueOnce({ data: RA1_Q314_HEN_003, status: 200 });

        let result = await getCartStores(10, 20);

        expect(axios).toHaveBeenCalled();
        expect(axios.mock.calls.length).toEqual(4);
        expect(result.length).toEqual(3);
        expect(result[0].code).toEqual('RA1-Q314-HEN-002');
        expect(result[0].formatted_distance).toEqual(8.75);
        expect(result[1].code).toEqual('RA1-Q314-HEN-003');
        expect(result[1].formatted_distance).toEqual(7.22);
        expect(result[2].code).toEqual('RA1-Q314-HEN-001');
        expect(result[2].formatted_distance).toEqual(8.8);
      });

      it('When fetching 1 store info fails', async () => {
        axios
          .mockResolvedValueOnce({ data: cncStoreList, status: 200 })
          .mockResolvedValueOnce({ data: RA1_Q314_HEN_001, status: 200 })
          .mockResolvedValueOnce({ data: RA1_Q314_HEN_002, status: 200 })
          .mockResolvedValueOnce({ data: [], status: 200 });

        const result = await getCartStores(10, 20);

        expect(axios.mock.calls.length).toEqual(4);
        expect(result.length).toEqual(2);
        expect(result[0].code).toEqual('RA1-Q314-HEN-002');
        expect(result[0].formatted_distance).toEqual(8.75);
      });

      it('When fetching the cnc store list fails', async () => {
        axios
          .mockResolvedValueOnce({ data: cncStoreList, status: 500 });

        let result = await getCartStores(10, 20);
        expect(axios.mock.calls.length).toEqual(1);
        expect(result.data.error).toEqual(true);
      });
    });

    describe('Test getCncStores()', () => {
      const getCncStores = utilsRewire.__get__('getCncStores');

      it('When cart ID is not provided', async () => {
        let result = await getCncStores(10, 20);

        expect(axios.mock.calls.length).toEqual(0);
        expect(result).toEqual(expect.objectContaining({
          'error': true,
          'error_code': 404,
        }));
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

      it('When fetching the cart stores fails', async () => {
        jest
          .spyOn(window.commerceBackend, 'getCartId')
          .mockImplementation(() => '1234');

        axios
          .mockResolvedValueOnce({ data: {}, status: 500 });

        let result = await getCncStores(10, 20);

        expect(axios.mock.calls.length).toEqual(1);
        expect(result.data.error).toEqual(true);
      });
    });

    describe('Test getPaymentMethods()', () => {
      const getPaymentMethods = utilsRewire.__get__('getPaymentMethods');

      it('With Shipping type for getPaymentMethods', async () => {
        window.commerceBackend.setRawCartDataInStorage(null);

        axios
          .mockResolvedValueOnce({ data: cartData, status: 200 })
          .mockResolvedValueOnce({ data: paymentMethods, status: 200 });

        jest
          .spyOn(window.commerceBackend, 'getCartId')
          .mockImplementation(() => '1234');

        const result = await getPaymentMethods();

        expect(axios).toHaveBeenCalled();
        expect(result.length).toEqual(4);
        expect(result[0].code).toEqual('checkout_com_upapi_vault');
        expect(result[0].title).toEqual('Saved Cards (Checkout.com UPAPI)');
        expect(result[3].code).toEqual('cashondelivery');
        expect(result[3].title).toEqual('Cash On Delivery');
      });

      it('With null value when shipping method is not provided', async () => {
        window.commerceBackend.setRawCartDataInStorage(null);
        cartData.shipping = {
          method: {
            method: {},
          },
        };

        axios
          .mockResolvedValueOnce({ data: cartData, status: 200 })
          .mockResolvedValueOnce({ data: {}, status: 200 });
        jest
          .spyOn(window.commerceBackend, 'getCartId')
          .mockImplementation(() => '1234');

        const result = await getPaymentMethods();
        expect(result).toEqual({});
      });
    });

    describe('Test getHomeDeliveryShippingMethods()', () => {
      const getHomeDeliveryShippingMethods = utilsRewire.__get__('getHomeDeliveryShippingMethods');
      it('With static value from getHomeDeliveryShippingMethods', async () => {
        const data = homeDeliveryShippingMethods;
        window.commerceBackend.setRawCartDataInStorage(null);

        axios
          .mockResolvedValueOnce({data, status: 200});

        await getHomeDeliveryShippingMethods({ country_id: 'EG' });
        // Call the function for the second time to test the static data.
        let result = await getHomeDeliveryShippingMethods({ country_id: 'EG' });
        expect(axios).toBeCalledTimes(1);
        expect(result.length).toEqual(1);
      });

      it('With country_id for getHomeDeliveryShippingMethods', async () => {
        const data = homeDeliveryShippingMethods;
        window.commerceBackend.setRawCartDataInStorage(null);

        axios
          .mockResolvedValueOnce({data, status: 200});

        let result = await getHomeDeliveryShippingMethods({ country_id: 'EG' });
        expect(result.length).toEqual(1);
        expect(result[0].carrier_code).toEqual('alshayadelivery');
        expect(result[0].carrier_title).toEqual('Standard Delivery');
      });

      it('With null for getHomeDeliveryShippingMethods when country_id not provided', async () => {
        const data = null;
        window.commerceBackend.setRawCartDataInStorage(null);

        axios
          .mockResolvedValueOnce({data, status: 200});

        let result = await getHomeDeliveryShippingMethods({});
        expect(result).toEqual([]);
      });
    });
  });
});
