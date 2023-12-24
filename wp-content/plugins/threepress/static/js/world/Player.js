import Player from './Player.js?v=162'
import BROKER from './WorldBroker.js?v=162'
// import STATE from './STATE.js?v=162'
// import RAYCASTER from '../controls/RAYCASTER.js?v=162'
// import SCENE from './SCENE.js?v=162'
import {
	trim,
	// identify,
	// debug_log,
	// FALL_BUFFER,
} from '../lib.js?v=162'
// import {
// 	Vector3,
// } from '../../inc/three.module.js?v=162'




const player = new Player()

player.need_stream = false
// player.standard_actions = [
// 	'walking',
// 	'strafing',
// 	'turning',
// 	'idle',
// 	'running',
// 	'jump',
// 	'attack',
// ]

// player.animation_map = { 
// 	/*
// 		game actions -> the embedded animation names for that modeltype
// 	*/
// 	quaternius_low: {
// 		'walking': {
// 			localized: 'Walk',
// 			fade: 500,
// 		},
// 		'running': {
// 			localized: 'Run',
// 			fade: 500,
// 		},
// 		'strafing': {
// 			localized: 'Walk',
// 			fade: 500,
// 		},
// 		'turning': {
// 			localized: 'Walk',
// 			fade: 500,
// 		},
// 		'idle': {
// 			localized: 'Idle',
// 			fade: 500,
// 		},
// 	},

// }

THREEPRESS.PLAYER1 = player

let p
player.build_packet = () => {
	p = {
		type: 'core',
		p: {
			x: trim( player.GROUP.position.x, 1 ),
			y: trim( player.GROUP.position.y, 1 ),
			z: trim( player.GROUP.position.z, 1 ),
		},
		q: {
			x: trim( player.GROUP.quaternion._x, 1 ),
			y: trim( player.GROUP.quaternion._y, 1 ),
			z: trim( player.GROUP.quaternion._z, 1 ),
			w: trim( player.GROUP.quaternion._w, 1 ),
		},
		s: {
			w: player.state.walking,						
			r: player.state.running,
			s: player.state.strafing,						
		}		
	}
	return p
}






player.begin_pulse = () => {
	if( !player.player1 || player.core_pulse ){
		return
	}

	// movement detection
	player.core_pulse = setInterval(() => {
		if( !player.need_stream ) return
		player.send_update()
	}, 500)

	// gravity
	if( player.gravity ) return
	player.gravity = setInterval(() => {	
		player.update_falling()
	}, 300 )// 1000 / 4
}

player.send_update = () => {
	BROKER.publish('SOCKET_SEND', player.build_packet() )
	// console.log( player.state )
	player.need_stream = false
}








export default player