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

  window.addEventListener("load", removeInitialLayout);

})();
