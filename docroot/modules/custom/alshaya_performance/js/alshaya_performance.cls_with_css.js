(function () {
  /**
   * All custom js for alshaya_performance.
   */

  const removeInitialLayout = () => {
    const initialLayoutElement = document.querySelector(
      "body.cls-initial-layout"
    );
    if (initialLayoutElement) {
      initialLayoutElement.classList.remove("cls-initial-layout");
    }
  };

  const getDelay = () => {
    const parsedUrl = new URL(window.location.href);
    const delay = parsedUrl.searchParams.get("delay");
    return parseInt(delay) || 0;
  };

  const loadEventHandler = () => {
    setTimeout(removeInitialLayout, getDelay());
  };

  window.addEventListener("load", loadEventHandler);

})();
