<template>
	<div class="empty-room-list">
		<img
			src="./../../img/fairmeeting_icon.png"
			alt=""
			class="empty-room-list__icon">
		<h2 class="empty-room-list__title">
			{{ t("fairmeeting", "No conference rooms yet") }}
		</h2>
		<p class="empty-room-list__subtitle">
			{{ t("fairmeeting", "Conference rooms are persistent meeting spaces you can share with others. Give your first one a name to get started.") }}
		</p>
		<form class="empty-room-list__form" @submit.prevent="create">
			<input
				ref="roomNameInput"
				v-model="name"
				class="empty-room-list__input"
				:placeholder="t('fairmeeting', 'e.g. Team standup')"
				maxlength="100"
				type="text">
			<button
				type="submit"
				class="primary empty-room-list__button"
				:disabled="!name.trim()">
				{{ t("fairmeeting", "Create room") }}
			</button>
		</form>
	</div>
</template>

<script>
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

export default {
	name: 'EmptyRoomListItem',
	data() {
		return {
			name: '',
		}
	},
	mounted() {
		this.$refs.roomNameInput.focus()
	},
	methods: {
		async create() {
			const data = {
				name: this.name,
			}
			await axios.post(generateUrl('/apps/fairmeeting/rooms'), data)
			this.$emit('created')
		},
	},
}
</script>

<style scoped>
.empty-room-list {
	display: flex;
	flex-direction: column;
	align-items: center;
	padding: 80px 32px 64px;
	text-align: center;
}

.empty-room-list__icon {
	width: 64px;
	height: auto;
	opacity: 0.85;
	margin-bottom: 24px;
}

.empty-room-list__title {
	font-size: 24px;
	font-weight: 600;
	margin: 0 0 12px;
	color: var(--color-main-text);
}

.empty-room-list__subtitle {
	color: var(--color-text-maxcontrast);
	max-width: 420px;
	margin: 0 0 32px;
	line-height: 1.5;
}

.empty-room-list__form {
	display: flex;
	gap: 8px;
	align-items: center;
	width: 100%;
	max-width: 480px;
}

.empty-room-list__input {
	flex: 1 1 auto;
	min-width: 0;
}

.empty-room-list__button {
	white-space: nowrap;
}
</style>
