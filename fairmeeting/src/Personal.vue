<template>
	<div class="section">
		<h2 class="section-title">{{ t('fairmeeting', 'fairmeeting') }}</h2>

		<div v-if="effectiveServerHost" :class="['server-banner', inProGroup ? 'server-banner--pro' : 'server-banner--default']">
			<div class="server-banner__label">{{ t('fairmeeting', 'Your meetings host on') }}</div>
			<div class="server-banner__host-row">
				<span class="server-banner__host">{{ effectiveServerHost }}</span>
				<span v-if="inProGroup" class="server-banner__pill">{{ t('fairmeeting', 'pro') }}</span>
			</div>
			<div v-if="serverBannerReason" class="server-banner__reason">{{ serverBannerReason }}</div>
		</div>

		<p class="settings-hint">
			{{ t('fairmeeting', 'Set a personal JWT token to join meetings under your own identity.') }}
		</p>

		<div class="form-row">
			<label for="fairmeeting_personal_jwt_token" class="form-label">
				{{ t('fairmeeting', 'Your JWT Token') }}
			</label>
			<div class="form-control">
				<input
					id="fairmeeting_personal_jwt_token"
					v-model="personalJwtToken"
					class="input"
					type="text"
					:placeholder="t('fairmeeting', 'eyJ…')">
				<button
					v-if="tokenServiceUrl"
					type="button"
					class="button"
					@click="openTokenService">
					{{ t('fairmeeting', 'Open token service') }}
				</button>
			</div>
			<p v-if="tokenStatus" :class="['form-status', tokenStatus.kind]">
				<strong>{{ tokenStatus.label }}</strong>
				<span v-if="tokenStatus.detail">— {{ tokenStatus.detail }}</span>
			</p>
			<p class="form-help">
				<template v-if="tokenServiceUrl">
					<strong>{{ t('fairmeeting', 'How to get a token:') }}</strong>
					{{ t('fairmeeting', 'Click "Open token service", sign in via Keycloak, copy the long string after "token":, and paste it above.') }}
				</template>
				<template v-else>
					{{ t('fairmeeting', 'Your administrator has not configured a token service yet. Without a personal token, you join meetings under your Nextcloud display name.') }}
				</template>
			</p>
		</div>

		<div class="form-actions">
			<button type="button" class="primary" :disabled="saving" @click="save">
				{{ t('fairmeeting', 'Save') }}
			</button>
			<span v-if="!saving && saved" class="msg success">
				✓ {{ t('fairmeeting', 'Saved') }}
			</span>
			<span v-if="saving" class="msg">
				{{ t('fairmeeting', 'Saving…') }}
			</span>
		</div>
	</div>
</template>

<script>
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

export default {
	name: 'Personal',
	data() {
		const el = document.getElementById('fairmeeting-personal')
		return {
			personalJwtToken: el?.dataset.personalJwtToken ?? '',
			tokenServiceUrl: el?.dataset.tokenServiceUrl ?? '',
			effectiveServerUrl: el?.dataset.effectiveServerUrl ?? '',
			proServerUrl: el?.dataset.proServerUrl ?? '',
			proGroupName: el?.dataset.proGroupName ?? '',
			inProGroup: el?.dataset.inProGroup === '1',
			saving: false,
			saved: false,
		}
	},
	computed: {
		effectiveServerHost() {
			try {
				return this.effectiveServerUrl ? new URL(this.effectiveServerUrl).host : ''
			} catch (e) {
				return ''
			}
		},
		serverBannerReason() {
			if (!this.proServerUrl) {
				return ''
			}
			if (this.inProGroup) {
				return this.t(
					'fairmeeting',
					'you are a member of the "{group}" group.',
					{ group: this.proGroupName }
				)
			}
			return this.t(
				'fairmeeting',
				'members of the "{group}" group host on the pro server.',
				{ group: this.proGroupName }
			)
		},
		// Inspect the pasted JWT for its `exp` claim. Returns null when
		// the field is empty, malformed, or doesn't carry an exp.
		tokenStatus() {
			const token = this.personalJwtToken
			if (!token) {
				return null
			}
			const parts = token.split('.')
			if (parts.length !== 3) {
				return {
					kind: 'invalid',
					label: this.t('fairmeeting', 'Invalid token'),
					detail: this.t('fairmeeting', 'Not a JWT — expected three dot-separated parts.'),
				}
			}
			let payload
			try {
				payload = JSON.parse(this.base64UrlDecode(parts[1]))
			} catch (e) {
				return {
					kind: 'invalid',
					label: this.t('fairmeeting', 'Invalid token'),
					detail: this.t('fairmeeting', 'Could not decode the payload.'),
				}
			}
			if (!payload.exp) {
				return {
					kind: 'ok',
					label: this.t('fairmeeting', 'Token loaded'),
					detail: '',
				}
			}
			const expMs = payload.exp * 1000
			const now = Date.now()
			const days = Math.round((expMs - now) / (1000 * 60 * 60 * 24))
			if (expMs < now) {
				return {
					kind: 'expired',
					label: this.t('fairmeeting', 'Token expired'),
					detail: this.n('fairmeeting',
						'Expired {n} day ago — fetch a new token.',
						'Expired {n} days ago — fetch a new token.',
						-days, { n: -days }),
				}
			}
			return {
				kind: 'ok',
				label: this.t('fairmeeting', 'Token valid'),
				detail: this.n('fairmeeting',
					'Expires in {n} day.',
					'Expires in {n} days.',
					days, { n: days }),
			}
		},
	},
	methods: {
		base64UrlDecode(s) {
			const pad = s.length % 4 === 0 ? '' : '='.repeat(4 - (s.length % 4))
			return atob((s + pad).replace(/-/g, '+').replace(/_/g, '/'))
		},
		openTokenService() {
			if (this.tokenServiceUrl) {
				window.open(this.tokenServiceUrl, '_blank', 'noopener,noreferrer')
			}
		},
		async save() {
			this.saving = true
			this.saved = false
			try {
				await axios.put(
					generateUrl('/apps/fairmeeting/api/personal/jwt-token'),
					{ jwtToken: this.personalJwtToken }
				)
				this.saved = true
			} catch (e) {
				if (window.OC && window.OC.Notification) {
					window.OC.Notification.showTemporary(
						this.t('fairmeeting', 'Failed to save personal settings')
					)
				}
			} finally {
				this.saving = false
			}
		},
	},
}
</script>

<style scoped>
.section {
	max-width: 720px;
}
.section-title {
	font-size: 20px;
	font-weight: 600;
	margin: 0 0 8px;
}
.settings-hint {
	color: var(--color-text-maxcontrast);
	margin: 0 0 24px;
}
.server-banner {
	position: relative;
	padding: 16px 20px 16px 24px;
	margin-bottom: 24px;
	border-radius: var(--border-radius-large, 12px);
	border-left: 6px solid;
	background: var(--color-background-hover, #f5f5f5);
	box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}
.server-banner--default {
	border-left-color: var(--color-text-maxcontrast);
	background: var(--color-background-hover, #f5f5f5);
}
.server-banner--pro {
	border-left-color: var(--color-success);
	background: var(--color-background-hover, #f5f5f5);
}
.server-banner__label {
	font-size: 11px;
	font-weight: 600;
	letter-spacing: 0.6px;
	text-transform: uppercase;
	color: var(--color-text-maxcontrast);
	margin-bottom: 4px;
}
.server-banner__host-row {
	display: flex;
	align-items: center;
	gap: 10px;
	flex-wrap: wrap;
}
.server-banner__host {
	font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', monospace;
	font-size: 22px;
	font-weight: 700;
	color: var(--color-main-text);
	line-height: 1.2;
	word-break: break-all;
}
.server-banner__pill {
	display: inline-block;
	padding: 2px 10px;
	font-size: 11px;
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: 0.8px;
	color: #fff;
	background: var(--color-success);
	border-radius: 12px;
	line-height: 16px;
}
.server-banner__reason {
	margin-top: 6px;
	font-size: 13px;
	color: var(--color-text-maxcontrast);
}
.form-row {
	margin-bottom: 24px;
}
.form-label {
	display: block;
	font-weight: 500;
	margin-bottom: 6px;
}
.form-control {
	display: flex;
	gap: 8px;
	align-items: center;
}
.form-control .input {
	flex: 1;
	min-width: 0;
}
.form-control .button {
	white-space: nowrap;
}
.form-help {
	margin: 6px 0 0;
	color: var(--color-text-maxcontrast);
	font-size: 13px;
	line-height: 1.4;
}
.form-help strong {
	color: var(--color-main-text);
}
.form-actions {
	display: flex;
	gap: 12px;
	align-items: center;
	margin-top: 16px;
}
.form-status {
	margin: 8px 0 0;
	padding: 6px 10px;
	border-radius: var(--border-radius);
	font-size: 13px;
}
.form-status.ok {
	background: rgba(70, 186, 97, 0.12);
	color: var(--color-success);
}
.form-status.expired {
	background: rgba(228, 81, 65, 0.12);
	color: var(--color-error);
}
.form-status.invalid {
	background: rgba(228, 81, 65, 0.12);
	color: var(--color-error);
}
.msg.success { color: var(--color-success); }
.msg.error { color: var(--color-error); }
</style>
