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

						<strong class="group-label">JSON Web Token</strong>

						<div class="group jwt-option">
							<label for="fairmeeting_jwt_token" class="label">
								{{ t("fairmeeting", "JWT Token (optional)") }}
							</label>
							<div class="input-group">
								<input
									id="fairmeeting_jwt_token"
									v-model="jwtToken"
									class="input"
									type="text">
								<div class="info-text">
									{{
										t(
											"fairmeeting",
											"Enter a pre-generated JWT token. This takes precedence over any token generated from the secret."
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
						<strong class="group-label">Nextcloud-Settings</strong>
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

						<strong class="group-label">Invite & Share</strong>

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

						<strong class="group-label">Calendar Integration</strong>

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
			jwtToken: '',
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
			roomNamePrefix: '',
		}
	},
	computed: {
		hasError() {
			return this.serverUrlStatus === 'error' || this.jwtAppIdMessage
		},
	},
	async created() {
		this.jwtToken = await this.loadSetting('jwt_token', '')
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
		this.roomNamePrefix = await this.loadSetting(
			'room_name_prefix',
			''
		)
		this.loading = false
	},
	methods: {
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
				await this.updateSetting('jwt_token', this.jwtToken),
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

			// Only validate JWT App ID if secret is provided but no token
			if (this.jwtSecret && !this.jwtToken && !this.jwtAppId) {
				this.jwtAppIdMessage = this.t(
					'fairmeeting',
					'Please provide the App ID'
				)
			}

			// Basic JWT token format validation
			if (
				this.jwtToken
				&& !this.jwtToken.match(/^[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+$/)
			) {
				this.jwtAppIdMessage = this.t(
					'fairmeeting',
					'Invalid JWT token format'
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
</style>
