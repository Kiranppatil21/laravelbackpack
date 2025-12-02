import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
// Ensure cookies (session/XSRF) are sent with requests when using Vite dev server
// or cross-origin requests during development.
window.axios.defaults.withCredentials = true;
// Ensure axios uses Laravel's default XSRF cookie/header names so it will
// automatically read the XSRF-TOKEN cookie and send X-XSRF-TOKEN on requests.
window.axios.defaults.xsrfCookieName = 'XSRF-TOKEN';
window.axios.defaults.xsrfHeaderName = 'X-XSRF-TOKEN';
