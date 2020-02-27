export const createFetcher = promiseFunc => {
  return {
    read: arg => {
      try {
        return promiseFunc(arg)
          .then(response => {
            if (!response) {
              return {error: 'error!'};
            }
            if (typeof response.data !== 'object') {
              return {error: 'error!'};
            }

            if (!response.data.error && response.data.error) {
              console.error(cart_result.error_message);
              return {error: 'error!'};
            }
            return response.data;
          },
          reject => {
            return {error: reject};
          });
      }
      catch (error) {
        return new Promise(
          resolve => resolve({error: error})
        );
      }
    }
  }
}
