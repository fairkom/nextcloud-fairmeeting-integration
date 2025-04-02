import Vue from "vue";
import Room from "./Room.vue";
import AppGlobal from "./mixins/AppGlobal";
import VueClipboard from "vue-clipboard2";

Vue.use(VueClipboard);

/**
 * @param fn
 */
function ready(fn) {
	if (document.readyState !== "loading") {
		fn();
	} else {
		document.addEventListener("DOMContentLoaded", fn);
	}
}

Vue.mixin(AppGlobal);

ready(() => {
	// eslint-disable-next-line
	new Vue({
		el: "#fairmeeting",
		render: (h) => h(Room),
	});
});
