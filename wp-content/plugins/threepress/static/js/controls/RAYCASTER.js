import {
	Raycaster,
} from '../../inc/three.module.js?v=162'
import CAMERA from '../world/CAMERA.js?v=162'

const raycaster = new Raycaster(); 
raycaster.camera = CAMERA

THREEPRESS.RAYCASTER = raycaster

export default raycaster