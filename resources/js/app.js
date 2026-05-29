import { createApp } from 'vue';
import App from './App.vue';
import '../css/app.css';

const mountPoint = document.getElementById('app');

if (mountPoint) {
	createApp(App).mount(mountPoint);
}
