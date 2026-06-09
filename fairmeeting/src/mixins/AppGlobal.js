import { generateUrl } from '@nextcloud/router'

export default {
	methods: {
		t,
		n,
		link(path) {
			return generateUrl(path)
		},
	},
}
