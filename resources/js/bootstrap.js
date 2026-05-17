import axios from 'axios';
window.Axios = axios;

window.Axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
