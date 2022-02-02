function rafAsync() {
  return new Promise((resolve) => {
    requestAnimationFrame(resolve);
  });
}

function WaitForElement(selector) {
  if (document.querySelector(selector) === null) {
    return rafAsync().then(() => WaitForElement(selector));
  }

  return Promise.resolve(true);
}

export default WaitForElement;
