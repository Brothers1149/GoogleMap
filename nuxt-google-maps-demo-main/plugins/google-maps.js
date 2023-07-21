import Vue from "vue";
import * as VueGoogleMaps from "vue2-google-maps";

Vue.config.productionTip = false;
Vue.use(VueGoogleMaps, {
  load: {
    key: 'AIzaSyCZi9MB9rv5iXekRpDfNkJilgNmyFvUhb0',
    libraries: "places",
    region: 'TH',
    language: 'TH',
  },
});
