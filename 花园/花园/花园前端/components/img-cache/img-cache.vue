<template>
	<view class="imgbg">
		<!-- <image src="../../static/images/loading1.png" mode=""></image> -->
		<image class="img-cache" :src="resource" :mode="mode" :lazy-load="lazyLoad" :fade-show="fadeShow" :webp="webp"
		 :show-menu-by-longpress="showMenuByLongpress" :style="[style]" @click="fnEvent('click', $event)" @error="fnEvent('error', $event)"
		 @load="fnEvent('load', $event)">
		</image>
	</view>
</template>

<script>
	// #ifdef APP-PLUS
	import storage from './storage'
	import download from './download'
	import {
		resolveFile
	} from './index'
	// #endif

	/**
	 * ImgCache 图片缓存
	 * @description APP端图片缓存
	 * @tutorial https://ext.dcloud.net.cn/plugin?id=2515
	 * @property {string} bgSrc 站位图片
	 * @property {string} src 图片资源地址
	 * @property {string} mode 图片裁剪、缩放的模式
	 * @property {boolean} lazyLoad 是否图片懒加载
	 * @property {boolean} fade-show 图片显示动画效果
	 * @property {boolean} webp 默认不解析 webP 格式，只支持网络资源
	 * @property {boolean} show-menu-by-longpress 开启长按图片显示识别小程序码菜单
	 * @property {string} dir 缓存的文件目录，文件夹开头不能有_
	 * @property {string} width 宽度，单位任意，如果为数值，则为 rpx 单位
	 * @property {string} height 高度，单位任意，如果为数值，则为 rpx 单位
	 * @property {object} custom-style 自定义样式
	 * @event {Function} click 点击图片
	 * @event {Function} error 错误发生
	 * @event {Function} load 图片载入完毕
	 * @example <img-cache v-if="imgs" :src="url + imgs" :customStyle="customStyle" width="218rpx" height="218rpx" mode="aspectFill" bgSrc="../../static/images/loading1.png"></img-cache>
	 */
	export default {
		name: 'ImgCache',
		props: {
			bgSrc: {
				type: String,
				default: ""
			},
			src: {
				type: String
			},
			mode: {
				type: String,
				default: 'aspectFill'
			},
			lazyLoad: {
				type: Boolean,
				default: true
			},
			fadeShow: {
				type: Boolean,
				default: true
			},
			webp: {
				type: Boolean,
				default: false
			},
			showMenuByLongpress: {
				type: Boolean,
				default: false
			},
			dir: {
				type: String,
				default: 'imgcache'
			},
			width: {
				type: [String, Number]
			},
			height: {
				type: [String, Number]
			},
			customStyle: {
				type: Object,
				default: () => ({})
			}
		},
		data() {
			return {
				resource: ''
			}
		},
		computed: {
			style() {
				let style = {
					willChange: 'transform',
					backgroundImage: `url(${this.bgSrc})`,
					// backgroundImage: 'url(../../static/images/loading1.png)',
					overflow: "hidden"
				}

				// 判断传过来的值不为 undefined null ''
				if ((this.width ?? '') !== '') style.width = this.addUnit(this.width)
				if ((this.height ?? '') !== '') style.height = this.addUnit(this.height)
				return {
					...style,
					...this.customStyle
				}
			}
		},
		watch: {
			src: {
				handler: 'init',
				immediate: true
			}
		},
		methods: {
			// 初始化
			init() {
				// #ifdef APP-PLUS
				this.fnCache()
				// #endif

				// #ifndef APP-PLUS
				this.setSrc()
				// #endif
			},
			// 获取缓存
			async fnCache() {
				const url = this.src // 赋值到新变量，避免下载时 src 更改，从而网络地址和本地地址图片不一致

				if (!/^https?:\/\//.test(url)) return this.setSrc() // 判断是否网络地址

				const [select] = storage.select({
					url
				}) // 查询缓存是否存在

				if (select) {
					const path = select.local
					if (await resolveFile(path)) return this.setSrc(path) // 判断本地文件是否存在 如果存在则显示本地文件
					storage.delete(select) // 如果本地文件不存在则删除缓存数据
				}
				this.setSrc()

				const local = await download(url, this.dir) // 下载文件
				if (local) storage.insert({
					url,
					local
				}) // 缓存数据
			},
			// 发送事件
			fnEvent(emit, event) {
				this.$emit(emit, event)
			},
			// 设置图片资源地址
			setSrc(src) {
				this.resource = src || this.src
			},
			// 添加单位，如果为数值则为rpx单位，否则直接返回
			addUnit(value) {
				value = String(value ?? '')
				return /^(?:-?\d+|-?\d{1,3}(?:,\d{3})+)?(?:\.\d+)?$/.test(value) ? `${value}rpx` : value
			}
		}
	}
</script>
<style>
	.imgbg {
		background-position: center;
		background-size: cover;
	}

	.img-cache {
		display: block;
		background-position: center;
		background-size: cover;
	}
</style>
