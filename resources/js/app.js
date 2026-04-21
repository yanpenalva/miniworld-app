import App from '@/App.vue';
import router from '@/routes';
import { createPinia } from 'pinia';
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';
import { Dark, Dialog, Loading, LocalStorage, Notify, Platform, Quasar } from 'quasar';
import materialIcons from 'quasar/icon-set/material-symbols-outlined';
import langPTBR from 'quasar/lang/pt-BR';
import 'quasar/src/css/index.sass';
import { createApp } from 'vue';

Quasar.lang.set(Quasar.lang.ptBR);
Quasar.iconSet.set(materialIcons);
const pinia = createPinia();
pinia.use(piniaPluginPersistedstate);
const app = createApp(App);

app.use(pinia);
app.use(router);

app.use(Quasar, {
  lang: langPTBR,
  iconSet: materialIcons,
  config: {},
  plugins: {
    Notify,
    Loading,
    Dialog,
    Dark,
    Platform,
    LocalStorage,
  },
});

app.mount('#app');
