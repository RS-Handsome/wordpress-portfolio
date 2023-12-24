import * as lib from '../lib.js?v=162'

import PLAYER from './PLAYER.js?v=162'
import FactoryObject from './FactoryObject.js?v=162'
import Install from './Install.js?v=162'
import OBJECTS from './OBJECTS.js?v=162'
import INSTALLS from './INSTALLS.js?v=162'
import COLLIDERS from './COLLIDERS.js?v=162'
import BROKER from './WorldBroker.js?v=162'
import SCENE from './SCENE.js?v=162'
import RENDERER from './RENDERER.js?v=162'
import STATE from './STATE.js?v=162'

import MOUSE from '../controls/MOUSE.js?v=162'





const handle_obj = event => {

	const { obj } = event 

	if( OBJECTS[ obj.uuid ]){
		return console.log('obj already exists', OBJECTS[ obj.uuid ] )
	}

	const object = FactoryObject( obj )

	if( !object ) return console.log('failed to construct: ', obj )

	if( object.isEntity ){ // toons, ... , ?
		// debugger
		object.construct_model()
		.then( res => {
			// debugger
			// console.log( res )
			// console.log( object )
			SCENE.add( object.GROUP )
			object.GROUP.position.set( object.x || 0, object.y || 0, object.z || 0 )

		})		
		.catch( err => {
			console.log( err )
		})

	}else if( object.type === 'pillar' ){ // pillars 

		console.error('deprecated pillar load', object )

		// THREEPRESS.PILLARS = THREEPRESS.PILLARS || {}
		// THREEPRESS.PILLARS[ obj.uuid ] = obj

		// object.construct_model()
		// .then( res => {
		// 	console.log('loaded pillar')
		// 	SCENE.add( object.GROUP )
		// 	object.GROUP.position.x = object.x
		// 	object.GROUP.position.z = object.z
		// 	object.GROUP.position.y = ( object.height / 2 ) + 1
		// })

	}else{

		console.log('unhandled construct obj: ', obj )
	}

}





const send_install = event => {

	const { e } = event

	// console.log('installing ', STATE.held_url , ' at ', x, y )

	const bounds = RENDERER.domElement.getBoundingClientRect()

	const { intersection } = MOUSE.detect_object_hovered( e, bounds )
	if( !intersection ){
		console.log('no install location found' )
		return
	}

	const target = intersection.object
	const is_ground = target?.userData?.is_ground
	if( !target?.userData?.clickable && !is_ground ){
		// console.log('no install target found')
		lib.hal('error', 'cannot install there', 2000 )
		return console.log( target )
	}

	console.log( 'send install: intersection: ', intersection )

	BROKER.publish('SOCKET_SEND', {
		type: 'send_install',
		subtype: STATE.held_subtype,
		value: STATE.held_url,
		point: intersection.point,
		mount_uuid: target.userData?.uuid, //  || target.uuid,
		is_ground: is_ground,
	})

}



const handle_install = event => {

	const { install } = event 

	lib.debug_log('install-load', 'handle install', event )

	/*
	const {
		is_install,
		ref,
		subtype,
		uuid,
		value,
		_REF,
	} = install
	*/

	const subtype = install.subtype
	// const subtype = lib.get_object_subtype( install )

	const installation = new Install( install )

	if( subtype === 'upload' || subtype === 'readymade'){

		installation.construct_model()
		.then( res => {

			// add to SCENE
			if( !installation.GROUP ){
				return lib.hal('error', 'failed to load image', 5000 )
			}
			SCENE.add( installation.GROUP )
			installation.GROUP.position.set( 
				installation.REF.position.x, 
				installation.REF.position.y, 
				installation.REF.position.z,
			)
			INSTALLS[ installation.uuid ] = installation

			// add to C-OLLIDERS
			let has_install = false
			let uuid
			for( const obj of COLLIDERS ){
				uuid = obj.userData?.uuid
				if( uuid && uuid === installation.uuid ){
					has_install = true
				}
			}
			if( !has_install ){

				// only add meshes !
				installation.GROUP.traverse( child => {
					if( child.isMesh ){
						COLLIDERS.push( child )
					}
				})
			}

		})

	// }else if( subtype == 'readymade' ){

	// 	installation.

	}else{

		lib.debug_log('install-load', 'handle-install, unknown type:', subtype )

	}

}





const update_object = event => {

	const { obj, sender_uuid } = event

	if( sender_uuid === PLAYER.uuid ) return;

	const target = INSTALLS[ obj.uuid ] // || x || y ...
	if( !target ) return;

	target.GROUP.quaternion.set(
		obj._REF.quaternion._x,
		obj._REF.quaternion._y,
		obj._REF.quaternion._z,
		obj._REF.quaternion._w,
	)

	target.GROUP.position.set(
		obj._REF.position.x,
		obj._REF.position.y,
		obj._REF.position.z,
	)

	target.GROUP.scale.set(
		obj._REF.scale.x,
		obj._REF.scale.y,
		obj._REF.scale.z,
	)

	// move to lerp eventually....
	target.REF.quaternion = obj._REF.quaternion 
	target.REF.position = obj._REF.position
	target.REF.scale = obj._REF.scale

}



const remove_object = event => {

	const { uuid } = event
	const obj = INSTALLS[ uuid ]
	if( !obj ){
		return console.log('remove object: ', 'no object')
	}

	obj.set_controls( false )
	
	// remove from SCENE
	SCENE.remove( obj.GROUP )

	// remove from INSTALLS
	delete INSTALLS[ uuid ]

	// remove from C-OLLIDERS
	let c
	for( let i = COLLIDERS.length - 1; i >= 0; i-- ){
		c = COLLIDERS[i].userData
		if( !c || c.uuid === uuid || !COLLIDERS[i].isMesh ){ // either invalid obj, or its valid uuid remove
			COLLIDERS.splice( i, 1 )
			console.log('C-OLLIDER removed')
		}
	}

	lib.debug_log('user-action', 'removed: ', obj )

}







BROKER.subscribe('WORLD_HANDLE_OBJ', handle_obj )
BROKER.subscribe('WORLD_INSTALL', send_install )
BROKER.subscribe('WORLD_PONG_INSTALL', handle_install )
BROKER.subscribe('WORLD_UPDATE_OBJ', update_object )
BROKER.subscribe('WORLD_REMOVE_OBJ', remove_object )


export default {}