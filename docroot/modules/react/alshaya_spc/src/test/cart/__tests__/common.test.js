jest.mock('axios');
import axios from 'axios';
import each from 'jest-each'
import utilsRewire, { updateCart } from '../../../../js/backend/v2/common';
import { drupalSettings } from '../globals';
import * as cartData from '../data/cart.json';
import _ from 'lodash';

describe('Common', () => {
  describe('Functions from common.js', () => {

    beforeEach(() => {
      window.drupalSettings = drupalSettings;
    });

    afterEach(() => {
      jest.clearAllMocks();
    });

    describe('Test formatCart()', () => {
      const formatCart = utilsRewire.__get__('formatCart');

      it('With Cart data', () => {
        const data = _.cloneDeep(cartData);
        const result = formatCart(data);
        expect(result.cart.customer).toEqual(undefined);
        expect(result.customer.addresses[0].region).toEqual('0');
        expect(result.customer.addresses[0].id).toEqual(undefined);
        expect(result.customer.addresses[0].customer_address_id).toEqual('69');
        expect(result.customer.addresses[0].street).toEqual(['1 London Rd']);
        expect(result.customer.addresses[1].street).toEqual(['17 crewdosn rd']);
        expect(result.shipping.method).toEqual('home_delivery');
        expect(result.shipping.type).toEqual('home_delivery');
        expect(result.shipping.clickCollectType).toEqual('home_delivery');
        expect(result.shipping.extension_attributes).toEqual(undefined);
        expect(result.cart.extension_attributes.shipping_assignments).toEqual(undefined);
        expect(result.payment).toEqual({});
      });

      it('Without customer address', () => {
        const data = _.cloneDeep(cartData);
        delete data.cart.customer.addresses;
        const result = formatCart(data);
        expect(result.customer.addresses).toEqual(undefined);
      });

      it('Without customer data', () => {
        const data = _.cloneDeep(cartData);
        delete data.cart.customer;
        const result = formatCart(data);
        expect(result.customer).toEqual(undefined);
      });

      it('Without extension_attributes data', () => {
        const data = _.cloneDeep(cartData);
        delete data.cart.extension_attributes;
        const result = formatCart(data);
        expect(result.shipping).toEqual({});
      });

      it('Without shipping data', () => {
        const data = _.cloneDeep(cartData);
        delete data.cart.extension_attributes.shipping_assignments[0].shipping;
        const result = formatCart(data);
        expect(result.shipping).toEqual({});
      });

      it('Custom shipping method', () => {
        const data = _.cloneDeep(cartData);
        data.cart.extension_attributes.shipping_assignments[0].shipping.method = 'foo';
        const result = formatCart(data);
        expect(result.shipping.method).toEqual('foo');
        expect(result.shipping.type).toEqual('home_delivery');
      });

      it('Click and collect', () => {
        const data = _.cloneDeep(cartData);
        data.cart.extension_attributes.shipping_assignments[0].shipping.method = 'click_and_collect';
        const result = formatCart(data);
        expect(result.shipping.method).toEqual('click_and_collect');
        expect(result.shipping.type).toEqual('click_and_collect');
      });

      it('No shipping method', () => {
        const data = _.cloneDeep(cartData);
        data.cart.billing_address = { foo: 'bar' };
        delete data.cart.extension_attributes.shipping_assignments[0].shipping.method;
        const result = formatCart(data);
        expect(result.shipping).toEqual({});
        expect(result.cart.billing_address).toEqual({});
      });

      it('Store code', () => {
        const data = _.cloneDeep(cartData);
        data.cart.extension_attributes.shipping_assignments[0].shipping.extension_attributes.store_code = '1234';
        const result = formatCart(data);
        expect(result.shipping.storeCode).toEqual('1234');
      });
    });

    describe('Test getCartCustomerId()', () => {
      const getCartCustomerId = utilsRewire.__get__('getCartCustomerId');

      beforeEach(async () => {
        jest
          .spyOn(window.commerceBackend, 'getCartId')
          .mockImplementation(() => '111');
      });

      each`
       input                                                    | expectedResult
       ${''}                                                    | ${null}
       ${{}}                                                    | ${null}
       ${{ cart: 'foo' }}                                       | ${null}
       ${{ cart: { customer: 'foo' }}}                          | ${null}
       ${{ cart: { customer: { id: 'foo' }}}}                   | ${'foo'}
       ${{ cart: { customer: { id: 1234 }}}}                    | ${1234}
     `.test('Test that getCartCustomerId($input) returns "$expectedResult"', async ({ input, expectedResult }) => {
        axios.mockResolvedValue({
          data: input,
          status: 200,
        });

        const result = await getCartCustomerId();
        expect(axios).toHaveBeenCalled();
        expect(result).toEqual(expectedResult);
        // jest.clearAllMocks();
      });
    });

    describe('Test validateRequestData()', () => {
      const validateRequestData = utilsRewire.__get__('validateRequestData');

      each`
       input                                                            | expectedResult
       ${null}                                                          | ${500}
       ${{}}                                                            | ${500}
       ${{ cart_id: 555, action: 'foo' }}                               | ${200}
       ${{ cart_id: 555, action: 'add item' }}                          | ${400}
       ${{ cart_id: 555, action: 'add item', sku: 1, quantity: 1 }}     | ${200}
       ${{ cart_id: 555, action: 'add item', sku: '1', quantity: 1 }}   | ${200}
       ${{ cart_id: null, action: 'add item', sku: 1, quantity: 1 }}    | ${200}
       ${{ cart_id: 555, action: 'remove item' }}                       | ${400}
       ${{ cart_id: 555, action: 'remove item', sku: '1' }}             | ${200}
       ${{ cart_id: 555, action: 'remove item', sku: 1 }}               | ${200}
       ${{ cart_id: 555, action: 'remove item', sku: 1 }}               | ${200}
     `.test('Test that validateRequestData($input) returns "$expectedResult"', async ({ input, expectedResult }) => {
        axios.mockResolvedValue({
          data: {
            customer: {
              id: 987,
            },
            status: 200,
          },
        });

        jest
          .spyOn(window.commerceBackend, 'getCartId')
          .mockImplementation(() => input.cart_id);

        const result = await validateRequestData(input);
        expect(result).toBe(expectedResult);
        // jest.clearAllMocks();
      });

      it('With authenticated user but without customer Id', async () => {
        axios.mockResolvedValue({
          data: {},
          status: 200,
        });

        window.drupalSettings.user.uid = 1;

        const data = {
          cart_id: 555,
          action: 'add item',
          sku: 1,
          qty: 1,
        };

        const result = await validateRequestData(data);
        expect(result).toEqual(400);
      });

      it('With authenticated user and different customer Id', async () => {
        axios.mockResolvedValue({
          data: {
            customer: {
              id: 987,
            },
          },
          status: 200,
        });

        window.drupalSettings.user.uid = 1;
        window.drupalSettings.userDetails.customerId = 789;

        const data = {
          cart_id: 555,
          action: 'add item',
          sku: 1,
          qty: 1,
        };

        const result = await validateRequestData(data);
        expect(result).toEqual(400);
      });
    });
  });
});
