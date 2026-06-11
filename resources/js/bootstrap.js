// Load plugins
import helper from "./helper";
import axios from "axios";
import * as Popper from "@popperjs/core";
import dom from "@left4code/tw-starter/dist/js/dom";

// Set plugins globally
window.helper = helper;
window.axios = axios;
window.Popper = Popper;
window.$ = dom;

// CSRF token
let token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common["X-CSRF-TOKEN"] = token.content;
} else {
    console.error(
        "CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token"
    );
}

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';
