<template>
	<div>
		<div class="tol-check">
			<div class="check-result-icon-container">
				<CheckStatusIcon :status="status" />
			</div>
			<div class="tol-check-right">
				<div class="tol-check-title-row">
					<div class="tol-check-title">
						{{ t("fairmeeting", "Browser") }}
						<span v-if="isNonOptimalBrowser">
							{{ t("fairmeeting", "non-optimal") }}</span
						>
						<span v-if="isNotWorkingBrowser">
							{{ t("fairmeeting", "not supported") }}</span
						>
					</div>
				</div>
				<div class="tol-check-text">
					{{ deviceType }}: {{ browserName }} ({{ browserVersion }})
				</div>
				<div v-if="isNonOptimalBrowser" class="tol-check-text">
					{{ t('fairmeeting', 'Audio and video quality could be poor.<br />
					It is recommended to use a recent
					<b>Firefox/Chrome/Chromium</b> version.') }}
				</div>
				<a
					v-if="$root.helpLink && status !== 'pending' && status !== 'ok'"
					class="button tol-check-help-button"
					:href="$root.helpLink + '#browser'"
				>
					<img class="tol-check-help-button__icon" :src="infoSrc" />
					<div>{{ t("fairmeeting", "Help") }}</div>
				</a>
			</div>
		</div>
	</div>
</template>

<script>
import CheckStatusIcon from "./CheckStatusIcon";

const OPTIMAL_BROWSERS = {
	chrome: ">=78",
	edge: ">=79",
	firefox: ">=78",
	safari: ">=10",
};

const NON_OPTIMAL_BROWSERS = {};

const NOT_WORKING_BROWSERS = {
	"internet explorer": "*",
};

export default {
	name: "BrowserTest",
	components: { CheckStatusIcon },
	props: {
		browser: {
			type: Object,
			required: true,
		},
	},
	data() {
		return {
			deviceType: "",
			browserName: "",
			browserVersion: "",
			isOptimalBrowser: false,
			isNonOptimalBrowser: false,
			isNotWorkingBrowser: false,
			status: "pending",
		};
	},
	computed: {
		infoSrc() {
			return this.link("/svg/core/actions/info?color=000");
		},
	},
	async created() {
		const deviceType = this.browser.getPlatformType();
		this.deviceType = deviceType.charAt(0).toUpperCase() + deviceType.slice(1);
		this.browserName = this.browser.getBrowserName();
		this.browserVersion = this.browser.getBrowserVersion();

		this.isOptimalBrowser = this.browser.satisfies(OPTIMAL_BROWSERS);
		this.isNotWorkingBrowser = this.browser.satisfies(NOT_WORKING_BROWSERS);
		this.isNonOptimalBrowser = this.browser.satisfies(NON_OPTIMAL_BROWSERS);

		if (this.isOptimalBrowser) {
			this.status = "ok";
			return;
		}

		if (this.isNotWorkingBrowser) {
			this.status = "error";
			return;
		}

		this.status = "warning";

		this.$root.$emit("tol-browser-status", this.status);
	},
};
</script>

<style scoped>
@import "../css/check.css";
</style>
