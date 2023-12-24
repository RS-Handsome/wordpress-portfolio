import BROKER from './WorldBroker.js?v=162'
import {
	get_object_subtype,
	get_uri_type,
	hal,
	debug_log,
	get_bbox,
	random_hex_color,
	identify,
	is_num,
} from '../lib.js?v=162'
import {
	Texture,
	BoxBufferGeometry,
	Group,
	MeshLambertMaterial,
	Mesh,
	PlaneGeometry,
	TextureLoader,
	Color,
	AnimationMixer,
	AnimationClip,
} from '../../inc/three.module.js?v=162'
import { GLTFLoader } from '../../inc/GLTFLoader.js?v=162'
import { TransformControls } from '../../inc/TransformControls.js?v=162'
import CAMERA from './CAMERA.js?v=162'
import RENDERER from './RENDERER.js?v=162'
import SCENE from './SCENE.js?v=162'
import { MeshoptDecoder } from '../../inc/meshopt_decoder.js?v=162'


const texLoader = new TextureLoader()
// const gltfLoader = new GLTFLoader()






// TRANSFORMER
const transformer = THREEPRESS.transformer = new TransformControls( CAMERA, RENDERER.domElement )
SCENE.add( transformer )

// send changes to server
const updating = {}
transformer.addEventListener('objectChange', e => {
	const target_obj = e.target?.object
	if( !target_obj ){
		debug_log('transformer', 'missing obj for update')
		return
	}
	if( !updating[ target_obj.uuid ] ){
		updating[ target_obj.uuid ] = setTimeout(()=>{
			const packet = {
				type: 'update_object',
				uuid: target_obj.userData?.uuid,
				scale: target_obj.scale,
				quaternion: target_obj.quaternion,
				position: target_obj.position,
			}
			// console.log('sending', packet )
			BROKER.publish('SOCKET_SEND', packet )
			clearTimeout( updating[ target_obj.uuid ])
			delete updating[ target_obj.uuid ]
		}, 1000)
	}

})

// controls for different transformer states
const build_control = type => {
	const wrapper = document.createElement('div')
	wrapper.classList.add('object-control')
	const img = document.createElement('img')
	img.src = THREEPRESS.ARCADE.URLS.https + '/resource/image/control-' + type + '.jpg'
	wrapper.append( img )
	wrapper.addEventListener('click', () => {
		// BROKER.publish('CONTROLS_SET_STATE', { type: type } )
		transformer.mode = type
	})
	return wrapper
}

// transformer controls UI
const controls_ui = document.createElement('div')
controls_ui.id = 'controls-ui'
controls_ui.append( build_control('translate') )
controls_ui.append( build_control('scale') )
controls_ui.append( build_control('rotate') )
setTimeout( () => { // just needs to wait for compile to be done
	RENDERER.domElement.parentElement.append( controls_ui )
}, 1000 ) 

// togggle transformer UI as needed
let t_current, t_previous
let last_transformer
transformer.addEventListener('change', e => { // update UI on transform changes..
	debug_log('transformer', 'obj..', transformer.object )
	t_current = !!transformer.object
	if( t_current !== t_previous ){
		if( t_current ){
			controls_ui.style.display = 'inline-block'
			// set_transparent( transformer.object, true )
			last_transformer = transformer.object
		}else{
			controls_ui.style.display = 'none'
			if( last_transformer ){
				// set_transparent( last_transformer, false )
				last_transformer = false
			}
		}
	}
	t_previous = t_current
})


const set_transparent = ( obj, state ) => {
	let t
	if( !obj ) return;
	obj.traverse( child => {
		if( !child.material ) return;
		t = child.transparent
		if( state ){
			child.material.opacity = .5
			child.material.transparent = true
		}else{
			child.material.opacity = 1
			child.material.transparent = false
		}
	})
}









// install components
const framegeo = new BoxBufferGeometry(1,1,1)
const framemat = new MeshLambertMaterial({
	color: 'wheat',
})
const planegeo = new PlaneGeometry(1,1,1)





class Install {
	/*
		cant extend Entity becuase that's intended for Threepress formatted models
	*/

	constructor( init ){

		init = init || {}

		if( typeof init.value !== 'string' ){
			console.error("installation is missing url / value", init.value )
		}

		// standard hydrate
		Object.assign( this, init )

		// instantiated
		this.subtype = get_object_subtype( this )
		this.upload_type = get_uri_type( init.value )
		this.held_mesh = init.held_mesh

	}

	update_animate(){

		for( const type in this.animation?.actions || {} ){

			const action = this.animation?.actions[ type ]
			if( !action ) return console.error('cannot animate: ', identify( this), type )

			let anim_data = this._DATA.animations[ type ]
			if( !anim_data ){
				debug_log('install-info', 'no anim data for install: ' + identify( this ) + ', initializing')
				this._DATA.animations[ type ] = anim_data = {
					weight: 1,
					speed: 1,
					enabled: false,
				}
			}

			const {
				weight,
				speed,
				enabled,
			} = anim_data

			debug_log('install-anims', 'update anim: ', type, anim_data )

			if( is_num( weight ) ){
				this.animation.actions[ type ].weight = Number( weight )
			}
			if( is_num( speed ) ){
				this.animation.actions[ type ].timeScale = Number( speed )
			}

			if( !enabled ){
				this.animation.actions[ type ].stop()//fadeOut( 500 )
			}else{
				this.animation.actions[ type ].play()//( 500 )
			}

		}
	}

	add_animation( model ){ // animation_map

		if( !model  ){ // || !animation_map
			debug_log( 'install-anims', 'invalid animation init' )
			debug_log( 'install-anims', 'animation_map', animation_map )
			debug_log( 'install-anims', 'model', model )
			return
		}

		this.animation = {
			mixer: new AnimationMixer( model.scene ),
			clips: model.animations,
			actions: {},
			fades: {},
		}

		// match syntax of Entity / Player anim maps here:
		this.animation_map = {}
		for( const clip of model.animations ){
			this.animation_map[ clip.name ] = {
				localized: clip.name, // (just to match syntax of )
				fade: 500,
			}
		}

		let given_name, clip
		for( const type in this.animation_map ){
			given_name = this.animation_map[ type ]?.localized // redundant on Instals, but....
			// console.log('looking to init: ', given_name )
			clip = AnimationClip.findByName( this.animation.clips, given_name )
			if( !clip ){
				// if( type === 'running' ){ // ( a weird edge case - no 'Run' available )
				// 	clip = AnimationClip.findByName( this.animation.clips, 'Run_Carry' )
				// }
				// if( !clip ){
				debug_log('install-anims', 'animation map failed to find: ', type )
				continue					
				// }

			}
			this.animation.actions[ type ] = this.animation.mixer.clipAction( clip )
		}

	}



	async construct_model(){

		let group = new Group()
		if( this.held_mesh ){
			group.userData.held_mesh = true
		}

		switch( this.subtype ){

		case 'upload':

			switch( this.upload_type ){

			case 'image':
				await construct_model_image( this, false, group )
				break;

			case 'model':
				await construct_model_all( this, false, group ) // forget why I made sep function here.. convenience?
				break;

			default:
				debug_log('install-load', 'invalid installation construct', this)
				return
			}
			break;

		case 'readymade':
			if( this.value === 'tree'){
				await construct_model_all( this, false, group )
			}else{
				await construct_readymade( this, false, group )
			}
			break;

		default:
			debug_log('install-load', 'construct-model: unknown subtype: ', this )
			break;
		}

			// return gltf
		this.process_model()

		this.update_animate()

		// console.log('constructing', this._REF.scale )

	}

	process_model(){

		switch( this.subtype ){
		case 'upload':
			if( this.upload_type === 'image' ){

				this.frame.castShadow = true 
				this.frame.receiveShadow = true 

			}else if( this.upload_type === 'model' ){

				const shadow_count = 45
				let c = 0
				this.GROUP.traverse( child => {
					if( child.isMesh ){
						if( c > shadow_count ){
							//
						}else{
							child.castShadow = true
							child.receiveShadow = true
						}
						c++
					}
				})	
				if( c > shadow_count ){
					console.log('model had many meshes; skipped '+ ( c - shadow_count ) + ' shadows: ' + ( this.name || this.uuid.substr(0,4) ) )
				}
				// consoo

			}else{
				console.log('unknown process_model', this )
			}
			break;

		case 'readymade':
			switch( this.value ){
			case 'tree':
				this.GROUP.traverse( child => {
					if( child.isMesh ){
						const collor = random_hex_color('#22ccaa', '#22ffaa')
						child.material.color = new Color( collor )
						child.castShadow = true
					}
				})
				break;
			case 'cube':
				this.GROUP.traverse( child => {
					if( child.isMesh ){
						child.castShadow = true
					}
				})
				break;
			default:
				console.log('unkown type to proces for readymade', this.value )
				break;
			}
			break;

		default:
			return console.log('unknown process model subtype: ', this )
		}

		this.GROUP.userData.clickable = true
		this.GROUP.userData.uuid = this.uuid
		this.GROUP.userData.name = identify( this )
		this.GROUP.userData.description = this.description

		this.GROUP.traverse( ele => {
			if( ele.isMesh ){
				ele.userData = ele.userData || {}
				ele.userData.clickable = true
				ele.userData.uuid = this.uuid
				ele.userData.name = this.GROUP.userData.name
				ele.userData.description = this.description
			}
		})

		// debug_log('install-load', 'maybe missing clickables: ', this.GROUP.userData?.name || this.GROUP.userData?.uuid?.substr(0,6) )

		// user data is not persisting clickable to big Installs...

		try{

			this.REF = JSON.parse( this.ref )

			// set QUATERNION and SCALE internally here, but POSITION handle externally
			this.GROUP.quaternion.set( 
				this.REF.quaternion._x, 
				this.REF.quaternion._y, 
				this.REF.quaternion._z, 
				this.REF.quaternion._w 
			)

			this.GROUP.scale.set(
				this.REF.scale.x,
				this.REF.scale.y,
				this.REF.scale.z,
			)

			// console.log('parsed: ', parse )
		}catch( err ){
			console.log('install-load', 'failed to parse Install quaternion', err )
		}

	}

	set_controls( state ){
		if( state ){
			transformer.attach( this.GROUP )
		}else{
			transformer.detach()
		}
	}

	add_group( is_update ){ // mirrors Entity
		if( is_update ){
			this.GROUP.remove( this.MODEL )
			delete this.animation
		}else{
			this.GROUP = new Group()
		}
	}

	update( delta_seconds ){

		// animations
		if( this.anim_mixer ){
			this.anim_mixer.update( delta_seconds )
			// console.log('anim')
		}

		// // movement
		// // running
		// if( this.state.running > 0 ){
		// 	this.GROUP.translateZ( this.speed * delta_seconds )
		// }else if( this.state.running < 0 ){
		// 	this.GROUP.translateZ( -this.speed * delta_seconds )
		// } 
		// // walking
		// else if( this.state.walking > 0 ){
		// 	this.GROUP.translateZ( this.speed * delta_seconds * .5 )
		// }else if( this.state.walking < 0 ){
		// 	this.GROUP.translateZ( -this.speed * delta_seconds * .5 )
		// }
		// // strafing
		// else if( this.state.strafing > 0 ){
		// 	this.GROUP.translateX( -this.speed * delta_seconds )
		// }else if( this.state.strafing < 0 ){
		// 	this.GROUP.translateX( this.speed * delta_seconds )
		// }
		
		// // turning
		// if( this.state.turning > 0 ){
		// 	this.GROUP.rotateY( 1.5 * delta_seconds )
		// 	// rotation.y -= 1.5 * delta_seconds
		// }else if( this.state.turning < 0 ){
		// 	this.GROUP.rotateY( -1.5 * delta_seconds )
		// 	// this.GROUP.rotation.y += 1.5 * delta_seconds
		// }

		// // gravity - jumping or falling
		// if( this.jumping || this.falling ){ // ( make sure to use negative momentum for falling...)
		// 	this.GROUP.position.y += this.ref.momentum.y
		// }

	}

}

















const construct_model_image = async( entity, is_update, group ) => {

	entity.frame = new Mesh( framegeo, framemat )
	entity.frame.userData.held_mesh = entity.held_mesh
	group.add( entity.frame )

	let image = document.createElement('img')
	const tex = new Texture()
	const logurl = entity.value.substr( entity.value.length - 30 )
	try{

		await new Promise( (resolve, reject) => {
			// load img to test url...
			image.crossOrigin = 'Anonymous'
			image.onload = () => {
				resolve()
			}
			image.onerror = err => {
				console.error( 'error 1', logurl, err )
				image.src = THREEPRESS.ARCADE.URLS.https + '/resource/image/no-load.jpg'
				resolve()
			}
			image.src = entity.value
			setTimeout
		})

	}catch(error){
		console.error( 'error 2', logurl, error )
		image.src = THREEPRESS.ARCADE.URLS.https + '/resource/image/no-load.jpg'
	}

	tex.image = image
	setTimeout(()=>{
		tex.needsUpdate = true
	}, 1000)
	entity.image = new Mesh( planegeo, new MeshLambertMaterial({
		map: tex,
	}))
	entity.image.userData.held_mesh = entity.held_mesh
	group.add( entity.image )


	group.scale.x = 5
	group.scale.y = 5
	group.scale.z = 1
	entity.image.position.z = .6
	entity.GROUP = group

} // construct image


const construct_model_all = async( entity, is_update, group ) => {

	let url
	if( entity.subtype === 'readymade' ){
		url = map_readymade( entity.value )
	}else{
		url = entity.value
	}

	const gltf = new GLTFLoader()
	gltf.threeOrigin = 4

	const filepath = url //entity.value

	const result = await new Promise(( resolve, reject ) => {

		if( entity.use_cache && MODEL_CACHE[ filepath ] ){ // cached loads
			/*
				--- do not use_cache for models with animations, they will not load them --- 
			*/

			if( MODEL_CACHE[ filepath ] === 'loading' ){ // interim loads
				/*
					begin wait; model is loading..
				*/
				let count = 0
				let waiting = setInterval(() => {
					if( MODEL_CACHE[ filepath] !== 'loading' ){
						clearInterval( waiting )
						entity.MODEL = MODEL_CACHE[ filepath ].clone()
						entity.add_group( is_update )
						entity.GROUP.add( entity.MODEL )	
						entity.process_model()
						resolve('loaded from wait: ' + filepath )	
					}else{
						debug_log('still waiting', filepath)
					}
					if( count > 20 ){
						clearInterval( waiting )
						reject('unable to load from cache: ' + filepath)
					}
					count++
				}, 300)

			}else{ // cached loads

				entity.MODEL = MODEL_CACHE[ filepath ].clone()
				entity.add_group( is_update )
				entity.GROUP.add( entity.MODEL )
				entity.process_model()
				resolve('loaded from cache: ' + filepath)

			}

			/*
				this does not yet  kick into effect until model is loaded
				- MOST - models are still going to load un-cached, if requested at once ( trees )
				fix...
			*/

		}else{ // no-cache loads AND first-time cache loads

			if( entity.use_cache && !MODEL_CACHE[ filepath ]){ // first time cache loads
				MODEL_CACHE[ filepath] = 'loading'
				debug_log('install-cache', 'beginning cache load:', filepath)
			}else{
				debug_log('install-cache', 'beginning single load: ', filepath )
			}

			if( entity.use_mesh_decode ){
				entity.setMeshoptDecoder( MeshoptDecoder )
			}

			gltf.load( filepath, 

				obj => {

					debug_log('install-load', 'gltf loaded: ', obj )

					// handle CREATE / UPDATE of model
					entity.add_group( is_update )

					entity.MODEL = obj.scene

					if( entity.use_cache && MODEL_CACHE[ filepath ] === 'loading' ){
						debug_log('install-cache', 'instantiated cache: ', filepath )
						MODEL_CACHE[ filepath ] = entity.MODEL.clone()
					}

					entity.GROUP.add( entity.MODEL )	
					entity.process_model()

					// debug_log('install-load', identify( entity ), 'anims: ', obj.animations )

					// animations
					if( obj.animations?.length ){

						if( entity.add_animation ){
							entity.add_animation( obj, {} )
						}else{
							debug_log('install-anims', 'entity add-animation undefined', identify( entity ) )
						}

					}

					// done
					resolve('loaded: ' + filepath )

				},
				xhr => {
					if( xhr && xhr.type !== 'progress' ) console.log( `bad xhr: ${ modeltype } ${ slug }: ${ xhr.type }` )
				}, 
				error => {
					// const report = entity.handle || entity.name || entity.type
					// hal('error', 'failed to load model: ' + report, 2000 )
					// console.log( `failed load path: ${ modeltype } ${ slug }` )
					reject('failed model load: ' + filepath )
				}
			)
		} // uncached loads

	}) // result

	debug_log( 'install-load', 'construct model all res', result )

	// post processing:

	entity.bbox = get_bbox( entity.MODEL )

	if( entity.animation ) entity.anim_mixer = entity.animation.mixer // ( for anim loop access )

	// entity.GROUP = group // ?? maybe ??

	// post_process( entity )

} // construct model all



const construct_readymade = async( entity, is_update, group ) => {

	if( is_update ) return console.error('unhandled readymade update', entity )

	let cube

	switch( entity.value ){
	case 'tree':
		return debug_log('readymade', 'tree should be gltf load, not fabrication')

	case 'cube':
		const geo = new BoxBufferGeometry(1,1,1)
		cube = new Mesh( geo, new MeshLambertMaterial({
			color: 'wheat',
		}))
		// cube.scale.set(3,3,3)
		// cube._
		group.add( cube )
		break;

	default:
		return debug_log('readymade', 'unknown type: ', entity.value )
	}

	entity.bbox = get_bbox( cube )

	debug_log('readymade', 'FAIL 1 - construct readymade seems failing here?')

	entity.GROUP = group

}



const map_readymade = url => {
	/*
		for any model readymades. 
		might just be trees
	*/

	switch( url ){
	case 'tree':
		return THREEPRESS.ARCADE.URLS.https + '/' + THREEPRESS.ARCADE.URLS.models + '/trees/tree_pine.glb'
	// case 'cube':
		// break;
	default:
		return debug_log('readymade', 'unknown readymade model mapping', url )
	}

}





// BROKER.subscribe('CONTROLS_SET_STATE', controls_set_state )


export default Install