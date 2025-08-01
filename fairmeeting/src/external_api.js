!(function(e, t) {
	typeof exports === 'object' && typeof module === 'object'
		? (module.exports = t())
		: typeof define === 'function' && define.amd
			? define([], t)
			: typeof exports === 'object'
				? (exports.JitsiMeetExternalAPI = t())
				: (e.JitsiMeetExternalAPI = t())
})(window, function() {
	return (function(e) {
		const t = {}
		/**
		 * @param r
		 */
		function n(r) {
			if (t[r]) return t[r].exports
			const i = (t[r] = { i: r, l: !1, exports: {} })
			return e[r].call(i.exports, i, i.exports, n), (i.l = !0), i.exports
		}
		return (
			(n.m = e),
			(n.c = t),
			(n.d = function(e, t, r) {
				n.o(e, t) || Object.defineProperty(e, t, { enumerable: !0, get: r })
			}),
			(n.r = function(e) {
				typeof Symbol !== 'undefined'
					&& Symbol.toStringTag
					&& Object.defineProperty(e, Symbol.toStringTag, { value: 'Module' }),
				Object.defineProperty(e, '__esModule', { value: !0 })
			}),
			(n.t = function(e, t) {
				if ((1 & t && (e = n(e)), 8 & t)) return e
				if (4 & t && typeof e === 'object' && e && e.__esModule) return e
				const r = Object.create(null)
				if (
					(n.r(r),
					Object.defineProperty(r, 'default', { enumerable: !0, value: e }),
					2 & t && typeof e !== 'string')
				) {
					for (const i in e) {
						n.d(
							r,
							i,
							function(t) {
								return e[t]
							}.bind(null, i)
						)
					}
				}
				return r
			}),
			(n.n = function(e) {
				const t
					= e && e.__esModule
						? function() {
							return e.default
						  }
						: function() {
							return e
						  }
				return n.d(t, 'a', t), t
			}),
			(n.o = function(e, t) {
				return Object.prototype.hasOwnProperty.call(e, t)
			}),
			(n.p = '/libs/'),
			n((n.s = 6))
		)
	})([
		function(e, t, n) {
			'use strict';
			(function(e) {
				n.d(t, 'a', function() {
					return s
				}),
				n.d(t, 'b', function() {
					return o
				}),
				n.d(t, 'c', function() {
					return a
				}),
				n.d(t, 'd', function() {
					return c
				}),
				n.d(t, 'e', function() {
					return l
				}),
				n.d(t, 'f', function() {
					return u
				}),
				n.d(t, 'g', function() {
					return d
				}),
				n.d(t, 'h', function() {
					return p
				})
				const r = n(5)
				const i = n.n(r).a.getLogger(e)
				/**
				 * @param e
				 */
				function s(e) {
					return e
						.sendRequest({ type: 'devices', name: 'getAvailableDevices' })
						.catch((e) => (i.error(e), {}))
				}
				/**
				 * @param e
				 */
				function o(e) {
					return e
						.sendRequest({ type: 'devices', name: 'getCurrentDevices' })
						.catch((e) => (i.error(e), {}))
				}
				/**
				 * @param e
				 * @param t
				 */
				function a(e, t) {
					return e.sendRequest({
						deviceType: t,
						type: 'devices',
						name: 'isDeviceChangeAvailable',
					})
				}
				/**
				 * @param e
				 */
				function c(e) {
					return e.sendRequest({
						type: 'devices',
						name: 'isDeviceListAvailable',
					})
				}
				/**
				 * @param e
				 */
				function l(e) {
					return e.sendRequest({
						type: 'devices',
						name: 'isMultipleAudioInputSupported',
					})
				}
				/**
				 * @param e
				 * @param t
				 * @param n
				 */
				function u(e, t, n) {
					return h(e, { id: n, kind: 'audioinput', label: t })
				}
				/**
				 * @param e
				 * @param t
				 * @param n
				 */
				function d(e, t, n) {
					return h(e, { id: n, kind: 'audiooutput', label: t })
				}
				/**
				 * @param e
				 * @param t
				 */
				function h(e, t) {
					return e.sendRequest({
						type: 'devices',
						name: 'setDevice',
						device: t,
					})
				}
				/**
				 * @param e
				 * @param t
				 * @param n
				 */
				function p(e, t, n) {
					return h(e, { id: n, kind: 'videoinput', label: t })
				}
			}).call(this, 'modules/API/external/functions.js')
		},
		function(e, t) {
			const n = { trace: 0, debug: 1, info: 2, log: 3, warn: 4, error: 5 }
			a.consoleTransport = console
			const r = [a.consoleTransport];
			(a.addGlobalTransport = function(e) {
				r.indexOf(e) === -1 && r.push(e)
			}),
			(a.removeGlobalTransport = function(e) {
				const t = r.indexOf(e)
				t !== -1 && r.splice(t, 1)
			})
			let i = {}
			/**
			 *
			 */
			function s() {
				const e = { methodName: '', fileLocation: '', line: null, column: null }
				const t = new Error()
				const n = t.stack ? t.stack.split('\n') : []
				if (!n || n.length < 1) return e
				let r = null
				return (
					n[3]
						&& (r = n[3].match(/\s*at\s*(.+?)\s*\((\S*)\s*:(\d*)\s*:(\d*)\)/)),
					!r || r.length <= 4
						? (n[2].indexOf('log@') === 0
							? (e.methodName = n[3].substr(0, n[3].indexOf('@')))
							: (e.methodName = n[2].substr(0, n[2].indexOf('@'))),
						  e)
						: ((e.methodName = r[1]),
						  (e.fileLocation = r[2]),
						  (e.line = r[3]),
						  (e.column = r[4]),
						  e)
				)
			}
			/**
			 *
			 */
			function o() {
				const e = arguments[0]
				const t = arguments[1]
				const o = Array.prototype.slice.call(arguments, 2)
				if (!(n[t] < e.level)) {
					for (
						let a
								= !(e.options.disableCallerInfo || i.disableCallerInfo) && s(),
							c = r.concat(e.transports),
							l = 0;
						l < c.length;
						l++
					) {
						const u = c[l]
						const d = u[t]
						if (d && typeof d === 'function') {
							const h = []
							h.push(new Date().toISOString()),
							e.id && h.push('[' + e.id + ']'),
							a
									&& a.methodName.length > 1
									&& h.push('<' + a.methodName + '>: ')
							const p = h.concat(o)
							d.bind(u).apply(u, p)
						}
					}
				}
			}
			/**
			 * @param e
			 * @param t
			 * @param r
			 * @param i
			 */
			function a(e, t, r, i) {
				(this.id = t),
				(this.options = i || {}),
				(this.transports = r),
				this.transports || (this.transports = []),
				(this.level = n[e])
				for (let s = Object.keys(n), a = 0; a < s.length; a++) { this[s[a]] = o.bind(null, this, s[a]) }
			}
			(a.setGlobalOptions = function(e) {
				i = e || {}
			}),
			(a.prototype.setLevel = function(e) {
				this.level = n[e]
			}),
			(e.exports = a),
			(a.levels = {
				TRACE: 'trace',
				DEBUG: 'debug',
				INFO: 'info',
				LOG: 'log',
				WARN: 'warn',
				ERROR: 'error',
			})
		},
		function(e, t, n) {
			'use strict'
			let r
			const i = typeof Reflect === 'object' ? Reflect : null
			const s
					= i && typeof i.apply === 'function'
						? i.apply
						: function(e, t, n) {
							return Function.prototype.apply.call(e, t, n)
						  }
			r
				= i && typeof i.ownKeys === 'function'
					? i.ownKeys
					: Object.getOwnPropertySymbols
						? function(e) {
							return Object.getOwnPropertyNames(e).concat(
								Object.getOwnPropertySymbols(e)
							)
					  }
						: function(e) {
							return Object.getOwnPropertyNames(e)
					  }
			const o
				= Number.isNaN
				|| function(e) {
					return e != e
				}
			/**
			 *
			 */
			function a() {
				a.init.call(this)
			}
			(e.exports = a),
			(a.EventEmitter = a),
			(a.prototype._events = void 0),
			(a.prototype._eventsCount = 0),
			(a.prototype._maxListeners = void 0)
			let c = 10
			/**
			 * @param e
			 */
			function l(e) {
				if (typeof e !== 'function') {
					throw new TypeError(
						'The "listener" argument must be of type Function. Received type '
							+ typeof e
					)
				}
			}
			/**
			 * @param e
			 */
			function u(e) {
				return void 0 === e._maxListeners
					? a.defaultMaxListeners
					: e._maxListeners
			}
			/**
			 * @param e
			 * @param t
			 * @param n
			 * @param r
			 */
			function d(e, t, n, r) {
				let i, s, o, a
				if (
					(l(n),
					void 0 === (s = e._events)
						? ((s = e._events = Object.create(null)), (e._eventsCount = 0))
						: (void 0 !== s.newListener
								&& (e.emit('newListener', t, n.listener ? n.listener : n),
								(s = e._events)),
						  (o = s[t])),
					void 0 === o)
				) { (o = s[t] = n), ++e._eventsCount } else if (
					(typeof o === 'function'
						? (o = s[t] = r ? [n, o] : [o, n])
						: r
							? o.unshift(n)
							: o.push(n),
					(i = u(e)) > 0 && o.length > i && !o.warned)
				) {
					o.warned = !0
					const c = new Error(
						'Possible EventEmitter memory leak detected. '
							+ o.length
							+ ' '
							+ String(t)
							+ ' listeners added. Use emitter.setMaxListeners() to increase limit'
					);
					(c.name = 'MaxListenersExceededWarning'),
					(c.emitter = e),
					(c.type = t),
					(c.count = o.length),
					(a = c),
					console && console.warn && console.warn(a)
				}
				return e
			}
			/**
			 *
			 */
			function h() {
				if (!this.fired) {
					return (
						this.target.removeListener(this.type, this.wrapFn),
						(this.fired = !0),
						arguments.length === 0
							? this.listener.call(this.target)
							: this.listener.apply(this.target, arguments)
					)
				}
			}
			/**
			 * @param e
			 * @param t
			 * @param n
			 */
			function p(e, t, n) {
				const r = { fired: !1, wrapFn: void 0, target: e, type: t, listener: n }
				const i = h.bind(r)
				return (i.listener = n), (r.wrapFn = i), i
			}
			/**
			 * @param e
			 * @param t
			 * @param n
			 */
			function f(e, t, n) {
				const r = e._events
				if (void 0 === r) return []
				const i = r[t]
				return void 0 === i
					? []
					: typeof i === 'function'
						? n
							? [i.listener || i]
							: [i]
						: n
							? (function(e) {
								for (var t = new Array(e.length), n = 0; n < t.length; ++n) { t[n] = e[n].listener || e[n] }
								return t
					  })(i)
							: v(i, i.length)
			}
			/**
			 * @param e
			 */
			function m(e) {
				const t = this._events
				if (void 0 !== t) {
					const n = t[e]
					if (typeof n === 'function') return 1
					if (void 0 !== n) return n.length
				}
				return 0
			}
			/**
			 * @param e
			 * @param t
			 */
			function v(e, t) {
				for (var n = new Array(t), r = 0; r < t; ++r) n[r] = e[r]
				return n
			}
			Object.defineProperty(a, 'defaultMaxListeners', {
				enumerable: !0,
				get() {
					return c
				},
				set(e) {
					if (typeof e !== 'number' || e < 0 || o(e)) {
						throw new RangeError(
							'The value of "defaultMaxListeners" is out of range. It must be a non-negative number. Received '
								+ e
								+ '.'
						)
					}
					c = e
				},
			}),
			(a.init = function() {
				(void 0 !== this._events
						&& this._events !== Object.getPrototypeOf(this)._events)
						|| ((this._events = Object.create(null)), (this._eventsCount = 0)),
				(this._maxListeners = this._maxListeners || void 0)
			}),
			(a.prototype.setMaxListeners = function(e) {
				if (typeof e !== 'number' || e < 0 || o(e)) {
					throw new RangeError(
						'The value of "n" is out of range. It must be a non-negative number. Received '
								+ e
								+ '.'
					)
				}
				return (this._maxListeners = e), this
			}),
			(a.prototype.getMaxListeners = function() {
				return u(this)
			}),
			(a.prototype.emit = function(e) {
				for (var t = [], n = 1; n < arguments.length; n++) { t.push(arguments[n]) }
				let r = e === 'error'
				const i = this._events
				if (void 0 !== i) r = r && void 0 === i.error
				else if (!r) return !1
				if (r) {
					let o
					if ((t.length > 0 && (o = t[0]), o instanceof Error)) throw o
					const a = new Error(
						'Unhandled error.' + (o ? ' (' + o.message + ')' : '')
					)
					throw ((a.context = o), a)
				}
				const c = i[e]
				if (void 0 === c) return !1
				if (typeof c === 'function') s(c, this, t)
				else {
					const l = c.length
					const u = v(c, l)
					for (n = 0; n < l; ++n) s(u[n], this, t)
				}
				return !0
			}),
			(a.prototype.addListener = function(e, t) {
				return d(this, e, t, !1)
			}),
			(a.prototype.on = a.prototype.addListener),
			(a.prototype.prependListener = function(e, t) {
				return d(this, e, t, !0)
			}),
			(a.prototype.once = function(e, t) {
				return l(t), this.on(e, p(this, e, t)), this
			}),
			(a.prototype.prependOnceListener = function(e, t) {
				return l(t), this.prependListener(e, p(this, e, t)), this
			}),
			(a.prototype.removeListener = function(e, t) {
				let n, r, i, s, o
				if ((l(t), void 0 === (r = this._events))) return this
				if (void 0 === (n = r[e])) return this
				if (n === t || n.listener === t) {
					--this._eventsCount == 0
						? (this._events = Object.create(null))
						: (delete r[e],
							  r.removeListener
									&& this.emit('removeListener', e, n.listener || t))
				} else if (typeof n !== 'function') {
					for (i = -1, s = n.length - 1; s >= 0; s--) {
						if (n[s] === t || n[s].listener === t) {
							(o = n[s].listener), (i = s)
							break
						}
					}
					if (i < 0) return this
					i === 0
						? n.shift()
						: (function(e, t) {
							for (; t + 1 < e.length; t++) e[t] = e[t + 1]
							e.pop()
							  })(n, i),
					n.length === 1 && (r[e] = n[0]),
					void 0 !== r.removeListener
								&& this.emit('removeListener', e, o || t)
				}
				return this
			}),
			(a.prototype.off = a.prototype.removeListener),
			(a.prototype.removeAllListeners = function(e) {
				let t, n, r
				if (void 0 === (n = this._events)) return this
				if (void 0 === n.removeListener) {
					return (
						arguments.length === 0
							? ((this._events = Object.create(null)),
								  (this._eventsCount = 0))
							: void 0 !== n[e]
								  && (--this._eventsCount == 0
								  	? (this._events = Object.create(null))
								  	: delete n[e]),
						this
					)
				}
				if (arguments.length === 0) {
					let i
					const s = Object.keys(n)
					for (r = 0; r < s.length; ++r) { (i = s[r]) !== 'removeListener' && this.removeAllListeners(i) }
					return (
						this.removeAllListeners('removeListener'),
						(this._events = Object.create(null)),
						(this._eventsCount = 0),
						this
					)
				}
				if (typeof (t = n[e]) === 'function') this.removeListener(e, t)
				else if (void 0 !== t) { for (r = t.length - 1; r >= 0; r--) this.removeListener(e, t[r]) }
				return this
			}),
			(a.prototype.listeners = function(e) {
				return f(this, e, !0)
			}),
			(a.prototype.rawListeners = function(e) {
				return f(this, e, !1)
			}),
			(a.listenerCount = function(e, t) {
				return typeof e.listenerCount === 'function'
					? e.listenerCount(t)
					: m.call(e, t)
			}),
			(a.prototype.listenerCount = m),
			(a.prototype.eventNames = function() {
				return this._eventsCount > 0 ? r(this._events) : []
			})
		},
		function(e, t) {
			e.exports = function(e) {
				let t
				const n = e.scope
				const r = e.window
				const i = e.windowForEventListening || window
				const s = {}
				let o = []
				const a = {}
				let c = !1
				const l = function(e) {
					let t
					try {
						t = JSON.parse(e.data)
					} catch (e) {
						return
					}
					if (t.postis && t.scope === n) {
						const r = s[t.method]
						if (r) { for (let i = 0; i < r.length; i++) r[i].call(null, t.params) } else { (a[t.method] = a[t.method] || []), a[t.method].push(t.params) }
					}
				}
				i.addEventListener('message', l, !1)
				var u = {
					listen(e, t) {
						(s[e] = s[e] || []), s[e].push(t)
						const n = a[e]
						if (n) {
							for (let r = s[e], i = 0; i < r.length; i++) { for (let o = 0; o < n.length; o++) r[i].call(null, n[o]) }
						}
						delete a[e]
					},
					send(e) {
						const t = e.method;
						(c || e.method === '__ready__')
							&& r
							&& typeof r.postMessage === 'function'
							? r.postMessage(
								JSON.stringify({
									postis: !0,
									scope: n,
									method: t,
									params: e.params,
								}),
								'*'
								  )
							: o.push(e)
					},
					ready(e) {
						c
							? e()
							: setTimeout(function() {
								u.ready(e)
								  }, 50)
					},
					destroy(e) {
						clearInterval(t),
						(c = !1),
						i
									&& typeof i.removeEventListener === 'function'
									&& i.removeEventListener('message', l),
						e && e()
					},
				}
				const d = +new Date() + Math.random() + ''
				return (
					(t = setInterval(function() {
						u.send({ method: '__ready__', params: d })
					}, 50)),
					u.listen('__ready__', function(e) {
						if (e === d) {
							clearInterval(t), (c = !0)
							for (let n = 0; n < o.length; n++) u.send(o[n])
							o = []
						} else u.send({ method: '__ready__', params: e })
					}),
					u
				)
			}
		},
		function(e) {
			e.exports = JSON.parse(
				'{"google-auth":{"matchPatterns":{"url":"accounts.google.com"},"target":"electron"},"dropbox-auth":{"matchPatterns":{"url":"dropbox.com/oauth2/authorize"},"target":"electron"}}'
			)
		},
		function(e, t, n) {
			const r = n(1)
			const i = n(7)
			const s = {}
			const o = []
			let a = r.levels.TRACE
			e.exports = {
				addGlobalTransport(e) {
					r.addGlobalTransport(e)
				},
				removeGlobalTransport(e) {
					r.removeGlobalTransport(e)
				},
				setGlobalOptions(e) {
					r.setGlobalOptions(e)
				},
				getLogger(e, t, n) {
					const i = new r(a, e, t, n)
					return e ? ((s[e] = s[e] || []), s[e].push(i)) : o.push(i), i
				},
				setLogLevelById(e, t) {
					for (let n = t ? s[t] || [] : o, r = 0; r < n.length; r++) { n[r].setLevel(e) }
				},
				setLogLevel(e) {
					a = e
					for (var t = 0; t < o.length; t++) o[t].setLevel(e)
					for (const n in s) {
						const r = s[n] || []
						for (t = 0; t < r.length; t++) r[t].setLevel(e)
					}
				},
				levels: r.levels,
				LogCollector: i,
			}
		},
		function(e, t, n) {
			e.exports = n(8).default
		},
		function(e, t, n) {
			const r = n(1)
			/**
			 * @param e
			 * @param t
			 */
			function i(e, t) {
				(this.logStorage = e),
				(this.stringifyObjects
						= !(!t || !t.stringifyObjects) && t.stringifyObjects),
				(this.storeInterval = t && t.storeInterval ? t.storeInterval : 3e4),
				(this.maxEntryLength
						= t && t.maxEntryLength ? t.maxEntryLength : 1e4),
				Object.keys(r.levels).forEach(
					function(e) {
						this[r.levels[e]] = function() {
							this._log.apply(this, arguments)
						}.bind(this, e)
					}.bind(this)
				),
				(this.storeLogsIntervalID = null),
				(this.queue = []),
				(this.totalLen = 0),
				(this.outputCache = [])
			}
			(i.prototype.stringify = function(e) {
				try {
					return JSON.stringify(e)
				} catch (e) {
					return '[object with circular refs?]'
				}
			}),
			(i.prototype.formatLogMessage = function(e) {
				for (var t = '', n = 1, i = arguments.length; n < i; n++) {
					let s = arguments[n];
					(!this.stringifyObjects && e !== r.levels.ERROR)
							|| typeof s !== 'object'
							|| (s = this.stringify(s)),
					(t += s),
					n !== i - 1 && (t += ' ')
				}
				return t.length ? t : null
			}),
			(i.prototype._log = function() {
				const e = arguments[1]
				const t = this.formatLogMessage.apply(this, arguments)
				if (t) {
					const n = this.queue[this.queue.length - 1]
					const r = n && n.text
					r === t
						? (n.count += 1)
						: (this.queue.push({ text: t, timestamp: e, count: 1 }),
							  (this.totalLen += t.length))
				}
				this.totalLen >= this.maxEntryLength && this._flush(!0, !0)
			}),
			(i.prototype.start = function() {
				this._reschedulePublishInterval()
			}),
			(i.prototype._reschedulePublishInterval = function() {
				this.storeLogsIntervalID
						&& (window.clearTimeout(this.storeLogsIntervalID),
						(this.storeLogsIntervalID = null)),
				(this.storeLogsIntervalID = window.setTimeout(
					this._flush.bind(this, !1, !0),
					this.storeInterval
				))
			}),
			(i.prototype.flush = function() {
				this._flush(!1, !0)
			}),
			(i.prototype._flush = function(e, t) {
				this.totalLen > 0
						&& (this.logStorage.isReady() || e)
						&& (this.logStorage.isReady()
							? (this.outputCache.length
									&& (this.outputCache.forEach(
										function(e) {
											this.logStorage.storeLogs(e)
										}.bind(this)
									),
									(this.outputCache = [])),
							  this.logStorage.storeLogs(this.queue))
							: this.outputCache.push(this.queue),
						(this.queue = []),
						(this.totalLen = 0)),
				t && this._reschedulePublishInterval()
			}),
			(i.prototype.stop = function() {
				this._flush(!1, !1)
			}),
			(e.exports = i)
		},
		function(e, t, n) {
			'use strict'
			n.r(t),
			n.d(t, 'default', function() {
				return k
			})
			const r = n(2)
			const i = n.n(r)
			/**
			 * @param e
			 * @param t
			 * @param n
			 */
			function s(e, t = !1, n = 'hash') {
				const r = n === 'search' ? e.search : e.hash
				const i = {}
				const s = (r && r.substr(1).split('&')) || []
				if (n === 'hash' && s.length === 1) {
					const e = s[0]
					if (e.startsWith('/') && e.split('&').length === 1) return i
				}
				return (
					s.forEach((e) => {
						const n = e.split('=')
						const r = n[0]
						if (!r) return
						let s
						try {
							if (((s = n[1]), !t)) {
								const e = decodeURIComponent(s).replace(/\\&/, '&')
								s = e === 'undefined' ? void 0 : JSON.parse(e)
							}
						} catch (e) {
							return void (function(e, t = '') {
								console.error(t, e),
								window.onerror && window.onerror(t, null, null, null, e)
							})(e, 'Failed to parse URL parameter value: ' + String(s))
						}
						i[r] = s
					}),
					i
				)
			}
			/**
			 * @param e
			 */
			function o(e) {
				const t = new RegExp('^([a-z][a-z0-9\\.\\+-]*:)+', 'gi')
				const n = t.exec(e)
				if (n) {
					let r = n[n.length - 1].toLowerCase()
					r !== 'http:' && r !== 'https:' && (r = 'https:'),
					(e = e.substring(t.lastIndex)).startsWith('//') && (e = r + e)
				}
				return e
			}
			/**
			 * @param e
			 */
			function a(e = {}) {
				const t = []
				for (const n in e) {
					try {
						t.push(`${n}=${encodeURIComponent(JSON.stringify(e[n]))}`)
					} catch (e) {
						console.warn(`Error encoding ${n}: ${e}`)
					}
				}
				return t
			}
			/**
			 * @param e
			 */
			function c(e) {
				const t = { toString: l }
				let n, r, i
				if (
					((e = e.replace(/\s/g, '')),
					(n = new RegExp('^([a-z][a-z0-9\\.\\+-]*:)', 'gi')),
					(r = n.exec(e)),
					r
						&& ((t.protocol = r[1].toLowerCase()), (e = e.substring(n.lastIndex))),
					(n = new RegExp('^(//[^/?#]+)', 'gi')),
					(r = n.exec(e)),
					r)
				) {
					let i = r[1].substring(2)
					e = e.substring(n.lastIndex)
					const s = i.indexOf('@')
					s !== -1 && (i = i.substring(s + 1)), (t.host = i)
					const o = i.lastIndexOf(':')
					o !== -1 && ((t.port = i.substring(o + 1)), (i = i.substring(0, o))),
					(t.hostname = i)
				}
				if (
					((n = new RegExp('^([^?#]*)', 'gi')),
					(r = n.exec(e)),
					r && ((i = r[1]), (e = e.substring(n.lastIndex))),
					i ? i.startsWith('/') || (i = '/' + i) : (i = '/'),
					(t.pathname = i),
					e.startsWith('?'))
				) {
					let n = e.indexOf('#', 1)
					n === -1 && (n = e.length),
					(t.search = e.substring(0, n)),
					(e = e.substring(n))
				} else t.search = ''
				return (t.hash = e.startsWith('#') ? e : ''), t
			}
			/**
			 * @param e
			 */
			function l(e) {
				const {
					hash: t,
					host: n,
					pathname: r,
					protocol: i,
					search: s,
				} = e || this
				let o = ''
				return (
					i && (o += i),
					n && (o += '//' + n),
					(o += r || '/'),
					s && (o += s),
					t && (o += t),
					o
				)
			}
			/**
			 * @param e
			 */
			function u(e) {
				let t
				t
					= e.serverURL && e.room
						? new URL(e.room, e.serverURL).toString()
						: e.room
							? e.room
							: e.url || ''
				const n = c(o(t))
				if (!n.protocol) {
					let t = e.protocol || e.scheme
					t && (t.endsWith(':') || (t += ':'), (n.protocol = t))
				}
				let { pathname: r } = n
				if (!n.host) {
					const t = e.domain || e.host || e.hostname
					if (t) {
						const {
							host: e,
							hostname: i,
							pathname: s,
							port: a,
						} = c(o('org.jitsi.meet://' + t))
						e && ((n.host = e), (n.hostname = i), (n.port = a)),
						r === '/' && s !== '/' && (r = s)
					}
				}
				const i = e.roomName || e.room
				!i
					|| (!n.pathname.endsWith('/') && n.pathname.endsWith('/' + i))
					|| (r.endsWith('/') || (r += '/'), (r += i)),
				(n.pathname = r)
				const { jwt: s } = e
				if (s) {
					let { search: e } = n
					e.indexOf('?jwt=') === -1
						&& e.indexOf('&jwt=') === -1
						&& (e.startsWith('?') || (e = '?' + e),
						e.length === 1 || (e += '&'),
						(e += 'jwt=' + s),
						(n.search = e))
				}
				let { hash: l } = n
				for (const t of ['config', 'interfaceConfig', 'devices', 'userInfo']) {
					const n = a(e[t + 'Overwrite'] || e[t] || e[t + 'Override'])
					if (n.length) {
						let e = `${t}.${n.join(`&${t}.`)}`
						l.length ? (e = '&' + e) : (l = '#'), (l += e)
					}
				}
				return (n.hash = l), n.toString() || void 0
			}
			const d = n(3)
			const h = n.n(d)
			/**
			 * @param e
			 * @param t
			 * @param n
			 */
			function p(e, t, n) {
				return (
					t in e
						? Object.defineProperty(e, t, {
							value: n,
							enumerable: !0,
							configurable: !0,
							writable: !0,
						  })
						: (e[t] = n),
					e
				)
			}
			const f = { window: window.opener || window.parent }
			class m {

				constructor({ postisOptions: e } = {}) {
					(this.postis = h()(
						(function(e) {
							for (let t = 1; t < arguments.length; t++) {
								var n = arguments[t] != null ? arguments[t] : {}
								let r = Object.keys(n)
								typeof Object.getOwnPropertySymbols === 'function'
									&& (r = r.concat(
										Object.getOwnPropertySymbols(n).filter(function(e) {
											return Object.getOwnPropertyDescriptor(n, e).enumerable
										})
									)),
								r.forEach(function(t) {
									p(e, t, n[t])
								})
							}
							return e
						})({}, f, e)
					)),
					(this._receiveCallback = () => {}),
					this.postis.listen('message', (e) => this._receiveCallback(e))
				}

				dispose() {
					this.postis.destroy()
				}

				send(e) {
					this.postis.send({ method: 'message', params: e })
				}

				setReceiveCallback(e) {
					this._receiveCallback = e
				}

			}
			class v {

				constructor({ backend: e } = {}) {
					(this._listeners = new Map()),
					(this._requestID = 0),
					(this._responseHandlers = new Map()),
					(this._unprocessedMessages = new Set()),
					(this.addListener = this.on),
					e && this.setBackend(e)
				}

				_disposeBackend() {
					this._backend && (this._backend.dispose(), (this._backend = null))
				}

				_onMessageReceived(e) {
					if (e.type === 'response') {
						const t = this._responseHandlers.get(e.id)
						t && (t(e), this._responseHandlers.delete(e.id))
					} else {
						e.type === 'request'
							? this.emit('request', e.data, (t, n) => {
								this._backend.send({
									type: 'response',
									error: n,
									id: e.id,
									result: t,
								})
							  })
							: this.emit('event', e.data)
					}
				}

				dispose() {
					this._responseHandlers.clear(),
					this._unprocessedMessages.clear(),
					this.removeAllListeners(),
					this._disposeBackend()
				}

				emit(e, ...t) {
					const n = this._listeners.get(e)
					let r = !1
					return (
						n
							&& n.size
							&& n.forEach((e) => {
								r = e(...t) || r
							}),
						r || this._unprocessedMessages.add(t),
						r
					)
				}

				on(e, t) {
					let n = this._listeners.get(e)
					return (
						n || ((n = new Set()), this._listeners.set(e, n)),
						n.add(t),
						this._unprocessedMessages.forEach((e) => {
							t(...e) && this._unprocessedMessages.delete(e)
						}),
						this
					)
				}

				removeAllListeners(e) {
					return e ? this._listeners.delete(e) : this._listeners.clear(), this
				}

				removeListener(e, t) {
					const n = this._listeners.get(e)
					return n && n.delete(t), this
				}

				sendEvent(e = {}) {
					this._backend && this._backend.send({ type: 'event', data: e })
				}

				sendRequest(e) {
					if (!this._backend) { return Promise.reject(new Error('No transport backend defined!')) }
					this._requestID++
					const t = this._requestID
					return new Promise((n, r) => {
						this._responseHandlers.set(t, ({ error: e, result: t }) => {
							void 0 !== t
								? n(t)
								: r(
									void 0 !== e ? e : new Error('Unexpected response format!')
								  )
						}),
						this._backend.send({ type: 'request', data: e, id: t })
					})
				}

				setBackend(e) {
					this._disposeBackend(),
					(this._backend = e),
					this._backend.setReceiveCallback(
						this._onMessageReceived.bind(this)
					)
				}

			}
			const g = s(window.location).jitsi_meet_external_api_id
			const y = {}
			let _
			typeof g === 'number' && (y.scope = 'jitsi_meet_external_api_' + g),
			((window.JitsiMeetJS || (window.JitsiMeetJS = {}),
			window.JitsiMeetJS.app || (window.JitsiMeetJS.app = {}),
			window.JitsiMeetJS.app).setExternalTransportBackend = (e) =>
				_.setBackend(e))
			const b = n(4)
			const w = n(0)
			/**
			 * @param e
			 * @param t
			 */
			function L(e, t) {
				if (e == null) return {}
				let n
				let r
				const i = (function(e, t) {
					if (e == null) return {}
					let n
					let r
					const i = {}
					const s = Object.keys(e)
					for (r = 0; r < s.length; r++) { (n = s[r]), t.indexOf(n) >= 0 || (i[n] = e[n]) }
					return i
				})(e, t)
				if (Object.getOwnPropertySymbols) {
					const s = Object.getOwnPropertySymbols(e)
					for (r = 0; r < s.length; r++) {
						(n = s[r]),
						t.indexOf(n) >= 0
								|| (Object.prototype.propertyIsEnumerable.call(e, n)
									&& (i[n] = e[n]))
					}
				}
				return i
			}
			/**
			 * @param e
			 * @param t
			 * @param n
			 */
			function O(e, t, n) {
				return (
					t in e
						? Object.defineProperty(e, t, {
							value: n,
							enumerable: !0,
							configurable: !0,
							writable: !0,
						  })
						: (e[t] = n),
					e
				)
			}
			const x = ['css/all.css', 'libs/alwaysontop.min.js']
			const j = {
				avatarUrl: 'avatar-url',
				displayName: 'display-name',
				e2eeKey: 'e2ee-key',
				email: 'email',
				hangup: 'video-hangup',
				muteEveryone: 'mute-everyone',
				password: 'password',
				sendEndpointTextMessage: 'send-endpoint-text-message',
				sendTones: 'send-tones',
				setVideoQuality: 'set-video-quality',
				startRecording: 'start-recording',
				stopRecording: 'stop-recording',
				subject: 'subject',
				submitFeedback: 'submit-feedback',
				toggleAudio: 'toggle-audio',
				toggleChat: 'toggle-chat',
				toggleFilmStrip: 'toggle-film-strip',
				toggleShareScreen: 'toggle-share-screen',
				toggleTileView: 'toggle-tile-view',
				toggleVideo: 'toggle-video',
			}
			const E = {
				'avatar-changed': 'avatarChanged',
				'audio-availability-changed': 'audioAvailabilityChanged',
				'audio-mute-status-changed': 'audioMuteStatusChanged',
				'camera-error': 'cameraError',
				'device-list-changed': 'deviceListChanged',
				'display-name-change': 'displayNameChange',
				'email-change': 'emailChange',
				'endpoint-text-message-received': 'endpointTextMessageReceived',
				'feedback-submitted': 'feedbackSubmitted',
				'feedback-prompt-displayed': 'feedbackPromptDisplayed',
				'filmstrip-display-changed': 'filmstripDisplayChanged',
				'incoming-message': 'incomingMessage',
				'mic-error': 'micError',
				'outgoing-message': 'outgoingMessage',
				'participant-joined': 'participantJoined',
				'participant-kicked-out': 'participantKickedOut',
				'participant-left': 'participantLeft',
				'participant-role-changed': 'participantRoleChanged',
				'password-required': 'passwordRequired',
				'proxy-connection-event': 'proxyConnectionEvent',
				'video-ready-to-close': 'readyToClose',
				'video-conference-joined': 'videoConferenceJoined',
				'video-conference-left': 'videoConferenceLeft',
				'video-availability-changed': 'videoAvailabilityChanged',
				'video-mute-status-changed': 'videoMuteStatusChanged',
				'screen-sharing-status-changed': 'screenSharingStatusChanged',
				'dominant-speaker-changed': 'dominantSpeakerChanged',
				'subject-change': 'subjectChange',
				'suspend-detected': 'suspendDetected',
				'tile-view-changed': 'tileViewChanged',
			}
			let C = 0
			/**
			 * @param e
			 * @param t
			 */
			function S(e, t) {
				e._numberOfParticipants += t
			}
			/**
			 * @param e
			 * @param t
			 */
			function I(e, t = {}) {
				return u(
					(function(e) {
						for (let t = 1; t < arguments.length; t++) {
							var n = arguments[t] != null ? arguments[t] : {}
							let r = Object.keys(n)
							typeof Object.getOwnPropertySymbols === 'function'
								&& (r = r.concat(
									Object.getOwnPropertySymbols(n).filter(function(e) {
										return Object.getOwnPropertyDescriptor(n, e).enumerable
									})
								)),
							r.forEach(function(t) {
								O(e, t, n[t])
							})
						}
						return e
					})({}, t, {
						url: `${
							t.noSSL ? 'http' : 'https'
						}://${e}/#jitsi_meet_external_api_id=${C}`,
					})
				)
			}
			/**
			 * @param e
			 */
			function R(e) {
				let t
				return (
					typeof e === 'string'
					&& String(e).match(/([0-9]*\.?[0-9]+)(em|pt|px|%)$/) !== null
						? (t = e)
						: typeof e === 'number' && (t = e + 'px'),
					t
				)
			}
			class k extends i.a {

				constructor(e, ...t) {
					super()
					const {
						roomName: n = '',
						width: r = '100%',
						height: i = '100%',
						parentNode: s = document.body,
						configOverwrite: o = {},
						interfaceConfigOverwrite: a = {},
						noSSL: c = !1,
						jwt: l,
						onload: u,
						invitees: d,
						devices: h,
						userInfo: p,
						e2eeKey: f,
					} = (function(e) {
						if (!e.length) return {}
						switch (typeof e[0]) {
						case 'string':
						case void 0: {
							const [t, n, r, i, s, o, a, c, l] = e
							return {
								roomName: t,
								width: n,
								height: r,
								parentNode: i,
								configOverwrite: s,
								interfaceConfigOverwrite: o,
								noSSL: a,
								jwt: c,
								onload: l,
							}
						}
						case 'object':
							return e[0]
						default:
							throw new Error("Can't parse the arguments!")
						}
					})(t);
					(this._parentNode = s),
					(this._url = I(e, {
						configOverwrite: o,
						interfaceConfigOverwrite: a,
						jwt: l,
						noSSL: c,
						roomName: n,
						devices: h,
						userInfo: p,
					})),
					this._createIFrame(i, r, u),
					(this._transport = new v({
						backend: new m({
							postisOptions: {
								scope: 'jitsi_meet_external_api_' + C,
								window: this._frame.contentWindow,
							},
						}),
					})),
					Array.isArray(d) && d.length > 0 && this.invite(d),
					(this._tmpE2EEKey = f),
					(this._isLargeVideoVisible = !0),
					(this._numberOfParticipants = 0),
					(this._participants = {}),
					(this._myUserID = void 0),
					(this._onStageParticipant = void 0),
					this._setupListeners(),
					C++
				}

				_createIFrame(e, t, n) {
					const r = 'jitsiConferenceFrame' + C;
					(this._frame = document.createElement('iframe')),
					(this._frame.allow = 'camera; microphone; display-capture'),
					(this._frame.src = this._url),
					(this._frame.name = r),
					(this._frame.id = r),
					this._setSize(e, t),
					this._frame.setAttribute('allowFullScreen', 'true'),
					(this._frame.style.border = 0),
					n && (this._frame.onload = n),
					(this._frame = this._parentNode.appendChild(this._frame))
				}

				_getAlwaysOnTopResources() {
					const e = this._frame.contentWindow
					const t = e.document
					let n = ''
					const r = t.querySelector('base')
					if (r && r.href) n = r.href
					else {
						const { protocol: t, host: r } = e.location
						n = `${t}//${r}`
					}
					return x.map((e) => new URL(e, n).href)
				}

				_getOnStageParticipant() {
					return this._onStageParticipant
				}

				_getLargeVideo() {
					const e = this.getIFrame()
					if (
						this._isLargeVideoVisible
						&& e
						&& e.contentWindow
						&& e.contentWindow.document
					) { return e.contentWindow.document.getElementById('largeVideo') }
				}

				_getParticipantVideo(e) {
					const t = this.getIFrame()
					if (t && t.contentWindow && t.contentWindow.document) {
						return void 0 === e || e === this._myUserID
							? t.contentWindow.document.getElementById('localVideo_container')
							: t.contentWindow.document.querySelector(
								`#participant_${e} video`
							  )
					}
				}

				_setSize(e, t) {
					const n = R(e)
					const r = R(t)
					void 0 !== n && (this._frame.style.height = n),
					void 0 !== r && (this._frame.style.width = r)
				}

				_setupListeners() {
					this._transport.on('event', (e) => {
						const { name: t } = e
						const n = L(e, ['name'])
						const r = n.id
						switch (t) {
						case 'video-conference-joined':
							void 0 !== this._tmpE2EEKey
									&& (this.executeCommand(j.e2eeKey, this._tmpE2EEKey),
									(this._tmpE2EEKey = void 0)),
							(this._myUserID = r),
							(this._participants[r] = { avatarURL: n.avatarURL })
						case 'participant-joined':
							(this._participants[r] = this._participants[r] || {}),
							(this._participants[r].displayName = n.displayName),
							(this._participants[r].formattedDisplayName
										= n.formattedDisplayName),
							S(this, 1)
							break
						case 'participant-left':
							S(this, -1), delete this._participants[r]
							break
						case 'display-name-change': {
							const e = this._participants[r]
							e
									&& ((e.displayName = n.displayname),
									(e.formattedDisplayName = n.formattedDisplayName))
							break
						}
						case 'email-change': {
							const e = this._participants[r]
							e && (e.email = n.email)
							break
						}
						case 'avatar-changed': {
							const e = this._participants[r]
							e && (e.avatarURL = n.avatarURL)
							break
						}
						case 'on-stage-participant-changed':
							(this._onStageParticipant = r), this.emit('largeVideoChanged')
							break
						case 'large-video-visibility-changed':
							(this._isLargeVideoVisible = n.isVisible),
							this.emit('largeVideoChanged')
							break
						case 'video-conference-left':
							S(this, -1), delete this._participants[this._myUserID]
						}
						const i = E[t]
						return !!i && (this.emit(i, n), !0)
					})
				}

				addEventListener(e, t) {
					this.on(e, t)
				}

				addEventListeners(e) {
					for (const t in e) this.addEventListener(t, e[t])
				}

				dispose() {
					this.emit('_willDispose'),
					this._transport.dispose(),
					this.removeAllListeners(),
					this._frame
							&& this._frame.parentNode
							&& this._frame.parentNode.removeChild(this._frame)
				}

				executeCommand(e, ...t) {
					e in j
						? this._transport.sendEvent({ data: t, name: j[e] })
						: console.error('Not supported command name.')
				}

				executeCommands(e) {
					for (const t in e) this.executeCommand(t, e[t])
				}

				getAvailableDevices() {
					return Object(w.a)(this._transport)
				}

				getCurrentDevices() {
					return Object(w.b)(this._transport)
				}

				isAudioAvailable() {
					return this._transport.sendRequest({ name: 'is-audio-available' })
				}

				isDeviceChangeAvailable(e) {
					return Object(w.c)(this._transport, e)
				}

				isDeviceListAvailable() {
					return Object(w.d)(this._transport)
				}

				isMultipleAudioInputSupported() {
					return Object(w.e)(this._transport)
				}

				invite(e) {
					return Array.isArray(e) && e.length !== 0
						? this._transport.sendRequest({ name: 'invite', invitees: e })
						: Promise.reject(new TypeError('Invalid Argument'))
				}

				isAudioMuted() {
					return this._transport.sendRequest({ name: 'is-audio-muted' })
				}

				isSharingScreen() {
					return this._transport.sendRequest({ name: 'is-sharing-screen' })
				}

				getAvatarURL(e) {
					const { avatarURL: t } = this._participants[e] || {}
					return t
				}

				getDisplayName(e) {
					const { displayName: t } = this._participants[e] || {}
					return t
				}

				getEmail(e) {
					const { email: t } = this._participants[e] || {}
					return t
				}

				_getFormattedDisplayName(e) {
					const { formattedDisplayName: t } = this._participants[e] || {}
					return t
				}

				getIFrame() {
					return this._frame
				}

				getNumberOfParticipants() {
					return this._numberOfParticipants
				}

				isVideoAvailable() {
					return this._transport.sendRequest({ name: 'is-video-available' })
				}

				isVideoMuted() {
					return this._transport.sendRequest({ name: 'is-video-muted' })
				}

				removeEventListener(e) {
					this.removeAllListeners(e)
				}

				removeEventListeners(e) {
					e.forEach((e) => this.removeEventListener(e))
				}

				sendProxyConnectionEvent(e) {
					this._transport.sendEvent({
						data: [e],
						name: 'proxy-connection-event',
					})
				}

				setAudioInputDevice(e, t) {
					return Object(w.f)(this._transport, e, t)
				}

				setAudioOutputDevice(e, t) {
					return Object(w.g)(this._transport, e, t)
				}

				setVideoInputDevice(e, t) {
					return Object(w.h)(this._transport, e, t)
				}

				_getElectronPopupsConfig() {
					return Promise.resolve(b)
				}

			}
		},
	])
})
// # sourceMappingURL=external_api.min.map
