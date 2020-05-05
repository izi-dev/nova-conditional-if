Nova.booting((Vue, router, store) => {
    Vue.component('form-panel', require('./components/form-panel-custom'))
    Vue.component('panel', require('./components/panel-custom'))
})
