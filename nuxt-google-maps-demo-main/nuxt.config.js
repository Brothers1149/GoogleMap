export default {
  ssr: false,
  target: "static",
  head: {
    title: "nuxt-map-demo",
    htmlAttrs: {
      lang: "en",
    },
    meta: [
      { charset: "utf-8" },
      { name: "viewport", content: "width=device-width, initial-scale=1" },
      { hid: "description", name: "description", content: "" },
      { name: "format-detection", content: "telephone=no" },
    ],
    link: [{ rel: "icon", type: "image/x-icon", href: "/favicon.ico" }],
  },
  css: [
		'~/assets/scss/style.scss',
  ],
  plugins: [
    { src: "~/plugins/sweetalert.js" },
    { src: "~/plugins/google-maps.js" },
		{ src: '~/plugins/bootstrap.client.js'},
		{ src: '~/plugins/lazysizes.client.js'},
		{ src: '~/plugins/loading-overlay.js'}
  ],
  components: true,
  buildModules: [],
  modules: ['@nuxtjs/axios'],
  build: {}
};
