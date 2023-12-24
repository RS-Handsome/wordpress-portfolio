import INSTALLS from '../world/INSTALLS.js?v=162'
import BROKER from '../world/WorldBroker.js?v=162'
import RENDERER from '../world/RENDERER.js?v=162'
import { Modal } from '../helpers/Modal.js?v=162'
import {
	debug_log,
	b,
	is_num,
} from '../lib.js?v=162'


const target = document.createElement('div')
target.id = 'world-target-ele'
setTimeout(() => {
	RENDERER.domElement.parentElement.appendChild( target )
}, 1000)



const input_update_anim = e => {
	const uuid = e.target.getAttribute('data-uuid')
	const anim_type = e.target.getAttribute('data-anim-type')
	const key = e.target.getAttribute('data-setting')
	const install = INSTALLS[ uuid ]
	if( !install ) return console.error('error setting anim')
	let value
	if( e.target.type === 'checkbox'){
		value = e.target.checked
	}else{
		value = e.target.value
	}
	BROKER.publish('SOCKET_SEND', {
		type: 'update_install_data',
		subtype: 'animations',
		anim_type: anim_type,
		key: key,
		value: value,
		uuid: uuid,
	})
}


const build_anim_option = ( anim_type, install ) => {
	const wrap = b('div', false, 'anim-option')
	const label = b('label')
	label.innerText = anim_type
	wrap.append( label )

	// setting inputs:
	const input_enable = b('input')
	input_enable.type = 'checkbox'
	const input_weight = b('input')
	input_weight.type = 'range'
	input_weight.setAttribute('data-setting', 'weight')
	const input_speed = b('input')
	input_speed.type = 'range'
	input_speed.setAttribute('data-setting', 'speed')

	// shared props:
	for( const input of [ input_enable, input_weight, input_speed ] ){
		input.setAttribute('data-anim-type', anim_type )
		input.setAttribute('data-uuid', install.uuid )		
		input.addEventListener('change', input_update_anim )
		if( input.type === 'range' ){
			input.min = 0
			input.max = 1
			input.step = .1
			const range_wrap = b('div', false, 'threepress-range-wrap')
			const range_label = b('label')
			range_label.innerText = input.getAttribute('data-setting')
			range_wrap.append( range_label )
			range_wrap.append( input )
			wrap.append( range_wrap ) // << 
		}else{
			input.setAttribute('data-setting', 'enabled' )
			wrap.append( input ) // <<
		}
	}

	const anims = install._DATA?.animations
	let adata = anims?.[ anim_type ]
	if( adata ){
		if( adata.enabled && adata.enabled !== 'false' ) input_enable.checked = true 
		if( is_num( adata.weight ) ) input_weight.value = Number( adata.weight )
		if( is_num( adata.speed ) ) input_speed.value = Number( adata.speed )
	}
	return wrap
}



const set = event => {

	const { mesh } = event

	debug_log('interactions-target', 'target set: ', mesh )

	if( !mesh ) return clear()

	const install = INSTALLS[ mesh.userData?.uuid ]
	if( install ) install.set_controls( true )

	target.innerHTML =  mesh.userData?.name || 'unknown target'
	if( mesh.userData.description ){
		target.innerHTML += '<div>' + mesh.userData.description + '</div>'
	}
	if( install?.animation ){
		const btn = b('div', false, 'threepress-button')
		btn.innerText = 'animations'
		btn.addEventListener('click', () => {

			const modal = new Modal({
				type: 'anim-picker',
			})	

			const header = b('div', false, 'p3-header')
			header.innerText = 'Enable animations:'
			modal.content.append( header )

			for( const anim_type in install.animation.actions ){
				modal.content.append( build_anim_option( anim_type, install ) )
			}

			RENDERER.domElement.parentElement.append( modal.ele )
			// document.body.append( modal.ele )
		})
		target.append( b('br') )
		target.append( btn )
	}

	target.classList.add('active')

	THREEPRESS.target_mesh = mesh

	// console.log( 'target set: ', mesh )
	
}


const clear = () => {

	target.innerHTML = ''
	target.classList.remove('active')
	for( const uuid in INSTALLS ){
		INSTALLS[ uuid ].set_controls( false )
	}
	delete THREEPRESS.target_mesh

}








const init = () => {

	BROKER.subscribe('TARGET_SET', set )
	BROKER.subscribe('TARGET_CLEAR', clear )

}


export default {
	init,
}

