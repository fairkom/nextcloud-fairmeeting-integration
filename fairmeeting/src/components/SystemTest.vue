<template>
	<div>
		<div class="tol-system-check-title">
			{{ t("fairmeeting", "System check") }}
		</div>

		<div
			v-if="!permissionDenied && showProblems"
			class="tol-system-checks-permission-denied problems"
		>
			<div class="tol-system-checks-permission-denied__message-container">
				<CheckStatusIcon
					class="tol-system-checks-permission-denied__icon"
					status="warning"
				/>
				<div class="tol-system-checks-permission-denied__title">
					{{ t("fairmeeting", "Got issues?") }}
				</div>
			</div>
			<div class="tol-system-checks-timeout__message-container">
				<div class="mb">
					{{ t("fairmeeting", "Known problems / what you can try") }}
				</div>
				<ul>
					<li>
						{{
							t(
								"fairmeeting",
								"• Accept the microphone/camera access permissions at the top of the screen"
							)
						}}
					</li>
					<li>
						{{
							t(
								"fairmeeting",
								"• Check the microphone/camera access permissions by clicking the icon next to the address bar. Then reload the page."
							)
						}}
					</li>
					<li>
						{{
							t(
								"fairmeeting",
								"• If you have DroidCam: Connect to the mobile camera, then reload the page"
							)
						}}
					</li>
					<li>
						{{
							t(
								"fairmeeting",
								"• Try join using the fairmeeting app (follow the instructions at the bottom of the page)"
							)
						}}
					</li>
				</ul>
			</div>
		</div>

		<div
			v-if="!loading && !permissionDenied"
			class="tol-system-checks-container"
		>
			<div class="tol-system-check tol-system-check--first">
				<CameraTest
					:permission-denied="permissionDenied"
					:cameras="cameras"
					v-on="$listeners"
				/>
			</div>
			<div class="tol-system-check">
				<MicTest
					:permission-denied="permissionDenied"
					:microphones="microphones"
					v-on="$listeners"
				/>
				<hr class="tol-check-divider" />
				<SpeakerTest
					:permission-denied="permissionDenied"
					:browser="browser"
					:speakers="speakers"
					v-on="$listeners"
				/>
				<hr class="tol-check-divider" />
				<BrowserTest :browser="browser" />
			</div>
		</div>

		<div
			v-if="!loading && permissionDenied"
			class="tol-system-checks-permission-denied"
		>
			<div class="tol-system-checks-permission-denied__message-container">
				<CheckStatusIcon
					class="tol-system-checks-permission-denied__icon"
					status="error"
				/>
				<div class="tol-system-checks-permission-denied__title">
					{{ t("fairmeeting", "No camera/microphone access") }}
				</div>
			</div>
			<div class="tol-system-checks-permission-denied__message-container">
				{{
					t(
						"fairmeeting",
						"Review your browser camera and microphone access settings"
					)
				}}<br />
			</div>
			<div style="text-align: center">
				<ul style="display: inline-block; text-align: left">
					<li>
						<a
							href="https://support.mozilla.org/kb/how-manage-your-camera-and-microphone-permissions"
							target="_blank"
						>
							•
							{{
								t("fairmeeting", "Click here for {browser} instructions", {
									browser: "Firefox",
								})
							}}
							↗
						</a>
					</li>
					<li>
						<a
							href="https://support.google.com/chrome/answer/2693767"
							target="_blank"
						>
							•
							{{
								t("fairmeeting", "Click here for {browser} instructions", {
									browser: "Chromium/Chrome",
								})
							}}
							↗
						</a>
					</li>
					<li>
						<a
							href="https://support.microsoft.com/windows/windows-camera-microphone-and-privacy-a83257bc-e990-d54a-d212-b5e41beba857"
							target="_blank"
						>
							•
							{{
								t("fairmeeting", "Click here for {browser} instructions", {
									browser: "Edge",
								})
							}}
							↗
						</a>
					</li>
					<li>
						<a
							href="https://support.apple.com/guide/safari/ibrwe2159f50/mac"
							target="_blank"
						>
							•
							{{
								t("fairmeeting", "Click here for {browser} instructions", {
									browser: "Safari",
								})
							}}
							↗
						</a>
					</li>
				</ul>
			</div>
			<div v-if="$root.helpLink">
				<div class="tol-system-checks-permission-denied__title2">
					{{ t("fairmeeting", "Does not work?") }}
				</div>
				<a
					:href="$root.helpLink + '#permissions'"
					class="tol-system-checks-permission-denied__button button secondary"
				>
					{{ t("fairmeeting", "Click here for troubleshooting help") }}
				</a>
			</div>
		</div>
	</div>
</template>

<script>
import Bowser from "bowser";
import BrowserTest from "./BrowserTest";
import CameraTest from "./CameraTest";
import MicTest from "./MicTest";
import SpeakerTest from "./SpeakerTest";
import CheckStatusIcon from "./CheckStatusIcon";
import { generateFilePath } from "@nextcloud/router";

/**
 * @param constraints
 * @param vm
 * @param notFoundCallback
 */
async function askPermissions(constraints, vm, notFoundCallback) {
	try {
		const stream = await navigator.mediaDevices.getUserMedia(constraints);
		stream.getTracks().forEach((track) => {
			track.stop();
		});
	} catch (err) {
		// eslint-disable-next-line
		console.log(`[fairmeeting] getUserMedia() error.name: ${err.name}`);
		// eslint-disable-next-line
		console.log(`[fairmeeting] getUserMedia() error.message: ${err.message}`);

		if (err.name === "NotFoundError" && notFoundCallback) {
			await notFoundCallback();
		}

		if (err.name === "NotAllowedError") {
			vm.permissionDenied = true;
			// eslint-disable-next-line
			vm.$root.$emit("fairmeeting.device_permission_denied");
		}
	}
}

export default {
	name: "SystemTest",
	components: {
		CheckStatusIcon,
		SpeakerTest,
		MicTest,
		CameraTest,
		BrowserTest,
	},
	data() {
		return {
			browser: undefined,
			selectedCamera: "",
			selectedMicrophone: "",
			cameras: [],
			microphones: [],
			speakers: [],
			loading: true,
			permissionDenied: false,
			timeout: false,
			deviceWithoutLabel: false,
			showProblems: false,
			showProblemsTimeoutHandle: null,
		};
	},
	computed: {
		allowSrc() {
			return generateFilePath("fairmeeting", "img", "allow.png");
		},
		problems() {
			return this.timeout || this.deviceWithoutLabel;
		},
	},
	watch: {
		problems(val) {
			this.clearTimeout(this.showProblemsTimeoutHandle);

			if (val) {
				this.showProblemsTimeoutHandle = setTimeout(() => {
					this.showProblems = true;
				}, 1000);
			} else {
				this.showProblems = false;
			}
		},
	},
	async created() {
		this.browser = Bowser.getParser(window.navigator.userAgent);

		const permissionsTimeoutHandle = setTimeout(() => {
			// eslint-disable-next-line
			console.log("[fairmeeting] system test timeout reached");
			this.problems = true;
		}, 2500);

		// eslint-disable-next-line
		console.log("[fairmeeting] before askPermissions");
		this.askPermissions();
		// eslint-disable-next-line
		console.log("[fairmeeting] after askPermissions");

		this.clearTimeout(permissionsTimeoutHandle);

		try {
			// eslint-disable-next-line
			console.log("[fairmeeting] before queryDevices");
			await this.queryDevices();
			// eslint-disable-next-line
			console.log("[fairmeeting] after queryDevices");
		} catch (err) {
			// eslint-disable-next-line
			console.log("Error detecting devices");
			// eslint-disable-next-line
			console.log(err);
		}

		// eslint-disable-next-line
		this.$root.$emit("fairmeeting.system_test_done");
		this.loading = false;
		this.timeout = false;

		this.$root.$on("tol-refresh-devices", () => {
			// console.log('[fairmeeting] tol-refresh-devices')
			this.queryDevices();
		});
	},
	methods: {
		clearTimeout(handle) {
			try {
				clearTimeout(handle);
			} catch (ignore) {}
		},
		async askPermissions() {
			await askPermissions({ audio: true, video: true }, this, async () =>
				askPermissions({ audio: true }, this)
			);
		},
		async queryDevices() {
			const devices = await navigator.mediaDevices.enumerateDevices();
			const cameras = [];
			const microphones = [];
			const speakers = [];
			let deviceWithoutLabel = false;

			devices.forEach((device) => {
				// eslint-disable-next-line
				console.log("[fairmeeting] device");
				// eslint-disable-next-line
				console.log(device);

				if (!device.label) {
					deviceWithoutLabel = true;
				}

				if (device.kind === "videoinput") {
					cameras.push(device);
				}

				if (
					device.kind === "audioinput" &&
					device.label.toLocaleLowerCase().includes("monitor of") === false
				) {
					microphones.push(device);
				}

				if (device.kind === "audiooutput") {
					speakers.push(device);
				}
			});

			this.deviceWithoutLabel = deviceWithoutLabel;
			this.cameras = cameras;
			this.microphones = microphones;
			this.speakers = speakers;
		},
	},
};
</script>

<style scoped>
.tol-system-check-title {
	font-size: 18px;
	margin-bottom: 16px;
	text-align: center;
}

.tol-system-checks-container {
	display: flex;
	flex-direction: column;
}

.tol-system-check {
	border: 1px solid var(--color-border);
	padding: 16px;
	width: 100%;
}

.tol-system-check--first {
	margin-bottom: 16px;
	margin-right: 0;
}

.tol-system-checks-permission-denied {
	border: 1px solid var(--color-border);
	display: flex;
	flex-direction: column;
	margin-left: auto;
	margin-right: auto;
	padding: 16px;
}

.tol-system-checks-permission-denied__message-container {
	align-items: center;
	display: flex;
	margin-bottom: 16px;
	margin-left: auto;
	margin-right: auto;
}

.tol-system-checks-timeout__message-container {
	align-items: center;
	display: flex;
	flex-direction: column;
}

.tol-system-checks-permission-denied__title {
	font-size: 18px;
	font-weight: bold;
	line-height: 1.2;
}

.tol-system-checks-permission-denied__title2 {
	color: var(--color-text-lighter);
	font-size: 24px;
	font-weight: bold;
	line-height: 1.2;
	margin-bottom: 8px;
	text-align: center;
}

.tol-system-checks-permission-denied__icon {
	flex-shrink: 0;
	margin-right: 8px;
}

.tol-system-checks-permission-denied__allow-img {
	margin-bottom: 8px;
	max-width: 100%;
}

.tol-system-checks-permission-denied__button {
	display: block;
	margin-left: auto;
	margin-right: auto;
}

@media only screen and (min-width: 768px) {
	.tol-system-checks-container {
		flex-direction: row;
	}

	.tol-system-check {
		width: 50%;
	}

	.tol-system-check--first {
		margin-bottom: 0;
		margin-right: 16px;
	}
}

.mb {
	margin-bottom: 8px;
}

.problems {
	margin-bottom: 32px;
}
</style>
