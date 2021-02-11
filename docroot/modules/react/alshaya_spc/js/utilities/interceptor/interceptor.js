import axios from 'axios';

// Axios response interceptor.
axios.interceptors.response.use((response) => {
  // If error code is 503, assuming site is offline.
  if (response.data.error === true
    && parseInt(response.data.error_code, 10) === 503) {
    // Redirect to home page.
    window.location.href = Drupal.url('/?maintenance-on=1');
    return false;
  }

  return response;
}, (error) => Promise.reject(error));
