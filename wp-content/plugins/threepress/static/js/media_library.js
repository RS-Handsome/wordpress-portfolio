import * as lib from './lib.js?v=162'
import ThreepressGallery from "./ThreepressGallery.js?v=162"


// console.log('init media lib')


const init = () => {
	/*
		return basic thumbnails
	*/
	return new Promise((resolve, reject ) => {
		let c = 0
		let last_length
		let waiting = setInterval(() => {
			const thumbs = document.querySelectorAll('.attachments-wrapper .thumbnail')
			if( thumbs.length && last_length === thumbs.length ){
				clearInterval( waiting )
				if( thumbs.length ){ // init; we have efficiently waited for thumbs to bind:
					resolve( thumbs )
				}
			}
			if( c > 20 ){
				clearInterval( waiting )
				reject('no media library entries found for Threepress')
			}
			last_length = thumbs.length
			c++
		}, 300 )
	})
}


let wrapper
const add_toggle = async( file_url ) => {
	const state = localStorage.getItem('threepress-preview-ml') === 'true'
	// bind to DOM
	const container = await new Promise((resolve, reject) => {
		let i = 0
		let waiting = setInterval(() => {
			const c = document.querySelector('.media-modal-content')
			if( c ){
				clearInterval( waiting )
				resolve( c )
			}
			if( i > 20 ){
				clearInterval( waiting )
				resolve( false )
			}
			i++
		}, 200 )
	}) 
	if( !container ) return console.log('Threepress: no container for toggle')

	// toggle
	if( wrapper ){ // only needs built once
		// set state
		wrapper.querySelector('input[type=checkbox]').checked = state
		console.log('returned early; already got toggle wrapper')
		container.append( wrapper )
		return wrapper
	}else{
		wrapper = document.createElement('div')
		wrapper.id = 'threepress-preview-toggle'
		const label = document.createElement('label')
		label.innerText = 'load Threepress model previews? (glb only)'
		wrapper.append( label )
		const toggle = document.createElement('input')
		toggle.classList.add('threepress-input')
		toggle.type = 'checkbox'
		toggle.checked = state
		toggle.addEventListener('click', () => {
			const value = toggle.checked ? 'true' : 'false'
			localStorage.setItem('threepress-preview-ml', value )
			if( !toggle.checked ){
				const close = document.querySelector('.media-modal-content .threepress-modal-close')
				if( close ) close.click()
			}else{
				// hal('success', '(reload preview to see model)', 5000 )
				show_modal( file_url, container )
			}
		})
		wrapper.append( toggle )
		// append
		container.append( wrapper )
		return wrapper
	}
}

const wait_for = ( selector, speed, limit ) => {
	limit = limit || 10
	speed = speed || 100
	let c = 0
	return new Promise((resolve, reject ) => {
		let waiting = setInterval(() => {
			const ele = document.querySelector( selector )
			if( ele ){
				clearInterval( waiting )
				resolve( ele )
			}
			if( c > limit ) resolve(false)
			c++
		}, speed )
	})
}


const get_media_window = async() => {
	return new Promise((resolve, reject) => {
		let c = 0
		let waiting = setInterval(() => {
			const modal = document.querySelector('.media-modal-content')
			const bounds = modal.getBoundingClientRect()
			if( bounds.width ){
				clearInterval(waiting)
				resolve( modal )
			}else if( c > 20 ){
				clearInterval(waiting)
				resolve(false)
			}
			c++
		}, 100 )
	})
}


const show_modal = ( url, modal ) => {

	const gallery_preview = ThreepressGallery({
		preview_type: 'media_lib',
		// model_row: document.createElement('div'),
		model: { 
			guid: url, // file_input.value,
			// input.value.trim(),
		},
		name: lib.random_hex(6),
		rotate_scene: true,
		bg_color: 'linear-gradient(45deg,white,transparent)',
		controls: 'orbit',
		form: document.createElement('form'),// obj.form,
	})
	gallery_preview.preview()

	let bounds
	let closing = setInterval(() => {
		bounds = modal.getBoundingClientRect()
		if( !bounds.width ){
			clearInterval( closing )
			modal.querySelector('.threepress-modal-close').click()
			console.log('closing model modal..')
		}else{
			console.log('still got modal')
		}
	}, 300 )

}






// init
;(async() => {

	// make sure we're on media library
	if( !location.href.match(/wp-admin\/upload\.php/) ) return

	// wait till thumbs loaded
	const thumbs = await init()
	if( !thumbs.length ) return console.log('Threepress: skipping model previews; no gallery items')

	for( const thumb of thumbs ){
		thumb.addEventListener('click', async() => {

			let halt

			// get window
			const modal = await get_media_window()
			if( !modal ) halt = 'no media window found'

			// get input with file
			const file_input = await wait_for( '.setting[data-setting="url"] input', 200, 10 )
			if( !file_input ) halt = 'no fileURL found'

			// get file value
			const text = file_input.value
			if( !text.match(/glb/) ) halt = 'no glb found for preview' // && !text.match(/gltf/)

			let toggle

			// halt
			if( halt ){
				toggle = modal.querySelector('#threepress-preview-toggle')
				if( toggle ) toggle.remove()
				return console.log( 'Threepress: ' + halt )
			}

			// check toggle
			toggle = await add_toggle( file_input.value ) // allow !== 'false'
			if( !toggle.querySelector('input').checked ) return console.log('Threepress: no preview selected')

			// show
			show_modal( file_input.value, modal )

		})
	}
	
})();

export default {}