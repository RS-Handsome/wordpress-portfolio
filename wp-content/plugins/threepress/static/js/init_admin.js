// import hal from '../hal.js?v=162'

import ThreepressGallery from './ThreepressGallery.js?v=162'
import {
	ModelRow,
	fetch_wrap,
	tstack,
	hal,
	// format_date,
	// get_domain,
} from './lib.js?v=162'

import build_form from './build_form.js?v=162'


tstack('init_admin')





//--------------------------------------------------------------- declare vars

const wrap = document.querySelector('.wrap.threepress')

const sections = wrap.querySelectorAll('.threepress .section')
const ext_sections = wrap.querySelectorAll('.threepress .ext-section')
const tabs = wrap.querySelectorAll('.nav-tab.main-tab')
const ext_tabs = wrap.querySelectorAll('.ext-nav-tab')

// toggle action areas
const add_model = wrap.querySelector('#add-toggle')
const upload_types = wrap.querySelector('#threepress-upload-types')
const add_gallery = wrap.querySelector('#create-toggle')


// action buttons
const model_upload = wrap.querySelector('form#upload-model')
const browse_threepress = wrap.querySelector('form#browse-threepress')

// content areas
const model_library = wrap.querySelector('#model-library')
const library_content = model_library.querySelector('.content')
const model_galleries = wrap.querySelector('#model-galleries')
const galleries_content = model_galleries.querySelector('.content')
const worlds_content = wrap.querySelector('#tab-worlds')


const gallery_container = document.querySelector('#gallery-container')













//--------------------------------------------------------------- declare functions & classes





const fill = async( type, scroll_top ) => {

	loaded[ type ] = true

	const res = await fetch_wrap( ajaxurl, 'post', {
		action: 'fill_' + type,
	}, false )

	switch( type ){

		case 'library':
			if( !res || !res.length ){
				library_content.innerHTML = 'no models uploaded - only glb files supported'
				return
			}

			// console.log( res )
			for( const post of res ){
				// console.log('model row: ', post )
				const model = new ModelRow( post )
				model.form = gallery_admin.form
				library_content.append( model.gen_row() )
			}
			break;

		case 'gallery':
			if( !res || !res.length ){
				galleries_content.innerHTML = 'no galleries yet'
				return
			}
			// console.log( res )
			for( const g of res ){
				const gallery = ThreepressGallery( g )
				gallery.threeOrigin = 'fill'
				gallery.form = gallery_admin.form
				gallery.ingest_shortcode( g.shortcode, 'admin' )
				const row = gallery.gen_row()
				galleries_content.append( row )
			}
			break;

		default: 
			console.log('unhandled fill: ', type )
			break;

	}

	if( scroll_top ) window.scroll({ top: 0, behavior: 'smooth' })

}









const build_game_row = game => {

	if( location.href.match(/localhost/) ){
		console.log( 'info to game row: ', game )
	}

	const row = document.createElement('div')
	row.classList.add('threepress-row')
	row.setAttribute('data-slug', game.slug )

	// const image = document.createElement('div')
	// image.classList.add('threepress-column')

	// const img = document.createElement('img')
	// img.src = THREEPRESS.ARCADE.URLS.https + '/resource/image/' + game.img_url

	// image.append( img )

	const name = document.createElement('div')
	name.classList.add('threepress-column')
	name.innerHTML = `<h3>world: ${game.name}</h3>`
	name.title = 'game name'

	const description = document.createElement('div')
	description.classList.add('threepress-column')
	description.innerHTML = game.description || 'no description'

	row.append( name )
	// row.append( image )
	row.append( description )

	let purchase_area = document.createElement('div')
	purchase_area.classList.add('threepress-column', 'threepress-purchase-area')

	if( game.remaining ){
		if( game.remaining > 0 ){
			const days = Math.floor( game.remaining / 1000 / 60/ 60/ 24 )
			purchase_area.innerHTML = `<div style="color: green">days remaining: ${ days }</div>
			<div>use shortcode <code>[threepress_world]</code> to embed on a page</div>
`
		}else{
			if( game.last_pay ){
				const exp = Math.floor( game.remaining / 1000 / 60 / 60/ 24 ) * -1
				purchase_area.innerHTML = `<div style="color: orange">expired for: ${ exp } days</div>`				
			}else{
				purchase_area.innerHTML = `<div style="color: orange">unknown last payment</div>`				
			}
		}
		row.classList.add('threepress-purchased')
	}else{
		purchase_area.innerHTML = `<div style="color: orange">not active this domain</div>`				
	}

	const link = document.createElement('a')
	link.href = THREEPRESS.ARCADE.URLS.https + '/' // + '?d=' + location.host
	link.target='_blank'
	link.classList.add('threepress-column', 'button')
	link.innerHTML = 'visit Threepress Arcade'
	purchase_area.append( link )

	const version = document.createElement('div')
	version.classList.add('clarification')
	version.innerHTML = `
	Server version: ${game.version}<br>
	Threepress version: ${THREEPRESS.version}<br>
	Keep plugin updated to ensure best performance.`
	purchase_area.append( version )

	row.append( purchase_area )

	return row 

}








const fill_worlds = async() => {

	// ---------- server messagin ------------
	/*
		no need to await
	*/

	fetch_wrap( THREEPRESS.ARCADE.URLS.https + '/game_messaging', 'get')
	.then( res => {
		if( res && res.success ){
			const messaging = document.getElementById('server-messaging')
			if( !messaging ){
				console.log('could not find server msg block')
				return
			}
			messaging.innerHTML = res.html
		}
		// console.log( res )
	})

	// ---------- the main funciton ----------

	loaded.world = true

	// const url = THREEPRESS.ARCADE.URLS.https + '/game_listing'

	// // console.log('requesting: ', url )

	// const res = await fetch_wrap( url, 'get')
	// if( !res ){
	// 	hal('error', 'error fetching worlds', 5 * 1000)
	// 	return
	// }

	// // console.log( 'fill worlds res', res )

	// if( res.success ){
	// 	if( res.worlds && res.worlds.length ){
	// 		for( const r of res.worlds ){
	// 			worlds_content.append( build_game_row( r ))
	// 		}		
	// 	}else{
	const expl = document.createElement('div')
	expl.innerHTML = `
<div style="max-width: 200px">
<img src="${ THREEPRESS.ARCADE.URLS.https }/resource/image/quaternius-chat.jpg">
</div>
<p>Threepress World is a shortcode that you can embed anywhere on your site to instantly support a 3d multiplayer world for visitors to your site.</p>
<p>The backend runs on Threepress Arcade server, where you will also get admin controls for your domain's world.</p>
<p>After pasting a verification code on your site, you will be able to embed <code>[threepress_world]</code> anywhere on your site.</p>
<p>Head to Threepress Arcade to get started for free:<br>
<a class='button' href="${ THREEPRESS.ARCADE.URLS.https }" target='_blank'>Threepress Arcade</a>
</p>
<p>Models are provided by <a href="https://quaternius.com" target="_blank">Quaternius</a></p>
`
	worlds_content.append( expl )
	// 	}
	// }else if( res.msg ){
	// 	hal('error', res.msg || 'failed to fetch worlds', 5 * 1000 )
	// }



}

























//--------------------------------------------------------------- bindings

const loaded = {
	library: false,
	gallery: false,
	game: false,
}



add_model.addEventListener('click', () => {
	if( !upload_types.style.display || upload_types.style.display === 'none' ){
		upload_types.style.display = 'initial'
		add_model.querySelector('div').innerText = '-'
	}else{
		upload_types.style.display = 'none'
		add_model.querySelector('div').innerText = '+'
	}
})

model_upload.addEventListener('submit', e => {
	e.preventDefault()
	window.location.assign( THREEPRESS.home_url + '/wp-admin/media-new.php')
})


browse_threepress.addEventListener('submit', e => {
	e.preventDefault()
})


add_gallery.addEventListener('click', () => {
	if( !gallery_admin.form.style.display || gallery_admin.form.style.display === 'none' ){
		gallery_admin.form.style.display = 'inline-block'
		gallery_admin.hydrate_form( gallery_admin.form, gallery_admin.shortcode, null, 'admin add gallery' )
	}else{
		if( confirm('clear the current form and start anew?')){
			const close = document.querySelector('#close-gallery')
			if( close ) close.click()
			setTimeout(() => {				
				const new_gallery = ThreepressGallery({
					form: gallery_admin.form,
					threeOrigin: 'add gallery button',
				})
				new_gallery.hydrate_form( gallery_admin.form, 'admin restart gallery' )
			}, 50 )
		}
	}
})




























//--------------------------------------------------------------- init
// if( !$ ){
// 	THREEPRESS.hal('error', 'threepress requires that jquery be enabled')
// 	return false
// }
const gallery_admin = ThreepressGallery({
	threeOrigin: 'gallery admin',
})
build_form( gallery_admin, galleries_content )
gallery_container.append( gallery_admin.form )


for( const tab of tabs ){
	tab.addEventListener('click', () => {
		for( const section of sections ){
			section.style.display = 'none'
		}
		for( const t of tabs ){
			t.classList.remove('selected')
		}
		tab.classList.add('selected')
		const cat = tab.getAttribute('data-section')
		wrap.querySelector('#' + cat ).style.display = 'initial'
		if( cat.match(/library/) && !loaded.library ){
			fill('library', true ).catch( err => { console.log( err )})
		}else if( cat.match(/galler/) && !loaded.gallery ){
			fill('gallery', true ).catch( err => { console.log( err )})
		}else if( cat.match(/world/) && !loaded.world ){
			fill_worlds().catch( err => { 
				hal('error', err.msg || 'failed to fetch worlds', 5 * 1000 )
				console.log( err )
			})
		}else{
			// console.log('non-ajax tab: ', cat )
		}
	})
}

for( const tab of ext_tabs ){
	tab.addEventListener('click', () => {
		for( const section of ext_sections ){
			section.style.display = 'none'
		}
		for( const t of ext_tabs ){
			t.classList.remove('selected')
		}
		tab.classList.add('selected')
		const cat = tab.getAttribute('data-section')
		wrap.querySelector('#' + cat ).style.display = 'initial'
	})
}
if( ext_tabs[0] ) ext_tabs[0].click()




const load_type = localStorage.getItem('threepress-dev-view')

;(async() => {

	await new Promise((resolve, reject) => {

		switch( load_type ){

			case 'galleries':
				tabs[1].click()
				resolve()
				break;

			case 'new':
				tabs[1].click()
				setTimeout(() => {
					add_gallery.click()	
					resolve()
				}, 50)
				break;

			case 'edit':
				tabs[1].click()
				setTimeout(() => {
					const row = document.querySelector('.threepress-row')
					if( row ) row.click()
					resolve()
				}, 500)
				break;

			case 'preview':
				tabs[1].click()
				setTimeout(() => {
					const row = document.querySelector('.threepress-row')
					if( row ) row.click()
					setTimeout(() => {
						const preview = document.querySelector('#gallery-preview')
						if( preview ) preview.click()
						resolve()
					}, 500)
				}, 500)
				break;

			default: 
				tabs[0].click()
				break;
		}
	})

	if( load_type ) hal('success', 'dev: loading ' + load_type, 1000 )

})();



document.addEventListener('keyup', e => {
	if( e.keyCode === 27 ){
		const close = document.querySelector('.threepress-modal-close')
		if( close ) close.click()
	}
})


