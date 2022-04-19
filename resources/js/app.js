/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue').default;

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

// Vue.component('example-component', require('./components/ExampleComponent.vue').default);
Vue.component('chats', require('./components/ChatsComponent.vue').default);
Vue.component('profile-edit-form', require('./components/SampleProfileEditForm.vue').default);

/** Loading any external Vue plugins */
import VueChatScroll from 'vue-chat-scroll'
Vue.use(VueChatScroll)

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
});


function toggleheaderleft() {
    
    var origHeader = document.getElementById('orig');
    var toBar = document.getElementById('toBar');
    
    var displaySetting = origHeader.style.display;

    var button = document.getElementById('headerToggle').addEventListener("click", hello);;

    
    if (displaySetting == 'flex') {
      
      origHeader.style.display = 'none';
      toBar.style.display='flex';
    }
    else {
      origHeader.style.display = 'flex';
      toBar.style.display='none';
      
    }
  }