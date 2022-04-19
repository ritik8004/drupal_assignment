/* eslint-disable */

/**
 * Utility function to process return data.
 */
function processReturnData(returns) {
  const allReturns = [];

  returns.forEach((returnItem) => {
    let itemsData = {};
    returnItem.items.forEach((item) => {
      itemsData = {
        entityId: item.entity_id,
      };
    });

    const returnData = {
      returnId: returnItem.increment_id,
      items: itemsData,
    };

    allReturns.push(returnData);
  });

  return allReturns;
}

export {
  processReturnData,
};
