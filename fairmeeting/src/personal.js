import Vue from 'vue'
import Personal from './Personal.vue'
import AppGlobal from './mixins/AppGlobal'

/**
 * @param fn
 */
function ready(fn) {
	if (document.readyState !== 'loading') {
		fn()
	} else {
		document.addEventListener('DOMContentLoaded', fn)
	}
}

Vue.mixin(AppGlobal)

ready(() => {
	// eslint-disable-next-line
	new Vue({
		el: '#fairmeeting-personal',
		render: (h) => h(Personal),
	})
})
