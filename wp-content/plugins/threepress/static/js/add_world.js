import * as lib from './lib.js?v=162'
// three stuff
import {
	Vector3,
	Quaternion,
	DoubleSide,
	// BoxBufferGeometry,
	Mesh,
    MeshLambertMaterial,
    MeshBasicMaterial,
    PlaneBufferGeometry,
    TextureLoader,
} from '../inc/three.module.js?v=162'
import SCENE from './world/SCENE.js?v=162'
import RENDERER from './world/RENDERER.js?v=162'
import CAMERA from './world/CAMERA.js?v=162'
import LIGHT from './world/LIGHT.js?v=162'
import GROUND from './world/GROUND.js?v=162'
// engine stuff
import animate from './world/animate.js?v=162'
import BROKER from './world/WorldBroker.js?v=162'
import WS from './world/WS.js?v=162'
// game stuff
import STATE from './world/STATE.js?v=162'
import Install from './world/Install.js?v=162'
import Player from './world/Player.js?v=162'
// registers
import PLAYER from './world/PLAYER.js?v=162'
import TOONS from './world/TOONS.js?v=162'
import OBJECTS from './world/OBJECTS.js?v=162'
// import COLLIDERS from './world/COLLIDERS.js?v=162'
import INSTALLS from './world/INSTALLS.js?v=162'
// controls
import KEYS from './controls/KEYS.js?v=162'
import BINDS from './controls/BINDS.js?v=162'
import MOUSE from './controls/MOUSE.js?v=162'
import CHAT from './world_ui/CHAT.js?v=162'
import TARGET from './world_ui/TARGET.js?v=162'
import HUD from './world_ui/HUD.js?v=162'
import HOLDS from './world/HOLDS.js?v=162'
// import FOREST from './world/forest.js?v=162'
// import FactoryObject from './world/FactoryObject.js?v=162'
import SKYBOX from './world/SKYBOX.js?v=162'
import GLOBAL from './GLOBAL.js?v=162'
import add_objects from './world/add_objects.js?v=162'


// import varLogger from './helpers/varLogger.js?v=162'

lib.tstack('add_world')

// basic init
const eles = document.querySelectorAll('#threepress-world')
const world_ele = eles[0] // ( already checked in init_base.js )
world_ele.innerHTML = ''
world_ele.appendChild( RENDERER.domElement )
// world_ele.appendChild( varLogger )

// SCENE.add( CAMERA ) // ( move to player on load )
SCENE.add( LIGHT.hemispherical )
SCENE.add( LIGHT.directional )
SCENE.add( LIGHT.directional.target )



// expose 
THREEPRESS.RENDERER = RENDERER
THREEPRESS.CAMERA = CAMERA
THREEPRESS.SCENE = SCENE




if( eles.length > 1 ){
	console.log('too many Threepress World elements found', eles )
	// lib.hal('error', 'currently only one Threepress world is allowed per page', 5 * 1000 )
}else{

	const style = document.createElement('link')
	style.rel = 'stylesheet'
	style.href = THREEPRESS.plugin_url + '/static/css/world.css?v=162'
	document.head.appendChild( style )

	const wrapper = document.createElement('div')
	wrapper.id = 'threepress-ui-wrapper'
	world_ele.prepend( wrapper )
	// insertBefore( RENDERER.domElement, wrapper )

	GLOBAL.init()
	.then( res => {
		WS.init( world_ele )
		KEYS.init()
		MOUSE.init()
		TARGET.init()
		HUD.init()
		CHAT.init()
	})
}




// const voxel_type_map = {
// 	lambert: MeshLambertMaterial,
// 	basic: MeshBasicMaterial,
// }


const WORLD = {
	voxel_scale: 1,
	voxel_mats: {},
}





// callbacks

const init_toon = async( event, toon_data, is_player1 ) => {
	/*
		called both directly and as a callback... probably unecessary
		player1 should init like any other player
	*/

	if( event ) toon_data = event.toon 
		
	lib.debug_log('init-toon' ,'init toon event:', event )

	if( is_player1 ){
		PLAYER.hydrate( toon_data )
		PLAYER.player1 = true
	}else{
		if( TOONS[ toon_data.uuid ] ){
			return lib.debug_log('toon-info', 'toon already exists', toon_data.uuid.substr(0,4) )
		}
	}

	const toon = is_player1 ? PLAYER : new Player( toon_data )

	TOONS[ toon.uuid ] = toon

	switch( toon_data.modeltype ){

		case 'quaternius_low':
			await toon.construct_model()
			break;

		default: 
			lib.debug_log('error', 'unknown toon modeltype: ', toon_data )
			break;
	}

	toon.rest()

	SCENE.add( toon.GROUP )

	if( GROUND.BLOCK_REGISTER ){ // only for perlin
		toon.begin_tile_detect( GROUND.BLOCK_REGISTER ) // maybe extend to all toons ? sets "toon.current_tile"
	}

	if( is_player1 ){

		PLAYER.begin_pulse() 

		LIGHT.track( PLAYER, true )

		CAMERA.fixture.position.y = PLAYER.bbox.max.y

		BROKER.publish('LOGGER_LOG', {
			parent_obj: PLAYER.animation.actions,
			keys: ['walking', 'running', 'idle'],
			callback: value => {
				return value?.isRunning?.()
			}
		})
		setTimeout(() => {
			BROKER.publish('LOGGER_STATE', { 
				state: true,
				ms: 500,
			} )			
		}, 1000 )

	}

}








const init_world = async( world_obj ) => {

	const { 
		// description,
		domain,
		environment,
		// name,
	} = world_obj

	if( !location.href.match( new RegExp( domain, 'i' ) ) ){
		lib.hal('error', 'invalid world data', 5000)
		return
	}

	// // console.log('skipping trees')
	// BROKER.publish('SOCKET_SEND', {
	// 	type: 'ping_trees',
	// })

	// // console.log('skipping pillars')
	// BROKER.publish('SOCKET_SEND', {
	// 	type: 'ping_pillars',
	// })

	// console.log('skipping installs')
	BROKER.publish('SOCKET_SEND', {
		type: 'ping_installs',
	})

	SCENE.add( SKYBOX )

	let skytrack = setInterval(() => {
		SKYBOX.position.copy( PLAYER.GROUP.position )
	}, 5000 )

	// break;

	// 	case 'voxel':

	// 		const { voxel_mats, voxels, scale } = world_obj

	// 		if( !voxel_mats || !voxels || !scale ){
	// 			console.log('missing voxel data: ', voxel_mats, voxels, scale)
	// 			return
	// 		}

	// 		// ___ scale
	// 		WORLD.voxel_scale = scale

	// 		// ___ mats 
	// 		/* 
	// 		-- name
	// 		-- type
	// 		-- color
	// 		server-sent voxels must be tagged with a key of an existing voxel mat
	// 		*/
	// 		for( const mat of voxel_mats ){
	// 			const{ name, type, color } = mat
	// 			if( !name || !type || !color ){
	// 				console.log('voxel mat data missing', mat )
	// 				continue
	// 			}
	// 			WORLD.voxel_mats[ mat.name ] = new voxel_type_map[ mat.type ]({
	// 				color: mat.color,
	// 			})
	// 		}

	// 		// ___ the voxels
	// 		let i = 0
	// 		for( const v of voxels ){
	// 			setTimeout(()=>{
	// 				BROKER.publish('WORLD_ADD_VOXEL', { data: v })
	// 			}, i * 100)
	// 			i++
	// 		}
	// 		break;

	// 	default: 
	// 		console.log('unknown worldtype: ', world_obj )
	// 		break;
	// }

}







const init_entry = async( event ) => {

	lib.debug_log('init-world', 'world init: ', event )
	
	const { type, world, toon, version } = event	

	if( version !== THREEPRESS.version ){
		lib.hal('error', 'Threepress version does not match server - update plugin to avoid bugs', 20 * 1000 )
	}

	if( !world ){
		return lib.debug_log('error', 'missing world init')
	}
	if( !toon ){
		return lib.debug_log('error', 'missing toon init')
	}

	animate()

	GROUND.init( world )

	await init_toon( null, toon, true )

	await init_world( world )

	PLAYER.GROUP.add( CAMERA.fixture )

	CAMERA.position.set( 0, 10, -50 ) // 1 == just slightly elevated
	// PLAYER.bbox.max.y * 1.5

	CAMERA.lookAt( CAMERA.fixture.position )

	setTimeout(() => {
		BROKER.publish('CAMERA_LOOK_HOME')
		BROKER.publish('SOCKET_SEND', {
			type: 'ping_toon',
			all: true
		})
	}, 2000)

}








// const add_voxel = event => {
	
// 	const { data } = event

// 	const { coords, mat, shared } = data

// 	if( !coords || !mat ){
// 		console.log('invalid voxel data', data )
// 		return
// 	}

// 	const reference = WORLD.voxel_mats[ mat ]

// 	const material = shared ? reference : reference.clone()

// 	const voxel = new Mesh( WORLD.voxel_geo, material )

// 	voxel.position.set( 
// 		coords.x * WORLD.voxel_scale, 
// 		coords.y * WORLD.voxel_scale,
// 		coords.z * WORLD.voxel_scale
// 	)

// 	SCENE.add( voxel )

// }



const ping_toon = uuid => {
	BROKER.publish('SOCKET_SEND', {
		type: 'ping_toon',
		uuid: uuid,
	})
}




const handle_core = event => {
	const { uuid, p, q, s, force } = event

	// p q s - position quaternion state[walking|strafing|running]

	// console.log( s )

	if( PLAYER.uuid === uuid && !force ) return

	const toon = TOONS[ uuid ]
	if( !toon ) return ping_toon( uuid )
	if( !toon.GROUP ) return // pre-load

	toon.animate('running', s.r, 500 )
	toon.animate('walking', s.w, 500 )
	toon.animate('strafing', s.s, 500 )
	
	toon.state.strafing = s.s
	toon.state.running = s.r
	toon.state.walking = s.w

	// pos
	if( toon.lerping ){
		clearInterval( toon.lerping )
		clearTimeout( toon.lerpstop )
	}
	toon.lerping = setInterval(() => {
		toon.GROUP.position.lerp( new Vector3( p.x, p.y, p.z ), .3 )
	}, 50)
	toon.lerpstop = setTimeout(()=> {
		toon.GROUP.position.set( p.x, p.y, p.z )
		clearInterval( toon.lerping )
	}, 400)

	// quat
	if( toon.slerping ){
		clearInterval( toon.slerping )
		clearTimeout( toon.slerpstop )
	}
	toon.slerping = setInterval(() => {
		toon.GROUP.quaternion.slerp( new Quaternion( q.x, q.y, q.z, q.w ), .3 )
	}, 50)
	toon.slerpstop = setTimeout(()=> {
		toon.GROUP.quaternion.set( q.x, q.y, q.z, q.w )
		clearInterval( toon.slerping )
	}, 400)

}




const update_toon_model = event => {
	const { toon } = event
	const le_toon = TOONS[ toon.uuid ]
	if( !le_toon ){
		console.log('missing toon for update: ', toon)
		return
	}

	le_toon.deconstruct_model()

	le_toon.slug = toon.slug
	le_toon.modeltype = toon.modeltype

	le_toon.construct_model( true )
	.then( res => {
		console.log('updated toon ', le_toon )
		le_toon.rest()
	}).catch( err => {
		console.log( err )
	})

}




const remove_toon = event => {
	const{ uuid } = event
	const toon = TOONS[ uuid ]
	if( !toon ){
		console.log('no toon for delete: ', uuid )
		return
	}
	
	clearInterval( toon.tile_detect )

	const group = toon.GROUP
	SCENE.remove( group )
	delete TOONS[ uuid ]

}




const set_active = event => {
	const { state } = event
	STATE.active = state
	if( state ){
		RENDERER.domElement.parentElement.classList.add('threepress-world-active')
	}else{
		RENDERER.domElement.parentElement.classList.remove('threepress-world-active')
	}
}




const install_update_data = event => {
	const {
		uuid,
		key,
		anim_type,
		subtype: subtype,
		value,
	} = event

	const install = INSTALLS[ uuid ]
	if( !install ) return debug_log('install-info', 'missing install for update', uuid )

	switch( subtype ){
	case 'animations':
		lib.debug_log('install-anims', 'update packet', event )
		// update data
		if( typeof install._DATA.animations[ anim_type ] !== 'object' ){
			install._DATA.animations[ anim_type ] = {}
		}
		const specific_anim_data = install._DATA.animations[ anim_type ]
		specific_anim_data[ key ] = value
		// update live model
		// install.
		// install.animation.actions[ anim_type ]
		install.update_animate()
		// WHAT IS THE LIVE MODEL ANIM CONTROLLER ?

		break;

	default:
		return debug_log('install-info', 'unknown update anim type: ' + subtype )
	}

}











BROKER.subscribe('WORLD_SET_ACTIVE', set_active )
BROKER.subscribe('WORLD_INIT', init_entry )
// BROKER.subscribe('WORLD_ADD_VOXEL', add_voxel )

BROKER.subscribe('TOON_INIT', init_toon )
BROKER.subscribe('TOON_CORE', handle_core )
BROKER.subscribe('TOON_UPDATE_MODEL', update_toon_model )
BROKER.subscribe('TOON_REMOVE', remove_toon )
BROKER.subscribe('INSTALL_UPDATE_DATA', install_update_data )


// BROKER.subscribe('TOON_WALK', handle_walk )
// BROKER.subscribe('TOON_TURN', handle_turn )
// BROKER.subscribe('TOON_STRAFE', handle_strafe )

