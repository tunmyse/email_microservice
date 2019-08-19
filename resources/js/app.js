import 'bootstrap'
import 'bootstrap/dist/css/bootstrap.min.css'
import Vue from 'vue'
import VueRouter from 'vue-router'
import './bootstrap'

Vue.use(VueRouter)

import AppComponent from './components/AppComponent'
import SendEmailComponent from './components/SendEmailComponent'
import EmailStatusComponent from './components/EmailStatusComponent'
import EmailListComponent from './components/EmailListComponent'

const router = new VueRouter({
    mode: 'history',
    routes: [
        {
            path: '/sendemail',
            name: 'send',
            component: SendEmailComponent,
            props: true
        },
        {
            path: '/email',
            name: 'status',
            component: EmailStatusComponent, 
            props: true 
        },
        {
            path: '/',
            name: 'list',
            component: EmailListComponent
        }
    ],
});

const app = new Vue({
    el: '#app',
    components: { AppComponent },
    router
});