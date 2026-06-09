<template>
	<div class="app-content">
		<Breadcrumbs>
			<Breadcrumb :disable-drop="true" title="Home" />
		</Breadcrumbs>
		<div class="app-title">
			<h1 class="h1 app-title__text">
				{{ t("fairmeeting", "Conference rooms") }}
			</h1>
			<img
				src="./../img/fairmeeting_icon.png"
				alt="Fairmeeting Icon"
				class="app-icon">
			<button
				v-if="!loading && rooms.length > 0"
				class="icon-add app-title__button"
				:title="t('fairmeeting', 'Create new room')"
				@click="showCreateRoom = true" />
		</div>

		<CreateRoomItem
			v-if="showCreateRoom"
			@cancelled="showCreateRoom = false"
			@created="onRoomCreated" />

		<div v-if="!loading && rooms.length > 1" class="toolbar">
			<input
				v-model="search"
				class="toolbar__search"
				type="search"
				:placeholder="t('fairmeeting', 'Search rooms…')">
			<select v-model="sortBy" class="toolbar__sort">
				<option value="recent">{{ t("fairmeeting", "Sort by recent activity") }}</option>
				<option value="name">{{ t("fairmeeting", "Sort by name") }}</option>
				<option value="server">{{ t("fairmeeting", "Sort by server") }}</option>
			</select>
		</div>

		<RoomList v-if="loading">
			<div v-for="i in 3" :key="i" class="room-skeleton">
				<div class="room-skeleton__avatar" />
				<div class="room-skeleton__text">
					<div class="room-skeleton__line room-skeleton__line--name" />
					<div class="room-skeleton__line room-skeleton__line--meta" />
				</div>
			</div>
		</RoomList>

		<RoomList v-else>
			<RoomListItem
				v-for="room in visibleRooms"
				:key="room.id"
				:room="room"
				@deleted="refreshRooms" />
			<div
				v-if="rooms.length > 0 && visibleRooms.length === 0"
				class="empty-search">
				{{ t("fairmeeting", "No rooms match \"{q}\"", { q: search }) }}
			</div>
			<EmptyRoomListItem v-if="rooms.length === 0" @created="onRoomCreated" />
		</RoomList>
	</div>
</template>

<script>
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import EmptyRoomListItem from './components/EmptyRoomListItem'
import RoomList from './components/RoomList'
import RoomListItem from './components/RoomListItem'
import CreateRoomItem from './components/CreateRoomItem'
import Breadcrumb from '@nextcloud/vue/dist/Components/Breadcrumb'
import Breadcrumbs from '@nextcloud/vue/dist/Components/Breadcrumbs'

import '../css/styles.css'

export default {
	name: 'Index',
	components: {
		Breadcrumb,
		Breadcrumbs,
		CreateRoomItem,
		EmptyRoomListItem,
		RoomList,
		RoomListItem,
	},
	data() {
		return {
			showCreateRoom: false,
			rooms: [],
			loading: true,
			search: '',
			sortBy: 'recent',
		}
	},
	computed: {
		visibleRooms() {
			const q = this.search.trim().toLowerCase()
			let list = this.rooms
			if (q !== '') {
				list = list.filter(r => r.name.toLowerCase().includes(q))
			}
			const tsOf = (r) => {
				const t = r.lastJoinedAt || r.createdAt
				return t ? new Date(t).getTime() : 0
			}
			list = [...list].sort((a, b) => {
				if (this.sortBy === 'recent') {
					const diff = tsOf(b) - tsOf(a)
					if (diff !== 0) {
						return diff
					}
					return a.name.localeCompare(b.name)
				}
				if (this.sortBy === 'server') {
					const sa = (a.serverUrl || '').toLowerCase()
					const sb = (b.serverUrl || '').toLowerCase()
					if (sa !== sb) {
						return sa.localeCompare(sb)
					}
				}
				return a.name.localeCompare(b.name)
			})
			return list
		},
	},
	async created() {
		await this.refreshRooms()
	},
	methods: {
		async onRoomCreated() {
			this.showCreateRoom = false
			await this.refreshRooms()
		},
		async refreshRooms() {
			this.loading = true
			try {
				const response = await axios.get(generateUrl('/apps/fairmeeting/rooms'))
				this.rooms = response.data
			} finally {
				this.loading = false
			}
		},
	},
}
</script>

<style scoped>
.app-icon {
	display: block;
	width: 50px;
	height: auto;
	margin: 5px;
}

.app-title {
	align-items: center;
	display: flex;
	margin-bottom: 16px;
}

.app-title__text {
	margin-right: 8px;
	padding: 9px 0;
}

.app-title__button {
	padding: 16px;
}

.h1 {
	font-size: 24px;
}

.app-content {
	display: flex;
	flex-direction: column;
	padding: 16px;
	width: 100%;
}

.breadcrumb {
	flex: 0 0 auto;
}

.toolbar {
	display: flex;
	gap: 8px;
	margin-bottom: 12px;
}

.toolbar__search {
	flex: 1 1 auto;
	min-width: 0;
}

.toolbar__sort {
	flex: 0 0 auto;
}

.room-skeleton {
	display: flex;
	align-items: center;
	padding: 10px 16px;
	border-bottom: 1px solid var(--color-border);
	animation: skeleton-pulse 1.4s ease-in-out infinite;
}

.room-skeleton__avatar {
	width: 32px;
	height: 32px;
	border-radius: 50%;
	background: var(--color-background-darker);
	margin-right: 16px;
	flex-shrink: 0;
}

.room-skeleton__text {
	flex: 1;
	display: flex;
	flex-direction: column;
	gap: 6px;
}

.room-skeleton__line {
	height: 12px;
	border-radius: 6px;
	background: var(--color-background-darker);
}

.room-skeleton__line--name {
	width: 40%;
}

.room-skeleton__line--meta {
	width: 25%;
	height: 10px;
	opacity: 0.7;
}

@keyframes skeleton-pulse {
	0%, 100% { opacity: 1; }
	50% { opacity: 0.55; }
}

.empty-search {
	padding: 32px;
	text-align: center;
	color: var(--color-text-maxcontrast);
}

@media only screen and (min-width: 576px) {
	.app-content {
		margin-left: auto;
		margin-right: auto;
		max-width: 992px;
	}
}
</style>
