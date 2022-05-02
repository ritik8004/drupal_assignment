/* eslint-disable */

/**
 * Utility function to process return data.
 */
function processReturnData(returns) {
  const allReturns = [];

  returns.forEach((returnItem) => {
    let itemsData = [];
    returnItem.items.forEach((item) => {
      const productDetails = drupalSettings.onlineReturns.products.find((element) => {
        return element.item_id === item.order_item_id;
      });

      itemsData.push(productDetails);
    });

    const returnData = {
      returnInfo: returnItem,
      items: itemsData,
    };

    allReturns.push(returnData);
  });

  return allReturns;
}

export {
  processReturnData,
};
