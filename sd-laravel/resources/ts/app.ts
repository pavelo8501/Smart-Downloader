
import './bootstrap';
import '../css/app.css';

import { createApp } from 'vue';

import Panel from "./DownloadPanel/Panel.vue";


const applicationFormApp = createApp(Panel);
applicationFormApp.mount("#app");


// import App from "./SlidingContainers/App.vue";

// import ApplicationFormApp from "./ApplicationForm/ApplFApp.vue";

// import { createI18n } from "vue-i18n";
// import lv from "./../js/locales/lv.json"
// import ru from "./../js/locales/ru.json"
// import en from "./../js/locales/en.json"

// import {
//     NDatePicker,
//     NInput,
//     NSelect,
//     NSpin,
//     create
//   } from 'naive-ui'

// const naive = create({
//     components: [
//         NDatePicker,
//         NSpin,
//         NSelect,
//         NInput
//     ]
// });

// const locale = (window as any).appLocale;
// console.log(locale);

// const i18n = createI18n({
//     locale: locale,
//     fallbackLocale: "lv",
//     messages: { lv, ru, en },
// });

// if (document.getElementById('applicationformapp')) {
//     const applicationFormApp = createApp(ApplicationFormApp);
//     applicationFormApp.use(naive);
//     applicationFormApp.use(i18n);
//     applicationFormApp.mount("#applicationformapp");
// }


// if (document.getElementById('slidingapp')) {
//     const slidinfApp = createApp(App);
//     slidinfApp.use(i18n);
//     slidinfApp.mount("#slidingapp");
// }
