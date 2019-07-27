
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');
// import VueRouter from 'vue-router'
// Vue.use(VueRouter)

import App from './components/App'
import iView from 'iview';
// import 'iview/dist/styles/iview.css';
Vue.use(iView)

import VuePlyr from 'vue-plyr'
import 'vue-plyr/dist/vue-plyr.css';
Vue.use(VuePlyr)

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('subscriptionupdate', require('./components/SubscriptionUpdate.vue'));
Vue.component('plyr', require('./components/Plyr.vue'));
Vue.component('plyrmp3', require('./components/Plyrmp3.vue'));
Vue.component('larecipe-back-to-top', require('./components/LarecipeBackToTop.vue'));

// import Routers from './router.js';
// import SubscriptionUpdate from './components/SubscriptionUpdate'
// // The routing configuration
// const RouterConfig = {
//     routes: Routers
// };
// const router = new VueRouter(RouterConfig);

// const router = new VueRouter({
//     mode: 'history',
//     routes: router,
// })
const app = new Vue({
    el: '#app',
    components: { App },
    // router,
});

(function($) {
    $(document).ready( function() {
        $('#fee').val(Math.floor((Math.random() * 10) + 1));
        $('button.wxpay_link').on('click',function(e){
            $('#fee').val($(this).data('value'));
            $('#submitForm').trigger('click');
        });

        $('#submitForm').on('click', function(e){
            if(!$('#fee').val()){
                alert('请选择或输入金额!');
                $('#fee').focus();
                event.preventDefault();
            }
            $("form").submit();
        });
    })
})( jQuery );
