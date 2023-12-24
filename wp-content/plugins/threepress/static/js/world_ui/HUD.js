import RENDERER from '../world/RENDERER.js?v=162'
import BROKER from '../world/WorldBroker.js?v=162'
import PLAYER from '../world/PLAYER.js?v=162'
import { Modal } from '../helpers/Modal.js?v=162'
import BINDS from '../controls/BINDS.js?v=162'
import STATE from '../world/STATE.js?v=162'
import { 
	fetch_wrap,
	hal,
	random_hex,
	spinner,
	b,
} from '../lib.js?v=162'






const build_action = ( type ) => {

	let ele =b('div', false, 'world-action-button', 'button')
	ele.innerHTML = type
	switch( type ){

		case 'install':
			ele.addEventListener('click', () => {
				BROKER.publish('ACTION', {
					type: type,
				})
			})
			break;

		case 'remove':
			ele.addEventListener('click', () => {
				BROKER.publish('ACTION', {
					type: type,
				})
			})
			break;

		default: 	
			console.log('unknown action', type)
			break;
	}

	return ele

}









let wrapper

const toggle = b('div')
toggle.id = 'threepress-admin-toggle'
toggle.innerHTML = 'menu'
toggle.addEventListener('click', () => {
	BROKER.publish('ADMIN_TOGGLE')
})

const actions = b('div')
actions.id = 'threepress-actions'
const install = build_action('install')
actions.append( install )
const remove = build_action('remove')
actions.append( remove )

// actions.innerHTML = 'actions'
// actions.addEventListener('click', () => {
// 	BROKER.publish('ACTIONS_TOGGLE')
// })




const toggle_admin = event => {
	const modal = new Modal({
		type: 'admin'
	})

	const menu = b('div')
	menu.id = 'threepress-admin-world-nav'

	add_section( 'login', modal.content, menu )
	add_section( 'toon', modal.content, menu )
	add_section( 'settings', modal.content, menu )
	add_section( 'keys', modal.content, menu )

	modal.content.append( menu )

	wrapper.append( modal.ele )

	modal.content.querySelector('.threepress-admin-tab').click()

	BROKER.publish('WORLD_UNFOCUS')

}

// const toggle_actions = event => {
// 	const modal = new Modal({
// 		type: 'actions'
// 	})

// 	const menu = b('div')
// 	menu.id = 'threepress-actions-world-nav'

// 	add_section( 'actions', modal.content, menu )

// 	modal.content.append( menu )

// 	wrapper.append( modal.ele )

// 	modal.content.querySelector('.threepress-admin-tab').click()

// }

const build_auth = ( type, auth_section ) => {

	const button = b('div', false, 'auth-button', 'threepress-button')
	button.innerHTML = type
	button.addEventListener('click', () => {
		const user = auth_section.querySelector('input[type=text]')
		const pw = auth_section.querySelector('input[type=password]')
		const email = auth_section.querySelector('input[type=email]')
		// console.log('submitting---' , button.getAttribute('debug-hash'))
		const packet = {
			type: 'auth',
			auth_type: type,
			user: user.value.trim(),
			email: email?.value?.trim(),
			pw: pw.value.trim(),
		}
		// console.log('submitting', packet )
		BROKER.publish('SOCKET_SEND', packet )
	})
	// const hash = random_hex( 4 ) // debugging
	// button.setAttribute('debug-hash', hash)
	// console.log("returning", hash )
	return button
}

const add_section = ( type, container, menu ) => {

	add_tab( type, container, menu )

	let section = b('div', false, 'threepress-admin-section')
	section.setAttribute('data-type', type )

	switch( type ){

		case 'keys':
			const guide = BINDS._generate_guide()
			section.append( guide )
			// section.innerHTML = 'settings content...'
			break;

		case 'settings':
			section.innerHTML = 'in development...'
			break;

		case 'toon':
			// toon list
			const toonlist = b('div')
			const toonfetch = b('div', false, 'threepress-button')
			toonfetch.innerHTML = 'view available toons'
			toonfetch.addEventListener('click', () => {
				fetch_wrap( THREEPRESS.ARCADE.URLS.https + '/toon_listing', 'get')
				.then( res => {
					if( res && res.success ){
						toonlist.innerHTML = ''
						for( const toon of res.toons ){
							toonlist.append( build_toon_listing( toon ) )
						}
						if( !res.toons.length ) hal('error', 'no more toons were found available', 5000 )
					}else{
						hal('error', res ? ( res.msg || 'error retrieving toons' ) : 'error fetching toons', 5000 )
					}
					console.log( res )
				})
				.catch(err => {
					console.log( err )
					hal('error', 'error fetching toons', 5000)
				})
			})

			const tooninfo = build_toon_info()
			section.append( tooninfo )

			section.append( toonfetch )
			section.append( toonlist )
			break;

		case 'login':

			// auth section
			const auth_section = b('div', false, 'threepress-auth-section')

			// login section
			const user = b('input')
			user.type = 'text'
			user.placeholder = 'toon name'
			const pw = b('input')
			pw.type = 'password'
			pw.placeholder = 'password'
			const login = build_auth('login', auth_section )

			// create section
			const create_prompt = b('div', 'create-prompt')
			create_prompt.innerHTML = 'Or create a new toon for this world: '
			const prompt_btn = b('div', false, 'threepress-button')
			prompt_btn.innerHTML = 'click here'
			create_prompt.append( prompt_btn )
			prompt_btn.addEventListener('click', () => {
				create_prompt.remove()
				create_drop.style.display = 'block'
			})
			const create_drop = b('div', 'create-drop-wrap')
			create_drop.style.display = 'none'
			const email = b('input')
			email.type = 'email'
			email.placeholder = 'threepress email (required to save a toon)'
			const desc = b('div', false, 'clarification')
			desc.innerHTML = 'Creating a toon requires a (free) <a href="' + THREEPRESS.ARCADE.URLS.https + '" target="_blank">Threepress</a> account.<br>You can also view toons and reset passwords from there.'
			const register = build_auth('create', auth_section )
		
			auth_section.append( build_toon_info() )
			auth_section.append( user )
			auth_section.append( pw )
			auth_section.append( login )
			auth_section.append( b('br') )

			create_drop.append( email )
			create_drop.append( register )
			create_drop.append( desc )

			auth_section.append( create_prompt )
			auth_section.append( create_drop )

			section.append( auth_section )

			break;

		// case 'actions':
		// 	const install = b('div')
		// 	install.classList.add("threepress-button")
		// 	install.innerHTML = 'install'
		// 	install.addEventListener('click', () => {
		// 		// const close = container.parentElement.querySelector('.threepress-modal-close')
		// 		// if( close ) close.click()
		// 		const hash = random_hex( 6 )
		// 		BROKER.publish('SOCKET_SEND', {
		// 			type: 'ping_admin_domain',
		// 			hash: hash,
		// 		})
		// 		container.parentElement.setAttribute('data-await-hash', hash )
		// 		setTimeout(() => {
		// 			if( container.parentElement.getAttribute('data-await-hash') ){
		// 			// 	// a valid response will remove hash so this doesn't happen
		// 				hal('error', 'install request was blocked', 3000 )
		// 				container.parentElement.removeAttribute('data-await-hash')
		// 			}
		// 		}, 3000)
		// 	})
		// 	section.append( install )
		// 	break;

		default: 
			console.log('admin section not configured yet: ', type )
			break;

	}

	container.append( section )

}

const add_tab = ( type, container, menu ) => {
	const tab = b('div', false, 'threepress-admin-tab', 'threepress-button')
	tab.innerHTML = type
	tab.addEventListener('click', () => {
		console.log('clicked', type )
		for( const tab of menu.querySelectorAll('.threepress-admin-tab')) tab.classList.remove('selected')
		tab.classList.add('selected')
		for( const section of container.querySelectorAll('.threepress-admin-section')) section.classList.remove('selected')
		container.querySelector('.threepress-admin-section[data-type="'+type+'"]').classList.add('selected')
	})
	menu.append( tab )
}


const build_toon_info = () => {
	const wrapper = b('div', false, 'toon-info')

	const intro = b('div')
	intro.innerHTML = 'current toon:'
	wrapper.append( intro )

	wrapper.innerHTML += `<span>${ PLAYER.handle }</span>`

	return wrapper
}


const build_toon_listing = res => {

	const { filename, modeltype } = res

	const formatted = filename.replace(/_/g, ' ')
	.replace(/.gltf/i, '')
	.replace(/.glb/i, '')
	.replace(/.fbx/i, '')

	const wrapper = b('div', false, 'threepress-toon-listing')
	// wrapper.setAttribute('data-slug', filename )
	const namediv = b('div')
	namediv.innerHTML = formatted
	wrapper.append( namediv )
	// const img = b('img')
	// img.src = THREEPRESS.ARCADE.URLS.https + '/resource/world-model-img/' + filename + '.jpg'
	// wrapper.append( img )
	wrapper.addEventListener('click', () => {
		// if( !confirm('set ' + formatted + ' to be your new toon?') ) return
		const close = document.querySelector('.threepress-modal-close')
		if( close ) close.click()
		BROKER.publish('SOCKET_SEND', {
			type: 'update_toon_model',
			modeltype: modeltype,
			filename: filename,
		})
	})

	return wrapper

}














const add_readymade = e => {
	const btn = e.target
	const item = btn.getAttribute('data-item')
	submit_install({
		value: item,
		subtype: 'readymade',
	})
}





const create_install_form = modal => {

	const form = b('div', false, 'threepress-install-form')

	// --- pre-made area

	const section1 = b('div', false, 'upload-section')

	const expl1 = b('div')
	expl1.innerHTML = `<b>Readymades</b>`
	section1.append( expl1 )
	// tree
	const tree = b('div', false, 'threepress-button')
	tree.innerHTML = 'tree'
	tree.setAttribute('data-item', 'tree')
	tree.addEventListener('click', add_readymade )
	section1.append( tree )
	// cube
	const cube = b('div', false, 'threepress-button')
	cube.innerHTML = 'cube'
	cube.setAttribute('data-item', 'cube')
	cube.addEventListener('click', add_readymade )
	section1.append( cube )
	form.append( section1 )


	// --- upload model area

	const section2 = b('div', false, 'upload-section')

	const expl = b('div')
	expl.innerHTML = `
<div><b>Upload a model</b></div>
paste the URL of an image (png / jpg / jpeg) or 3d model (glb / gltf).<br>If you have models loaded into this site, you can find the URL in the <a href="${ THREEPRESS.home_url }/wp-admin/admin.php?page=threepress%2Finc%2Fadmin.php" target="_blank">Threepress Gallery</a> or <a href="${ THREEPRESS.home_url }/wp-admin/upload.php" target="_blank">Media Library</a>.`
	section2.append( expl )
	const input = b('input')
	input.type = 'text'
	input.placeholder = 'paste URL here'
	input.addEventListener('keyup', e => {
		if( e.keyCode === 13 ){
			submit_install({
				value: input.value.trim(),
				subtype: 'upload',
			})
		}
	})
	if( THREEPRESS.home_url.match(/localhost/i)){
		setTimeout(() => {
			input.value = THREEPRESS.ARCADE.URLS.https + '/resource/world-models/trees/pine.glb'
			// input.value = THREEPRESS.ARCADE.URLS.https + '/resource/image/quaternius-chat.jpg'
		}, 500 )
	}
	section2.append( input )
	const submit = b('div', false, 'threepress-button', 'submit')
	submit.innerHTML = 'submit'
	submit.addEventListener('click', () => {
		submit_install({
			value: input.value.trim(),
			subtype: 'upload',
		})
	})
	section2.append( submit )
	setTimeout(() => {
		input.focus()
	}, 100 )
	form.append( section2 )

	return form

}



const allow_upload = event => {

	const { 
		hash, 
		is_admin, 
		auth_type 
	} = event

	console.log('pong auth / allow upload', event )

	if( auth_type !== 'install') return console.log('skipping allow upload; unmatched pong type')

	const current_hash = RENDERER.domElement.getAttribute('data-await-hash')

	if( current_hash !== hash ){
		console.log('got upload permissions for non existent request', hash, current_hash )
		return 
	}

	const modal = new Modal({
		type: 'create_install'
	})

	// const section = modal.querySelector('.threepress-admin-section')

	const form = create_install_form( modal )

	modal.close.addEventListener('click', () => {
		STATE.splice('create-install')
	})

	modal.content.append( form )

	wrapper.append( modal.ele )

	STATE.set('create-install')

	// section.append( form )

}



const submit_install = data => {

	const {
		value,
		subtype,
	} = data

	console.log('submit install', data )

	if( typeof value !== 'string' ) return console.log('invalid upload val', data )

	BROKER.publish('SOCKET_SEND', {
		type: 'begin_install', // returns begin_install -> handle_hold
		value: value,
		subtype: subtype,
		secret: localStorage.getItem('threepress-secret-pass'),
	})

	hal('standard', 'querying resource....', 3000 )

	spinner.show()

	setTimeout(() => {
		spinner.hide()
	}, 5 * 1000 )

}




const handle_hold = event => {
	/*
		initiate a hold from server
	*/

	const { 
		value,  // ( url / readymade )
		state, 
		msg, 
		resource_type,
		subtype,
	} = event

	console.log('handle hold', event )

	spinner.hide()

	STATE.splice('create-install') // ( URL entry )

	const modal = document.querySelector('.threepress-modal')	

	if( !state ){
		modal?.querySelectorAll('.threepress-button').forEach( ele => {
			ele.style['pointer-events'] = 'initial'
		})
		hal('error', msg, 5000)
		return BROKER.publish('CLEAR_HOLD')
	}

	modal?.querySelector('.threepress-modal-close')?.click()

	BROKER.publish('RENDER_HOLD', {
		resource_type: resource_type,
		value: value,
		subtype: subtype,
		state: state,
	})

} // handle hold



const handle_action = event => {

	const { type } = event

	console.log('handle action', event )

	switch( type ){

		case 'install':

			const hash = random_hex( 6 )

			BROKER.publish('SOCKET_SEND', {
				type: 'install_auth_status',
				auth_type: 'install',
				hash: hash,
			})

			RENDERER.domElement.setAttribute('data-await-hash', hash )
			break;

		case 'remove':

			const uuid = THREEPRESS.target_mesh?.userData?.uuid
			if( !uuid ){
				hal('error', 'invalid target object', 3000)
				return
			}

			const name = THREEPRESS.target_mesh?.userData.name || 'object'
			if( confirm('remove ' + name + '? (use Backspace for instant deletes)') ){
				BROKER.publish('SOCKET_SEND', {
					type: 'remove_object',
					uuid: uuid,
				})
			}

			break;

		default: 
			console.log('unknown action: ', type )
			break;

	}

} // handle action


const step_close = event => {
	
	const { e } = event

	// these do not stop separate scripts from listening to these events :(
	e.preventDefault()
	e.stopPropagation()

	const modal = RENDERER.domElement.parentElement.querySelector('.threepress-modal')
	if( modal ){
		const close = modal.querySelector('.threepress-modal-close')
		if( close ){
			close.click()
			return
		}
	}

	if( STATE.get() === 'chat'){
		BROKER.publish('CHAT_BLUR')
		return
	}

	if( STATE.get() === 'holding'){
		BROKER.publish('CLEAR_HOLD')
		return
	}

}


const handle_auth = event => {
	const { toon}= event
	if( !toon || !toon._id ){
		hal('error', 'error handling auth', 5000)
		return
	}

	// const player1 = new Player( toon )

	PLAYER.hydrate( toon )

	// update modal info
	const infos = document.querySelectorAll('.threepress-modal .toon-info')
	for( const ele of infos ) ele.remove()
	// replace in Toon tab
	const toon_section = document.querySelector('.threepress-modal .threepress-admin-section[data-type=toon]')
	if( toon_section ) toon_section.prepend( build_toon_info() )
	// replace in Login tab
	const login_section = document.querySelector('.threepress-modal .threepress-admin-section[data-type=login]')
	if( login_section ) login_section.prepend( build_toon_info() )

	// separate event handles model update:
	// BROKER.publish('TOON_UPDATE_MODEL', event )

	hal('success','updated toon', 2000)

}



const unfocus = event => {
	PLAYER.rest()
	BROKER.publish('WORLD_SET_ACTIVE', { 
		state: false 
	})	
	BROKER.publish('CLICKUP')
}



const init = () => {

	wrapper = RENDERER.domElement.parentElement
	wrapper.append( toggle )
	wrapper.append( actions )

	BROKER.subscribe('ADMIN_TOGGLE', toggle_admin )
	BROKER.subscribe('WORLD_PONG_AUTH', allow_upload )
	BROKER.subscribe('WORLD_BEGIN_INSTALL', handle_hold )
	BROKER.subscribe('ACTION', handle_action )
	BROKER.subscribe('STEP_CLOSE', step_close )
	BROKER.subscribe('AUTH_RESPONSE', handle_auth )
	BROKER.subscribe('WORLD_UNFOCUS', unfocus )

}

export default {
	init,
}