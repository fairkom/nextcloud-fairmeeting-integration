<template>
	<div class="room-list-item">
		<Avatar
			class="room-list-item__icon"
			:display-name="filteredName"
			:disable-tooltip="true" />
		<div class="room-list-item__text">
			<div class="room-list-item__name">
				{{ room.name }}
			</div>
			<div v-if="serverHost" class="room-list-item__server">
				<span class="room-list-item__server-host">{{ serverHost }}</span>
				<span v-if="room.serverBadge" class="room-list-item__pro-badge">
					{{ room.serverBadge }}
				</span>
				<span v-if="room.source === 'calendar'" class="room-list-item__source-badge" :title="t('fairmeeting', 'Auto-created for a calendar event')">
					{{ t("fairmeeting", "Calendar") }}
				</span>
				<span v-if="activityLabel" class="room-list-item__activity">· {{ activityLabel }}</span>
			</div>
		</div>
		<div class="room-list-item__actions">
			<Actions>
				<ActionLink :href="roomUrl" icon="icon-play">
					{{ t("fairmeeting", "Join") }}
				</ActionLink>
			</Actions>
			<Actions ref="copyLinkActions">
				<ActionLink
					:href="roomUrl"
					:icon="copied && copySuccess ? 'icon-checkmark-color' : 'icon-clippy'"
					@click.stop.prevent="copyLink">
					{{ clipboardTooltip }}
				</ActionLink>
			</Actions>
			<Actions :force-menu="true">
				<ActionButton icon="icon-delete" @click="deleteRoom">
					{{ t("fairmeeting", "Delete room") }}
				</ActionButton>
			</Actions>
		</div>
	</div>
</template>

<script>
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'
import ActionLink from '@nextcloud/vue/dist/Components/ActionLink'
import Actions from '@nextcloud/vue/dist/Components/Actions'
import Avatar from '@nextcloud/vue/dist/Components/Avatar'

export default {
	name: 'RoomListItem',
	components: {
		ActionButton,
		ActionLink,
		Actions,
		Avatar,
	},
	props: {
		room: {
			type: Object,
			required: true,
		},
	},
	data() {
		return {
			copied: false,
			copySuccess: false,
		}
	},
	computed: {
		filteredName() {
			return this.room.name.replace('.', '_').replace(/[^a-zA-Z0-9_-]+/g, '')
		},
		roomUrl() {
			return (
				window.location.protocol
				+ '//'
				+ window.location.host
				+ generateUrl(
					`/apps/fairmeeting/rooms/${this.room.publicId}/${this.filteredName}`
				)
			)
		},
		serverHost() {
			const url = this.room?.serverUrl
			if (!url) {
				return ''
			}
			try {
				return new URL(url).host
			} catch (e) {
				return ''
			}
		},
		activityLabel() {
			if (this.room.lastJoinedAt) {
				return this.t('fairmeeting', 'last joined {time}', {
					time: this.relativeTime(this.room.lastJoinedAt),
				})
			}
			if (this.room.createdAt) {
				return this.t('fairmeeting', 'created {time}', {
					time: this.relativeTime(this.room.createdAt),
				})
			}
			return ''
		},
		clipboardTooltip() {
			if (this.copied) {
				return this.copySuccess
					? t('jfairmeeting', this.t('Link copied'))
					: t(
						'jfairmeeting',
						this.t('Cannot copy, please copy the link manually')
					  )
			}
			return t('jfairmeeting', this.t('Copy to clipboard'))
		},
	},
	methods: {
		relativeTime(iso) {
			if (!iso) {
				return ''
			}
			const diffSeconds = (Date.now() - new Date(iso).getTime()) / 1000
			if (diffSeconds < 0 || diffSeconds < 60) {
				return this.t('fairmeeting', 'just now')
			}
			if (diffSeconds < 3600) {
				const m = Math.round(diffSeconds / 60)
				return this.n('fairmeeting', '{n} minute ago', '{n} minutes ago', m, { n: m })
			}
			if (diffSeconds < 86400) {
				const h = Math.round(diffSeconds / 3600)
				return this.n('fairmeeting', '{n} hour ago', '{n} hours ago', h, { n: h })
			}
			if (diffSeconds < 86400 * 30) {
				const d = Math.round(diffSeconds / 86400)
				return this.n('fairmeeting', '{n} day ago', '{n} days ago', d, { n: d })
			}
			const months = Math.round(diffSeconds / (86400 * 30))
			return this.n('fairmeeting', '{n} month ago', '{n} months ago', months, { n: months })
		},
		async deleteRoom() {
			const msg = this.t(
				'fairmeeting',
				'Delete the room "{name}"? This cannot be undone.',
				{ name: this.room.name }
			)
			if (!window.confirm(msg)) {
				return
			}
			await axios.delete(
				generateUrl('/apps/fairmeeting/rooms/' + this.room.publicId)
			)
			this.$emit('deleted')
		},
		async copyLink() {
			try {
				await this.$copyText(this.roomUrl)
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
	},
}
</script>

<style scoped>
.room-list-item {
	align-items: center;
	border-bottom: 1px solid var(--color-border);
	display: flex;
	padding: 10px 16px;
}

.room-list-item:last-of-type {
	border-bottom: 0;
}

.room-list-item__icon {
	margin-right: 16px;
}

.room-list-item__text {
	flex: 1 1 auto;
	min-width: 0;
	margin-right: 16px;
}

.room-list-item__name {
	font-weight: 500;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}

.room-list-item__server {
	display: flex;
	align-items: center;
	gap: 8px;
	margin-top: 2px;
	font-size: 12px;
	color: var(--color-text-maxcontrast);
}

.room-list-item__server-host {
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}

.room-list-item__pro-badge {
	display: inline-block;
	padding: 1px 6px;
	font-size: 10px;
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	color: var(--color-primary-element-text, #fff);
	background: var(--color-primary-element, #0082c9);
	border-radius: 10px;
	line-height: 14px;
}

.room-list-item__activity {
	color: var(--color-text-maxcontrast);
	font-size: 12px;
}

.room-list-item__source-badge {
	display: inline-block;
	padding: 1px 6px;
	font-size: 10px;
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	color: var(--color-text-maxcontrast);
	background: var(--color-background-darker);
	border-radius: 10px;
	line-height: 14px;
}

.room-list-item__actions {
	align-items: center;
	display: flex;
	flex-shrink: 0;
}
</style>
