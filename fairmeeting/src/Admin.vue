<template>
	<div>
		<form @submit.prevent="submit">
			<fieldset :disabled="saving">
				<SettingsSection title="fairmeeting">
					<div v-if="loading">
						{{ t("fairmeeting", "Loading …") }}
					</div>
					<div v-if="!loading">
						<div class="group">
							<label for="fairmeeting_server_url" class="label">
								{{ t("fairmeeting", "Server URL (required)") }}
							</label>
							<div class="input-group">
								<input
									id="fairmeeting_server_url"
									v-model="serverUrl"
									class="input"
									type="text">
								<div v-if="serverUrlStatus" :class="`${serverUrlStatus}-text`">
									{{ serverUrlMessage }}
								</div>
							</div>
						</div>
						<div class="group">
							<label for="fairmeeting_help_link" class="label">
								{{ t("fairmeeting", "Help link (optional)") }}
							</label>
							<div class="input-group">
								<input
									id="fairmeeting_help_link"
									v-model="helpLink"
									class="input"
									type="text">
							</div>
						</div>

						<h3 class="section-heading">{{ t("fairmeeting", "Pro server (group-based routing)") }}</h3>

						<div class="group">
							<label for="fairmeeting_pro_server_url" class="label">
								{{ t("fairmeeting", "Pro Server URL (optional)") }}
							</label>
							<div class="input-group">
								<input
									id="fairmeeting_pro_server_url"
									v-model="proServerUrl"
									class="input"
									type="url"
									placeholder="https://pro.fairmeeting.net/">
								<div class="info-text">
									{{
										t(
											"fairmeeting",
											"Meetings created by users in the Nextcloud group below are routed to this server. Leave empty to route all meetings to the default server."
										)
									}}
								</div>
							</div>
						</div>

						<div class="group">
							<label for="fairmeeting_pro_group_name" class="label">
								{{ t("fairmeeting", "Pro Group Name") }}
							</label>
							<div class="input-group">
								<input
									id="fairmeeting_pro_group_name"
									v-model="proGroupName"
									class="input"
									type="text"
									placeholder="fairmeeting">
								<div class="info-text">
									{{
										t(
											"fairmeeting",
											"Nextcloud group whose members host on the pro server. Typically mirrored from a Keycloak group via OIDC group sync. Check is dynamic — losing or gaining membership applies on the next join."
										)
									}}
								</div>
							</div>
						</div>

						<div class="group">
							<label for="fairmeeting_pro_server_label" class="label">
								{{ t("fairmeeting", "Pro Server Badge Label") }}
							</label>
							<div class="input-group">
								<input
									id="fairmeeting_pro_server_label"
									v-model="proServerLabel"
									class="input"
									type="text"
									placeholder="pro">
								<div class="info-text">
									{{
										t(
											"fairmeeting",
											"Short tag shown next to the hostname in the room list when a room runs on the pro server (e.g. 'pro', 'premium', 'business'). Leave empty to hide the badge."
										)
									}}
								</div>
							</div>
						</div>

						<h3 class="section-heading">{{ t("fairmeeting", "Authentication (JSON Web Token)") }}</h3>

						<div class="group jwt-option">
							<label for="fairmeeting_jwt_token_service_url" class="label">
								{{ t("fairmeeting", "JWT Token Service URL (optional)") }}
							</label>
							<div class="input-group">
								<input
									id="fairmeeting_jwt_token_service_url"
									v-model="jwtTokenServiceUrl"
									class="input"
									type="url"
									placeholder="https://your-jitsi-token-service.example.com/jwt">
								<div class="info-text">
									{{
										t(
											"fairmeeting",
											"Endpoint where users can fetch a personal long-lived JWT. When set, an 'Open token service' button appears in each user's Personal settings → fairmeeting."
										)
									}}
								</div>
							</div>
						</div>

						<div class="group">
							<label for="fairmeeting_jwt_secret" class="label">
								{{ t("fairmeeting", "JWT Secret (optional)") }}
							</label>
							<div class="input-group">
								<input
									id="fairmeeting_jwt_secret"
									v-model="jwtSecret"
									class="input"
									type="text">
								<div class="info-text">
									{{
										t(
											"fairmeeting",
											"Used to generate JWT tokens automatically if no token is provided above."
										)
									}}
								</div>
							</div>
						</div>
						<div v-if="jwtSecret" class="group">
							<label for="fairmeeting_jwt_app_id" class="label">
								{{ t("fairmeeting", "JWT App ID") }}
							</label>
							<div class="input-group">
								<input
									id="fairmeeting_jwt_app_id"
									v-model="jwtAppId"
									class="input"
									type="text">
								<div v-if="jwtAppIdMessage" :class="`error-text`">
									{{ jwtAppIdMessage }}
								</div>
							</div>
						</div>
						<div v-if="jwtSecret" class="group">
							<label for="fairmeeting_jwt_audience" class="label">
								{{ t("fairmeeting", "JWT Audience (optional)") }}
							</label>
							<div class="input-group">
								<input
									id="fairmeeting_jwt_audience"
									v-model="jwtAudience"
									class="input"
									type="text">
							</div>
						</div>
						<div v-if="jwtSecret" class="group">
							<label for="fairmeeting_jwt_issuer" class="label">
								{{ t("fairmeeting", "JWT Issuer (optional)") }}
							</label>
							<div class="input-group">
								<input
									id="fairmeeting_jwt_issuer"
									v-model="jwtIssuer"
									class="input"
									type="text">
							</div>
						</div>
						<h3 class="section-heading">{{ t("fairmeeting", "Room defaults") }}</h3>
						<div class="group">
							<label for="room_name_prefix" class="label">
								{{ t("fairmeeting", "Room name prefix (optional)") }}
							</label>
							<div class="input-group">
								<input
									id="room_name_prefix"
									v-model="roomNamePrefix"
									class="input"
									type="text"
									:placeholder="t('fairmeeting', 'e.g. Company-')">
								<div class="info-text">
									{{
										t(
											"fairmeeting",
											"Optional prefix that will be automatically added to all room names."
										)
									}}
								</div>
							</div>
						</div>
						<div class="group">
							<label for="fairmeeting_open_in_new_tab" class="label">
								{{ t("fairmeeting", "Open in new tab") }}
							</label>
							<div class="input-group">
								<input
									id="fairmeeting_open_in_new_tab"
									v-model="openInNewTab"
									true-value="1"
									false-value="0"
									class="admin-checkbox"
									type="checkbox">
							</div>
						</div>
						<div class="group">
							<label for="display_join_using_the_fairmeeting_app" class="label">
								{{
									t("fairmeeting", 'Display "Join using the fairmeeting app"')
								}}
							</label>
							<div class="input-group">
								<input
									id="display_join_using_the_fairmeeting_app"
									v-model="displayJoinUsingThefairmeetingApp"
									true-value="1"
									false-value="0"
									class="admin-checkbox"
									type="checkbox">
							</div>
						</div>

						<h3 class="section-heading">{{ t("fairmeeting", "Invite & share") }}</h3>

						<div class="group">
							<label for="display_all_sharing_invites" class="label">
								{{ t("fairmeeting", "Show all sharing invites ") }}
							</label>
							<div class="input-group">
								<input
									id="display_all_sharing_invites"
									v-model="displayAllSharingInvites"
									true-value="1"
									false-value="0"
									class="admin-checkbox"
									type="checkbox">
							</div>
						</div>

						<h3 class="section-heading">{{ t("fairmeeting", "Calendar integration") }}</h3>

						<div class="group">
							<label for="calendar_integration_enabled" class="label">
								{{
									t(
										"fairmeeting",
										"Automatically add Server meeting links to calendar events"
									)
								}}
							</label>
							<div class="input-group">
								<input
									id="calendar_integration_enabled"
									v-model="calendarIntegrationEnabled"
									true-value="1"
									false-value="0"
									class="admin-checkbox"
									type="checkbox">
								<div class="info-text">
									{{
										t(
											"fairmeeting",
											"When enabled, video conference links will be automatically added to new calendar events that have attendees or are longer than the minimum duration."
										)
									}}
								</div>
							</div>
						</div>

						<div v-if="calendarIntegrationEnabled === '1' && calendarUseKeyword === '0'" class="group">
							<label for="calendar_minimum_duration" class="label">
								{{ t("fairmeeting", "Minimum event duration (minutes)") }}
							</label>
							<div class="input-group">
								<input
									id="calendar_minimum_duration"
									v-model="calendarMinimumDuration"
									class="input"
									type="number"
									min="1"
									max="480">
								<div class="info-text">
									{{
										t(
											"fairmeeting",
											"Events shorter than this duration will not automatically get Server meeting links (unless they have attendees)."
										)
									}}
								</div>
							</div>
						</div>

						<div v-if="calendarIntegrationEnabled === '1'" class="group">
							<label for="calendar_use_keyword" class="label">
								{{ t("fairmeeting", "Use keyword-based triggers") }}
							</label>
							<div class="input-group">
								<input
									id="calendar_use_keyword"
									v-model="calendarUseKeyword"
									true-value="1"
									false-value="0"
									class="admin-checkbox"
									type="checkbox">
								<div class="info-text">
									{{
										t(
											"fairmeeting",
											"When enabled, Server meeting links will only be added when events contain the specified keyword. When disabled, links are added based on duration and attendees."
										)
									}}
								</div>
							</div>
						</div>

						<div v-if="calendarIntegrationEnabled === '1' && calendarUseKeyword === '1'" class="group">
							<label for="calendar_keyword" class="label">
								{{ t("fairmeeting", "Trigger keyword") }}
							</label>
							<div class="input-group">
								<input
									id="calendar_keyword"
									v-model="calendarKeyword"
									class="input"
									type="text"
									:placeholder="t('fairmeeting', 'e.g. #fm, fairmeeting, online')">
								<div class="info-text">
									{{
										t(
											"fairmeeting",
											"When this keyword is found in the selected fields, it will be replaced with a Server meeting link."
										)
									}}
								</div>
							</div>
						</div>

						<div v-if="calendarIntegrationEnabled === '1' && calendarUseKeyword === '1'" class="group">
							<label class="label">
								{{ t("fairmeeting", "Replace keyword in") }}
							</label>
							<div class="input-group">
								<div class="keyword-checkboxes">
									<div class="checkbox-row">
										<input
											id="calendar_keyword_replace_location"
											v-model="calendarKeywordReplaceLocation"
											true-value="1"
											false-value="0"
											class="admin-checkbox"
											type="checkbox">
										<label for="calendar_keyword_replace_location" class="checkbox-label">
											{{ t("fairmeeting", "Location field") }}
										</label>
									</div>
									<div class="checkbox-row">
										<input
											id="calendar_keyword_replace_description"
											v-model="calendarKeywordReplaceDescription"
											true-value="1"
											false-value="0"
											class="admin-checkbox"
											type="checkbox">
										<label for="calendar_keyword_replace_description" class="checkbox-label">
											{{ t("fairmeeting", "Description field") }}
										</label>
									</div>
								</div>
								<div class="info-text">
									{{
										t(
											"fairmeeting",
											"Select where keywords should be replaced with Server meeting links. At least one option must be enabled."
										)
									}}
								</div>
							</div>
						</div>

						<div class="group">
							<label class="label">
								{{ t("fairmeeting", "Meeting URL defaults") }}
							</label>
							<div class="input-group">
								<div class="keyword-checkboxes">
									<div class="checkbox-row">
										<input
											id="meeting_skip_prejoin"
											v-model="meetingSkipPrejoin"
											true-value="1"
											false-value="0"
											class="admin-checkbox"
											type="checkbox">
										<label for="meeting_skip_prejoin" class="checkbox-label">
											{{ t("fairmeeting", "Skip prejoin page by default") }}
										</label>
									</div>
									<div class="checkbox-row">
										<input
											id="meeting_disable_deep_linking"
											v-model="meetingDisableDeepLinking"
											true-value="1"
											false-value="0"
											class="admin-checkbox"
											type="checkbox">
										<label for="meeting_disable_deep_linking" class="checkbox-label">
											{{ t("fairmeeting", "Disable mobile app prompt by default") }}
										</label>
									</div>
								</div>
								<div class="info-text">
									{{
										t(
											"fairmeeting",
											"Defaults for new rooms and calendar-generated meeting links. Users can override these per room on the meeting page."
										)
									}}
								</div>
							</div>
						</div>

						<div class="group group--centered">
							<button type="submit" class="primary" :disabled="saving">
								{{ t("fairmeeting", "save") }}
							</button>
							<span v-if="!saving && saved" class="msg success">
								{{ t("fairmeeting", "saved") }}
							</span>
							<span v-if="saving" class="msg">
								{{ t("fairmeeting", "Saving …") }}
							</span>
						</div>
					</div>
				</SettingsSection>
			</fieldset>
		</form>
	</div>
</template>

<script>
import SettingsSection from '@nextcloud/vue/dist/Components/SettingsSection'

export default {
	name: 'Admin',
	components: {
		SettingsSection,
	},
	data() {
		return {
			loading: true,
			saving: false,
			saved: false,
			errorMessage: '',
			jwtTokenServiceUrl: '',
			proServerUrl: '',
			proGroupName: '',
			proServerLabel: '',
			jwtSecret: '',
			jwtAppId: '',
			jwtAppIdMessage: '',
			jwtAudience: '',
			jwtIssuer: '',
			serverUrl: '',
			serverUrlStatus: false,
			serverUrlMessage: '',
			helpLink: '',
			displayJoinUsingThefairmeetingApp: 0,
			openInNewTab: 1,
			displayAllSharingInvites: 0,
			calendarIntegrationEnabled: '0',
			calendarMinimumDuration: 15,
			calendarUseKeyword: '0',
			calendarKeyword: '#fm',
			calendarKeywordReplaceLocation: '1',
			calendarKeywordReplaceDescription: '0',
			meetingSkipPrejoin: '0',
			meetingDisableDeepLinking: '0',
			roomNamePrefix: '',
		}
	},
	computed: {
		hasError() {
			return this.serverUrlStatus === 'error' || this.jwtAppIdMessage
		},
	},
	async created() {
		this.jwtTokenServiceUrl = await this.loadSetting('jwt_token_service_url', '')
		this.proServerUrl = await this.loadSetting('pro_server_url', '')
		this.proGroupName = await this.loadSetting('pro_group_name', 'fairmeeting')
		this.proServerLabel = await this.loadSetting('pro_server_label', 'pro')
		this.jwtSecret = await this.loadSetting('jwt_secret')
		this.jwtAppId = await this.loadSetting('jwt_app_id')
		this.jwtAudience = await this.loadSetting('jwt_audience')
		this.jwtIssuer = await this.loadSetting('jwt_issuer')
		this.serverUrl = await this.loadSetting('fairmeeting_server_url')
		this.helpLink = await this.loadSetting('help_link')
		this.displayJoinUsingThefairmeetingApp = await this.loadSetting(
			'display_join_using_the_fairmeeting_app',
			'1'
		)
		this.openInNewTab = await this.loadSetting('open_in_new_tab', '1')
		this.displayAllSharingInvites = await this.loadSetting(
			'display_all_sharing_invites',
			'1'
		)
		this.calendarIntegrationEnabled = await this.loadSetting(
			'calendar_integration_enabled',
			'0'
		)
		this.calendarMinimumDuration = await this.loadSetting(
			'calendar_minimum_duration',
			'15'
		)
		this.calendarUseKeyword = await this.loadSetting(
			'calendar_use_keyword',
			'0'
		)
		this.calendarKeyword = await this.loadSetting(
			'calendar_keyword',
			'#fm'
		)
		this.calendarKeywordReplaceLocation = await this.loadSetting(
			'calendar_keyword_replace_location',
			'1'
		)
		this.calendarKeywordReplaceDescription = await this.loadSetting(
			'calendar_keyword_replace_description',
			'0'
		)
		this.meetingSkipPrejoin = await this.loadSetting(
			'meeting_skip_prejoin',
			'0'
		)
		this.meetingDisableDeepLinking = await this.loadSetting(
			'meeting_disable_deep_linking',
			'0'
		)
		this.roomNamePrefix = await this.loadSetting(
			'room_name_prefix',
			''
		)
		this.loading = false
	},
	methods: {
		openTokenService() {
			if (!this.jwtTokenServiceUrl) {
				return
			}
			window.open(this.jwtTokenServiceUrl, '_blank', 'noopener,noreferrer')
		},
		async submit() {
			this.sanitise()
			this.validate()

			if (this.hasError) {
				return
			}

			this.saving = true
			this.saved = false

			await Promise.all([
				await this.updateSetting('fairmeeting_server_url', this.serverUrl),
				await this.updateSetting('jwt_token_service_url', this.jwtTokenServiceUrl),
				await this.updateSetting('pro_server_url', this.proServerUrl),
				await this.updateSetting('pro_group_name', this.proGroupName),
				await this.updateSetting('pro_server_label', this.proServerLabel),
				await this.updateSetting('jwt_secret', this.jwtSecret),
				await this.updateSetting('jwt_app_id', this.jwtAppId),
				await this.updateSetting('jwt_audience', this.jwtAudience),
				await this.updateSetting('jwt_issuer', this.jwtIssuer),
				await this.updateSetting('help_link', this.helpLink),
				await this.updateSetting(
					'display_join_using_the_fairmeeting_app',
					this.displayJoinUsingThefairmeetingApp
				),
				await this.updateSetting('open_in_new_tab', this.openInNewTab),
				await this.updateSetting(
					'display_all_sharing_invites',
					this.displayAllSharingInvites
				),
				await this.updateSetting(
					'calendar_integration_enabled',
					this.calendarIntegrationEnabled
				),
				await this.updateSetting(
					'calendar_minimum_duration',
					this.calendarMinimumDuration
				),
				await this.updateSetting(
					'calendar_use_keyword',
					this.calendarUseKeyword
				),
				await this.updateSetting(
					'calendar_keyword',
					this.calendarKeyword
				),
				await this.updateSetting(
					'calendar_keyword_replace_location',
					this.calendarKeywordReplaceLocation
				),
				await this.updateSetting(
					'calendar_keyword_replace_description',
					this.calendarKeywordReplaceDescription
				),
				await this.updateSetting(
					'meeting_skip_prejoin',
					this.meetingSkipPrejoin
				),
				await this.updateSetting(
					'meeting_disable_deep_linking',
					this.meetingDisableDeepLinking
				),
				await this.updateSetting(
					'room_name_prefix',
					this.roomNamePrefix
				),
			])

			this.saving = false
			this.saved = true
		},
		sanitise() {
			if (this.serverUrl && !this.serverUrl.endsWith('/')) {
				this.serverUrl += '/'
			}
		},
		validate() {
			this.serverUrlStatus = false
			this.serverUrlMessage = ''

			if (!this.serverUrl) {
				this.serverUrlStatus = 'error'
				this.serverUrlMessage = this.t(
					'fairmeeting',
					'Please provide a fairmeeting instance URL'
				)
			}

			if (!this.serverUrl.startsWith('https://')) {
				this.serverUrlStatus = 'error'
				this.serverUrlMessage = this.t(
					'fairmeeting',
					'The server URL must start with https://'
				)
			}

			if (this.serverUrl === 'https://meet.jit.si/') {
				this.serverUrlStatus = 'warning'
				this.serverUrlMessage = this.t(
					'fairmeeting',
					'It is highly recommended to set up a dedicated fairmeeting instance'
				)
			}

			this.jwtAppIdMessage = ''

			// JWT signing requires App ID alongside the secret.
			if (this.jwtSecret && !this.jwtAppId) {
				this.jwtAppIdMessage = this.t(
					'fairmeeting',
					'Please provide the App ID'
				)
			}
		},
		async updateSetting(name, value) {
			try {
				await new Promise((resolve, reject) =>
					OCP.AppConfig.setValue('fairmeeting', name, value, {
						success: resolve,
						error: reject,
					})
				)
			} catch (e) {
				this.error = this.t('fairmeeting', 'Failed to save settings')
				throw e
			}
		},
		async loadSetting(name, defaultValue = null) {
			try {
				const resDocument = await new Promise((resolve, reject) =>
					OCP.AppConfig.getValue('fairmeeting', name, defaultValue, {
						success: resolve,
						error: reject,
					})
				)
				if (resDocument.querySelector('status').textContent !== 'ok') {
					this.errorMessage = this.t('fairmeeting', 'Failed to load settings')
					console.error('Failed request', resDocument)
					return
				}
				const dataEl = resDocument.querySelector('data')
				return dataEl.firstElementChild.textContent
			} catch (e) {
				this.errorMessage = this.t('fairmeeting', 'Failed to load settings')
				throw e
			}
		},
	},
}
</script>

<style scoped>
.group {
	align-items: flex-start;
	display: flex;
}

.group--centered {
	align-items: center;
}

.group-label {
	display: block;
	margin-bottom: 8px;
	margin-top: 16px;
}

.section-heading {
	font-size: 16px;
	font-weight: 600;
	margin: 32px 0 8px;
	padding-bottom: 6px;
	border-bottom: 1px solid var(--color-border);
	color: var(--color-main-text);
}

.section-heading:first-of-type {
	margin-top: 8px;
}

.label {
	display: block;
	width: 100%;
}

.input {
	display: block;
	width: 100%;
}

.input-group {
	margin-bottom: 8px;
	position: relative;
	top: -7px;
	width: 100%;
}

.input {
	margin-bottom: 0;
}

.input--has-warning {
	border-color: var(--color-warning);
}

.input--has-error {
	border-color: var(--color-error);
}

.warning-text {
	color: var(--color-warning);
	font-size: 0.9em;
}

.error-text {
	color: var(--color-error);
	font-size: 0.9em;
}

.info-text {
	color: var(--color-text-lighter);
	font-size: 0.9em;
}

.admin-checkbox {
	cursor: pointer;
}

.jwt-option {
	margin-bottom: 12px;
}

.keyword-checkboxes {
	margin-bottom: 8px;
}

.checkbox-row {
	display: flex;
	align-items: center;
	margin-bottom: 4px;
}

.checkbox-label {
	margin-left: 8px;
	font-size: 0.9em;
}

@media only screen and (min-width: 576px) {
	.label {
		display: inline-block;
		margin-right: 10px;
		width: 200px;
	}

	.input-group {
		display: inline-block;
		width: 400px;
	}

	button.primary {
		margin-left: 210px;
	}
}

.jwt-token-row {
	display: flex;
	gap: 8px;
	align-items: center;
}

.jwt-token-row .input {
	flex: 1;
}

.jwt-token-fetch {
	white-space: nowrap;
	padding: 6px 12px;
}
</style>
