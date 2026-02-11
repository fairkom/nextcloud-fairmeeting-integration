<template>
	<div class="app-content">
		<Breadcrumbs v-if="user">
			<Breadcrumb :disable-drop="true" title="Home" :href="appHomeUrl" />
			<Breadcrumb :disable-drop="true" :title="room ? room.name : '?'" />
		</Breadcrumbs>

		<div v-if="ready" class="app-body">
			<div
				v-if="room"
				class="room"
				:style="{ 'padding-top': user ? '16px' : '64px' }">
				<div class="room__sub-title">
					{{ t("fairmeeting", "Conference") }}
				</div>
				<h1 class="room__title">
					{{ room.name }}
				</h1>

				<div v-if="openInNewTab === '1'" class="room__join-browser-section">
					<div v-if="!user" class="room__username">
						<label class="room__username-label">
							{{ t("fairmeeting", "Your name:") }}</label><br>
						<input
							v-model="userName"
							class="room__username-input"
							type="text"
							maxlength="20">
					</div>

					<!-- Room-specific Jitsi configuration for new tab mode - BEFORE join button -->
					<div v-if="isCreator" class="room__options">
						<div class="room__options-title">
							{{ t("fairmeeting", "Room settings") }}
						</div>
						<label class="room__option">
							<input
								v-model="allStartWithAudioMuted"
								class="room__option__checkbox"
								type="checkbox">
							{{ t("fairmeeting", "All participants start with audio muted") }}
						</label>
						<label class="room__option">
							<input
								v-model="allStartWithVideoMuted"
								class="room__option__checkbox"
								type="checkbox">
							{{ t("fairmeeting", "All participants start with video muted") }}
						</label>
					</div>

					<button
						class="primary room__join-button--browser"
						:disabled="!ready || error || joining"
						@click="joinBrowser">
						{{ t("fairmeeting", "Join Meeting") }}
					</button>
				</div>

				<div v-else>
					<div class="room__join-browser-section">
						<div v-if="conferenceDone" class="room__done-info">
							{{ t("fairmeeting", "Conference left") }}
						</div>
						<div
							v-if="!systemOk"
							class="room__system-test-summary room__system-test-summary--warning">
							<div class="room__system-test-summary__title__row">
								<CloseThickIcon class="room__system-test-summary__icon" />
								<div class="room__system-test-summary__title">
									{{ t("fairmeeting", "Problems detected") }}
								</div>
							</div>
							<div class="room__system-test-summary__text">
								<ul class="tol-ul-icons">
									<li v-if="browserStatus === 'warning'">
										<b>{{ t("fairmeeting", "Your browser is non-optimal:") }}</b><br>
										<span
											v-html="
												t(
													'fairmeeting',
													'Audio and video quality could be poor. It is recommended to use a recent <b>Firefox/Chrome/Chromium</b> version.'
												)
											" />
									</li>
									<li v-if="browserStatus === 'error'">
										{{ t("fairmeeting", "Browser not supported") }}
									</li>
								</ul>
							</div>
							<div class="room__system-test-summary__actions">
								<a class="button secondary" href="#system-test">
									{{ t("fairmeeting", "Show system check") }}
								</a>
							</div>
						</div>
						<div v-if="!user" class="room__username">
							<label class="room__username-label">
								{{ t("fairmeeting", "Your name:") }}</label><br>
							<input
								v-model="userName"
								class="room__username-input"
								type="text"
								maxlength="20">
						</div>

						<div class="room__options">
							<label class="room__option">
								<input
									v-model="startMuted"
									class="room__option__checkbox"
									type="checkbox">
								{{ t("fairmeeting", "Start muted") }}
							</label>
							<label class="room__option">
								<input
									v-model="startCameraOff"
									class="room__option__checkbox"
									type="checkbox">
								{{ t("fairmeeting", "Start with camera off") }}
							</label>

							<!-- Room-specific Jitsi configuration - BEFORE join button -->
							<template v-if="isCreator">
								<div class="room__options-title">
									{{ t("fairmeeting", "Room settings") }}
								</div>
								<label class="room__option">
									<input
										v-model="allStartWithAudioMuted"
										class="room__option__checkbox"
										type="checkbox">
									{{ t("fairmeeting", "All participants start with audio muted") }}
								</label>
								<label class="room__option">
									<input
										v-model="allStartWithVideoMuted"
										class="room__option__checkbox"
										type="checkbox">
									{{ t("fairmeeting", "All participants start with video muted") }}
								</label>
							</template>
						</div>

						<button
							class="primary room__join-button--browser"
							:disabled="!systemTestDone || !ready || error || joining"
							@click="joinBrowser">
							{{ t("fairmeeting", "Click here to join") }}
						</button>
					</div>

					<SystemTest
						id="system-test"
						class="tol-system-test-section"
						@microphone-selected="onMicrophoneSelected"
						@camera-selected="onCameraSelected"
						@speaker-selected="onSpeakerSelected" />

					<template v-if="displayJoinUsingThefairmeetingApp">
						<div class="room__join-app-buttons-section">
							<div class="room__join-app-button-section">
								<button
									class="secondary room__join-button--app"
									:disabled="!systemTestDone || !ready || error || joining"
									@click="joinDesktopApp">
									{{ t("fairmeeting", "Join with desktop app") }}
								</button>
							</div>
							<div class="room__join-app-button-section">
								<button
									class="secondary room__join-button--app"
									:disabled="!systemTestDone || !ready || error || joining"
									@click="joinMobileApp">
									{{ t("fairmeeting", "Join with mobile app") }}
								</button>
							</div>
						</div>

						<div
							class="room__join-app-toggle"
							@click="showJoinApp = !showJoinApp">
							{{ t("fairmeeting", "App button not working?") }}
							<ChevronUpIcon
								class="room__join-app-toggle-icon"
								:class="{ 'room__join-app-toggle-icon--up': showJoinApp }" />
						</div>
						<div v-if="showJoinApp" class="room__join-app-section">
							<ol class="room__app-instructions">
								<li class="room__app-instructions-item">
									<a
										target="_blank"
										href="https://github.com/fairmeeting/fairmeeting-meet-electron#installation">
										{{ t("fairmeeting", "Download the desktop app here ↗") }}
									</a>
									<br>
									{{
										t(
											"fairmeeting",
											"The mobile app is available via the app store of your choice."
										)
									}}
									<br>
									{{
										t(
											"fairmeeting",
											"After successful installation try the button again."
										)
									}}
								</li>
								<li
									class="room__app-instructions-item"
									v-html="
										t(
											'fairmeeting',
											'Still not working? Copy the link below and paste it into the input field on the fairmeeting App start screen.'
										)
									" />
							</ol>
							<div class="room__join-link-container">
								<input
									class="room__join-link-input"
									:value="joinAppLink"
									readonly>
								<Actions ref="copyLinkActions">
									<ActionLink
										:href="joinAppLink"
										:icon="
											copied && copySuccess
												? 'icon-checkmark-color'
												: 'icon-clippy'
										"
										@click.stop.prevent="copyLink">
										{{ clipboardTooltip }}
									</ActionLink>
								</Actions>
							</div>
						</div>
					</template>
				</div>
			</div>
			<div
				ref="conferenceContainer"
				class="conference-container"
				:class="{ 'conference-container--running': conferenceRunning }" />
			<a ref="linkHelper" class="link-helper" :href="linkHelperUrl" />
		</div>
		<RoomNotFound v-else-if="roomNotFound" />
	</div>
</template>

<script>
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import Bowser from 'bowser'
import JitsiMeetExternalAPI from './external_api'
import ActionLink from '@nextcloud/vue/dist/Components/ActionLink'
import Actions from '@nextcloud/vue/dist/Components/Actions'
import Breadcrumb from '@nextcloud/vue/dist/Components/Breadcrumb'
import Breadcrumbs from '@nextcloud/vue/dist/Components/Breadcrumbs'
import SystemTest from './components/SystemTest'
import RoomNotFound from './components/RoomNotFound'
import ChevronUpIcon from 'vue-material-design-icons/ChevronUp.vue'
import CloseThickIcon from 'vue-material-design-icons/CloseThick.vue'

import 'vue-material-design-icons/styles.css'
import '../css/styles.css'

export default {
	name: 'Room',
	components: {
		ChevronUpIcon,
		CloseThickIcon,
		RoomNotFound,
		ActionLink,
		Actions,
		Breadcrumb,
		Breadcrumbs,
		SystemTest,
	},
	data() {
		return {
			joining: false,
			conferenceRunning: false,
			conferenceDone: false,
			room: null,
			user: null,
			userName: '',
			linkHelperUrl: '',
			joinAppLink: '',
			joinDesktopAppLink: '',
			copied: false,
			copySuccess: false,
			showJoinApp: false,
			// eslint-disable-next-line
			_startCameraOff: false,
			// eslint-disable-next-line
			_startMuted: false,
			// Room settings (local state)
			localAllStartAudioMuted: false,
			localAllStartVideoMuted: false,
			savingSettings: false,
			serverUrl: null,
			serverHost: null,
			selectedCamera: null,
			selectedMicrophone: null,
			selectedSpeaker: null,
			permissionDenied: false,
			browserStatus: null,
			displayJoinUsingThefairmeetingApp: true,
			displaySharingInviteLink: true,
			displayAllSharingInvites: true,
			ready: false,
			error: false,
			roomNotFound: false,
			browser: null,
			blinkBasedBrowser: false,
			systemTestDone: false,
		}
	},
	computed: {
		appHomeUrl() {
			return (
				window.location.protocol
				+ '//'
				+ window.location.host
				+ generateUrl('/apps/fairmeeting')
			)
		},
		clipboardTooltip() {
			if (this.copied) {
				return this.copySuccess
					? t('jfairmeeting', this.t('fairmeeting', 'Link copied'))
					: t(
						'jfairmeeting',
						this.t(
							'fairmeeting',
							'Cannot copy, please copy the link manually'
						)
					  )
			}
			return t('jfairmeeting', this.t('fairmeeting', 'Copy to clipboard'))
		},
		systemOk() {
			if (this.browserStatus && this.browserStatus !== 'ok') {
				return false
			}

			return true
		},
		startMuted: {
			get() {
				return this._startMuted
			},
			set(startMuted) {
				this._startMuted = startMuted
				localStorage.setItem('fairmeeting.startMuted', startMuted)
			},
		},
		startCameraOff: {
			get() {
				return this._startCameraOff
			},
			set(startCameraOff) {
				this._startCameraOff = startCameraOff
				localStorage.setItem('fairmeeting.startCameraOff', startCameraOff)
			},
		},
		allStartWithAudioMuted: {
			get() {
				return this.localAllStartAudioMuted
			},
			set(value) {
				this.localAllStartAudioMuted = value
				// Auto-save on change
				this.saveSettings()
			},
		},
		allStartWithVideoMuted: {
			get() {
				return this.localAllStartVideoMuted
			},
			set(value) {
				this.localAllStartVideoMuted = value
				// Auto-save on change
				this.saveSettings()
			},
		},
		isCreator() {
			return this.user && this.room && this.user.uid === this.room.creatorId
		},
		displayName() {
			return this.user ? this.user.displayName : this.userName
		},
	},
	async created() {
		this.browser = Bowser.getParser(window.navigator.userAgent)
		const engineName = this?.browser?.parsedResult?.engine?.name

		if (engineName && engineName.toLowerCase() === 'blink') {
			this.blinkBasedBrowser = true
		}

		this.startMuted = localStorage.getItem('fairmeeting.startMuted') === 'true'
		this.startCameraOff
			= localStorage.getItem('fairmeeting.startCameraOff') === 'true'

		if (this.openInNewTab !== '1') {
			this.$root.$on('fairmeeting.device_permission_denied', () => {
				this.permissionDenied = true
			})

			this.$root.$on('fairmeeting.system_test_done', () => {
				this.systemTestDone = true
			})

			this.$root.$on('tol-browser-status', (status) => {
				this.browserStatus = status
			})
		}

		const fairmeetingEle = document.getElementById('fairmeeting')
		this.serverUrl = fairmeetingEle.dataset.serverUrl
		this.$root.helpLink = fairmeetingEle.dataset.helpLink
		this.displayJoinUsingThefairmeetingApp
			= fairmeetingEle.dataset.displayJoinUsingThefairmeetingApp === 'true'
		this.displaySharingInviteLink
			= fairmeetingEle.dataset.displaySharingInviteLink === 'true'
		this.displayAllSharingInvites
			= fairmeetingEle.dataset.displayAllSharingInvites === 'true'
		const url = new URL(this.serverUrl)
		this.serverHost = url.host
		this.openInNewTab
			= fairmeetingEle.dataset.openInNewTab === 'true' ? '1' : '0'
		this.hasManualJwtToken
			= fairmeetingEle.dataset.hasManualJwtToken === 'true'
		this.jwtToken = this.hasManualJwtToken
			? fairmeetingEle.dataset.jwtToken
			: ''
		const userResponse = await axios.get(
			generateUrl('/apps/fairmeeting/api/user')
		)
		this.user = userResponse.data.user

		if (!this.user) {
			this.userName = localStorage.getItem('fairmeeting.userName')
		}

		try {
			const roomResponse = await axios.get(
				generateUrl(`/apps/fairmeeting/api/rooms/${this.extractRoomId()}`)
			)
			this.room = roomResponse.data

			// Load room settings from database into local state
			this.loadSettings()
		} catch (e) {
			if (e?.response?.status === 404) {
				this.roomNotFound = true
			} else {
				this.error = true
			}
			return
		}

		this.joinAppLink = `${this.serverUrl}${this.room.publicId}`
		this.joinDesktopAppLink = this.joinAppLink.replace(
			/^http[s]*/,
			'fairmeeting-meet'
		)

		const token = await this.issueToken()
		if (token !== null) {
			this.joinDesktopAppLink += `?jwt=${token}`
			this.joinAppLink += `?jwt=${token}`
		}
		if (this.openInNewTab === '1') {
			this.systemTestDone = true
		}

		this.ready = true
	},
	methods: {
		onCameraSelected(camera) {
			this.selectedCamera = camera
		},
		onMicrophoneSelected(microphone) {
			this.selectedMicrophone = microphone
		},
		onSpeakerSelected(speaker) {
			this.selectedSpeaker = speaker
		},
		async createJoinLink() {
			await this.copyLink()
			setTimeout(() => {
				this.joinAppLink = ''
			}, 60 * 1000)
		},
		async copyLink() {
			try {
				await this.$copyText(this.joinAppLink)
				// focus and show the tooltip
				this.$refs.copyLinkActions.$el.focus()
				this.copySuccess = true
				this.copied = true
			} catch (error) {
				this.copySuccess = false
				this.copied = true
			} finally {
				setTimeout(() => {
					this.copySuccess = false
					this.copied = false
				}, 4000)
			}
		},
		async joinDesktopApp() {
			// We use '_self' to trigger an app launch (the default '_blank' just opens
			// Jitsi in a new browser tab on some browsers).
			window.open(this.joinDesktopAppLink, '_self')
		},
		async joinMobileApp() {
			window.open(this.joinAppLink, '_self')
		},
		async buildMeetingUrl() {
			let url = `${this.serverUrl}${this.room.name}`
			const params = new URLSearchParams()

			const token = await this.issueToken()
			if (token !== null) {
				params.append('jwt', token)
			}

			if (this._startMuted) {
				params.append('config.startWithAudioMuted', 'true')
			}

			if (this._startCameraOff) {
				params.append('config.startWithVideoMuted', 'true')
			}

			params.append('userInfo.displayName', this.displayName)

			params.append('config.disableDeepLinking', 'true')

			if (this.displayAllSharingInvites) {
				params.append(
					'interfaceConfig.SHARING_FEATURES',
					'email,url,dial-in,embed'
				)
			} else if (this.displaySharingInviteLink) {
				params.append('interfaceConfig.SHARING_FEATURES', 'email')
			} else {
				params.append('interfaceConfig.SHARING_FEATURES', '')
			}

			params.append(
				'interfaceConfig.HIDE_INVITE_MORE_HEADER',
				this.displaySharingInviteLink ? 'false' : 'true'
			)
			params.append('interfaceConfig.MOBILE_APP_PROMO', 'false')

			// For new tab mode, we don't need to set these device parameters
			if (this.openInNewTab !== '1') {
				if (this.selectedCamera) {
					params.append('devices.videoInput', this.selectedCamera.label)
				}

				if (this.selectedMicrophone) {
					params.append('devices.audioInput', this.selectedMicrophone.label)
				}

				if (this.selectedSpeaker) {
					params.append('devices.audioOutput', this.selectedSpeaker.label)
				}
			}

			// Room-specific Jitsi configuration
			console.log('[fairmeeting] Building meeting URL with room settings:', {
				allStartAudioMuted: this.room?.allStartAudioMuted,
				allStartVideoMuted: this.room?.allStartVideoMuted,
			})

			if (this.room && this.room.allStartAudioMuted) {
				params.append('config.startWithAudioMuted', 'true')
			}

			if (this.room && this.room.allStartVideoMuted) {
				params.append('config.startWithVideoMuted', 'true')
			}

			const paramsString = params.toString()
			if (paramsString) {
				url += `?${paramsString}`
			}

			return url
		},
		async joinBrowser() {
			if (this.joining) {
				return
			}

			this.joining = true

			if (!this.user && this.userName) {
				localStorage.setItem('fairmeeting.userName', this.userName)
			}

			if (this.openInNewTab === '1') {
				const url = await this.buildMeetingUrl()
				window.open(url, '_blank')
				this.joining = false
				return
			}

			document.getElementById('header').style.display = 'none'

			await this.stopStreams()

			const token = await this.issueToken()

			// fairmeeting Specific: set HIDE_INVITE_MORE_HEADER and SHARING_FEATURE
			let features = [] // show nothing
			let inviteMoreHeader = false

			if (this.displaySharingInviteLink) {
				inviteMoreHeader = true
				features.push('email') // shows also nothing
			}

			if (this.displayAllSharingInvites) {
				features = ['email', 'url', 'dial-in', 'embed'] // show all link
			}

			const options = {
				parentNode: this.$refs.conferenceContainer,
				width: '100%',
				height: '100%',
				roomName: this.room.publicId,
				devices: {},
				userInfo: {
					displayName: this.displayName,
				},
				interfaceConfigOverwrite: {
					HIDE_INVITE_MORE_HEADER: inviteMoreHeader,
					MOBILE_APP_PROMO: false,
					SHARING_FEATURES: features,
				},
			}

			if (token !== null) {
				options.jwt = token
			}

			const configOverwrite = {
				subject: this.room.name,
				enableClosePage: false,
				disableDeepLinking: true,
				prejoinPageEnabled: false,
				prejoinConfig: {
					enabled: false,
				},
				disableInviteFunctions: true,
			}

			if (this._startMuted) {
				configOverwrite.startWithAudioMuted = true
			}

			if (this._startCameraOff) {
				configOverwrite.startWithVideoMuted = true
			}

			// Room-specific Jitsi configuration
			if (this.room && this.room.allStartAudioMuted) {
				configOverwrite.startWithAudioMuted = true
			}

			if (this.room && this.room.allStartVideoMuted) {
				configOverwrite.startWithVideoMuted = true
			}

			if (this.selectedCamera) {
				options.devices.videoInput = this.selectedCamera.label
			}

			if (this.selectedMicrophone) {
				options.devices.audioInput = this.selectedMicrophone.label
			}

			if (this.selectedSpeaker) {
				options.devices.audioOutput = this.selectedSpeaker.label
			}

			options.configOverwrite = configOverwrite

			// workaround
			// https://community.jitsi.org/t/error-after-update-google-chrome-on-jitsi-with-iframe/109972/8
			if (this.blinkBasedBrowser) {
				let frameUrl = null

				options.onload = (e) => {
					const frame = e.target

					if (frameUrl === null) {
						frameUrl = frame.src

						frame.src = generateUrl('/apps/fairmeeting/blank')
						setTimeout(() => {
							// eslint-disable-next-line
							console.log("[fairmeeting] reloading frame");
							frame.src = frameUrl
						}, 1000)
					}
				}
			}

			this.conferenceRunning = true
			const api = new JitsiMeetExternalAPI(this.serverHost, options)
			api.executeCommand('subject', this.room.name)

			if (this.user) {
				api.executeCommand('avatarUrl', this.user.avatarURL)
			}

			api.addEventListener('readyToClose', () => {
				this.joining = false
				api.dispose()
				this.conferenceRunning = false
				this.conferenceDone = true
				document.getElementById('header').style.display = ''
				this.$root.$emit('resume-preview')
			})
		},
		async stopStreams() {
			return new Promise((resolve) => {
				let micStopped = false
				let camStopped = false

				this.$root.$once('mic-stopped', () => {
					micStopped = true
					if (camStopped) {
						resolve()
					}
				})

				this.$root.$once('cam-stopped', () => {
					camStopped = true
					if (micStopped) {
						resolve()
					}
				})

				this.$root.$emit('stop-streams')
			})
		},
		async issueToken() {
			if (this.hasManualJwtToken && this.jwtToken) {
				return this.jwtToken
			}
			const data = {
				displayName: this.user ? this.user.displayName : this.userName,
			}
			const url = generateUrl(
				`/apps/fairmeeting/api/rooms/${this.room.publicId}/tokens`
			)
			try {
				const response = await axios.post(url, data)
				return response.data.token
			} catch (err) {
				return null
			}
		},
		extractRoomId() {
			const urlParts = window.location.href.split('/').slice(-3)

			if (urlParts[0] === 'rooms') {
				return urlParts[1]
			}

			return urlParts[2]
		},
		loadSettings() {
			if (!this.room) {
				return
			}

			// Load settings from database into local state
			console.log('[fairmeeting] Loading settings from room:', this.room)
			this.localAllStartAudioMuted = this.room.allStartAudioMuted || false
			this.localAllStartVideoMuted = this.room.allStartVideoMuted || false
		},
		async saveSettings() {
			if (!this.room || !this.isCreator || this.savingSettings) {
				return
			}

			this.savingSettings = true

			try {
				const updatedSettings = {
					publicId: this.room.publicId,
					allStartAudioMuted: this.localAllStartAudioMuted,
					allStartVideoMuted: this.localAllStartVideoMuted,
				}

				const response = await axios.put(
					generateUrl(`/apps/fairmeeting/api/rooms/${this.room.publicId}`),
					updatedSettings
				)

				// Update the room object with the response
				this.room = response.data
			} catch (error) {
				console.error('Failed to save room settings:', error)

				// Show error message
				if (window.OC && window.OC.Notification) {
					window.OC.Notification.showTemporary(
						this.t('fairmeeting', 'Failed to save room settings'),
						{ type: 'error' }
					)
				}
			} finally {
				this.savingSettings = false
			}
		},
	},
}
</script>

<style scoped>
.room {
	align-items: center;
	display: flex;
	flex-direction: column;
	padding: 48px 16px 100px;
}

.room__sub-title {
	font-size: 16px;
	margin-bottom: 8px;
	text-align: center;
}

.room__title {
	color: var(--color-text-lighter);
	font-size: 48px;
	line-height: 1.2;
	margin-bottom: 64px;
	text-align: center;
}

.room__username {
	margin: 0 0 16px;
}

.room__username-input {
	width: 250px;
}

.room__join-browser-section {
	align-items: center;
	display: flex;
	flex-direction: column;
	margin-bottom: 48px;
	width: 100%;
}

.room__join-button--app {
	font-size: 16px;
	margin: 0 auto 32px;
	padding: 16px 32px;
	width: 250px;
}

.room__join-app-buttons-section {
	display: inline-block;
}

.room__join-app-button-section {
	float: left;
	padding: 16px;
}

.room__join-browser-section {
	align-items: center;
	display: inline-flex;
	flex-direction: column;
	margin-bottom: 48px;
}

.room__join-app-section {
	padding-top: 32px;
}

.room__join-app-toggle {
	cursor: pointer;
}

.room__join-app-toggle-icon {
	position: relative;
	top: 1px;
}

.room__join-app-toggle-icon--up {
	top: 4px;
	transform: rotate(180deg);
}

.room__app-instructions {
	display: inline-block;
	text-align: left;
}

.room__app-instructions-item {
	list-style-type: decimal;
}

.room__join-link-container {
	align-items: center;
	display: flex;
}

.room__join-link-input {
	margin-right: 8px;
	width: 200px;
}

.app-content {
	width: 100%;
}

@media only screen and (min-width: 576px) {
	.app-content {
		margin-left: auto;
		margin-right: auto;
		max-width: 992px;
	}
}

.conference-container {
	bottom: 0;
	display: none;
	left: 0;
	position: fixed;
	top: 0;
	width: 100%;
	z-index: 2500;
}

.conference-container--running {
	display: block;
}

.link-helper {
	display: none;
}

.room__options {
	margin-bottom: 16px;
}

.room__options-title {
	font-weight: bold;
	font-size: 16px;
	margin-bottom: 12px;
	margin-top: 16px;
}

.room__option {
	align-items: center;
	display: flex;
	margin-bottom: 8px;
	user-select: none;
}

.room__option__checkbox {
	margin: 0 4px 0 0;
	min-height: auto;
}

.tol-system-test-section {
	margin-bottom: 32px;
	width: 100%;
}

.room__done-info {
	color: #059669;
	font-size: 24px;
	font-weight: bold;
	margin-bottom: 16px;
}

.room__system-test-summary {
	border-radius: 4px;
	margin-bottom: 16px;
	padding: 16px;
	max-width: 400px;
}

.room__system-test-summary--warning {
	background-color: #eca700;
}

.room__system-test-summary__title__row {
	align-items: center;
	display: flex;
	justify-content: start;
	margin-bottom: 8px;
}

.room__system-test-summary__icon {
	height: 32px;
	margin-right: 8px;
	width: 32px;
}

.room__system-test-summary__title {
	color: #222;
	font-size: 24px;
	font-weight: bold;
}

.room__system-test-summary__text {
	color: #222;
	margin-bottom: 16px;
}

.room__system-test-summary__actions {
	text-align: center;
}

.tol-ul-icons {
	padding-left: 24px;
}

.tol-ul-icons li {
	list-style-type: disc;
}
</style>
