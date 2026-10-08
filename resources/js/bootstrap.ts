import axios from 'axios';

/**
 * Browser wiring: axios on the window. It does not exist on the server and
 * throws there — the SSR renderer imports this module exactly like the
 * browser does.
 */
if (typeof window !== 'undefined') {
    window.axios = axios;
    window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
}
