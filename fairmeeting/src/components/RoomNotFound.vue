<template>
	<div class="fairmeeting-info-container">
		<h1 class="fairmeeting-info-title">
			{{ t("fairmeeting", "Room not found") }}
		</h1>
		<p class="fairmeeting-info-text">
			{{ t("fairmeeting", "Please check the link/URL and ask your host") }}
		</p>
	</div>
</template>

<script>
export default {
	name: 'RoomNotFound',
}
</script>

<style scoped></style>
