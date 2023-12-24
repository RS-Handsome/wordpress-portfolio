import INSTALLS from './INSTALLS.js?v=162'


const colliders = window.COLLIDERS = []


let install, obj
let pruning = setInterval(() => {
	for( let i = colliders.length - 1; i >= 0; i-- ){
		obj = colliders[i]
		install = INSTALLS[ obj.userData?.uuid ]
		if( !install ){
			colliders.splice( i, 1 ) // colliders.indexOf( install )
		}
	}
}, 1000 )



export default colliders

